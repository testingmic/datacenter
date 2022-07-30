<?php 
namespace App\Controllers\v1;

use App\Models\v1\AuthModel;

class AuthController {

    private $auth_model;
    private $token_expiry = 12;

    public function __construct()
    {
        $this->auth_model = new AuthModel;
    }

    /**
     * Process the user login procedure
     * 
     * @param       String  $params['username']
     * @param       String  $params['password']
     * 
     * @return Array
     */
    public function login(array $params) {

        try {

            $data = $this->auth_model
                        ->where([
                            'email' => $params['username'],
                            'status' => 'active'
                        ])->limit(1);

            $result = $data->get();

            $data = !empty($result) ? $result->getResultArray() : [];
            
            if(!empty($data)) {
                
                // verify the user password
                if(!password_verify($params['password'], $data[0]['password'])) {
                    return false;
                }

                // update the last login
                $this->auth_model->db->table('users')->update(['last_login_at' => date('Y-m-d H:i:s')], ['id' => $data[0]['id']], 1);

                // set the response
                $response['user'] = $this->user_data($data[0]['id'], $params['version']);

                // get the user token variable
                $response['token'] = $this->access_token($data[0]['id']);

                return $response;

            }

        } catch(\Exception $e) {
            return false;
        }

    }

    /**
     * Generate an access token for a user
     * 
     * @param Int       $userId
     * 
     * @return Array
     */
    public function access_token($userId) {

        // create the temporary accesstoken
        $token = random_string("alnum", 64);
        $expiry = date("Y-m-d H:i:s", strtotime("+{$this->token_expiry} hour"));

        // most recent query
        $recent = $this->previous_token($userId);

        // access
        $access = base64_encode("{$userId}:{$token}");

        // if within the last 10 minutes
        if($recent) {
            $access = $recent;
        }
        
        try {

            // if $recent is empty
            if(empty($recent)) {

                // delete all temporary tokens
                $this->auth_model->db
                        ->query("UPDATE users_tokens SET status = 'inactive'
                            WHERE (TIMESTAMP(expiry_timestamp) < CURRENT_TIME()) 
                            AND status = 'active' AND user_id = '{$userId}' LIMIT 100");

                // insert a new token
                $this->auth_model->db->table('users_tokens')
                        ->insert([
                            'user_id' => $userId,
                            'token' => $access,
                            'expiry_timestamp' => strtotime($expiry),
                            'expired_at' => $expiry
                        ]);
            }

        } catch(\Exception $e) {
            return 'Error generating access token';
        } 
        
        // return the access token information
        return $access;
    }

    /**
     * If the user's last key generated is within a 10 minutes span
     * Then deny regeneration
     * 
     * @param String    $userId      The userId to use in loading record
     * 
     * @return Bool
     */
    private function previous_token($userId) {

        // run a query
        $stmt = $this->auth_model->db
                    ->table('users_tokens')
                    ->where(['user_id' => $userId, 'status' => 'active'])
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get();
        
        $data = !empty($stmt) ? $stmt->getResultArray() : [];

        // get the time of creation
		$lastToken = !empty($data) ? $data[0]['expired_at'] : 0;
        
        // if the last update was parsed
		return (!empty($lastToken) && strtotime($lastToken) > time()) ? $data[0]['token'] : null;
	
    }

    /**
     * Get the Full employee data
     * 
     * @param   Int     $userId     The unique employee
     * @param   String  $version    The version of the api in play
     * 
     * @return Array
     */
    public function user_data($userId, $version) {

        try {
            
            // set the controller name
            $classname = "\\App\\Controllers\\".$version."\\UsersController";

            // create a new class for handling the resource
            $usersObject = new $classname();

            // return the user data
            return $usersObject->show([], $userId);

        } catch(\Exception $e) {
            return [];
        }

    }

    /**
     * Validate Access Token
     * 
     * @param       String  $authToken      This is the api token to validate
     * @param       String  $api_version    This is the current api version in play
     * 
     * @return Array
     */
    public function validate_token($authToken, $api_version) {
        
        // if the token does not contain the keyword Bearer then end the query
        if( !contains($authToken, ['Bearer'])) {
            return 'Invalid token parsed.';
        }

        // clean the token
        $authToken = trim(str_ireplace('Bearer', '', $authToken));
        
        // run a query
        $stmt = $this->auth_model->db
                    ->table('users_tokens')
                    ->where(['token' => $authToken, 'status' => 'active'])
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get();
        
        // get the response data
        $data = !empty($stmt) ? $stmt->getResultArray() : [];
        
        // if the token was not found then end the query
        if(empty($data)) {
            return 'Invalid token parsed.';
        }

        // get the time of creation
		$lastToken = !empty($data) ? $data[0]['created_at'] : 0;
        
        // confirm if the access token has not yet expired
		if(!empty($lastToken) && (strtotime($lastToken) + (60 * 60 * $this->token_expiry)) >= time()) {
            
            try {
                
                // update the last login
                $this->auth_model->db->table('users')->update(['last_login_at' => date('Y-m-d H:i:s')], ['id' => $data[0]['user_id']], 1);

                // return the user data
                return $this->user_data($data[0]['user_id'], $api_version);;

            } catch(\Exception $e) {
                // remove the access token from the system
                $this->auth_model->db->query("UPDATE users_tokens SET status = 'inactive' WHERE token = '{$authToken}' AND status='active' LIMIT 1");

                // return error message
                return 'Sorry! You cannot use this api key because the user does no exist on the system.';
            }

        } else {
            return 'The token submitted has expired. Login to generate a new token.';
        }

    }

}
?>