<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * signup_manager.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_signup;

/**
 * Class signup_manager.
 */
class signup_manager {
    /** @var int */
    public const STATUS_WAITING = 0;

    /** @var int */
    public const STATUS_CONFIRMED = 1;

    /**
     * Method is_open.
     *
     * @param object $signup Parameter signup.
     * @param ?int $now Parameter now.
     * @return bool Return value.
     */
    public static function is_open(object $signup, ?int $now = null): bool {
        $now = $now ?? time();
        if (!empty($signup->timeopen) && $signup->timeopen > $now) {
            return false;
        }
        if (!empty($signup->timeclose) && $signup->timeclose < $now) {
            return false;
        }
        return true;
    }

    /**
     * Method get_closed_reason.
     *
     * @param object $signup Parameter signup.
     * @param ?int $now Parameter now.
     * @return string Return value.
     */
    public static function get_closed_reason(object $signup, ?int $now = null): string {
        $now = $now ?? time();
        if (!empty($signup->timeopen) && $signup->timeopen > $now) {
            return get_string("signupnotopen", "mod_signup");
        }
        if (!empty($signup->timeclose) && $signup->timeclose < $now) {
            return get_string("signupended", "mod_signup");
        }
        return "";
    }

    /**
     * Method choose.
     *
     * @param object $signup Parameter signup.
     * @param int $groupid Parameter groupid.
     * @param int $userid Parameter userid.
     * @return int Return value.
     */
    public static function choose(object $signup, int $groupid, int $userid): int {
        global $DB;

        if (!self::is_open($signup)) {
            throw new \moodle_exception("signupclosed", "mod_signup");
        }

        $lockfactory = \core\lock\lock_config::get_lock_factory("mod_signup_selection");
        $lock = $lockfactory->get_lock("signup:" . $signup->id, 10);
        if (!$lock) {
            throw new \moodle_exception("locktimeout", "mod_signup");
        }

        try {
            $group = $DB->get_record("signup_groups", [
                "id" => $groupid,
                "signupid" => $signup->id,
            ], "*", MUST_EXIST);
            $existing = $DB->get_record("signup_members", ["signupid" => $signup->id, "userid" => $userid]);
            if ($existing && (int) $existing->groupid === $groupid) {
                return (int) $existing->status;
            }
            if ($existing && empty($signup->allowchanges)) {
                throw new \moodle_exception("cannotchange", "mod_signup");
            }

            $confirmed = $DB->count_records("signup_members", [
                "groupid" => $groupid,
                "status" => self::STATUS_CONFIRMED,
            ]);
            $status = $confirmed < (int) $group->capacity ? self::STATUS_CONFIRMED : self::STATUS_WAITING;
            if ($status === self::STATUS_WAITING && empty($signup->waitlist)) {
                throw new \moodle_exception("groupfull", "mod_signup");
            }

            $transaction = $DB->start_delegated_transaction();
            $oldgroupid = 0;
            if ($existing) {
                $oldgroupid = (int) $existing->groupid;
                $DB->delete_records("signup_members", ["id" => $existing->id]);
                self::clear_leader_if_needed($oldgroupid, $userid);
            }

            $now = time();
            $memberid = $DB->insert_record("signup_members", (object) [
                "signupid" => (int) $signup->id,
                "groupid" => $groupid,
                "userid" => $userid,
                "status" => $status,
                "timecreated" => $now,
                "timemodified" => $now,
            ]);
            $transaction->allow_commit();

            if ($oldgroupid && $oldgroupid !== $groupid) {
                self::promote_waiting($oldgroupid);
            }

            self::trigger_created_event($signup, $memberid, $userid, $groupid, $status);
            return $status;
        } finally {
            $lock->release();
        }
    }

