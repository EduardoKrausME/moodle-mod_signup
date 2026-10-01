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
 * team_settings.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_signup\form\team_settings_form;
use mod_signup\signup_manager;

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$groupid = required_param("groupid", PARAM_INT);
$cm = get_coursemodule_from_id("signup", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$signup = $DB->get_record("signup", ["id" => $cm->instance], "*", MUST_EXIST);
$group = $DB->get_record("signup_groups", ["id" => $groupid, "signupid" => $signup->id], "*", MUST_EXIST);

require_course_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/signup:signup", $context);

$member = $DB->get_record("signup_members", [
    "signupid" => $signup->id,
    "groupid" => $group->id,
    "userid" => $USER->id,
    "status" => signup_manager::STATUS_CONFIRMED,
]);
if (!$member || signup_manager::get_group_controller((int)$group->id) !== (int)$USER->id) {
    throw new moodle_exception("teamsettingsnotallowed", "mod_signup");
}

$sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
          FROM {user} u
          JOIN {signup_members} sm ON sm.userid = u.id
         WHERE sm.groupid = :groupid AND sm.status = :status AND u.deleted = 0
      ORDER BY u.lastname, u.firstname";
$members = $DB->get_records_sql($sql, [
    "groupid" => $group->id,
    "status" => signup_manager::STATUS_CONFIRMED,
]);

$form = new team_settings_form(null, [
    "group" => $group,
    "members" => $members,
    "cmid" => $cm->id,
]);
$returnurl = new moodle_url("/mod/signup/view.php", ["id" => $cm->id]);
if ($form->is_cancelled()) {
    redirect($returnurl);
}
if ($data = $form->get_data()) {
    $name = !empty($group->allowrename) ? $data->groupname : $group->name;
    $leaderid = !empty($group->allowleader) ? (int)$data->leaderid : (int)$group->leaderid;
    signup_manager::save_team_settings($group, $USER->id, $name, $leaderid);
    redirect($returnurl, get_string("teamsettingssaved", "mod_signup"), null, notification::NOTIFY_SUCCESS);
}

$PAGE->set_url(new moodle_url("/mod/signup/team_settings.php", ["id" => $cm->id, "groupid" => $group->id]));
$PAGE->set_title(get_string("teamsettings", "mod_signup"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($group->name));
echo $OUTPUT->notification(get_string("firstmembercontrols", "mod_signup"), notification::NOTIFY_INFO);
$form->display();
echo $OUTPUT->footer();
