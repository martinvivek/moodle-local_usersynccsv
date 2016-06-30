<?php
defined('MOODLE_INTERNAL') || die();

$context = context_system::instance();

if ($hassiteconfig) {
  $settings = new admin_settingpage('local_usersynccsv', get_string('pluginname', 'local_usersynccsv'));
  $settings->add(new admin_setting_configselect('local_usersynccsv/importdir',
      get_string('importdir', 'local_usersynccsv'), get_string('importdirdesc', 'local_usersynccsv'),''));
}