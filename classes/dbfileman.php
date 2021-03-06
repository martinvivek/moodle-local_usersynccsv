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
    /**
     * To be imported state
     */
    const TOIMPORT = 0;

    /**
     * The file in this state is being processed
     */
    const WORKING = 1;

    /**
     * The file has been archived
     */
    const ARCHIVED = 2;

    /**
     * The file has been discarded
     */
    const DISCARDED = 3;

    /**
     * The file has been deleted from file system
     */
    const DELETEDFS = 4;

    /**
     * @var int id of the file we are currently working on. Used for logging
     */
    public static $currentfileid = 0;
    /**
     * Register file in db, create if not exists, update if exists
     * @param string $filename file absolute name
     * @param int $filestatus file status: 0:to import, 1:work, 2:archived, 3: discarded, 4:deleted from fs
     * @param string $archivesubdir sub dir where the file is stored
     */
    public static function registerfile($filename, $filestatus, $archivesubdir='') {
        global $DB;
        $file = $DB->get_record('local_usersynccsv_file', array('name' => $filename));
        $now = time();
        if ($file) {
            $file->status = $filestatus;
            $file->timemodified = $now;
            $file->archivesubdir = $archivesubdir;
            $DB->update_record('local_usersynccsv_file', $file);
        } else {
            $file = new stdClass();
            $file->name = $filename;
            $file->status = $filestatus;
            $file->timecreated = $now;
            $file->timemodified = $now;
            $file->archivesubdir = $archivesubdir;
            $newid = $DB->insert_record('local_usersynccsv_file', $file, true);
            $file->id = $newid;
        }
        self::$currentfileid = $file->id;
        switch ($filestatus) {
            case self::TOIMPORT:
                $filestatusstr = 'IMPORT';
                break;
            case self::WORKING:
                $filestatusstr = 'WORKING';
                break;
            case self::ARCHIVED:
                $filestatusstr = 'ARCHIVED';
                break;
            case self::DISCARDED:
                $filestatusstr = 'DISCARDED';
                break;
            case self::DELETEDFS:
                $filestatusstr = 'DELETEDFS';
                break;
            default:
                $filestatusstr = 'UNKNOWN';
                break;
        }
        local_usersynccsv_logger::logdofile(get_string('filemovedlog', 'local_usersynccsv', $filestatusstr));
    }

    /**
     * Retrieve the file object from DB
     * @param int $fileid
     * @return mixed
     */
    public static function getfilefromid($fileid) {
        global $DB;
        return $DB->get_record('local_usersynccsv_file', array('id' => $fileid));
    }
    /**
     * Clean up old records in file table
     */
    public static function cleanupdbfiletable() {
        global $DB;
        $config = get_config('local_usersynccsv');
        $maxday = $config->dbfiletablemaxday;
        $mindaytime = time();
        $mindaytime -= $maxday * 86400;
        $DB->delete_records_select('local_usersynccsv_file', 'timecreated < ? ', array($mindaytime));
    }
}