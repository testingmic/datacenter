<?php
namespace App\Controllers\cronjobs;

class HealthCron {

    /**
     * Initialize the cron job activity
     * 
     * Execute the various methods that are required in this class
     * 
     * @return Mixed
     */
    public function init() {

        // get the list of all health facilities
        // self::facilities();

        // update the db with the information loaded
        // self::data_dot_gov_facility();

        // update the diseases data
        self::diseases();

    }
    
    /**
     * Load health facilities from multiple sources
     * 
     * Prepare an array of multiple sources to get the right information
     */
    private static function facilities() {

        // set the sources
        $facility_sources = [
            'data_dot_gov_' => [
                'loop' => 40,
                'limit' => 100,
                'increment' => 100,
                'endpoint' =>  'https://data.gov.gh/api/action/datastore/search.json?resource_id=e83996f1-ae48-415f-9bf8-671332e85b70&limit={limit}&offset={offset}'
            ]
        ];        

        // init the cron activity
        print date("l, F jS, Y h:i:sa") . " - Loading facilities\n\n";

        // set the request options
        $options = [
            'max' => 10,
            'timeout' => 30,
            'verify' => false, 
            'connect_timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        // create the curl request object
        $client = \Config\Services::curlrequest();

        // set the file directory path
        $facilityDir = WRITEPATH . "data/health/facilities/";

        if( !is_dir($facilityDir) ) {
            mkdir($facilityDir, 0644, true);
        }

        // loop through the data sources
        foreach($facility_sources as $source => $facility) {

            // begin first facility
            print date("l, F jS, Y h:i:sa") . " - fetching facilities from {{$source}}.\n\n";

            // set the file name
            $filename = "{$facilityDir}" . $source . date("Y_m_d") . ".json";

            // set the offset
            $offset = 0;

            // init the result
            $result = [];

            // confirm if the file does already exists
            if( !file_exists($filename) ) {

                // extract data looping through incrementally
                for($i = 0; $i < $facility['loop']; $i++) {

                    // set the url link
                    $url_link = $facility['endpoint'];
                    $facility_endpoint = str_ireplace(["{offset}", "{limit}"], [$offset, $facility['limit']], $url_link);
                    
                    // make the curl request
                    $request = $client->request("GET", $facility_endpoint, $options);
                            
                    // // get the request body
                    $response = json_decode($request->getBody(), true)["result"]["records"] ?? [];

                    // // push the response
                    if (!empty($response) ) {
                        $result = array_merge($result, $response);
                    }
                                    
                    // increment the offset value by 100
                    $offset += $facility['increment'];
                }

                // if the result is not empty
                if( !empty($result) ) {

                    // today's file name to write
                    $fopen = fopen($filename, 'w');
                    fwrite($fopen, json_encode($result));
                    fclose($fopen);

                }

            }

            // print response
            print date("l, F jS, Y h:i:sa") . " - facilities data successfully loaded sourced from {{$source}}.\n\n";

        }

    }

    /**
     * Populate the data 
     * 
     * @return String
     */
    private static function data_dot_gov_facility() {

        // set the file directory path
        $facilityDir = WRITEPATH . "data/health/facilities/";

        // get the current api version
        $api_version = config('Api')->api_version;

        // set the country code
        $country_code = 'GH';
        $regions_count = 16;

        // set the file name
        $filename = "{$facilityDir}data_dot_gov_" . date("Y_m_d") . ".json";

        // confirm if the file exists
        if( is_file($filename) && file_exists($filename) ) {

            // convert the file content into an array
            $facilities = json_decode(file_get_contents($filename), true);

            // if the facilities list is not empty
            if( !empty($facilities) ) {

                // load the class
                $classname = "\\App\\Controllers\\".$api_version."\\HealthController";

                // confirm if the class actually exists
                if(class_exists($classname)) {
                    
                    // create a new class for handling the resource
                    $healthObj = new $classname();

                    // get regions list
                    $builder = $healthObj->db_model->db
                                        ->table('regions')
                                        ->where('country_code', $country_code)
                                        ->limit($regions_count);

                    $result = $builder->get();

                    // convert the list to an array
                    $regions_array = !empty($result) ? $result->getResultArray() : [];

                    // init the cron activity
                    print "\n" . date("l, F jS, Y h:i:sa") . " - Loading all facilities for the day.\n\n";

                    // loop through the content
                    foreach($facilities as $row_count => $facility) {

                        // generate the name slug
                        $name_slug = url_title($facility['facilityname'], '-', true);
                        $facility_type = strtolower($facility['type']);

                        // confirm if the item exists
                        $stmt = $healthObj->db_model->db
                                        ->table($healthObj->facility_table)
                                        ->select('id')
                                        ->where('name_slug', $name_slug)
                                        ->where('facility_type', $facility_type)
                                        ->whereNotIn('status', 'unverified')
                                        ->limit(1);

                        // set the value
                        $result = $stmt->get()->getResultArray();

                        // set the values for the facility
                        $data = [
                            'country_code' => $country_code,
                            'region_id' => array_data_column($regions_array, $facility['region']),
                            'district_name' => $facility['district'],
                            'name' => $facility['facilityname'],
                            'facility_type' => $facility_type,
                            'location' => $facility['town'],
                            'name_slug' => $name_slug
                        ];

                        // insert if empty
                        if( empty($result) ) {
                            $healthObj->add_facility($data);
                        } else {
                            $data['updated_at'] = date('Y-m-d H:i:s');
                            $healthObj->update_facility($data, $result[0]['id']);
                        }

                    }

                    // end activity
                    print date("l, F jS, Y h:i:sa") . " - all facilities record from {data_dot_gov_} successfully updated.\n\n";

                }
            
            }

        }

    }

    /**
     * Loop through the HTML Page and get the content
     * 
     * Clean the HMTL content and get the text only. Preformat the text before creating a file for it
     * 
     * @return Mixed
     */
    public static function diseases() {

        // get directory content
        $dir = WRITEPATH . "data/health/diseases_html/";
        $txt = WRITEPATH . "data/health/diseases_txt/";

        if (!is_dir($txt)) {
            mkdir($txt, 0644, true);
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0644, true);
        }

        $count = 0;
        
        // loop through the html pages list
        foreach (get_dir_file_info($dir, false) as $directory) {

            // proceed
            $htmlString = (string) file_get_contents($directory['server_path']);

            //add this line to suppress any warnings
            libxml_use_internal_errors(true);

            // if the string is not empty
            if (!empty($htmlString)) {

                $doc = new \DOMDocument();
                $doc->loadHTML($htmlString, LIBXML_NOEMPTYTAG);
                $xpath = new \DOMXPath($doc);

                $content = $xpath->evaluate('//div[@class="js-guide cf guide"]');
                $title = $xpath->evaluate('//h1');

                $sections = $xpath->evaluate('//div[@class="js-guide cf guide"]//*[contains(@class, "tab js-guide__section guide__section")]');

                $disease = null;
                $filename = null;
                foreach ($sections as $skey => $section) {
                    if (!empty($title[$skey])) {
                        $disease .= "TITLE---" . trim($title[$skey]->textContent) . PHP_EOL;
                        $filename = url_title(trim($title[$skey]->textContent), '-', true);
                    }
                    $disease .= "<{$section->tagName}>" . trim($section->textContent) . "</{$section->tagName}>";
                }

                if (!empty($disease)) {
                    print "Writing disease file {$filename}\n";
                    $fopen = fopen("{$txt}{$filename}.txt", 'w');
                    fwrite($fopen, $disease);
                    fclose($fopen);
                }

                $count++;
            }
        }

        // process the files log
        self::log_diseases($txt);

    }

