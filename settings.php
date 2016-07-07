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

    $settings->add(new admin_setting_configtext('local_usersynccsv/csvdelimiter',
        get_string('csvdelimiter', 'local_usersynccsv'), get_string('csvdelimiter_help', 'local_usersynccsv'), ',', PARAM_TEXT, 1));
    $settings->add(new admin_setting_configtext('local_usersynccsv/csvenclosure',
        get_string('csvenclosure', 'local_usersynccsv'), get_string('csvenclosure_help', 'local_usersynccsv'), '"', PARAM_TEXT, 1));
    $settings->add(new admin_setting_configtext('local_usersynccsv/csvescape',
        get_string('csvescape', 'local_usersynccsv'), get_string('csvescape_help', 'local_usersynccsv'), '\\', PARAM_TEXT, 1));

    $settings->add(new admin_setting_configtext('local_usersynccsv/userkey',
        get_string('userkey', 'local_usersynccsv'), get_string('userkey_help', 'local_usersynccsv'), 'idnumber', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('local_usersynccsv/importdir',
        get_string('importdir', 'local_usersynccsv'), get_string('importdir_help', 'local_usersynccsv'), '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('local_usersynccsv/archivedirmaxday',
        get_string('archivedirmaxday', 'local_usersynccsv'), get_string('archivedirmaxday_help',
            'local_usersynccsv'), 10, PARAM_INT));
    $settings->add(new admin_setting_configtext('local_usersynccsv/archivedirmaxsize',
        get_string('archivedirmaxsize', 'local_usersynccsv'), get_string('archivedirmaxsize_help',
            'local_usersynccsv'), '', PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('local_usersynccsv/isexport',
        get_string('isexport', 'local_usersynccsv'), get_string('isexport_help', 'local_usersynccsv'), '0'));
    $settings->add(new admin_setting_configtext('local_usersynccsv/exportdir',
        get_string('exportdir', 'local_usersynccsv'), get_string('exportdir_help', 'local_usersynccsv'), '', PARAM_TEXT));
    $ADMIN->add('localplugins', $settings);

    $auths = core_component::get_plugin_list('auth');
    if (array_key_exists('manual', $auths)) {
        $def = 'manual';
    } else {
        $def = array_values($auths)[0];
    }
    foreach ($auths as $authkey => &$authval) {
        $authval = $authkey;
    }
    $settings->add(new admin_setting_configselect('local_usersynccsv/defaultauth',
        get_string('defaultauth', 'local_usersynccsv'),
        get_string('defaultauth_help', 'local_usersynccsv'), $def , $auths));

    $settings->add(new admin_setting_configtext('local_usersynccsv/requiredfields',
        get_string('requiredfields', 'local_usersynccsv'), get_string('requiredfields_help', 'local_usersynccsv'), '', PARAM_TEXT));
}
