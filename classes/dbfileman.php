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
 *
 * User Sync CSV.
 *
 * @package   local_usersynccsv
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * File and directory manager
 *
 * @package    local_usersynccsv
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_usersynccsv_dbfileman
{
    const TOIMPORT = 0;

    const WORKING = 1;

    const ARCHIVED = 2;

    const DISCARDED = 3;

    const DELETEDFS = 4;

    /**
     * Register file in db, create if not exists, update if exists
     * @param string $filename file absolute name
     * @param int $filestatus file status: 0:to import, 1:work, 2:archived, 3: discarded, 4:deleted from fs
     */
    public static function registerfile($filename, $filestatus) {
        global $DB;
        $file = $DB->get_record('local_usersynccsv_file',array('name' => $filename));
        $now = time();
        if ($file) {
            $file->status = $filestatus;
            $file->timeupdated = $now;
            $DB->update_record('local_usersynccsv_file', $file);
        } else {
            $file = new stdClass();
            $file->name = $filename;
            $file->status = $filestatus;
            $file->timecreated = $now;
            $file->timeupdated = $now;
            $DB->insert_record('local_usersynccsv_file', $file);
        }
    }

    /**
     * Clean up old records in file table
     */
    public static function cleanupdbfiletable() {
        global $DB;
        $config = get_config('local_usersynccsv');
        $maxday = $config->dbfiletablemaxday;
        $mindaytime = time();
        $mindaytime -= $maxday*86400;
        $DB->delete_records('local_usersynccsv_file', array('timecreated' < $mindaytime));
    }
}