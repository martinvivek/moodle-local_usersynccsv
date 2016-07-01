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

$string['pluginname'] = 'User Sync CSV';
$string['modulenameplural'] = 'User Sync CSV';
$string['modulename_help'] = 'Synchronize Users with external system by means of CSV files';

$string['pluginadministration'] = 'User Sync CSV administration';
$string['importdir'] = 'Import Directory';
$string['importdir_help'] = 'Full path of the working import directory. It must exist on the FileSystem, and Moodle needs to have read/write access to this folder, otherwise the plugin won\'t work';
$string['archivedirmaxday'] = 'Max Archive Retention Day';
$string['archivedirmaxday_help'] = 'Max retention days of archive dir';
$string['archivedirmaxsize'] = 'Max Archive Retention Size';
$string['archivedirmaxsize_help'] = 'Max retention size of archive dir, in MB';
$string['isexport'] = 'Export User Data';
$string['isexport_help'] = 'If checked, we will export user data to a CSV file in the directory set by exportdir';
$string['exportdir'] = 'Export Directory';
$string['exportdir_help'] = 'Only used if exportdir is true. Full path of the working export directory. Moodle needs to have read/write access to this folder, otherwise the plugin won\'t work';

$string['importdirmissing'] = 'Could not find import dir: {$a}';
$string['importdirnotwritable'] = 'Import dir is not writable. Please check dir permissions: {$a}';

$string['exportdirmissing'] = 'Could not find export dir: {$a}';
$string['exportdirnotwritable'] = 'Export dir is not writable. Please check dir permissions: {$a}';