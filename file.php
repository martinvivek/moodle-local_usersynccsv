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
 * Show files
 *
 * @package   local_usersynccsv
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);

require_login();

$context = context_system::instance();

require_capability('local/usersynccsv:viewfile', $context);

$fm = new local_usersynccsv_fileman();
$filefullpath = $fm->getfilefullpathfromid($id);

if (file_exists($filefullpath)) {
    echo "<pre>";
    readfile($filefullpath);
    echo "</pre>";
} else {
    echo 'Error file not found';
}
