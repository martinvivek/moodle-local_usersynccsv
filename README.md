# moodle-usersynccsv

User Sync CSV is a Moodle local plugin allowing Moodle to import/export moodle users from/to other systems like SAP, using CSV files.
CSV files are and old but still largely used way of interaction among systems, because they don't require peculiar skillsets or advanced system configurations.
Hence sometimes it is necessary to set-up a background job that periodically extracts data from Moodle DB and writes the data into a CSV file, and vice-versa, reads data from an external CSV file and writes it into the Moodle DB.
This is exactly what this plugin does. It uses a configurable field as user key, typically idnumber, but can also be a custom field, and, according to this key, inserts or updates users found in the CSV files.
The CSV files must present field names in the first row. After the first one, each row represents a list of User fields.
The CSV files are expected to be found in a configurable directory called import. The plugin must have full permissions on the import dir.
Also, the plugin handles import file archiving and clean-up. File retention days is set by a configurable setting.
Other parameters, like CSV delimiter, can be configured in the settings page. Each parameter is provided with a detailed description.
The export functionality is foreseen in next releases, but it' still not available in version v1.0.0.
Please note that even though the plugin has been tested in pre-production environments, it's still in BETA version. Hence you are strongly encouraged to open issues in the git official issue manager, find the link in the plugin page. Please feel free to open bug, enhancements, thoughts or whatever should come into your mind.