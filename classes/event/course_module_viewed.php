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
 * course_module_viewed.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_signup\event;

/**
 * Class course_module_viewed.
 */
class course_module_viewed extends \core\event\course_module_viewed {
    /**
     * Method init.
     *
     * @return void Return value.
     */
    protected function init(): void {
        $this->data["objecttable"] = "signup";
        parent::init();
    }

    /**
     * Method get_name.
     *
     * @return string Return value.
     */
    public static function get_name(): string {
        return get_string("eventsignupviewed", "mod_signup");
    }

    /**
     * Returns the mapping used to restore the event object id.
     *
     * @return array Mapping information.
     */
    public static function get_objectid_mapping(): array {
        return ["db" => "signup", "restore" => "signup"];
    }
}
