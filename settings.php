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

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_usersynccsv', get_string('pluginname', 'local_usersynccsv'));

    $settings->add(new admin_setting_configtext('local_usersynccsv/importdir',
        get_string('importdir', 'local_usersynccsv'), get_string('importdir_help', 'local_usersynccsv'), ''));

    $settings->add(new admin_setting_configcheckbox('local_usersynccsv/isexport',
        get_string('isexport', 'local_usersynccsv'), get_string('isexport_help', 'local_usersynccsv'), '0'));
    $settings->add(new admin_setting_configtext('local_usersynccsv/exportdir',
        get_string('exportdir', 'local_usersynccsv'), get_string('exportdir_help', 'local_usersynccsv'), ''));
    $ADMIN->add('localplugins', $settings);
}
