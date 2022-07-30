<?php

namespace App\Controllers;

use App\Controllers\cronjobs\FilesCron;
use App\Controllers\cronjobs\HealthCron;

class Crontab extends Controller {

    /**
     * Cronjob Handler
     * 
     * @return Bool
     */
    public function jobs() {

        # only accessible via the cli
        if( !is_cli() ) {
            return "Access denied!";
        }

        # delete all temporary files
        // $filesObj = new FilesCron;
        // $filesObj->delete_temp();

        # pull health data
        $healthObj = new HealthCron;
        $healthObj->init();

    }

}
?>