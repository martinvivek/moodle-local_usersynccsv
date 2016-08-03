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
$string['archivedirmaxday'] = 'Archive Dir Retention Days';
$string['archivedirmaxday_help'] = 'Max retention days of archive dirs';
$string['archivedirmaxsize'] = 'Max Archive Retention Size';
$string['archivedirmaxsize_help'] = 'Max retention size of archive dir, in MB';
$string['isexport'] = 'Export User Data (Not available yet)';
$string['isexport_help'] = 'If checked, will incrementally export user data to a CSV file in the directory set by exportdir.';
$string['exportdir'] = 'Export Directory (Not available yet)';
$string['exportdir_help'] = 'Only used if exportdir is true. Full path of the working export directory. Moodle needs to have read/write access to this folder, otherwise the plugin won\'t work';

$string['configdirmissing'] = 'Could not find required dir: {$a}';
$string['configdirnotwritable'] = 'Dir is not writable. Please check dir permissions: {$a}';

$string['csvdelimiter'] = 'CSV Delimiter';
$string['csvdelimiter_help'] = 'The delimiter parameter sets the field delimiter (one character only).';
$string['csvenclosure'] = 'CSV Enclosure';
$string['csvenclosure_help'] = 'The enclosure parameter sets the field enclosure character (one character only).';
$string['csvescape'] = 'CSV Escape';
$string['csvescape_help'] = 'The escape parameter sets the escape character (one character only).';
$string['userkey'] = 'User Key';
$string['userkey_help'] = 'The field used to uniquely identify a user. By default it\'s idnumber, but could also be email.';
$string['malformedfilemissingrequiredfield'] = 'Missing required field: {$a}';
$string['malformedfilemalformedline'] = 'Line number {$a}, numeber of elements differ from header number of elements';
$string['malformedfilegenericerror'] = 'Line number {$a}';
$string['defaultauth'] = 'User Default Auth Type';
$string['defaultauth_help'] = 'Used if auth field is not found in import file';
$string['requiredfields'] = 'Required Fields List';
$string['requiredfields_help'] = 'CSV List of required fields. If one of theese is not found in the import file header, the entire import file will be discarded. Default is blank, meaning that no required fields are provided, besides the Moodle required user fields: username , firstname, lastname, email, and of course the import key';
$string['malformedfilefoundunknownfield'] = 'Unknown field found: {$a}';
$string['synccsv_dofile-event'] = 'Sync CSV File processed';
$string['synccsv_error-event'] = 'Sync CSV File error';
$string['dbfiletablemaxday'] = 'File Table Retention Days';
$string['dbfiletablemaxday_help'] = 'Max retention days of file table, i.e. the table where we store file status';
$string['synccsveeventdescription'] = 'The file with id';
$string['maineventname'] = 'User Sync CSV';
$string['genericdberror'] = 'DB Error in table: {$a}';
$string['configerror'] = '{$a} configuration error, can\'t start execution';
$string['requiredconfigsetting'] = 'Required configuration setting not properly set: {$a}';
$string['defpassowrd'] = 'Default User Password';
$string['defpassowrd_help'] = 'Default user passowrd (plain), if not found in import file';
$string['discardlevel'] = 'File discard level';
$string['discardlevel_help'] = 'Set the discard file policy. USER_ERROR: At least one user is malformed, FILE_ERROR: the file is malformed, NEVER: never discard the file';
$string['synccsveeventconferrdescription'] = 'Conf error.';
$string['synccsvconf_error-event'] = 'Config Error';
$string['filepermissionserror'] = 'File permissions error';
$string['filemovedlog'] = 'moved to status {$a}';
$string['loglevel'] = 'Log level';
$string['loglevel_help'] = 'Set the log level. LOG_FATALERROR: fatal error only, LOG_FILEERROR: fatal/file/user errors, LOG_INFO: info on file processed and fatal/file/user errors';