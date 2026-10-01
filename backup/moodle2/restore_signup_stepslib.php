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
 * restore_signup_stepslib.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_signup_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return array Return value.
     */
    protected function define_structure(): array {
        $paths = [
            new restore_path_element("signup", "/activity/signup"),
            new restore_path_element("signup_group", "/activity/signup/groups/group"),
        ];
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("signup_member", "/activity/signup/groups/group/members/member");
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Method process_signup.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_signup($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $newitemid = $DB->insert_record("signup", $data);
        $this->apply_activity_instance($newitemid);
        $this->set_mapping("signup", $oldid, $newitemid, true);
    }

    /**
     * Method process_signup_group.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_signup_group($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->signupid = $this->get_new_parentid("signup");
        $data->leaderid = empty($data->leaderid) ? 0 : $this->get_mappingid("user", $data->leaderid, 0);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $newitemid = $DB->insert_record("signup_groups", $data);
        $this->set_mapping("signup_group", $oldid, $newitemid);
    }

    /**
     * Method process_signup_member.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_signup_member($data): void {
        global $DB;

        $data = (object)$data;
        $data->signupid = $this->get_new_parentid("signup");
        $data->groupid = $this->get_new_parentid("signup_group");
        $data->userid = $this->get_mappingid("user", $data->userid, 0);
        if (!$data->userid) {
            return;
        }
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $DB->insert_record("signup_members", $data);
    }

    /**
     * Method after_execute.
     *
     * @return void Return value.
     */
    protected function after_execute(): void {
        $this->add_related_files("mod_signup", "intro", null);
    }
}
