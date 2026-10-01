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
 * instance_manager.php
 *
 * @package   mod_signup
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_signup;

/**
 * Class instance_manager.
 */
class instance_manager {
    /**
     * Method add.
     *
     * @param object $data Parameter data.
     * @return int Return value.
     */
    public static function add(object $data): int {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $record = self::build_record($data);
        $record->timemodified = time();
        $id = $DB->insert_record("signup", $record);

        group_generator::create(
            $id,
            (int)($data->groupcount ?? 1),
            (int)($data->defaultcapacity ?? 1),
            !empty($data->defaultallowrename),
            !empty($data->defaultallowleader)
        );
        $transaction->allow_commit();

        return $id;
    }

    /**
     * Method update.
     *
     * @param object $data Parameter data.
     * @return bool Return value.
     */
    public static function update(object $data): bool {
        global $DB;

        $existing = $DB->get_record("signup", ["id" => (int)$data->instance], "*", MUST_EXIST);
        $record = self::build_record($data);
        if (!property_exists($data, "defaultallowrename")) {
            $record->defaultallowrename = (int)$existing->defaultallowrename;
        }
        if (!property_exists($data, "defaultallowleader")) {
            $record->defaultallowleader = (int)$existing->defaultallowleader;
        }
        $record->id = (int)$data->instance;
        $record->timemodified = time();
        return $DB->update_record("signup", $record);
    }

    /**
     * Method delete.
     *
     * @param int $id Parameter id.
     * @return bool Return value.
     */
    public static function delete(int $id): bool {
        global $DB;

        if (!$DB->record_exists("signup", ["id" => $id])) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records("signup_members", ["signupid" => $id]);
        $DB->delete_records("signup_groups", ["signupid" => $id]);
        $DB->delete_records("signup", ["id" => $id]);
        $transaction->allow_commit();
        return true;
    }

    /**
     * Method build_record.
     *
     * @param object $data Parameter data.
     * @return object Return value.
     */
    private static function build_record(object $data): object {
        return (object)[
            "course" => (int)$data->course,
            "name" => $data->name,
            "intro" => $data->intro ?? "",
            "introformat" => (int)($data->introformat ?? FORMAT_HTML),
            "waitlist" => !empty($data->waitlist) ? 1 : 0,
            "allowchanges" => !empty($data->allowchanges) ? 1 : 0,
            "defaultallowrename" => !empty($data->defaultallowrename) ? 1 : 0,
            "defaultallowleader" => !empty($data->defaultallowleader) ? 1 : 0,
            "timeopen" => !empty($data->timeopen) ? (int)$data->timeopen : 0,
            "timeclose" => !empty($data->timeclose) ? (int)$data->timeclose : 0,
        ];
    }
}
