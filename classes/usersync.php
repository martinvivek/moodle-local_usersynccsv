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
 * @package    local_usersynccsv
 * @copyright  2016 onwards Antonello Moro {http://antonellomoro.it}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_usersynccsv_usersync
{
    /**
     * @var array Moodle required User fields
     */
    private $requiredfields = ['username' , 'firstname', 'lastname', 'email'];

    /**
     * @var array custom user defined required fields
     */
    private $customrequiredfields;
    /**
     * @var local_usersynccsv_fileman
     */
    private $fm;

    /**
     * @var string key used to uniquely identify user in moodle
     */
    private $userkey;

    /**
     * @var string character used as csv delimiter
     */
    private $csvdelimiter;

    /**
     * @var string character used as csv enclosure
     */
    private $csvenclosure;

    /**
     * @var string character used as csv escape
     */
    private $csvescape;

    /**
     * @var array columns in user table
     */
    private $usertablecolumns;

    /**
     * @var array short name of user custom fields
     */
    private $usercustomfiledshortnames;

    /**
     * local_usersynccsv_usersync constructor.
     */
    public function __construct() {
        $this->fm = new local_usersynccsv_fileman();
        $config = get_config('local_usersynccsv');
        $this->userkey = $config->userkey;
        $this->csvdelimiter = $config->csvdelimiter;
        $this->csvenclosure = $config->csvenclosure;
        $this->csvescape = $config->csvescape;
        if (trim( $config->requiredfields) == '') {
            $this->customrequiredfields = array();
        } else {
            $this->customrequiredfields = explode(',', $config->requiredfields);
        }
    }

    /**
     * Writes log for malformed file.
     * @param string $reason reason why the file was malformed
     */
    private function reportmalformedfile($reason) {
        local_usersynccsv_logger::logerror($reason);
    }

    /**
     * Writes log for malformed user in file.
     * @param string $reason reason why the file was malformed
     */
    private function reportmalformeduser($reason) {
        local_usersynccsv_logger::logerror($reason);
    }

    /**
     * check if there're old files in working dir that need to be re-processed
     */
    private function checkoldfiles() {
        $files = $this->fm->listoldimportfiles();
        foreach ($files as $file) {
            $this->fm->movefiletoimportdir($file);
        }
    }

    /**
     * Trim fields in header file
     * @param array $csvheader csv header of import file
     */
    private function cleanfilerow(&$csvheader) {
        foreach ($csvheader as &$field) {
            $field = trim($field);
        }
    }

    /**
     * Check if a file is malformed, against various rules.
     * @param string $file string the file full path
     * @param resource $filehandle string file hanlder
     * @param array $csvheader string the header csv ros
     * @return bool true if ok, false otherwise
     * @throws coding_exception
     */
    private function checkmalformedfile($file, $filehandle, $csvheader) {
        if (!array_key_exists($this->userkey, $csvheader)) {
            $this->reportmalformedfile(get_string('malformedfilemissingrequiredfield',
                'local_usersynccsv', $this->userkey));
            fclose($filehandle);
            $this->fm->movefiletodiscarddir($file);
            return false;
        }
        // Check required moodle user fields.
        foreach ($this->requiredfields as $requiredfield) {
            if (!array_key_exists($requiredfield, $csvheader)) {
                $this->reportmalformedfile(get_string('malformedfilemissingrequiredfield',
                    'local_usersynccsv', $requiredfield));
                fclose($filehandle);
                $this->fm->movefiletodiscarddir($file);
                return false;
            }
        }
        foreach ($this->customrequiredfields as $requiredfield) {
            if (!array_key_exists($requiredfield, $csvheader)) {
                $this->reportmalformedfile(get_string('malformedfilemissingrequiredfield',
                    'local_usersynccsv', $requiredfield));
                fclose($filehandle);
                $this->fm->movefiletodiscarddir($file);
                return false;
            }
        }

        // Check that each field in the file has a corresponding column in user tables, or a custom user field.
        $csvheaderkeys = array_keys($csvheader);
        foreach ($csvheaderkeys as $fieldkey) {
            if (!array_key_exists($fieldkey, $this->usertablecolumns) &&
                !array_key_exists($fieldkey, $this->usercustomfiledshortnames)) {
                $this->reportmalformedfile(get_string('malformedfilefoundunknownfield',
                    'local_usersynccsv', $fieldkey));
                fclose($filehandle);
                $this->fm->movefiletodiscarddir($file);
                return false;
            }
        }
        return true;
    }

    /**
     * Check if user is malformed, against various rules
     * @param int $linenumber  line number in import file
     * @param array $csvuser  csv user to be imported
     * @param int $numexpectedfields
     * @param array $csvheader  csv header found in the import file
     * @param bool $filemalformed  true if file is malformed
     * @throws coding_exception
     */
    private function importuser($linenumber, $csvuser,
                                        $numexpectedfields, $csvheader, &$filemalformed) {
        if ($csvuser && false !== $csvuser) {
            if ($numexpectedfields == count($csvuser)) {
                $ret = $this->create_update_user($csvuser, $csvheader);
                if (true !== $ret) {
                    $this->reportmalformeduser( get_string('malformedfilegenericerror', 'local_usersynccsv',
                            $linenumber) . ' - ' . $ret);
                    $filemalformed = true;
                }
            } else {
                $this->reportmalformeduser( get_string('malformedfilemalformedline', 'local_usersynccsv',
                    $linenumber));
                $filemalformed = true;
            }

        }
    }

    /**
     * import specified file
     * @param string $file  file full path
     * @throws coding_exception
     */
    private function importfile($file) {
        $linenumber = 1;
        $filehandle = null;
        try {
            $filemalformed = false;
            $file = $this->fm->movefiletoworkdir($file);
            $filehandle = fopen($file, 'r');
            $csvheader = fgetcsv($filehandle, null, $this->csvdelimiter, $this->csvenclosure, $this->csvescape);
            $this->cleanfilerow($csvheader);
            $csvheader = array_flip($csvheader);
            $numexpectedfields = count($csvheader);

            if (!$this->checkmalformedfile($file, $filehandle, $csvheader)) {
                return;
            }

            while (!feof($filehandle)) {
                $csvuser = fgetcsv($filehandle, null, $this->csvdelimiter, $this->csvenclosure, $this->csvescape);
                $this->importuser($linenumber, $csvuser, $numexpectedfields, $csvheader, $filemalformed);
                $linenumber++;
            }
            fclose($filehandle);
            // Archive file. Discard if there were errors on user import.
            if ($filemalformed) {
                $this->fm->movefiletodiscarddir($file);
            } else {
                $this->fm->movefiletoarchivedir($file);
            }
        } catch (Exception $ex) {
            $this->reportmalformeduser(get_string('malformedfilegenericerror', 'local_usersynccsv', $linenumber)
                . ' - ' . $ex->getMessage());
            if (null !== $filehandle && is_resource($filehandle)) {
                fclose($filehandle);
            }
            $this->fm->movefiletodiscarddir($file);
        }
    }

    /**
     * @return bool true on success
     */
    private function checkconfigok() {

        if ($this->userkey == '') {
            local_usersynccsv_logger::logerror(get_string('requiredconfigsetting', 'local_usersynccsv', 'userkey'));
            return false;
        }
        if ($this->csvdelimiter == '') {
            local_usersynccsv_logger::logerror(get_string('requiredconfigsetting', 'local_usersynccsv', 'csvdelimiter'));
            return false;
        }
        return true;
    }
    /**
     * Check files to be imported, check tables to be exported
     */
    public function performcheck() {

        if (!$this->fm->checkconfigok()) {
            local_usersynccsv_logger::logerror(get_string('configerror', 'local_usersynccsv', 'File'));
            return;
        }
        if (!$this->checkconfigok()) {
            local_usersynccsv_logger::logerror(get_string('configerror', 'local_usersynccsv', 'Setting'));
            return;
        }

        // Check old files.
        $this->checkoldfiles();

        // Check for new files.
        $files = $this->fm->listnewimportfiles();
        if (count($files) > 0) {
            // Retrieve import info from DB, since there are files to be imported.
            if (!$this->retrievedbimportinfo()) {
                local_usersynccsv_logger::logerror(get_string('configerror', 'local_usersynccsv', 'Setting'));
                return;
            }
        }
        foreach ($files as $file) {
            $this->importfile($file);
        }

        $this->fm->docleanup();
    }

    /**
     * Retrieve user columns and user custom field shortnames from DB
     */
    private function retrievedbimportinfo() {

        global $DB;

        $founduserkey = false;
        // Users.
        $columns = $DB->get_columns('user');
        foreach ($columns as $column) {
            $dbfield = new local_usersynccsv_dbfield();
            $dbfield->name = $column->name;
            if ($dbfield->name == $this->userkey) {
                $founduserkey = true;
            }
            $dbfield->iscustomfield = false;
            $this->usertablecolumns[$dbfield->name] = $dbfield;
        }

        // Custom fields.
        $columns = $DB->get_records('user_info_field', array(), 'shortname');
        foreach ($columns as $column) {
            $dbfield = new local_usersynccsv_dbfield();
            $dbfield->name = $column->shortname;
            if ($dbfield->name == $this->userkey) {
                $founduserkey = true;
            }
            $dbfield->iscustomfield = true;
            $dbfield->customfieldid = $column->id;
            $this->usercustomfiledshortnames[$dbfield->name] = $dbfield;
        }
        if (!$founduserkey) {
            local_usersynccsv_logger::logerror(get_string('requiredconfigsetting', 'local_usersynccsv', 'userkey'));
        }
        return $founduserkey;

    }

    /**
     * function to grab Moodle user and update their fields then return the
     * account. If the account does not exist, create it.
     * Returns: the Moodle user (array).
     *
     * @param array $csvuser the user array.
     * @param array $csvheader the header fields array.
     *
     * @return bool|string true on success, string on error
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
            $customfields = array();
            // User fields and custom fields.
            foreach ($csvheader as $fieldname => $fieldpos) {
                $fieldname = trim($fieldname);
                if (array_key_exists($fieldname, $this->usertablecolumns)) {
                    $user->$fieldname = $csvuser[$fieldpos];
                } else {
                    $customfields[$fieldname] = $csvuser[$fieldpos];
                }
            }
            $user->lang = $CFG->lang;
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->password    = hash_internal_user_password('guest');
            $user->auth        = 'manual';
            $user->confirmed   = 1;
            if (empty($user->city)) {
                $user->city = "none";
            }
            if (empty($user->country)) {
                $user->country = "no";
            }
            if (!property_exists($user, 'id')) {
                // Add the new user to Moodle.
                $DB->insert_record('user', $user);
                $user = $DB->get_record('user', array($this->userkey => $userkey));
                if (!$user) {
                    return get_string('genericdberror', 'local_usersynccsv', 'user');
                }
            } else {

                // Update user information.
                // Username "could" change. userkey should never change.
                if (!$DB->update_record('user', $user)) {
                    return get_string('genericdberror', 'local_usersynccsv', 'user');
                }
            }
            // Custom fields, if any.
            return $this->create_update_user_custom_field($customfields, $user);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * Creates/Updates custom fields, if any
     * @param array $customfields
     * @param stdClass $user
     * @return bool|string true on success, string on error
     */
    private function create_update_user_custom_field($customfields, $user) {
        global $DB;
        // Custom fields, if any.
        foreach ($customfields as $customfieldshortname => $customfieldvalue) {
            $field = $DB->get_record('user_info_data',
                array('fieldid' => $this->usercustomfiledshortnames[$customfieldshortname]->customfieldid, 'userid' => $user->id));
            if ($field) {
                // Update.
                $field->data = $customfieldvalue;
                if (!$DB->update_record('user_info_data', $field)) {
                    return get_string('genericdberror', 'local_usersynccsv', 'user_info_data');
                }
            } else {
                // Insert.
                $field = new stdClass();
                $field->fieldid = $this->usercustomfiledshortnames[$customfieldshortname]->customfieldid;
                $field->userid = $user->id;
                $field->data = $customfieldvalue;
                // Data format is not supported.
                $DB->insert_record('user_info_data', $field);
                $field = $DB->get_record('user_info_data', array('fieldid' => $field->fieldid, 'userid' => $field->userid));
                if (!$field) {
                    return get_string('genericdberror', 'local_usersynccsv', 'user_info_data');
                }
            }
        }
        return true;
    }
}