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
     * @var array
     */
    private $requiredfields = ['username' , 'firstname', 'lastname', 'email'];
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

    private function reportmalformedfile($filefullpath, $reason) {
        echo '<div>'.$filefullpath . ' malformed: '.$reason .'</div>';
    }
    private function reportmalformeduser($filefullpath, $reason) {
        echo '<div>'.$filefullpath . ' malformed: '.$reason .'</div>';
    }
    private function checkoldfiles() {
        $files = $this->fm->listoldimportfiles();
        foreach ($files as $file) {
            $this->fm->movefiletoimportdir($file);
        }
    }
    private function cleanfilerow(&$csvheader) {
        foreach ($csvheader as &$field) {
            $field = trim($field);
        }
    }
    public function performcheck() {

        // Check old files.
        $this->checkoldfiles();

        // Check for new files.
        $files = $this->fm->listnewimportfiles();
        $linenumber = 1;
        $filehandle = null;
        foreach ($files as $file) {
            try {
                $filemalformed = false;
                $file = $this->fm->movefiletoworkdir($file);
                $filehandle = fopen($file, 'r');
                $csvheader = fgetcsv($filehandle, null, $this->csvdelimiter, $this->csvenclosure, $this->csvescape);
                $this->cleanfilerow($csvheader);
                $csvheader = array_flip($csvheader);
                $numexpectedfields = count($csvheader);
                if (!array_key_exists($this->userkey, $csvheader)) {
                    $this->reportmalformedfile($file, get_string('malformedfilemissingrequiredfield',
                        'local_usersynccsv', $this->userkey));
                    fclose($filehandle);
                    $this->fm->movefiletodiscarddir($file);
                    continue;
                }
                // Check required moodle user fields.
                foreach ($this->requiredfields as $requiredfield) {
                    if (!array_key_exists($requiredfield, $csvheader)) {
                        $this->reportmalformedfile($file, get_string('malformedfilemissingrequiredfield',
                            'local_usersynccsv', $requiredfield));
                        fclose($filehandle);
                        $this->fm->movefiletodiscarddir($file);
                        $filemalformed = true;
                        continue;
                    }
                }
                if ($filemalformed) continue;

                while (!feof($filehandle)) {
                    $csvuser = fgetcsv($filehandle, null, $this->csvdelimiter, $this->csvenclosure, $this->csvescape);
                    if ($csvuser && false !== $csvuser) {
                        if ($numexpectedfields == count($csvuser)) {
                            $ret = $this->create_update_user($csvuser, $csvheader);
                            if (true !== $ret) {
                                $this->reportmalformeduser($file, get_string('malformedfilegenericerror', 'local_usersynccsv',
                                        $linenumber) . ' - ' . $ret);
                                $filemalformed = true;
                            }
                        } else {
                            $this->reportmalformeduser($file, get_string('malformedfilemalformedline', 'local_usersynccsv',
                                $linenumber));
                            $filemalformed = true;
                        }

                    }
                    $linenumber++;
                }
                fclose($filehandle);
                // Archive file. Discard if there were errors on user import.
                if ($filemalformed) {
                    $this->fm->movefiletodiscarddir($file);
                }else {
                    $this->fm->movefiletoarchivedir($file);
                }
            } catch (Exception $ex) {
                $this->reportmalformeduser($file,
                    get_string('malformedfilegenericerror', 'local_usersynccsv', $linenumber) . ' - ' . $ex->getMessage());
                if (null !== $filehandle && is_resource($filehandle)) {
                    fclose($filehandle);
                }
                $this->fm->movefiletodiscarddir($file);
            }
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

        global $CFG, $DB;
        try {
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
            $user->lang = $CFG->lang;
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->password    = hash_internal_user_password('guest');
            $user->auth        = 'manual';
            $user->confirmed   = 1;
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
            return true;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }

    }
}