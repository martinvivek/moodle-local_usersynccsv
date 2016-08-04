# moodle-usersynccsv

User Sync CSV is a Moodle local plugin allowing Moodle to import/export Mpartoodle users from/to other systems like SAP, using CSV files.
CSV files are an old but still largely used way of interaction among systems, because they don't require specific skillsets or advanced system configurations.
Hence sometimes it is necessary to set-up a background job that periodically extracts data from Moodle DB and writes the data into a CSV file and vice-versa: reads data from an external CSV file and writes it into the Moodle DB.
This is exactly what this plugin does. It uses a configurable parameter for the user key, typically “idnumber”, but can also be a custom field. According to this key, the plugin inserts or updates users found in the CSV files.
The CSV files must present field names in the first row. After the first one, each row represents a list of User fields.
The CSV files are expected to be found in a configurable directory called “import”. The plugin must have  read/write/exec access in the import dir.
Also, the plugin handles archiving and clean-up of the import files.The file retention days are set by a configurable parameter.
Other parameters, like CSV delimiter, can be configured in the settings page. Each parameter is provided with a detailed description.
The plugin basically registers a new Moodle scheduled task, called User Sync CSV, that, by default, runs every day at 3AM.
At the beginning of each execution the task performs a series of checks on the configuration parameters. If one of these preliminary checks fail, the execution is aborted and a log is written in the Moodle logs.
The most important checks are:
* CSV Delimiter: it must be set. No empty string allowed.
* User key: it must be set. No empty string allowed. It must be a column of User table or the shortname of a custom field.
* Import Directory: it must be set. No empty string allowed. It must exist in the File System, and Moodle needs to have read/write/exec permissions in it.
Default User Password: it must be set. No empty string allowed.

At the start of each execution, the plugin will check for three subdirs of Import Directory. If not found, it will attempt to create them. The dirs are:
* work: Working directory. A file found in import dir is moved to this dir before being imported. It remains here during import and is moved to discard or archive at the end of import.
* discard: discard directory. Files would be moved here from work if there were any problems during execution. Discarding policy can also be set. See plugin parameters.
* archive: archive directory. Files would be moved here from work if import were OK. The dir is organized in daily subdirectory. Aging policy will remove entire subdirs after expiration time. See plugin parameters.

The export functionality is foreseen in the next releases, but it' still not available in version v1.0.0.
Please note that even though the plugin has been tested in pre-production environments, it's still in BETA version. Hence, you are strongly encouraged to open issues in the git official issue manager, find the link in the plugin page. Please feel free to open bugs, suggest enhancements, report thoughts or whatever should come into your mind.
