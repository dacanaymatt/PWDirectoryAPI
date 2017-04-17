<?php
if (!defined('SRKEY')){echo'This file can only be called via the main index.php file, and not directly';exit();}

/**
 * Demo
 *
 * @author Vince Urag
 */
class Users extends SR_Controller {

    public function __construct() {
        parent::__construct();
        $this->load("model");
        $this->load('jwt');
        $this->load('renewpassword');
        $this->load('mail');
    }

    public function get_index() {
        $auth = json_decode($this->jwt->check(), true);
        // $auth['authorization'] = "authorized";
        if($auth['authorization'] == "authorized") {
            $result = $this->model->getUsers();
            // var_dump($result);
            $this->sendResponse($result, HTTP_Status::HTTP_OK);
        } else {
            $this->sendResponse(array("error" => "unauthorized"), HTTP_Status::HTTP_UNAUTHORIZED);
        }

    }

    //changepassword
    public function post_edit() {

        $auth = json_decode($this->jwt->check(), true);
        // $auth['authorization'] = "authorized";
        if($auth['authorization'] == "authorized") {
        
            $json_request = $this->getJsonData();

            $username = $auth['data']['username'];
            $newPassword = $json_request['newPassword'];
            $oldPassword = $json_request['oldPassword'];


            if($this->model->editUser(TABLE_USERS, $oldPassword, $newPassword, $username)) {
                $this->sendResponse(array("header" => "success", "body" => "Password change successful."), HTTP_Status::HTTP_OK);
            } else {
                $this->sendResponse(array("header" => "fail", "body" => "Password change failed."), HTTP_Status::HTTP_BAD_REQUEST);
            }
        }
        else{
            $this->sendResponse(array("header" => "unauthorized"), HTTP_Status::HTTP_UNAUTHORIZED);   
        }

    }

    public function post_index() {
        $myArray = $this->getJsonData();

        $resArray = array("title" => "Hello",
                        "details" => array(
                            "choice 1" => "World",
                            "choice 2" => "Philippines"
                        )
                    );

        if($myArray['success'] == "yes") {
            $this->sendResponse($resArray, 200);
        } else {
            $this->sendResponse(array("title" => "error"), HTTP_Status::HTTP_NOT_FOUND);
        }
    }

    //login
    public function post_signin() {
        $json_request = $this->getJsonData();

        $username = $json_request['username'];
        $password = $json_request['password'];

        if($this->model->signIn($username, $password)){
            $token_payload = array("username" => $username, "password" => $password);

            $token = $this->jwt->generate_token($username, $token_payload);

            $response = array(
                'header' => 'success',
                'body' => [
                'name' => $username,
                'password' => $password,
                'premium' => $this->model->isPremium($username),
                'meta' => [
                'token' => $token  ]]
            );
    		$this->sendResponse($response, HTTP_Status::HTTP_OK);
        }
        else {
            $response = array(
                'header' => 'fail',
                'body' => [
                'name' => $username,
                'password' => $password,
                'premium' => $this->model->isPremium($username),
                'meta' => [
                'token' => 0  ]]
            );
            $this->sendResponse($response, HTTP_Status::HTTP_UNAUTHORIZED);
        }
        
        //$this->sendResponse($response, HTTP_Status::HTTP_CREATED);
    }

    public function get_getusers() {
        $json_request = $this->getJsonData();

        $arr = $this->model->getUsers();
        $this->sendResponse ($arr, HTTP_Status::HTTP_CREATED);

    }

     //create account
    public function post_create() {
        $json_request = $this->getJsonData();

        //create an account
        $res = $this->model->createAccount($json_request);

        if($res['result'] == 1) {
            
            foreach ($res as $key => $value){
                $$key = $value;
            }

            $token_payload = array("username" => $username, "password" => $password);

            $token = $this->jwt->generate_token($username, $token_payload);

            $json_response = array(
                'header' => 'success',
                'body' => [
                'details' => 'Account created successfully.',
                'firstname' => $firstname,
                'username' => $username,
                'password' => $password,
                'premium' => $this->model->isPremium($username),
                'meta' => [
                'token' => $token  ]]
            );
    
        
            $this->sendResponse($json_response, HTTP_Status::HTTP_CREATED);
        
        } else if($res['result'] == 0) {
          $json_response = array(
                  'header' => 'fail',
                  'body' => ['details' => 'Username is already existing. Please try another username.',
                            ]
                );

          $this->sendResponse($json_response, HTTP_Status::HTTP_BAD_REQUEST);

        } else if($res['result'] == -1) {
          $json_response = array(
                  'header' => 'fail',
                  'body' => ['details' => 'Email address belongs to an existing account. Please try another email address.',
                            ]
                );

          $this->sendResponse($json_response, HTTP_Status::HTTP_BAD_REQUEST);

        }
    }

