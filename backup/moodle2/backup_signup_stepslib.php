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
 * backup_signup_stepslib.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_signup_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");
        $signup = new backup_nested_element("signup", ["id"], [
            "name", "intro", "introformat", "waitlist", "allowchanges", "defaultallowrename",
            "defaultallowleader", "timeopen", "timeclose", "timemodified",
        ]);
        $groups = new backup_nested_element("groups");
        $group = new backup_nested_element("group", ["id"], [
            "sortorder", "name", "capacity", "allowrename", "allowleader", "customname", "leaderid",
            "timecreated", "timemodified",
        ]);
        $members = new backup_nested_element("members");
        $member = new backup_nested_element("member", ["id"], [
            "userid", "status", "timecreated", "timemodified",
        ]);

        $signup->add_child($groups);
        $groups->add_child($group);
        $group->add_child($members);
        $members->add_child($member);

        $signup->set_source_table("signup", ["id" => backup::VAR_ACTIVITYID]);
        $group->set_source_table("signup_groups", ["signupid" => backup::VAR_PARENTID], "sortorder ASC, id ASC");
        if ($userinfo) {
            $member->set_source_table("signup_members", ["groupid" => backup::VAR_PARENTID], "timecreated ASC, id ASC");
        }

        $group->annotate_ids("user", "leaderid");
        $member->annotate_ids("user", "userid");
        $signup->annotate_files("mod_signup", "intro", null);

        return $this->prepare_activity_structure($signup);
    }
}
