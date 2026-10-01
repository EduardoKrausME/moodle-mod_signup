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
 * manage.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_signup\form\manage_groups_form;
use mod_signup\signup_manager;

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("signup", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$signup = $DB->get_record("signup", ["id" => $cm->instance], "*", MUST_EXIST);
require_course_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/signup:manage", $context);

$groups = $DB->get_records("signup_groups", ["signupid" => $signup->id], "sortorder ASC, id ASC");
$membersbygroup = [];
foreach ($groups as $group) {
    $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename
              FROM {user} u
              JOIN {signup_members} sm ON sm.userid = u.id
             WHERE sm.groupid = :groupid AND sm.status = :status AND u.deleted = 0
          ORDER BY u.lastname, u.firstname";
    $membersbygroup[$group->id] = $DB->get_records_sql($sql, [
        "groupid" => $group->id,
        "status" => signup_manager::STATUS_CONFIRMED,
    ]);
}

$form = new manage_groups_form(null, [
    "groups" => $groups,
    "membersbygroup" => $membersbygroup,
    "cmid" => $cm->id,
]);
$returnurl = new moodle_url("/mod/signup/view.php", ["id" => $cm->id]);
if ($form->is_cancelled()) {
    redirect($returnurl);
}
if ($data = $form->get_data()) {
    foreach ($groups as $group) {
        signup_manager::save_teacher_group(
            $group,
            $data->{"groupname_{$group->id}"},
            (int)$data->{"capacity_{$group->id}"},
            !empty($data->{"allowrename_{$group->id}"}),
            !empty($data->{"allowleader_{$group->id}"}),
            (int)($data->{"leaderid_{$group->id}"} ?? 0)
        );
    }
    redirect($returnurl, get_string("groupssaved", "mod_signup"), null, notification::NOTIFY_SUCCESS);
}

$PAGE->set_url(new moodle_url("/mod/signup/manage.php", ["id" => $cm->id]));
$PAGE->set_title(get_string("managegroups", "mod_signup"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("managegroups", "mod_signup"));
$form->display();
echo $OUTPUT->footer();
