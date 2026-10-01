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
 * action.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_signup\signup_manager;

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$action = required_param("action", PARAM_ALPHA);
$groupid = optional_param("groupid", 0, PARAM_INT);

require_sesskey();
$cm = get_coursemodule_from_id("signup", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$signup = $DB->get_record("signup", ["id" => $cm->instance], "*", MUST_EXIST);
require_course_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/signup:signup", $context);

$returnurl = new moodle_url("/mod/signup/view.php", ["id" => $cm->id]);

if ($action === "join") {
    $status = signup_manager::choose($signup, $groupid, $USER->id);
    $message = $status === signup_manager::STATUS_CONFIRMED ?
        get_string("signupsaved", "mod_signup") : get_string("waitlistsaved", "mod_signup");
    redirect($returnurl, $message, null, notification::NOTIFY_SUCCESS);
}
if ($action === "leave") {
    signup_manager::leave($signup, $USER->id);
    redirect($returnurl, get_string("signupleft", "mod_signup"), null, notification::NOTIFY_SUCCESS);
}

throw new moodle_exception("invalidaction");
