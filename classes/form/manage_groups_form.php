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
 * manage_groups_form.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_signup\form;

use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/formslib.php");

/**
 * Class manage_groups_form.
 */
class manage_groups_form extends moodleform {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;
        $groups = $this->_customdata["groups"];
        $membersbygroup = $this->_customdata["membersbygroup"];

        foreach ($groups as $group) {
            $mform->addElement("header", "groupheader_{$group->id}", format_string($group->name));
            $mform->addElement("text", "groupname_{$group->id}", get_string("customgroupname", "mod_signup"), ["size" => 50]);
            $mform->setType("groupname_{$group->id}", PARAM_TEXT);
            $mform->setDefault("groupname_{$group->id}", $group->name);
            $mform->addRule("groupname_{$group->id}", null, "required", null, "client");

            $mform->addElement("text", "capacity_{$group->id}", get_string("capacity", "mod_signup"), ["size" => 8]);
            $mform->setType("capacity_{$group->id}", PARAM_INT);
            $mform->setDefault("capacity_{$group->id}", (int)$group->capacity);
            $mform->addRule("capacity_{$group->id}", null, "required", null, "client");
            $mform->addRule("capacity_{$group->id}", null, "numeric", null, "client");

            $mform->addElement("advcheckbox", "allowrename_{$group->id}", get_string("allowrename", "mod_signup"));
            $mform->setDefault("allowrename_{$group->id}", (int)$group->allowrename);
            $mform->addElement("advcheckbox", "allowleader_{$group->id}", get_string("allowleader", "mod_signup"));
            $mform->setDefault("allowleader_{$group->id}", (int)$group->allowleader);

            $options = [0 => get_string("noleader", "mod_signup")];
            foreach ($membersbygroup[$group->id] ?? [] as $member) {
                $options[$member->id] = fullname($member);
            }
            $mform->addElement("select", "leaderid_{$group->id}", get_string("leader", "mod_signup"), $options);
            $mform->setDefault("leaderid_{$group->id}", (int)$group->leaderid);
            $mform->hideIf("leaderid_{$group->id}", "allowleader_{$group->id}", "notchecked");
        }

        $mform->addElement("hidden", "id", $this->_customdata["cmid"]);
        $mform->setType("id", PARAM_INT);
        $this->add_action_buttons(true, get_string("savegroups", "mod_signup"));
    }

    /**
     * Method validation.
     *
     * @param mixed $data Parameter data.
     * @param mixed $files Parameter files.
     * @return array Return value.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        foreach ($this->_customdata["groups"] as $group) {
            $capacity = (int)($data["capacity_{$group->id}"] ?? 0);
            if ($capacity < 1 || $capacity > 100000) {
                $errors["capacity_{$group->id}"] = get_string("invaliddata");
            }
        }
        return $errors;
    }
}
