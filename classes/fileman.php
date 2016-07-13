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
class local_usersynccsv_fileman
{
    /**
     * @var bool true if any error occurred during execution
     */
    public $iserror = false;

    /**
     * @var string detailed error message
     */
    public $errormsg = '';

    /**
     * @var string format of archive subdirectory eg 20160721
     */
    private static $archivesubdirformat = 'Ymd';

    /**
     * @var string name of work subdir
     */
    private static $workdir = 'work';

    /**
     * @var string name of archive subdir
     */
    private static $archivedir = 'archive';

    /**
     * @var string name of discrd subdir
     */
    private static $discarddir = 'discard';

    /**
     * @var int num days archive sub dir will be kept
     */
    private $archiveretentionmaxdays;

    /**
     * @var string import dir full path
     */
    private $importdir;

    /**
     * @var bool true if tables incremental export is also enabled
     */
    private $isexport;

    /**
     * @var string export dir full path
     */
    private $exportdir;

    /**
     * @var string work dir full path
     */
    private $fullworkdir;

    /**
     * @var string archive dir full path
     */
    private $fullarchivedir;

    /**
     * @var string discard dir full path
     */
    private $fulldiscarddir;

    /**
     * local_usersynccsv_fileman constructor.
     */
    public function __construct() {
        $config = get_config('local_usersynccsv');
        $this->importdir = $config->importdir;
        $this->isexport = $config->isexport;
        $this->exportdir = $config->exportdir;
        $this->archiveretentionmaxdays = $config->archivedirmaxday;
        $this->fullworkdir = $this->importdir . DIRECTORY_SEPARATOR . self::$workdir;
        $this->fullarchivedir = $this->importdir . DIRECTORY_SEPARATOR . self::$archivedir;
        $this->fulldiscarddir = $this->importdir . DIRECTORY_SEPARATOR . self::$discarddir;
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
                    local_usersynccsv_dbfileman::registerfile($file, local_usersynccsv_dbfileman::TOIMPORT);
                }
            }
            closedir($handle);
            // Sort by key, ie creation date.
            ksort($files);

        }
        return $files;
    }

    /**
     * Check for old files in work dir. If there's something there, there was en error.
     * @return array list of files in work dir. The files are sorted by creation date, ascending. So older first
     */
    public function listoldimportfiles() {
        $files = array();
        if ($handle = opendir($this->fullworkdir)) {
            while (false !== ($file = readdir($handle))) {
                $filefullpath = $this->fullworkdir . DIRECTORY_SEPARATOR . $file;
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
     * Move file to import directory
     * @param string $filefullpath full path of the file to be moved
     * @return string filename
     */
    public function movefiletoworkdir($filefullpath) {
        $basename = basename($filefullpath);
        $newname = $this->fullworkdir . DIRECTORY_SEPARATOR . $basename;
        rename($filefullpath, $newname);
        local_usersynccsv_dbfileman::registerfile($basename, local_usersynccsv_dbfileman::TOIMPORT);
        return $newname;
    }

    /**
     * Move file to working directory
     * @param string $filefullpath full path of the file to be moved
     * @return string filename
     */
    public function movefiletoimportdir($filefullpath) {
        $basename = basename($filefullpath);
        $newname = $this->importdir . DIRECTORY_SEPARATOR . $basename;
        rename($filefullpath, $newname);
        local_usersynccsv_dbfileman::registerfile($basename, local_usersynccsv_dbfileman::TOIMPORT);
        return $newname;
    }

    /**
     * Move file to archive directory
     * @param string $filefullpath full path of the file to be moved
     * @return string filename
     */
    public function movefiletoarchivedir($filefullpath) {
        $basename = basename($filefullpath);
        $archivesubdir = $this->getarchivesubdir();
        if (!file_exists($archivesubdir)) {
            $this->makedir($archivesubdir);
        }
        $newname = $archivesubdir . DIRECTORY_SEPARATOR . $basename;
        rename($filefullpath, $newname);
        local_usersynccsv_dbfileman::registerfile($basename, local_usersynccsv_dbfileman::ARCHIVED);
        return $newname;
    }
    /**
     * Move file to archive directory
     * @param string $filefullpath full path of the file to be moved
     * @return string filename
     */
    public function movefiletodiscarddir($filefullpath) {
            $basename = basename($filefullpath);
            $newname = $this->fulldiscarddir . DIRECTORY_SEPARATOR . $basename;
            rename($filefullpath, $newname);
            local_usersynccsv_dbfileman::registerfile($basename, local_usersynccsv_dbfileman::DISCARDED);
            return $newname;
    }

    public function docleanup() {
        $this->cleanuparchivedir();
        local_usersynccsv_dbfileman::cleanupdbfiletable();
    }
    /**
     * Clean up archive dir, according to configuration params
     * @return bool true on success
     */
    private function cleanuparchivedir() {
        $dirs = array();
        if ($handle = opendir($this->fullarchivedir)) {
            while (false !== ($dir = readdir($handle))) {
                if ($dir != '.' && $dir != '..' && is_dir($this->fullarchivedir .  DIRECTORY_SEPARATOR . $dir)) {
                    $dirs[$dir] = $dir;
                }
            }
            closedir($handle);

        }

        $today = new DateTime();
        foreach ($dirs as $dir) {
            $dirdate = DateTime::createFromFormat(self::$archivesubdirformat . 'His', $dir.'000000');
            if ($today->diff($dirdate)->days > $this->archiveretentionmaxdays) {
                $this->removedir($this->fullarchivedir .  DIRECTORY_SEPARATOR . $dir);
            }
        }
    }

    /**
     * remove dir
     * @param string $dir dir to be removed
     * @return bool true if ok, false otherwise
     */
    private function removedir($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            local_usersynccsv_dbfileman::registerfile(basename($dir), local_usersynccsv_dbfileman::DELETEDFS);
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->removedir($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }

    /**
     * Get archivedir full name, according to the current date
     * @return string archive sub dir full path
     */
    private function getarchivesubdir() {
        // We get the archive dir according to the current date.
        return $this->fullarchivedir . DIRECTORY_SEPARATOR . gmdate(self::$archivesubdirformat);
    }

    /**
     * check that config dir exists and is writable
     * @param string $configdir dir to be checked
     * @return bool true if dir exists and is writable
     */
    private function checkconfigdir($configdir) {
        if (!file_exists($configdir)) {
            $this->handlefatalerror($configdir.'missing', 'local_usersynccsv', $configdir);
            return false;
        } else {
            if (!is_writable($configdir)) {
                $this->handlefatalerror($configdir.'notwritable', 'local_usersynccsv', $configdir);
                return false;
            } else {
                return true;
            }

        }
    }

    /**
     * check if subdir exists. If not, create it
     * @param string $subdir dir to be checked
     */
    private function checkrequiredsubdir($subdir) {
        if (!file_exists($subdir)) {
            $this->makedir($subdir);
        }
    }
    /**
     * Check import dir structure to see if every required subfolder exists
     */
    private function checkconfigdirs() {

        if (!$this->checkconfigdir($this->importdir)) {
            return;
        }
        if ($this->isexport && !$this->checkconfigdir($this->exportdir)) {
            return;
        }

        // Now check subfolders. Make them if they don't exist.
        $this->checkrequiredsubdir($this->fullworkdir);
        $this->checkrequiredsubdir($this->fullarchivedir);
        $this->checkrequiredsubdir($this->fulldiscarddir);
    }

    /**
     * TODO check for dir permissions
     * @param string $dirfullpath
     */
    private function makedir($dirfullpath) {
        global $CFG;

        @mkdir($dirfullpath, $CFG->directorypermissions, false);
    }
    /**
     * handle fatal error
     * @param string $smgconst to be resolved with get_string
     * @param string $component defaults to local_usersynccsv
     * @param string $a optional $smgconst parameter
     * @throws coding_exception
     */
    private function handlefatalerror($smgconst, $component, $a = null) {
        $this->errormsg = get_string($smgconst, $component, $a);
        $this->iserror = true;
        local_usersynccsv_logger::logerror($this->errormsg);
    }

    public function getfilefullpathfromid($fileid) {
        $file = local_usersynccsv_dbfileman::getfilefromid($fileid);
        switch ($file->status) {
            case local_usersynccsv_dbfileman::TOIMPORT:
                $filefullpath = $this->importdir . DIRECTORY_SEPARATOR . $file->name;
                break;
            case local_usersynccsv_dbfileman::WORKING:
                $filefullpath = $this->fullworkdir  . DIRECTORY_SEPARATOR . $file->name;
                break;
            case local_usersynccsv_dbfileman::DISCARDED:
                $filefullpath = $this->fulldiscarddir . DIRECTORY_SEPARATOR . $file->name;
                break;
            case local_usersynccsv_dbfileman::ARCHIVED:
                $filefullpath = $this->fullarchivedir . DIRECTORY_SEPARATOR .
                    $file->archivesubdir . DIRECTORY_SEPARATOR . $file->name;
                break;
            default:
                $filefullpath = '';
                break;
        }
        return $filefullpath;
    }

}