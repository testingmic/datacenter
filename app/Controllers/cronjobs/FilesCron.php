<?php
namespace App\Controllers\cronjobs;

class FilesCron {

    /**
     * Delete all temporary files list
     * 
     * @return Bool
     */
    final function delete_temp() {
        
        # session variable
        $sessVar = config('App')->sessionCookieName;

        # set the helper file
        helper(['filesystem', 'api']);

        # set the session file directory
        $files_list = [
            'session' => [
               'minutes' => 5,
               'contains' => [$sessVar]
            ],
            'uploads/tmp/pdf' => [
                'minutes' => 10
            ]
        ];

        # loop through the directors
        foreach($files_list as $dir => $data) {

            # remove all session files that was last modified the specified minutes ago
            foreach(get_dir_file_info(WRITEPATH . $dir) as $file) {
                # clean date
                $date_modified = $file['date'];
                
                # set the time
                $current_time = time();

                # convert the time to normal
                $time_diff = round(($current_time - $date_modified) / 60);

                # only how the files that were lasb t modified 5 minutes ago
                if($time_diff > $data['minutes']) {
                    if(isset($data['contains'])) {
                        if(contains($file['name'], $data['contains'])) {
                            # delete the temp files
                            unlink($file['server_path']);
                        }
                    } else {
                        # delete the file
                        unlink($file['server_path']);
                    }
                }
            }

        }

        # print success message
        print date("l, F jS, Y h:ia") . " Temporary files deleted successfully.\n";

    }

}
?>