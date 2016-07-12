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
 * The EVENTNAME event.
 *
 * @package    local_usersynccsv
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_usersynccsv\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The local_usersynccsv event class.
 *
 *
 * @since     Moodle MOODLEVERSION
 * @copyright 2014 YOUR NAME
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class synccsv_dofile extends \core\event\base {
    protected function init() {
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_usersynccsv_file';
        $this->data['crud'] = 'c';
        $this->context = \context_system::instance();
    }

    public static function get_name() {
        return get_string('synccsv_dofile-event', 'local_usersynccsv');
    }

    public function get_description() {
        return get_string('synccsveeventdescription','local_usersynccsv'). " {$this->objectid} {$this->other}.";
    }

    public function get_url()
    {
        global $CFG;
        return new \moodle_url($CFG->wwwroot .'\local\usersynccsv\file.php',array('id' => $this->objectid));
    }
}