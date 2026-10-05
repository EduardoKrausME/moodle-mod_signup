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

$userfieldsapi = \core_user\fields::for_identity($context)->with_name();
$userfieldssql = $userfieldsapi->get_sql("u", true, "", "", false);
$identityfields = $userfieldsapi->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]);

$sql = "SELECT sm.id, sm.userid, sm.groupid, sm.status, sm.timecreated,
               sg.name AS groupname, {$userfieldssql->selects}
          FROM {signup_members} sm
          JOIN {signup_groups} sg ON sg.id = sm.groupid
          JOIN {user} u ON u.id = sm.userid
               {$userfieldssql->joins}
         WHERE sm.signupid = :signupid
      ORDER BY sg.sortorder ASC, sm.status DESC, sm.timecreated ASC, sm.id ASC";
$params = array_merge(["signupid" => $signup->id], $userfieldssql->params);
$rows = $DB->get_records_sql($sql, $params);

if ($download === "csv") {
    require_once($CFG->libdir . "/csvlib.class.php");
    $csv = new csv_export_writer();
    $csv->set_filename(clean_filename($signup->name . "-signup"));

    $headers = [get_string("fullname")];
    foreach ($identityfields as $field) {
        $headers[] = \core_user\fields::get_display_name($field);
    }
    $headers[] = get_string("group", "mod_signup");
    $headers[] = get_string("status", "mod_signup");
    $headers[] = get_string("date", "mod_signup");
    $csv->add_data($headers);

    foreach ($rows as $row) {
        $status = (int)$row->status === signup_manager::STATUS_CONFIRMED ?
            get_string("confirmed", "mod_signup") : get_string("waiting", "mod_signup");

        $data = [fullname($row)];
        foreach ($identityfields as $field) {
            $data[] = (string)($row->{$field} ?? "");
        }
        $data[] = $row->groupname;
        $data[] = $status;
        $data[] = userdate($row->timecreated);
        $csv->add_data($data);
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

$columns = ["name"];
$headers = [get_string("fullname")];
foreach ($identityfields as $index => $field) {
    $columns[] = "identity{$index}";
    $headers[] = \core_user\fields::get_display_name($field);
}
$columns = array_merge($columns, ["group", "status", "date"]);
$headers = array_merge($headers, [
    get_string("group", "mod_signup"),
    get_string("status", "mod_signup"),
    get_string("date", "mod_signup"),
]);

$table = new flexible_table("mod-signup-report-{$cm->id}");
$table->define_columns($columns);
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);
$table->set_attribute("class", "generaltable generalbox");
$table->setup();

foreach ($rows as $row) {
    $status = (int)$row->status === signup_manager::STATUS_CONFIRMED ?
        get_string("confirmed", "mod_signup") : get_string("waiting", "mod_signup");

    $data = [fullname($row)];
    foreach ($identityfields as $field) {
        $data[] = s((string)($row->{$field} ?? ""));
    }
    $data[] = format_string($row->groupname, true, ["context" => $context]);
    $data[] = $status;
    $data[] = userdate($row->timecreated);
    $table->add_data($data);
}
$table->finish_output();
echo $OUTPUT->footer();
