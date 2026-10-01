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
 * mod_form.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("{$CFG->dirroot}/course/moodleform_mod.php");

/**
 * Class mod_signup_mod_form.
 */
class mod_signup_mod_form extends moodleform_mod {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        global $PAGE;

        $mform = $this->_form;
        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("name"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");
        $this->standard_intro_elements();

        $mform->addElement("html", html_writer::tag("h3", get_string("groupcreation", "mod_signup")));
        $isnew = empty($this->_cm) || empty($this->_cm->instance);
        if ($isnew) {
            $mform->addElement("text", "groupcount", get_string("groupcount", "mod_signup"), ["size" => 8]);
            $mform->setType("groupcount", PARAM_INT);
            $mform->setDefault("groupcount", 5);
            $mform->addRule("groupcount", null, "required", null, "client");
            $mform->addRule("groupcount", null, "numeric", null, "client");
            $mform->addHelpButton("groupcount", "groupcount", "mod_signup");

            $mform->addElement("text", "defaultcapacity", get_string("defaultcapacity", "mod_signup"), ["size" => 8]);
            $mform->setType("defaultcapacity", PARAM_INT);
            $mform->setDefault("defaultcapacity", 10);
            $mform->addRule("defaultcapacity", null, "required", null, "client");
            $mform->addRule("defaultcapacity", null, "numeric", null, "client");
            $mform->addHelpButton("defaultcapacity", "defaultcapacity", "mod_signup");

            $mform->addElement("advcheckbox", "defaultallowrename", get_string("defaultallowrename", "mod_signup"));
            $mform->addHelpButton("defaultallowrename", "defaultallowrename", "mod_signup");
            $mform->addElement("advcheckbox", "defaultallowleader", get_string("defaultallowleader", "mod_signup"));
            $mform->addHelpButton("defaultallowleader", "defaultallowleader", "mod_signup");

            $mform->addElement("button", "generatepreview", get_string("generategroups", "mod_signup"), [
                "id" => "id_signup_generatepreview",
                "class" => "btn btn-secondary",
            ]);
            $mform->addElement("html", '<div id="mod-signup-group-preview" class="mt-3"></div>');
            $mform->addElement("static", "groupsgeneratedonsave", "", get_string("groupsgeneratedonsave", "mod_signup"));
            $PAGE->requires->js_call_amd("mod_signup/groupgenerator", "init", [[
                "button" => "#id_signup_generatepreview",
                "count" => "#id_groupcount",
                "capacity" => "#id_defaultcapacity",
                "target" => "#mod-signup-group-preview",
                "title" => get_string("previewtitle", "mod_signup"),
                "groupPrefix" => get_string("group", "mod_signup"),
                "seatsSuffix" => get_string("seats", "mod_signup", "__COUNT__"),
            ]]);
        } else {
            $mform->addElement("static", "groupsalreadycreated", "", get_string("groupsalreadycreated", "mod_signup"));
        }

        $mform->addElement("html", html_writer::tag("h3", get_string("options")));
        $mform->addElement("advcheckbox", "waitlist", get_string("waitlist", "mod_signup"));
        $mform->setDefault("waitlist", 1);
        $mform->addHelpButton("waitlist", "waitlist", "mod_signup");
        $mform->addElement("advcheckbox", "allowchanges", get_string("allowchanges", "mod_signup"));
        $mform->setDefault("allowchanges", 1);

        $mform->addElement("html", html_writer::tag("h3", get_string("availability", "mod_signup")));
        $mform->addElement("date_time_selector", "timeopen", get_string("timeopen", "mod_signup"), ["optional" => true]);
        $mform->addElement("date_time_selector", "timeclose", get_string("timeclose", "mod_signup"), ["optional" => true]);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
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
        if (isset($data["groupcount"]) && ((int)$data["groupcount"] < 1 || (int)$data["groupcount"] > 200)) {
            $errors["groupcount"] = get_string("error");
        }
        if (isset($data["defaultcapacity"]) && ((int)$data["defaultcapacity"] < 1 || (int)$data["defaultcapacity"] > 100000)) {
            $errors["defaultcapacity"] = get_string("error");
        }
        if (!empty($data["timeopen"]) && !empty($data["timeclose"]) && $data["timeclose"] <= $data["timeopen"]) {
            $errors["timeclose"] = get_string("invaliddata");
        }
        return $errors;
    }
}
