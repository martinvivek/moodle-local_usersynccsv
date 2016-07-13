<?php

namespace local_usersynccsv\task;

class cron_task extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('maineventname', 'local_usersynccsv');
    }

    public function execute() {

        require_once(__DIR__ . '/../../../../config.php');
        try{
            $us = new \local_usersynccsv_usersync();
            $us->performcheck();
        } catch (\Exception $ex) {
            \local_usersynccsv_logger::logerror('execute - '. $ex->getMessage());
        }

        

    }
} 