    //forgotpassword
    public function post_forgotpassword() {

        $json_request = $this->getJsonData();

        $username = $json_request['username'];
        $email = $json_request['email'];

        if($this->model->isAccountExisting($username, $email)){

            //generate temporary password that will be sent to the user's associated email address
            $secretKey = $this->renewpassword->getSecretKey();

            //send email

            if($this->model->sendMail($email, $secretKey)){
                if($this->model->setSecretKey($username, $secretKey, 1)){
                    $json_response = array(
                        'header' => 'success',
                        'body' => [
                        'details' => 'An e-mail containing the temporary password key has been sent to '. $email. '.']
                    );
                }
            }

            $this->sendResponse($json_response, HTTP_Status::HTTP_OK);

        }
        else{
            $json_response = array(
                'header' => 'fail',
                'body' => [
                    'details' => 'Username and email do not match the records. Please double check.']
            );

            $this->sendResponse($json_response, HTTP_Status::HTTP_BAD_REQUEST);

        }

    }

    public function post_verifymail() {

        $json_request = $this->getJsonData();

        $username = $auth['data']['username'];
        $email = $json_request['email'];

        if($this->model->isAccountExisting($username, $email)){

            //generate SECRET KEY that will be sent to the user's associated email address
            $secretKey = $this->renewpassword->getSecretKey();

            //send email

            if($this->model->verifyMail($email, $secretKey, $username)){
                if($this->model->setSecretKey($username, $secretKey, 0)){
                    $json_response = array(
                        'header' => 'success',
                        'body' => [
                        'details' => 'An e-mail containing the verification key has been sent to '. $email. '.']
                    );
                }
            }

            $this->sendResponse($json_response, HTTP_Status::HTTP_OK);

        }
        else{
            $json_response = array(
                'header' => 'fail',
                'body' => [
                    'details' => 'Username and email do not match the records. Please double check.']
            );

            $this->sendResponse($json_response, HTTP_Status::HTTP_BAD_REQUEST);

        }

    }

    public function post_confirmverifymail() {

        $auth = json_decode($this->jwt->check(), true);

        if($auth['authorization'] == "authorized") {

            $json_request = $this->getJsonData();

            $username = $auth['data']['username'];
            $secretKey = $json_request['secretKey'];

            if($this->model->confirmVerifyMail($secretKey, $username)){

                $json_response = array(
                    'header' => 'success',
                    'body' => [
                    'details' => 'Email successfuly verified.']
                );

                $this->sendResponse($json_response, HTTP_Status::HTTP_OK);
            }
            else{
                $json_response = array(
                    'header' => 'fail',
                    'body' => [
                    'details' => 'Secret key or username provided is invalid.']
                );


                $this->sendResponse($json_response, HTTP_Status::HTTP_BAD_REQUEST);
            }
        }
        else {
            //STOPPED HERE - PUT AUTH ON EACH EMAIL ADDRESS VERIFICATION?
        }


    }

    //confirm secret key to reset password
    public function post_confirmforgotpassword() {

        $json_request = $this->getJsonData();

        $username = $json_request['username'];
        $secretKey = $json_request['secretKey'];
        $password = $json_request['password'];

        if($this->model->resetPassword($username, $secretKey, $password)){

            $json_response = array(
                'header' => 'success',
                'body' => [
                'details' => 'Password successfully changed.']
            );

            $this->sendResponse($json_response, HTTP_Status::HTTP_OK);
        }
        else{
            $json_response = array(
                'header' => 'fail',
                'body' => [
                'details' => 'Secret key or username provided is invalid, or the user account entered did not request a secret key.']
            );


            $this->sendResponse($json_response, HTTP_Status::HTTP_BAD_REQUEST);
        }


    }


    //vote
    public function post_vote(){

        $auth = json_decode($this->jwt->check(), true);

        if($auth['authorization'] == "authorized") {

            $json_request = $this->getJsonData();

            $username = $auth['data']['username'];
            //$username = $json_request['username'];
            $placeid = $json_request['placeid'];
            $overall_vote = $json_request['overall_vote'];

            $amenity_stairs = $json_request['amenity_stairs'];
            $amenity_walkways = $json_request['amenity_walkways'];
            $amenity_corridors = $json_request['amenity_corridors'];
            $amenity_doors = $json_request['amenity_doors'];
            $amenity_toilets = $json_request['amenity_toilets'];
            $amenity_elevators = $json_request['amenity_elevators'];
            $amenity_ramps = $json_request['amenity_ramps'];
            $amenity_parking = $json_request['amenity_parking'];
            $amenity_buzzers = $json_request['amenity_buzzers'];
            $amenity_handrails = $json_request['amenity_handrails'];
            $amenity_thresholds = $json_request['amenity_thresholds'];
            $amenity_floor = $json_request['amenity_floor'];
            $amenity_fountains = $json_request['amenity_fountains'];
            $amenity_phones = $json_request['amenity_phones'];
            $amenity_seating = $json_request['amenity_seating'];
            $place_type = $json_request['place_type'];
            $place_city = $json_request['place_city'];
            $place_name = $json_request['place_name'];
            $place_address = $json_request['place_address'];

            
            if($this->model->vote($username, $placeid, $place_name, $place_address, $place_type, $place_city, $overall_vote, $amenity_stairs, $amenity_walkways, $amenity_corridors, $amenity_doors, $amenity_toilets, $amenity_elevators,  $amenity_ramps, $amenity_parking, $amenity_buzzers, $amenity_handrails, $amenity_thresholds, $amenity_floor, $amenity_fountains, $amenity_phones, $amenity_seating) && $this->model->calculateScore($placeid) && $this->model->checkConsecutive($placeid)){
                $json_response = array(
                    'header' => 'success',
                    'body' => [
                        'details' => 'Your vote has successfully been placed in our records.']
                );

                // $this->sendResponse($json_response, HTTP_Status::HTTP_CREATED);
				echo "Your vote has successfully been placed in our records.";

            }
            else {
                $json_response = array(
                    'header' => 'fail',
                    'body' => [
                        'details' => 'your vote has not been placed in our records. Please try again.']
                );


                $this->sendResponse($json_response, HTTP_Status::HTTP_BAD_REQUEST);
            }
        }
        else {
            $this->sendResponse(array("header" => "unauthorized"), HTTP_Status::HTTP_UNAUTHORIZED);
        }
    }

