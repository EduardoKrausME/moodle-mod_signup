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
 * report.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_signup\signup_manager;

require_once(__DIR__ . "/../../config.php");
require_once($CFG->libdir . "/tablelib.php");

$id = required_param("id", PARAM_INT);
$download = optional_param("download", "", PARAM_ALPHA);
$cm = get_coursemodule_from_id("signup", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$signup = $DB->get_record("signup", ["id" => $cm->instance], "*", MUST_EXIST);
require_course_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/signup:viewreport", $context);

$sql = "SELECT sm.id, sm.userid, sm.groupid, sm.status, sm.timecreated,
               sg.name AS groupname,
               u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename, u.email
          FROM {signup_members} sm
          JOIN {signup_groups} sg ON sg.id = sm.groupid
          JOIN {user} u ON u.id = sm.userid
         WHERE sm.signupid = :signupid
      ORDER BY sg.sortorder ASC, sm.status DESC, sm.timecreated ASC, sm.id ASC";
$rows = $DB->get_records_sql($sql, ["signupid" => $signup->id]);

if ($download === "csv") {
    require_once($CFG->libdir . "/csvlib.class.php");
    $csv = new csv_export_writer();
    $csv->set_filename(clean_filename($signup->name . "-signup"));
    $csv->add_data([
        get_string("fullname"),
        get_string("email"),
        get_string("group", "mod_signup"),
        get_string("status", "mod_signup"),
        get_string("date", "mod_signup"),
    ]);
    foreach ($rows as $row) {
        $status = (int)$row->status === signup_manager::STATUS_CONFIRMED ?
            get_string("confirmed", "mod_signup") : get_string("waiting", "mod_signup");
        $csv->add_data([
            fullname($row),
            $row->email,
            $row->groupname,
            $status,
            userdate($row->timecreated),
        ]);
    }
    $csv->download_file();
    exit;
}

$PAGE->set_url(new moodle_url("/mod/signup/report.php", ["id" => $cm->id]));
$PAGE->set_title(get_string("report", "mod_signup"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("report", "mod_signup"));
echo html_writer::link(
    new moodle_url("/mod/signup/report.php", ["id" => $cm->id, "download" => "csv"]),
    get_string("exportcsv", "mod_signup"),
    ["class" => "btn btn-secondary mb-3"]
);

$table = new flexible_table("mod-signup-report-{$cm->id}");
$table->define_columns(["name", "email", "group", "status", "date"]);
$table->define_headers([
    get_string("fullname"),
    get_string("email"),
    get_string("group", "mod_signup"),
    get_string("status", "mod_signup"),
    get_string("date", "mod_signup"),
]);
$table->define_baseurl($PAGE->url);
$table->set_attribute("class", "generaltable generalbox");
$table->setup();

foreach ($rows as $row) {
    $status = (int)$row->status === signup_manager::STATUS_CONFIRMED ?
        get_string("confirmed", "mod_signup") : get_string("waiting", "mod_signup");
    $table->add_data([
        fullname($row),
        s($row->email),
        format_string($row->groupname, true, ["context" => $context]),
        $status,
        userdate($row->timecreated),
    ]);
}
$table->finish_output();
echo $OUTPUT->footer();