    /**
     * Method leave.
     *
     * @param object $signup Parameter signup.
     * @param int $userid Parameter userid.
     * @return bool Return value.
     */
    public static function leave(object $signup, int $userid): bool {
        global $DB;

        if (!self::is_open($signup)) {
            throw new \moodle_exception("signupclosed", "mod_signup");
        }

        $lockfactory = \core\lock\lock_config::get_lock_factory("mod_signup_selection");
        $lock = $lockfactory->get_lock("signup:" . $signup->id, 10);
        if (!$lock) {
            throw new \moodle_exception("locktimeout", "mod_signup");
        }

        try {
            $member = $DB->get_record("signup_members", ["signupid" => $signup->id, "userid" => $userid]);
            if (!$member) {
                return false;
            }
            if (empty($signup->allowchanges)) {
                throw new \moodle_exception("cannotchange", "mod_signup");
            }

            $DB->delete_records("signup_members", ["id" => $member->id]);
            self::clear_leader_if_needed((int) $member->groupid, $userid);
            if ((int) $member->status === self::STATUS_CONFIRMED) {
                self::promote_waiting((int) $member->groupid);
            }
            self::trigger_left_event($signup, $member, $userid);
            return true;
        } finally {
            $lock->release();
        }
    }

    /**
     * Method promote_waiting.
     *
     * @param int $groupid Parameter groupid.
     * @return int Return value.
     */
    public static function promote_waiting(int $groupid): int {
        global $DB;

        $group = $DB->get_record("signup_groups", ["id" => $groupid], "*", MUST_EXIST);
        $confirmed = $DB->count_records("signup_members", [
            "groupid" => $groupid,
            "status" => self::STATUS_CONFIRMED,
        ]);
        $available = max(0, (int) $group->capacity - $confirmed);
        if ($available === 0) {
            return 0;
        }

        $waiting = $DB->get_records(
            "signup_members",
            ["groupid" => $groupid, "status" => self::STATUS_WAITING],
            "timecreated ASC, id ASC",
            "*",
            0,
            $available
        );
        $count = 0;
        foreach ($waiting as $member) {
            $member->status = self::STATUS_CONFIRMED;
            $member->timemodified = time();
            $DB->update_record("signup_members", $member);
            $count++;
        }
        return $count;
    }

    /**
     * Method get_wait_position.
     *
     * @param int $memberid Parameter memberid.
     * @param int $groupid Parameter groupid.
     * @return int Return value.
     */
    public static function get_wait_position(int $memberid, int $groupid): int {
        global $DB;

        $member = $DB->get_record("signup_members", ["id" => $memberid, "groupid" => $groupid], "*", MUST_EXIST);
        if ((int) $member->status !== self::STATUS_WAITING) {
            return 0;
        }
        $sql = "SELECT COUNT(1)
                  FROM {signup_members}
                 WHERE groupid = :groupid
                   AND status = :status
                   AND (timecreated < :timecreated OR (timecreated = :timecreated2 AND id <= :memberid))";
        return $DB->count_records_sql($sql, [
            "groupid" => $groupid,
            "status" => self::STATUS_WAITING,
            "timecreated" => $member->timecreated,
            "timecreated2" => $member->timecreated,
            "memberid" => $member->id,
        ]);
    }

    /**
     * Method get_group_controller.
     *
     * @param int $groupid Parameter groupid.
     * @return int Return value.
     */
    public static function get_group_controller(int $groupid): int {
        global $DB;

        $group = $DB->get_record("signup_groups", ["id" => $groupid], "*", MUST_EXIST);
        if (!empty($group->leaderid)) {
            return (int) $group->leaderid;
        }
        $first = $DB->get_records(
            "signup_members",
            ["groupid" => $groupid, "status" => self::STATUS_CONFIRMED],
            "timecreated ASC, id ASC",
            "id, userid",
            0,
            1
        );
        $first = $first ? reset($first) : false;
        return $first ? (int) $first->userid : 0;
    }

    /**
     * Method save_team_settings.
     *
     * @param object $group Parameter group.
     * @param int $userid Parameter userid.
     * @param string $name Parameter name.
     * @param int $leaderid Parameter leaderid.
     * @return void Return value.
     */
    public static function save_team_settings(object $group, int $userid, string $name, int $leaderid): void {
        global $DB;

        $controller = self::get_group_controller((int) $group->id);
        if ($controller !== $userid) {
            throw new \moodle_exception("teamsettingsnotallowed", "mod_signup");
        }

        if (!empty($group->allowrename)) {
            $name = trim($name);
            if ($name === "") {
                throw new \moodle_exception("groupnameempty", "mod_signup");
            }
            $group->name = \core_text::substr(clean_param($name, PARAM_TEXT), 0, 255);
            $group->customname = 1;
        }

        if (!empty($group->allowleader)) {
            if ($leaderid > 0 && !$DB->record_exists("signup_members", [
                "groupid" => $group->id,
                "userid" => $leaderid,
                "status" => self::STATUS_CONFIRMED,
            ])) {
                throw new \moodle_exception("invalidleader", "mod_signup");
            }
            $group->leaderid = $leaderid;
        }

        $group->timemodified = time();
        $DB->update_record("signup_groups", $group);
    }

