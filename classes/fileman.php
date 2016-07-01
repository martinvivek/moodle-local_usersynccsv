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

class local_usersynccsv_fileman
{
    private static $workdir = 'work';
    private static $archivedir = 'archive';

    private $importdir;
    private $isexport;
    private $exportdir;
    private $fullworkdir;
    private $fullarchivedir;
    public function __construct() {
        $config = get_config('local_usersynccsv');
        $this->importdir = $config->importdir;
        $this->isexport = $config->isexport;
        $this->exportdir = $config->exportdir;
        $this->fullworkdir = $this->importdir . DIRECTORY_SEPARATOR . self::$workdir;
        $this->fullarchivedir = $this->importdir . DIRECTORY_SEPARATOR . self::$archivedir;
        $this->checkconfigdirs();
    }
    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function profile_field_dynamicmenu() {
        self::__construct();
    }

    /**
     * Check import dir structure to see if every required subfolder exists
     */
    private function checkconfigdirs() {
        if (!file_exists($this->importdir)) {
            $this->handlefatalerror('importdirmissing', 'local_usersynccsv', $this->importdir);
        }
        if (!is_writable($this->importdir)) {
            $this->handlefatalerror('importdirnotwritable', 'local_usersynccsv', $this->importdir);
        }
        if ($this->isexport && !file_exists($this->exportdir)) {
            $this->handlefatalerror('exportdirmissing', 'local_usersynccsv', $this->exportdir);
        }
        if ($this->isexport && !is_writable($this->exportdir)) {
            $this->handlefatalerror('exportdirnotwritable', 'local_usersynccsv', $this->exportdir);
        }

        // Now check subfolders.
        if (!file_exists($this->fullworkdir)) {
            mkdir($this->fullworkdir,'0700');
        }
        if (!file_exists($this->fullarchivedir)) {
            mkdir($this->fullarchivedir,'0700');
        }
    }

    /**
     * TODO
     * @param string $smgconst to be resolved with get_string
     * @param string $file defaults to local_usersynccsv
     * @param string $a optional $smgconst parameter
     * @throws coding_exception
     */
    private function handlefatalerror($smgconst,$component='local_usersynccsv',$a=null) {
        echo get_string($smgconst,$component,$a);
        die;
    }

}