    /**
     * Load the Txt File Downloaded and cleaned
     * 
     * Loop through all the txt files generated in the first instance and format it for db insertion
     * 
     * @return Bool
     */
    private static function log_diseases($txt) {

        // set the disease array
        $disease_array = [];
        $row = 0;

        // loop through the text files and get the information
        foreach (get_dir_file_info($txt, false) as $key => $file) {

            // open the file
            $handle = fopen($file['server_path'], "r");
            
            // if the file is found
            if ($handle) {

                while (($line = fgets($handle)) !== false) {
                    $line = trim($line);

                    // if the line is not empty
                    if (!empty($line)) {

                        // preset the description key
                        if(!isset($disease_array[$row]['description'])) {
                            $disease_array[$row]['description'] = null;
                        }

                        if (contains($line, ['TITLE'])) {
                            $disease_array[$row]['name'] = str_ireplace(['TITLE---'], [''], $line);
                            $disease_array[$row]['name_slug'] = url_title($disease_array[$row]['name']);
                        } elseif (contains($line, ['<div>'])) {
                            $disease_array[$row]['description'] .= htmlentities((str_ireplace(['<div>'], ['<div><h3>'], $line) . "</h3>"));
                        } else {
                            $word_count = str_word_count($line);

                            if ($word_count < 4) {
                                $disease_array[$row]['description'] .= htmlentities("<h4>" . ucwords($line) . "</h4>");
                            } else {
                                $disease_array[$row]['description'] .= htmlentities("<p>" . $line . "</p>");
                            }
                        }
                        $disease_array[$row]['status'] = 'active';
                    }
                }
                fclose($handle);
                $row++;
            }

        }

        // if the diseases list is not empty
        if(!empty($disease_array)) {

            // get the current api version
            $api_version = config('Api')->api_version;

            // load the class
            $classname = "\\App\\Controllers\\".$api_version."\\HealthController";

            // confirm if the class actually exists
            if(class_exists($classname)) {
                
                // create a new class for handling the resource
                $healthObj = new $classname();

                // init the cron activity
                print "\n" . date("l, F jS, Y h:i:sa") . " - Loading all diseases in the text file.\n\n";

                // loop through the content
                foreach($disease_array as $disease) {

                    // confirm if the item exists
                    $stmt = $healthObj->db_model->db
                                    ->table($healthObj->disease_table)
                                    ->select('id')
                                    ->where('name_slug', $disease['name_slug'])
                                    ->whereNotIn('status', 'unverified')
                                    ->limit(1);

                    // set the value
                    $result = $stmt->get()->getResultArray();

                    // modify the text a bit
                    $disease['description'] = html_entity_decode($disease['description']);
                    $disease['name'] = ucwords($disease['name']);
                    $disease['name_slug'] = strtolower($disease['name_slug']);

                    // insert if empty
                    if( empty($result) ) {
                        $healthObj->add_diseases($disease);
                    } else {
                        $disease['updated_at'] = date('Y-m-d H:i:s');
                        $healthObj->update_diseases($disease, $result[0]['id']);
                    }

                }

                // end activity
                print date("l, F jS, Y h:i:sa") . " - all diseases record successfully updated.\n\n";

            }
        }
        
    }

}