    /**
     * Method save_teacher_group.
     *
     * @param object $group Parameter group.
     * @param string $name Parameter name.
     * @param int $capacity Parameter capacity.
     * @param bool $allowrename Parameter allowrename.
     * @param bool $allowleader Parameter allowleader.
     * @param int $leaderid Parameter leaderid.
     * @return void Return value.
     */
    public static function save_teacher_group(object $group, string $name, int $capacity, bool $allowrename,
            bool $allowleader, int $leaderid): void {
        global $DB;

        $lockfactory = \core\lock\lock_config::get_lock_factory("mod_signup_selection");
        $lock = $lockfactory->get_lock("signup:" . $group->signupid, 10);
        if (!$lock) {
            throw new \moodle_exception("locktimeout", "mod_signup");
        }

        try {
            $name = trim($name);
            if ($name === "") {
                throw new \moodle_exception("groupnameempty", "mod_signup");
            }
            $capacity = min(100000, max(1, $capacity));
            if ($leaderid > 0 && !$DB->record_exists("signup_members", [
                "groupid" => $group->id,
                "userid" => $leaderid,
                "status" => self::STATUS_CONFIRMED,
            ])) {
                throw new \moodle_exception("invalidleader", "mod_signup");
            }

            $group->name = \core_text::substr(clean_param($name, PARAM_TEXT), 0, 255);
            $group->capacity = $capacity;
            $group->allowrename = (int) $allowrename;
            $group->allowleader = (int) $allowleader;
            $group->leaderid = $allowleader ? $leaderid : 0;
            $group->timemodified = time();
            $DB->update_record("signup_groups", $group);
            self::promote_waiting((int) $group->id);
        } finally {
            $lock->release();
        }
    }

    /**
     * Method clear_leader_if_needed.
     *
     * @param int $groupid Parameter groupid.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    private static function clear_leader_if_needed(int $groupid, int $userid): void {
        global $DB;

        $group = $DB->get_record("signup_groups", ["id" => $groupid]);
        if ($group && (int) $group->leaderid === $userid) {
            $group->leaderid = 0;
            $group->timemodified = time();
            $DB->update_record("signup_groups", $group);
        }
    }

    /**
     * Method trigger_created_event.
     *
     * @param object $signup Parameter signup.
     * @param int $memberid Parameter memberid.
     * @param int $userid Parameter userid.
     * @param int $groupid Parameter groupid.
     * @param int $status Parameter status.
     * @return void Return value.
     */
    private static function trigger_created_event(object $signup, int $memberid, int $userid, int $groupid, int $status): void {
        $cm = get_coursemodule_from_instance("signup", $signup->id, $signup->course, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $event = \mod_signup\event\signup_created::create([
            "objectid" => $memberid,
            "context" => $context,
            "userid" => $userid,
            "other" => ["groupid" => $groupid, "status" => $status],
        ]);
        $event->add_record_snapshot("signup", $signup);
        $event->trigger();
    }

    /**
     * Method trigger_left_event.
     *
     * @param object $signup Parameter signup.
     * @param object $member Parameter member.
     * @param int $userid Parameter userid.
     * @return void Return value.
     */
    private static function trigger_left_event(object $signup, object $member, int $userid): void {
        $cm = get_coursemodule_from_instance("signup", $signup->id, $signup->course, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        $event = \mod_signup\event\signup_left::create([
            "objectid" => $member->id,
            "context" => $context,
            "userid" => $userid,
            "other" => ["groupid" => $member->groupid, "status" => $member->status],
        ]);
        $event->add_record_snapshot("signup", $signup);
        $event->trigger();
    }
}
