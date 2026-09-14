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
 * team_settings_form.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_signup\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/formslib.php");

/**
 * Class team_settings_form.
 */
class team_settings_form extends \moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;
        $group = $this->_customdata["group"];
        $members = $this->_customdata["members"];

        if (!empty($group->allowrename)) {
            $mform->addElement("text", "groupname", get_string("customgroupname", "mod_signup"), ["size" => 50]);
            $mform->setType("groupname", PARAM_TEXT);
            $mform->setDefault("groupname", $group->name);
            $mform->addRule("groupname", null, "required", null, "client");
        }

        if (!empty($group->allowleader)) {
            $options = [0 => get_string("noleader", "mod_signup")];
            foreach ($members as $member) {
                $options[$member->id] = fullname($member);
            }
            $mform->addElement("select", "leaderid", get_string("chooseleader", "mod_signup"), $options);
            $mform->setDefault("leaderid", (int) $group->leaderid);
        }

        $mform->addElement("hidden", "id", $this->_customdata["cmid"]);
        $mform->setType("id", PARAM_INT);
        $mform->addElement("hidden", "groupid", $group->id);
        $mform->setType("groupid", PARAM_INT);
        $this->add_action_buttons(true, get_string("savechanges"));
    }
}
