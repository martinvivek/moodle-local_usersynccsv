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
 * @package    local_usersynccs
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_usersynccsv_fileman
{
    public $iserror = false;
    public $errormsg = '';
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


    public function getfullarchivedir() {
        return $this->fullarchivedir;
    }
    public function getimportdir() {
        return $this->importdir;
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
     * Check for new files to work
     * @return array list of files to be imported. The files are sorted by creation date, ascending. So older first
     */
    public function listnewimportfiles() {
        $files = array();
        if ($handle = opendir($this->importdir)) {
            while (false !== ($file = readdir($handle))) {
                $filefullpath = $this->importdir . DIRECTORY_SEPARATOR . $file;
                if (!is_dir($filefullpath)) {
                    $files[filemtime(utf8_decode($filefullpath))] = $filefullpath;
                }
            }
            closedir($handle);
            // Sort by key, ie creation date.
            ksort($files);
        }
        return $files;
    }

    /**
     * Move file to working directory
     * @param string $filefullpath full path of the file to be moved
     * @return bool true on success
     */
    public function movefiletoworkdir($filefullpath) {

    }

    /**
     * Move file to archive directory
     * @param string $filefullpath full path of the file to be moved
     * @return bool true on success
     */
    public function movefiletoarchivedir($filefullpath) {
        $archivesubdir = $this->getarchivesubdir();
        if (!file_exists($archivesubdir)) {
            $this->makedir($archivesubdir);
        }
        rename($filefullpath, $archivesubdir . DIRECTORY_SEPARATOR . basename($filefullpath));
    }

    /**
     * Clean up archive dir, according to configuration params
     * @return bool true on success
     */
    public function cleanuparchivedir() {

    }

    /**
     * Get archivedir full name, according to the current date
     * @return string archive sub dir full path
     */
    private function getarchivesubdir() {
        // We get the archive dir according to the current date.
        return $this->fullarchivedir . DIRECTORY_SEPARATOR . gmdate("Ymd");
    }
    /**
     * Check import dir structure to see if every required subfolder exists
     */
    private function checkconfigdirs() {
        if (!file_exists($this->importdir)) {
            $this->handlefatalerror('importdirmissing', 'local_usersynccsv', $this->importdir);
            return;
        }
        if (!is_writable($this->importdir)) {
            $this->handlefatalerror('importdirnotwritable', 'local_usersynccsv', $this->importdir);
            return;
        }
        if ($this->isexport && !file_exists($this->exportdir)) {
            $this->handlefatalerror('exportdirmissing', 'local_usersynccsv', $this->exportdir);
            return;
        }
        if ($this->isexport && !is_writable($this->exportdir)) {
            $this->handlefatalerror('exportdirnotwritable', 'local_usersynccsv', $this->exportdir);
            return;
        }

        // Now check subfolders. Make them if they don't exist.
        if (!file_exists($this->fullworkdir)) {
            $this->makedir($this->fullworkdir);

        }
        if (!file_exists($this->fullarchivedir)) {
            $this->makedir($this->fullarchivedir);
        }
    }

    /**
     * TODO
     * @param string $dirfullpath
     */
    private function makedir($dirfullpath) {
        global $CFG;

        @mkdir($dirfullpath, $CFG->directorypermissions, false);
    }
    /**
     * TODO
     * @param string $smgconst to be resolved with get_string
     * @param string $file defaults to local_usersynccsv
     * @param string $a optional $smgconst parameter
     * @throws coding_exception
     */
    private function handlefatalerror($smgconst, $component='local_usersynccsv', $a=null) {
        $this->errormsg = get_string($smgconst, $component, $a);
        $this->iserror = true;
    }

}