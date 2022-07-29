<?php 
namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController {

    use ResponseTrait;

    /**
     * Validate the User Login Procedures
     * 
     * @return Array
     */
    public function login($params = []) {

        // get the variables
        $vars = !empty($params) ? $params : $this->request->getVar();

        // validate data
        $validate = $this->process_login($vars);

        // set the data to return
        return $this->respond($validate);
    }

    /**
     * Format the credentials and perform the request
     * 
     * @return Array
     */
    private function process_login($params) {

        $data = [];
        $data['code'] = 405;

        $params = is_object($params) ? (array) $params : $params;

        if(empty($params['username']) || empty($params['password'])) {
            $data['data'] = 'Sorry! The username and password are required.';
        }

        elseif( is_email(null, $params['username'], 'username') !== true ) {
            $data['data'] = 'A valid email is required for the username.';
        } else {

            // get the current api version
            $version = config('Api')->api_version;

            // append the api version
            $params['version'] = $version;

            // set the controller name
            $classname = "\\App\\Controllers\\".$version."\\AuthController";

            // create a new class for handling the resource
            $authObject = new $classname();
            $loginCheck = $authObject->login($params);

            // set the additional parameters
            $data['data'] = !empty($loginCheck) ? $loginCheck : 'Invalid username and/or password.';
            $data['code'] = !empty($loginCheck) ? 200 : 203;
        }

        return $data;

    }

    /**
     * Logout the user from the system
     * 
     * @return Array
     */
    public function logout(array $params = []) {

        // log the user logout request

        // unset all the session data
        session()->remove([
            'isLockedOut', 'isLoggedIn', '_userId', '_userInfo', '_accessToken'
        ]);
        
        // return success
        return $this->respond([
            'code' => 200,
            'data' => 'You are successfully logged out'
        ]);

    }
    
}
?>