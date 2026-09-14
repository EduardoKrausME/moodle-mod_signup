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
 * lib.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * signup_supports
 *
 * @param string $feature
 * @return bool|string|null
 */
function signup_supports(string $feature): bool|string|null {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
    }

    if (defined("FEATURE_MOD_PURPOSE") && $feature === constant("FEATURE_MOD_PURPOSE")) {
        return defined("MOD_PURPOSE_OTHER") ? constant("MOD_PURPOSE_OTHER") : null;
    }

    return null;
}

/**
 * signup_add_instance
 *
 * @param $data
 * @param $mform
 * @return int
 */
function signup_add_instance($data, $mform = null): int {
    return \mod_signup\instance_manager::add($data);
}

/**
 * signup_update_instance
 *
 * @param $data
 * @param $mform
 * @return bool
 */
function signup_update_instance($data, $mform = null): bool {
    return \mod_signup\instance_manager::update($data);
}

/**
 * signup_delete_instance
 *
 * @param $id
 * @return bool
 */
function signup_delete_instance($id): bool {
    return \mod_signup\instance_manager::delete((int) $id);
}
