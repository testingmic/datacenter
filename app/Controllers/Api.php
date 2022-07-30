<?php

namespace App\Controllers;

use App\Models\ApiModel;
use CodeIgniter\API\ResponseTrait;

class Api extends BaseController {

    use ResponseTrait;

    protected $_userId;
    protected $accessCheck = [];
    protected $_userData;

    protected $response_code = 200;
    protected $request;
    protected $api_model;
    protected $class_method;
    protected $endpoints;
    protected $primaryKey;
    protected $inner_url;
    protected $req_method;
    protected $req_params;
    protected $default_params;
    protected $request_endpoint;
    protected $global_limit = 250;
    protected $AuthorizationToken;
    protected $accepted_endpoints;

    // keys to exempt when processing the request parameter keys
    protected $keys_exempted = ["ip_address", "ci_session", "access_token", "limit", "offset"];

    // keys to bypass when checking for the csrf_token
    protected $bypass_csrf_token_list = ['_logout', 'ajax_cronjob'];

    public function __construct() {

        $this->request = \Config\Services::request();
        $this->api_model = new ApiModel;

        // api configuration file
        $apiConfig = config('Api');

        // set the api global variable
        $this->allowed_methods = $apiConfig->allowed_methods;
        $this->api_version = $apiConfig->api_version;
        $this->available_versions = $apiConfig->versions_list;

    }

    /**
     * Receive the incoming request and process it.
     * 
     * It will validate the Api Keys, Request Endpoint & Request Parameters
     * The resulting data will be processed and returned as a JSON
     * 
     * @param String    $version_inner
     * @param String    $resource_endpoint
     * @param String    $resource
     * @param String    $primary_key
     * 
     * @return Array
     */
    public function index($init = null, string $version_inner = null, $parent = null, $resource = null, $primary_key = null) {

        // get the request method
        $this->req_method = strtoupper($_SERVER['REQUEST_METHOD']);

        // run a query to check if the user has parsed a version
        if(in_array($version_inner, $this->available_versions)) {
            $this->api_version = $version_inner;
            $version_inner = $parent;
            $parent = $resource;
            $resource = $primary_key;
        } else {
            $primary_key = $resource;
            $resource = $parent;
            $parent = $resource;
            $resource = $primary_key;
        }

        // set the full endpoint url and breakdown
        $this->request_endpoint = trim($version_inner, '/');
        $this->inner_url = trim($version_inner, '/');

        // set the primary key
        $this->primaryKey = $primary_key;

        // get the request path
        $this->req_path = $this->request->getPath();

        // get the variables
        $post_get_params = $this->request->getVar();
        $post_get_params = array_map('esc', (array) $post_get_params);
        
        // set the files list
        $files_list = $this->request->getFiles();

        // merge the post, get and files items
        $t_params = array_merge($post_get_params, $files_list);

        // get the user agent
        $userAgent = $this->request->getUserAgent();

        // set the user agent
        $this->user_agent = $userAgent->__toString();
        $this->ip_address = $this->request->getIPAddress();

        // get the authorization header parsed token
        $this->AuthorizationToken = $this->request->header('Authorization');

        // $outer_url check
        if(!empty($primaryKey) && !preg_match("/^[0-9]+$/", $primaryKey)) {
            return $this->respond($this->requestOutput(400), 400);
        }

        // if the request is either put or delete
        if(in_array($this->req_method, ["PUT", "DELETE"]) && empty($this->primaryKey)) {
            return $this->respond($this->requestOutput(400), 400);
        }

        // set the method to use
        $this->class_method = empty($parent) ? "index" : $parent;

        // set the default parameters
        $this->req_params = $t_params;

        /** Process the API Request */
        $make_request = $this->initRequest($t_params);

        // set the data to return
        return $this->respond($make_request, $this->response_code);
        
    }

    /**
     * Load the endpoint information for processing
     * 
     * @param Array $params         This is an array of the parameters parsed in the request
     * @param String $method        The request method
     * 
     * @return Array
     */
    private function initRequest($params) {

        /** Split the endpoint */
        $expl = explode("/", $this->request_endpoint);
        $endpoint = isset($expl[1]) && !empty($expl[1]) ? strtolower($this->request_endpoint) : null;

        // trim the edges
        $endpoint = trim($endpoint, '/');

        /* Run a check for the parameters and method parsed by the user */
        $paramChecker = $this->keysChecker($params);

        // if an error was found
        if( $paramChecker['code'] !== 100) {
            // print the json output
            return $paramChecker;
        }

        // run the request
        $ApiRequest = $this->requestHandler($params);
        
        // remove access token if in
        if(isset($params["access_token"])) {
            unset($params["access_token"]);
        }

        // return out the response
        return $ApiRequest;
    }

