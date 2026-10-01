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
 * index.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$course = get_course($id);
require_course_login($course);
$context = context_course::instance($course->id);

$PAGE->set_url(new moodle_url("/mod/signup/index.php", ["id" => $course->id]));
$PAGE->set_title(get_string("modulenameplural", "mod_signup"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$instances = get_all_instances_in_course("signup", $course);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("modulenameplural", "mod_signup"));
if (!$instances) {
    echo $OUTPUT->notification(get_string("nothingtodisplay"), notification::NOTIFY_INFO);
    echo $OUTPUT->footer();
    exit;
}

$table = new html_table();
$table->head = [get_string("name"), get_string("description")];
foreach ($instances as $instance) {
    $url = new moodle_url("/mod/signup/view.php", ["id" => $instance->coursemodule]);
    $table->data[] = [
        html_writer::link($url, format_string($instance->name)),
        format_text($instance->intro, $instance->introformat),
    ];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