    public function post_view() {

	    $json_request = $this->getJsonData();

	    $placeid = $json_request['placeid'];

	    $stats = $this->model->view($placeid);

	    // $upvotes = $stats['upvotes'];
	    // $downvotes = $stats['downvotes'];
	    // $totalvotes = $stats['votes'];

	    $json_response = $stats;

	    $this->sendResponse ($json_response, HTTP_Status::HTTP_CREATED);

    }

    public function post_retrieve() {

        $auth = json_decode($this->jwt->check(), true);
        // $auth['authorization'] = "authorized";
        if($auth['authorization'] == "authorized") {

            $json_request = $this->getJsonData();

            $username = $auth['data']['username'];
            $placeid = $json_request['placeid'];

            $json_response = $this->model->retrieve($username, $placeid);

            $this->sendResponse ($json_response, HTTP_Status::HTTP_OK);
        }else {
            $this->sendResponse(array("header" => "unauthorized"), HTTP_Status::HTTP_UNAUTHORIZED);
        }

    }

    public function post_getLeaderBoards() {

        $auth = json_decode($this->jwt->check(), true);
        // $auth['authorization'] = "authorized";
        if($auth['authorization'] == "authorized") {

            
            if($this->model->isPremium($auth['data']['username'])) {

                $json_request = $this->getJsonData();

                $placeFilter = $json_request['placeFilter'];
                $placeType = $json_request['placeType'];
                $placeOrder = $json_request['placeOrder'];

				//modified
                $json_response = array('places' =>  $this->model->getLeaderBoards($placeFilter, $placeType, $placeOrder)); 

                $this->sendResponse ($json_response, HTTP_Status::HTTP_OK);    
            }
            else {
                $this->sendResponse(array("header" => "unauthorized"), HTTP_Status::HTTP_UNAUTHORIZED);    
            }

            
        } else {
            $this->sendResponse(array("header" => "unauthorized"), HTTP_Status::HTTP_UNAUTHORIZED);
        }

    }

    public function post_selectDistinctTypes() {

        $auth = json_decode($this->jwt->check(), true);
        // $auth['authorization'] = "authorized";
        if($auth['authorization'] == "authorized") {
	
			//modified
            $json_response = array('types' =>   $this->model->getDistinctTypes()); 		

            $this->sendResponse ($json_response, HTTP_Status::HTTP_OK);    
            
        } else {
            $this->sendResponse(array("header" => "unauthorized"), HTTP_Status::HTTP_UNAUTHORIZED);
        }

    }

    public function post_selectDistinctCities() {

        $auth = json_decode($this->jwt->check(), true);
        // $auth['authorization'] = "authorized";
        if($auth['authorization'] == "authorized") {
			//modified
            $json_response = array('cities' =>   $this->model->getDistinctCities()); 		

            $this->sendResponse ($json_response, HTTP_Status::HTTP_OK);    
            
        } else {
            $this->sendResponse(array("header" => "unauthorized"), HTTP_Status::HTTP_UNAUTHORIZED);
        }

    }
    
    //TESTING PURPOSES ONLY, CHECK CONSECUTIVE IS CALLED AFTER A USER VOTES (SEE post_vote())

    public function post_checkConsecutive() {
        
        $json_request = $this->getJsonData();

        $placeid = $json_request["placeid"];

        $this->model->checkConsecutive($placeid); 
            $json_response = array(
                    'header' => 'success'
                );
        

        $this->sendResponse($json_response, HTTP_STATUS::HTTP_OK);
    }

    public function post_testScore() {
        
        $json_request = $this->getJsonData();

        $placeid = $json_request["placeid"];

        if($this->model->calculateScore($placeid)) {
            $json_response = array(
                    'header' => 'success'
                );
        } 
        else {
            $json_response = array(
                    'header' => 'failure'
                );
        }
            

        $this->sendResponse($json_response, HTTP_STATUS::HTTP_OK);
        
    }
}