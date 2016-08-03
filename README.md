# moodle-usersynccsv

User Sync CSV is a Moodle local plugin allowing Moodle to import/export moodle users from/to other systems like SAP, using CSV files.
CSV files are and old but still largely used way of interaction among systems, because they don't require peculiar skillsets or advanced system configurations.
Hence sometimes it is necessary to set-up a background job that periodically extracts data from Moodle DB and writes the data into a CSV file, and vice-versa, reads data from an external CSV file and writes it into the Moodle DB.
This is exactly what this plugin does. It uses a configurable field for the user key, typically idnumber, but can also be a custom field, and, according to this key, inserts or updates users found in the CSV files.
The CSV files must present field names in the first row. After the first one, each row represents a list of User fields.
The CSV files are expected to be found in a configurable directory called import. The plugin must have full permissions on the import dir.
Also, the plugin handles import file archiving and clean-up. File retention days is set by a configurable setting.
Other parameters, like CSV delimiter, can be configured in the settings page. Each parameter is provided with a detailed description.
The plugin basically registers a new Moodle scheduled task, called User Sync CSV, that, by default, runs every day at 3AM.
At the beginning of each execution, the task performs a series of checks on the configuration parameters. If one of theese priliminary checks fails, the execution is aborted and a log is written in the Moodle logs.
The most important checks are:
* CSV Delimiter: it must be set. No empty string allowed.
* User key: it must be set. No empty string allowed. It must be a column of User table or the shortname of a custom field.
* Import Directory: it must be set. No empty string allowed. It must exist on the File System, and Moodle needs to have read/write/exec permissions on it.
Default User Password: it must be set. No empty string allowed.

At the start of each execution, the plugin will check for three subdirs of Import Directory. If not found, it will attempt to create them. The dirs are:
* work: Working directory. A file found in import dir is moved to this dir before being imported. It remains here during import and is moved to discard or archive at the end of import.
* discard: discard directory. Files are moved here from work, if there was any problems during execution. Discarding policy can also be set. See plugin parameters.
* archive: archive direcotry. Files are moved here from work, if import was OK. The dir is roganized in daily subdirectory. Aging policy will remove entire subdir after expiration time. See plugin parameters.

The export functionality is foreseen in next releases, but it' still not available in version v1.0.0.
Please note that even though the plugin has been tested in pre-production environments, it's still in BETA version. Hence you are strongly encouraged to open issues in the git official issue manager, find the link in the plugin page. Please feel free to open bug, enhancements, thoughts or whatever should come into your mind.