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
 * group_generator.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_signup;

/**
 * Class group_generator.
 */
class group_generator {
    /**
     * Method create.
     *
     * @param int $signupid Parameter signupid.
     * @param int $count Parameter count.
     * @param int $capacity Parameter capacity.
     * @param bool $allowrename Parameter allowrename.
     * @param bool $allowleader Parameter allowleader.
     * @return void Return value.
     */
    public static function create(int $signupid, int $count, int $capacity, bool $allowrename, bool $allowleader): void {
        global $DB;

        $count = min(200, max(1, $count));
        $capacity = min(100000, max(1, $capacity));
        $now = time();

        for ($index = 0; $index < $count; $index++) {
            $suffix = self::alpha_label($index);
            $record = (object)[
                "signupid" => $signupid,
                "sortorder" => $index + 1,
                "name" => get_string("groupdefaultname", "mod_signup", $suffix),
                "capacity" => $capacity,
                "allowrename" => (int)$allowrename,
                "allowleader" => (int)$allowleader,
                "customname" => 0,
                "leaderid" => 0,
                "timecreated" => $now,
                "timemodified" => $now,
            ];
            $DB->insert_record("signup_groups", $record);
        }
    }

    /**
     * Method alpha_label.
     *
     * @param int $index Parameter index.
     * @return string Return value.
     */
    public static function alpha_label(int $index): string {
        $label = "";
        $number = $index + 1;
        while ($number > 0) {
            $number--;
            $label = chr(65 + ($number % 26)) . $label;
            $number = intdiv($number, 26);
        }
        return $label;
    }
}