    /**
     * This method checks the params parsed by the user
     * 
     *  @param {array} $params  This is the array of parameters sent by the user
    */
    private function keysChecker(array $params) {
        
        /** Load the Endpoint JSON File **/
        $db_req_params = json_decode(file_get_contents(WRITEPATH . 'data/endpoints.json'), true);
        
        /**
         * check if there is a valid request method in the endpoints
         * 
         * Return an error / success message with a specific code
         */
        if( !isset($db_req_params[$this->request_endpoint][$this->req_method]) ) {
            
            // set the code to return 
            $code = empty($this->inner_url) ? 200 : 404;

            // set the response code
            $this->response_code = $code;

            // return error if not valid
            return $this->requestOutput($code, $this->outputMessage($code));

        } elseif( !in_array($this->class_method, ['index']) && !isset($db_req_params[$this->request_endpoint][$this->req_method][$this->class_method]) ) {
            
            // set the code to return 
            $code = empty($this->inner_url) ? 200 : 404;

            // set the response code
            $this->response_code = $code;

            // return error if not valid
            return $this->requestOutput($code, $this->outputMessage($code));
        } else {
            
            // set the acceptable parameters
            $accepted =  $db_req_params[$this->request_endpoint][$this->req_method][$this->class_method] ?? [];

            // set the endpoint sub requests
            $this->accepted_endpoints = $db_req_params[$this->request_endpoint][$this->req_method];

            // confirm that the parameters parsed is not more than the accpetable ones
            if( empty(array_keys($accepted)) ) {
                // return all tests parsed
                return $this->requestOutput(100, $this->outputMessage(200));
            }

            else {
                
                // get the keys of all the acceptable parameters
                $endpointKeys = array_keys($accepted);
                $errorFound = [];
                
                // confirm that the supplied parameters are within the list of expected parameters
                foreach($params as $key => $value) {
                    if(!in_array($key,  $this->keys_exempted) && !in_array($key, $endpointKeys)) {
                        // set the error variable to true
                        $errorFound[] = $key;                   
                        // break the loop
                        break;
                    }
                }

                // if an invalid parameter was parsed
                if($errorFound) {
                    // return invalid parameters parsed to the endpoint
                    return $this->requestOutput(405, [
                        'accepted' => ["parameters" => $accepted],
                        'invalids' => $errorFound
                    ]);
                } else {

                    /* Set the required into an empty array list */
                    $required = [];
                    $required_text = [];
                    $validate_rules = [];

                    // loop through the accepted parameters and check which one has the description 
                    // required and append to the list
                    foreach($accepted as $key => $value) {

                        // evaluates to true
                        if( strpos($value, "required") !== false) {
                            $required[] = $key;
                            $required_text[] = $key;
                        }

                        // if the rules are not empty
                        if(!empty($value) && isset($params[$key])) {

                            // validate all the various data parsed
                            $param_value = str_ireplace('|', '&', $value);
                            parse_str($param_value, $rules);

                            // if the rules for the endpoint is not empty
                            if(!empty($rules)) {
                                // permform validation
                                $val = validate_value($rules, $params[$key], $key);
                                
                                // validation
                                if(!empty($val)) {
                                    $validate_rules[] = $val;
                                }
                            }
                        }

                    }

                    if(!empty($validate_rules)) {
                        $rules = [];
                        foreach($validate_rules as $value) {
                            foreach($value as $item) {
                                $rules[] = $item;
                            }
                        }

                        return $this->requestOutput(402, ['validation_errors' => $rules]);
                    }

                    /**
                     * Confirm the count using an array_intersect
                     * What is happening
                     * 
                     * Get the keys of the parsed parameters
                     * count the number of times the required keys appeared in it
                     * 
                     * compare to the count of the required keys if it matches.
                     * 
                     */
                    $confirm = (count(array_intersect($required, array_keys($params))) == count($required));
                    
                    // If it does not evaluate to true
                    if(!$confirm) {
                        // return the response of required parameters
                        return $this->requestOutput(401, ['required' => $required_text]);
                    } else {
                        // return all tests parsed
                        return $this->requestOutput(100, $this->outputMessage(200));
                    }

                }

            }

        }

    }

