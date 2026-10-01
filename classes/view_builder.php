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
 * view_builder.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_signup;

use cm_info;
use context_module;
use moodle_url;

/**
 * Class view_builder.
 */
class view_builder {
    /**
     * Method build.
     *
     * @param object $signup Parameter signup.
     * @param cm_info $cm Parameter cm.
     * @param context_module $context Parameter context.
     * @param int $userid Parameter userid.
     * @return array Return value.
     */
    public static function build(object $signup, cm_info $cm, context_module $context, int $userid): array {
        global $DB;

        $groups = $DB->get_records("signup_groups", ["signupid" => $signup->id], "sortorder ASC, id ASC");
        $membership = $DB->get_record("signup_members", ["signupid" => $signup->id, "userid" => $userid]);
        $open = signup_manager::is_open($signup);
        $cansignup = has_capability("mod/signup:signup", $context);
        $canmanage = has_capability("mod/signup:manage", $context);
        $canreport = has_capability("mod/signup:viewreport", $context);
        $canchange = !$membership || !empty($signup->allowchanges);
        $items = [];

        foreach ($groups as $group) {
            $confirmed = $DB->count_records("signup_members", [
                "groupid" => $group->id,
                "status" => signup_manager::STATUS_CONFIRMED,
            ]);
            $waiting = $DB->count_records("signup_members", [
                "groupid" => $group->id,
                "status" => signup_manager::STATUS_WAITING,
            ]);
            $remaining = max(0, (int)$group->capacity - $confirmed);
            $full = $remaining === 0;
            $iscurrent = $membership && (int)$membership->groupid === (int)$group->id;
            $currentwaiting = $iscurrent && (int)$membership->status === signup_manager::STATUS_WAITING;
            $waitposition = $currentwaiting
                ? signup_manager::get_wait_position((int)$membership->id, (int)$group->id)
                : 0;
            $leadername = "";
            if (!empty($group->leaderid)) {
                $leader = $DB->get_record("user", ["id" => $group->leaderid],
                    "id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename");
                if ($leader) {
                    $leadername = fullname($leader);
                }
            }

            $controller = signup_manager::get_group_controller((int)$group->id);
            $canconfig = $iscurrent && (int)$membership->status === signup_manager::STATUS_CONFIRMED &&
                $controller === $userid && (!empty($group->allowrename) || !empty($group->allowleader));
            $joinlabel = $full ? get_string("joinwaitlist", "mod_signup") : get_string("signupbutton", "mod_signup");
            $canjoin = $cansignup && $open && $canchange && !$iscurrent && (!$full || !empty($signup->waitlist));

            $items[] = [
                "id" => (int)$group->id,
                "name" => format_string($group->name, true, ["context" => $context]),
                "capacity" => (int)$group->capacity,
                "confirmed" => $confirmed,
                "waiting" => $waiting,
                "remaining" => $remaining,
                "remaininglabel" => get_string("seatsremaining", "mod_signup", $remaining),
                "full" => $full,
                "notfull" => !$full,
                "haswaiting" => $waiting > 0,
                "allowleader" => !empty($group->allowleader),
                "hasleader" => $leadername !== "",
                "leadername" => $leadername,
                "iscurrent" => $iscurrent,
                "currentwaiting" => $currentwaiting,
                "currentconfirmed" => $iscurrent && !$currentwaiting,
                "waitposition" => $waitposition,
                "waitpositionlabel" => $currentwaiting
                    ? get_string("waitposition", "mod_signup", $waitposition)
                    : "",
                "canjoin" => $canjoin,
                "joinlabel" => $joinlabel,
                "canconfig" => $canconfig,
                "settingsurl" => new moodle_url("/mod/signup/team_settings.php", ["id" => $cm->id, "groupid" => $group->id]),
            ];
        }

        return [
            "groups" => $items,
            "hasgroups" => !empty($items),
            "open" => $open,
            "closedreason" => $open ? "" : signup_manager::get_closed_reason($signup),
            "hasmembership" => $membership,
            "canleave" => ($membership && $open && !empty($signup->allowchanges) && $cansignup),
            "actionurl" => new moodle_url("/mod/signup/action.php"),
            "sesskey" => sesskey(),
            "cmid" => (int)$cm->id,
            "canmanage" => $canmanage,
            "canreport" => $canreport,
            "hasteacherbuttons" => $canmanage || $canreport,
            "manageurl" => new moodle_url("/mod/signup/manage.php", ["id" => $cm->id]),
            "reporturl" => new moodle_url("/mod/signup/report.php", ["id" => $cm->id]),
        ];
    }
}
