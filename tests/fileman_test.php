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

global $CFG;

/**
 *
 * User Sync CSV.
 *
 * @package   local_usersynccsv
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_usersynccsv_fileman_testcase extends advanced_testcase {

    public function test_create_instance() {
        $this->resetAfterTest();
        $this->setAdminUser();

        try {
            $fm = new local_usersynccsv_fileman();
            $fmok = true;
        } catch (Error $e) {
            $fmok = false;
        }
        $this->assertEquals(true, $fmok);

        // Create a couple of mock import files
        $mokcfile = fopen($fm->getimportdir() . 'importtestone', "w");
        fclose($mokcfile);
        $mokcfile = fopen($fm->getimportdir() . 'importtesttwo', "w");
        fclose($mokcfile);
        $files=$fm->listnewimportfiles();

        $this->assertEquals(2, $files);

        foreach ($files as $file){
            $fm->movefiletoarchivedir($file);
        }
        $direxists = file_exists($fm->getfullarchivedir() . DIRECTORY_SEPARATOR . gmdate("Ymd"));

        $this->assertEquals(true, $direxists);

    }

}
