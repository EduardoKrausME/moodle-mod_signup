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
 * provider.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_signup\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("signup_members", [
            "userid" => "privacy:metadata:signup_members:userid",
            "groupid" => "privacy:metadata:signup_members:groupid",
            "status" => "privacy:metadata:signup_members:status",
            "timecreated" => "privacy:metadata:signup_members:timecreated",
        ], "privacy:metadata:signup_members");
        $collection->add_database_table("signup_groups", [
            "leaderid" => "privacy:metadata:signup_groups:leaderid",
        ], "privacy:metadata:signup_groups");
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {signup} s ON s.id = cm.instance
             LEFT JOIN {signup_members} sm ON sm.signupid = s.id
             LEFT JOIN {signup_groups} sg ON sg.signupid = s.id
                 WHERE ctx.contextlevel = :contextlevel
                   AND (sm.userid = :userid OR sg.leaderid = :leaderid)";
        $contextlist->add_from_sql($sql, [
            "modname" => "signup",
            "contextlevel" => CONTEXT_MODULE,
            "userid" => $userid,
            "leaderid" => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id("signup", $context->instanceid);
            if (!$cm) {
                continue;
            }
            $signup = $DB->get_record("signup", ["id" => $cm->instance]);
            if (!$signup) {
                continue;
            }
            $member = $DB->get_record("signup_members", ["signupid" => $signup->id, "userid" => $userid]);
            if ($member) {
                $group = $DB->get_record("signup_groups", ["id" => $member->groupid]);
                writer::with_context($context)->export_data([], (object) [
                    "activity" => format_string($signup->name, true, ["context" => $context]),
                    "group" => $group ? format_string($group->name, true, ["context" => $context]) : "",
                    "status" => (int) $member->status === \mod_signup\signup_manager::STATUS_CONFIRMED ?
                        get_string("confirmed", "mod_signup") : get_string("waiting", "mod_signup"),
                    "timecreated" => transform::datetime($member->timecreated),
                ]);
            }
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param \context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id("signup", $context->instanceid);
        if (!$cm) {
            return;
        }
        $DB->delete_records("signup_members", ["signupid" => $cm->instance]);
        $DB->set_field("signup_groups", "leaderid", 0, ["signupid" => $cm->instance]);
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id("signup", $context->instanceid);
            if (!$cm) {
                continue;
            }
            $DB->delete_records("signup_members", ["signupid" => $cm->instance, "userid" => $userid]);
            $DB->set_field("signup_groups", "leaderid", 0, ["signupid" => $cm->instance, "leaderid" => $userid]);
        }
    }

    /**
     * Method get_users_in_context.
     *
     * @param userlist $userlist Parameter userlist.
     * @return void Return value.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $sql = "SELECT sm.userid
                  FROM {signup_members} sm
                  JOIN {course_modules} cm ON cm.instance = sm.signupid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                 WHERE cm.id = :cmid
                 UNION
                SELECT sg.leaderid AS userid
                  FROM {signup_groups} sg
                  JOIN {course_modules} cm2 ON cm2.instance = sg.signupid
                  JOIN {modules} m2 ON m2.id = cm2.module AND m2.name = :modname2
                 WHERE cm2.id = :cmid2 AND sg.leaderid > 0";
        $userlist->add_from_sql("userid", $sql, [
            "modname" => "signup",
            "cmid" => $context->instanceid,
            "modname2" => "signup",
            "cmid2" => $context->instanceid,
        ]);
    }

    /**
     * Method delete_data_for_users.
     *
     * @param approved_userlist $userlist Parameter userlist.
     * @return void Return value.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id("signup", $context->instanceid);
        if (!$cm) {
            return;
        }
        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params["signupid"] = $cm->instance;
        $DB->delete_records_select("signup_members", "signupid = :signupid AND userid {$insql}", $params);
        $DB->execute("UPDATE {signup_groups} SET leaderid = 0 WHERE signupid = :signupid AND leaderid {$insql}", $params);
    }
}
