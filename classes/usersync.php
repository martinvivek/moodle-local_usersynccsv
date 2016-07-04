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
 * @package    local_usersynccs
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_usersynccsv_usersync
{
    /**
     * @var local_usersynccsv_fileman
     */
    private $fm;

    private $userkey;
    private $csvdelimiter;
    private $csvenclosure;
    private $csvescape;

    public function __construct() {
        $this->fm = new local_usersynccsv_fileman();
        $config = get_config('local_usersynccsv');
        $this->userkey = $config->userkey;
        $this->csvdelimiter = $config->csvdelimiter;
        $this->csvenclosure = $config->csvenclosure;
        $this->csvescape = $config->csvescape;
    }

    private function reportmalformedfile($filefullpath) {
        echo $filefullpath . ' malformed';
    }
    public function performcheck() {
        // Check for new files.
        $files = $this->fm->listnewimportfiles();

        foreach ($files as $file) {
            $filehandle = fopen($file, 'r');
            $csvheader = fgetcsv($filehandle, null, $this->csvdelimiter, $this->csvenclosure, $this->csvescape);
            $csvheader = array_flip($csvheader);
            if (!array_key_exists($this->userkey, $csvheader)) {
                $this->reportmalformedfile($file);
            }
            while (!feof($filehandle) ) {
                $csvuser = fgetcsv($filehandle, null, $this->csvdelimiter, $this->csvenclosure, $this->csvescape);
                if (is_array($csvuser)) {
                    $this->create_update_user($csvuser, $csvheader);
                }
            }
            fclose($filehandle);
            // Archive file.
            $this->fm->movefiletoarchivedir($file);
        }

    }

    /**
     * function to grab Moodle user and update their fields then return the
     * account. If the account does not exist, create it.
     * Returns: the Moodle user (array).
     *
     * @param array $csvuser the user array.
     * @param array $csvheader the header fields array.
     *
     * @return array Moodle user
     */
    private function create_update_user($csvuser, $csvheader) {

        global $DB;

        $userkey = $csvuser[$csvheader[$this->userkey]];
        // Look for user with key.
        $user = $DB->get_record('user', array($this->userkey => $userkey));

        if (empty($user)) {
            // Build the new user object to be put into the Moodle database.
            $user = new stdClass();
        }
        $user->modified = time();
        foreach ($csvheader as $fieldname => $fieldpos) {
            $fieldname = trim($fieldname);
            $user->$fieldname = $csvuser[$fieldpos];

        }

        if (!property_exists($user, 'id')) {
            // Add the new user to Moodle.

            $DB->insert_record('user', $user);
            $user = $DB->get_record('user', array($this->userkey => $userkey));
            if (!$user) {
                print_error('auth_drupalservicescantinsert', 'auth_db', $user->username);
            }
        } else {

            // Update user information.
            // Username "could" change. userkey should never change.
            if (!$DB->update_record('user', $user)) {
                print_error('auth_drupalservicescantupdate', 'auth_db', $user->username);
            }
        }
        return $user;
    }
}