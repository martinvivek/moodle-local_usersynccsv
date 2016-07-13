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

namespace local_usersynccsv\task;

defined('MOODLE_INTERNAL') || die();

class cron_task extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('maineventname', 'local_usersynccsv');
    }

    public function execute() {

        require_once(__DIR__ . '/../../../../config.php');
        try {
            $us = new \local_usersynccsv_usersync();
            $us->performcheck();
        } catch (\Exception $ex) {
            \local_usersynccsv_logger::logerror('execute - '. $ex->getMessage());
        }
    }
}