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
 * view.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("signup", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$signup = $DB->get_record("signup", ["id" => $cm->instance], "*", MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/signup:view", $context);

$PAGE->set_url(new moodle_url("/mod/signup/view.php", ["id" => $cm->id]));
$PAGE->set_title(format_string($signup->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$event = \mod_signup\event\course_module_viewed::create([
    "objectid" => $signup->id,
    "context" => $context,
]);
$event->add_record_snapshot("course", $course);
$event->add_record_snapshot("signup", $signup);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$data = \mod_signup\view_builder::build($signup, cm_info::create($cm), $context, $USER->id);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($signup->name));
if (trim((string) $signup->intro) !== "") {
    echo $OUTPUT->box(format_module_intro("signup", $signup, $cm->id), "generalbox mod_introbox", "signupintro");
}
echo $OUTPUT->render_from_template("mod_signup/group_cards", $data);
echo $OUTPUT->footer();