    /**
     * Outputs to the screen
     * 
     * @param Int                   $code   This is the code after processing the user request
     * @param Mixed{string/array}    $data   Any addition data to parse to the user
     */
    private function requestOutput($code, $message = null) {
        // format the data to return
        $data = [ 'code' => $code ];

        // unset code from data
        if(isset($message['code'])) {
            unset($message['code']);
        }

        ( !empty($message) ) ? ($data['data'] = $message) : $data['data'] = [];

        !empty($this->req_params) ? ($data['params'] = $this->req_params) : null; 

        return $data;
    }

    /**
     * This is the output message based on the code
     * 
     * @param Int $code
     * 
     * @return String
     */
    private function outputMessage($code) {

        $description = [
            200 => "The request was successfully executed and returned some results.",
            201 => "The request was successful however, no results was found.",
            205 => "The record was successfully updated.",
            202 => "The data was successfully inserted into the database.",
            203 => "No Content Found.",
            400 => "Invalid request method parsed.",
            401 => "Sorry! Please ensure all required fields are not empty.",
            402 => "Variable validation errors found.",
            404 => "Page not found.",
            405 => "Invalid parameters was parsed to the endpoint.",
            100 => "All tests parsed",
            500 => "The request method does not exist.",
            501 => "Sorry! You do not have the required permissions to perform this action.",
            600 => "Sorry! Your current subscription does not grant you permission to perform this action.",
            700 => "Unknown request parsed",
            999 => "An error occurred please try again later",
            1000 => "Blocked!!! CSRF Attempt"
        ];
        
        return $description[$code] ?? $description[700];
    }

    /**
     * This handles all requests by redirecting it to the appropriate
     * Controller class for that particular endpoint request
     * 
     * @param stdClass $params         - This the array of parameters that the user parsed in the request
     * 
     * @return  Array
     */
    private function requestHandler() {

        // preset the response
        $code = 501;
        $params['_apiRequest'] = false;
        $result = ['result' => $this->outputMessage(501)];

        // set the parameters
        $authorized = false;
        $params = $this->req_params;
        
        // if the authorization token is not empty
        if( !empty($this->AuthorizationToken) || !empty($params['access_token']) ) {

            // set the authorization token
            $token = !empty($params['access_token']) ? "Bearer {$params['access_token']}" : $this->AuthorizationToken->getValue();
            
            // set the controller name
            $classname = "\\App\\Controllers\\".$this->api_version."\\AuthController";

            // confirm if the class actually exists
            if(class_exists($classname)) {

                // create a new class for handling the resource
                $authObject = new $classname();
                $validate = $authObject->validate_token($token, $this->api_version);

                // set the result content if the validation was successful
                $authorized = (bool) is_array($validate);
                $result['result'] = $validate;
                $this->response_code = is_array($validate) ? 200 : 401;

                // set the user information as a variable for the request
                if( is_array($validate) ) {
                    $params['_userData'] = $validate;
                }

                // if an api request
                $params['_apiRequest'] = true;
            }

        }       

        // reqest made via api request
        $params['remote'] = (bool) isset($apiKeyValidation);

        // set the default limit to 1000
        $params['_limit'] = isset($params['limit']) ? (int) $params['limit'] : $this->global_limit;
        
        // if the user is authorized to make the query
        if( !empty($authorized) ) {

            // set the classname
            $classname = "\\App\\Controllers\\".$this->api_version."\\".ucfirst($this->inner_url)."Controller";
            
            // confirm if the class actually exists
            if(class_exists($classname)) {

                // create a new class for handling the resource
                $classObject = new $classname($this->accepted_endpoints);
                            
                // confirm that there is a method to process the resource endpoint
                if(method_exists($classObject, $this->class_method)) {

                    // set the method to load
                    $method = $this->class_method;
                    
                    // set additional parameters
                    $params['user_agent'] = $this->user_agent;
                    $params['ip_address'] = $this->ip_address;
                    
                    // convert the response into an arry if not already in there
                    $request = $classObject->$method($params, $this->primaryKey);
                    
                    // set the response code to return
                    $code = is_array($request) && isset($request['code']) ? $request['code'] : 200;
                                
                    // set the result
                    $result =  is_array($request) && isset($request["data"]) ? ($request["data"]['result'] ?? $request["data"]) : $request;

                    // No content to display
                    $this->response_code = empty($result) ? 200 : $code;
                    
                }

            }
            
        }

        // output the results
        return $this->requestOutput($code, $result);

    }

}
?>