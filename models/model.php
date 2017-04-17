<?php

/**
 * Demo model
 *
 * @author Vince Urag
 */
class Model extends SR_Model {


    public function __construct() {
        parent::__construct();

        $this->load("wilsoncalcu");
    }

    public function getUsers() {
        // return $this->db->get_row("users", array("id"=>1, "user_name"=>"V"));
        // return $this->db->get_value("users", "password", array("id"=>1,"user_name"=>"V"));
        // return $this->db->update_row("users", array("user_name"=>"Kabedng", "password" => "new_pass"), array("id"=>2));
        // return $this->db->has_row("users", array("id"=>1, "user_name"=>"V"));\
        // return $this->db->insert_row("users", array("user_name"=>"from insert", "password"=>"testing"));
        return $this->db->exec("SELECT * FROM users");
    }

    public function editUser($name, $oldpassword, $newpassword, $id) {

        $filterarr = array(TABLE_USERS_USERID => $id, TABLE_USERS_PASSWORD => $oldpassword);

            //check first if the old password is correct before changing the password
            if($this->db->has_row(TABLE_USERS, $filterarr)){
                return $this->db->update_row(TABLE_USERS, array(TABLE_USERS_PASSWORD => $newpassword), array(TABLE_USERS_USERID => $id));    
            }
            else{
                return false;
            }
        }

    public function createAccount($myArray) {

      $username = array(TABLE_USERS_USERID => $myArray['username']);
      $email = array(TABLE_USERS_EMAIL => $myArray['email']);
      $encrypted_pass = hash('SHA512', $myArray['password']);

      //check if the email is already registered
      if($this->db->has_row(TABLE_USERS, $email)) {
            $data['result'] = -1;
      }

      //check if the email is already registered
      else if($this->db->has_row(TABLE_USERS, $username)) {
            $data['result'] = 0;
      } else {
            $insert = array(
              TABLE_USERS_USERID => $myArray['username'],
              TABLE_USERS_PASSWORD => $encrypted_pass,
              TABLE_USERS_PREMIUM => 0,
              TABLE_USERS_FIRSTNAME => $myArray['firstname'],
              TABLE_USERS_LASTNAME => $myArray['lastname'],
              TABLE_USERS_EMAIL => $myArray['email'],
            );

            //insert the data if the $insert variable is not empty
            if($this->db->insert_row(TABLE_USERS, $insert)) {
              $data['username'] = $myArray['username'];
              $data['password'] = $encrypted_pass;
              $data['firstname'] = $myArray['firstname'];
              $data['premium'] = 0;

              $data['result'] = 1;
            } else {
              $data['result'] = 0;
            }
      }
      //pass all the data retrieved
      return $data;
    }

    public function signIn($username, $password) {

        $encrypted_pass = hash('SHA512', $password);

        $credentials = array(TABLE_USERS_USERID => $username, TABLE_USERS_PASSWORD => $encrypted_pass);

        //check if usernamd and password are inside the database, and match each other
        if($this->db->has_row(TABLE_USERS, $credentials)){
            return true;
        }
        else{
            return false;
        }
    }

    public function isAccountExisting($username, $email){

        $credentials = array(TABLE_USERS_USERID => $username, TABLE_USERS_EMAIL => $email);

        //check if email is associated with the right username
        if($this->db->has_row(TABLE_USERS, $credentials)){
            return true;
        }
        else{
            return false;
        }
    }

    public function sendMail($email_address, $secretKey) {
      //message to be sent to
      $to = $email_address;
      //message of the mail to be sent
      $message = "<html><body>";
      $message .= "<h1>Good day!</h1> \n <p>Someone requested a new password for your account.</p>\n\n <h2>Secret Key:
                  <b><font color='red'>" . $secretKey . "</font></b></h2>
                  \n\n <p>The text above will serve as your secret key. Upon launching of the application, Click on the 'Forgot Password' page, and click on 'Enter Secret Key'. Enter your username, your secret key, and your new password, to be entered twice for security measures.</p>

                  \n <p>If you do not remember requesting this, please ignore this message, and change your password.</p>\n\n\n <br />
                  <br /> <p>Thank you!</p>\n<p><b>PWDirectory Support Team</b></p>";
      $message .= "</body></html>";

      //subject of the mail to be sent
      $subject = "PWDirectory Mobile Application - Password Reset";

      //headers of the mail to be sent
      $headers = 'From: PWDirectory Support Team <help.pwdirectory@gmail.com>' . "\r\n" .
                  'Reply-To: PWDirectory Support Team <help.pwdirectory@gmail.com>' . "\r\n" .
                  'Reply-Path: PWDirectory Support Team <help.pwdirectory@gmail.com>' . "\r\n" .
                  'MIME-Version: 1.0' . "\r\n" .
                  "Organization: PWDirectory Support Team" . "\r\n" .
                  "X-Priority: 3" . "\r\n" .
                  'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                  'X-Mailer: PHP/' . phpversion();

      return mail($to, $subject, $message, $headers);
    }

    public function setSecretKey($username, $secretKey, $mode) {

        //change the secret kety of the user to enable password change
         if($mode == 1) {
            return $this->db->update_row(TABLE_USERS, array(TABLE_USERS_SECRET_KEY_PASSWORD => $secretKey), array(TABLE_USERS_USERID => $username));
         }
         else{
            return $this->db->update_row(TABLE_USERS, array(TABLE_USERS_SECRET_KEY_EMAIL => $secretKey), array(TABLE_USERS_USERID => $username));
         }
         
    }


    public function vote($username, $placeid, $place_name, $place_address, $place_type, $place_city, $overall_vote, $amenity_stairs, $amenity_walkways, $amenity_corridors, $amenity_doors, $amenity_toilets, $amenity_elevators,  $amenity_ramps, $amenity_parking, $amenity_buzzers, $amenity_handrails, $amenity_thresholds, $amenity_floor, $amenity_fountains, $amenity_phones, $amenity_seating){

      //filterarr here is used for checking(everything you need to check in the db, will have to be stored in an array)
      $filterarr = array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid);

      //if the user has an existing vote on that place already
      if($this->db->has_row(TABLE_VOTES, $filterarr)){

        //initialize data to be inserted to db, via an array
         $insert = array(
              TABLE_VOTES_USERNAME => $username,
              TABLE_VOTES_PLACE_ID => $placeid,
              TABLE_VOTES_OVERALL_VOTE => $overall_vote,
              TABLE_VOTES_AMENITY_STAIRS => $amenity_stairs, 
              TABLE_VOTES_AMENITY_WALKWAYS => $amenity_walkways, 
              TABLE_VOTES_AMENITY_CORRIDORS => $amenity_corridors, 
              TABLE_VOTES_AMENITY_DOORS => $amenity_doors, 
              TABLE_VOTES_AMENITY_TOILETS => $amenity_toilets, 
              TABLE_VOTES_AMENITY_ELEVATORS => $amenity_elevators, 
              TABLE_VOTES_AMENITY_RAMPS => $amenity_ramps, 
              TABLE_VOTES_AMENITY_PARKING => $amenity_parking, 
              TABLE_VOTES_AMENITY_BUZZERS => $amenity_buzzers, 
              TABLE_VOTES_AMENITY_HANDRAILS => $amenity_handrails, 
              TABLE_VOTES_AMENITY_THRESHOLDS => $amenity_thresholds, 
              TABLE_VOTES_AMENITY_FLOOR => $amenity_floor, 
              TABLE_VOTES_AMENITY_FOUNTAINS => $amenity_fountains, 
              TABLE_VOTES_AMENITY_PHONES => $amenity_phones, 
              TABLE_VOTES_AMENITY_SEATING => $amenity_seating
            );

         //to be inserted in the table_places

          $insert_places = array(
              TABLE_PLACES_PLACE_TYPE => $place_type,
              TABLE_PLACES_PLACE_CITY => $place_city,
              TABLE_PLACES_PLACE_NAME => $place_name,
              TABLE_PLACES_PLACE_ADDRESS => $place_address
            );
      //update the data if the $insert variable is not empty

          if($overall_vote == 1){

            //get previous vote of the user to have a basis
            $previous_vote = $this->db->get_value(TABLE_VOTES, TABLE_VOTES_OVERALL_VOTE, array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid));

            //DOWNVOTE to UPVOTE
            if($previous_vote == 0){
              
              //decrement downvotes by 1, increment upvotes by 1 since DOWNVOTE to UPVOTE
              $arr = $this->db->get_value(TABLE_PLACES, TABLE_PLACES_TOTAL_UPVOTES, array(TABLE_PLACES_PLACE_ID => $placeid));
              $total_upvotes = $arr + 1;
              $arr = $this->db->get_value(TABLE_PLACES, TABLE_PLACES_TOTAL_DOWNVOTES, array(TABLE_PLACES_PLACE_ID => $placeid));
              $total_downvotes = $arr - 1;

              //include these values in an array for them to be updated in the db
              $update = array(TABLE_PLACES_TOTAL_UPVOTES => $total_upvotes, TABLE_PLACES_TOTAL_DOWNVOTES => $total_downvotes);

              //execute update OF UPVOTES / DOWNVOTES
              if($this->db->update_row(TABLE_PLACES, $update, array(TABLE_PLACES_PLACE_ID => $placeid))){

                //execute update OF THE VOTE of the user
                if($this->db->update_row(TABLE_VOTES, $insert, array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid)) &&
                    $this->db->update_row(TABLE_PLACES, $insert_places, array(TABLE_PLACES_PLACE_ID => $placeid))){
                  return true;
                }
                else {
                  return false;
                }

              }
              else {
                  return true;
              }
            }
            else {
                //THIS IS WHEN USER DOES NOT CHANGE HIS/HER OVERALL VOTE BUT CHANGES THE AMENITIES AVAILABLE IN THE AREA 
                if($this->db->update_row(TABLE_VOTES, $insert, array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid))) {
                  return true;
                }
                else {
                  return false;
                }
            }
          }
          else {

            $previous_vote = $this->db->get_value(TABLE_VOTES, TABLE_VOTES_OVERALL_VOTE, array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid));

            //UPVOTE to DOWNVOTE
            if($previous_vote == 1){
              
              $arr = $this->db->get_value(TABLE_PLACES, TABLE_PLACES_TOTAL_UPVOTES, array(TABLE_PLACES_PLACE_ID => $placeid));
              $total_upvotes = $arr - 1;
              $arr = $this->db->get_value(TABLE_PLACES, TABLE_PLACES_TOTAL_DOWNVOTES, array(TABLE_PLACES_PLACE_ID => $placeid));
              $total_downvotes = $arr + 1;

              $update = array(TABLE_PLACES_TOTAL_UPVOTES => $total_upvotes, TABLE_PLACES_TOTAL_DOWNVOTES => $total_downvotes);
              
              if($this->db->update_row(TABLE_PLACES, $update, array(TABLE_PLACES_PLACE_ID => $placeid))){

                if($this->db->update_row(TABLE_VOTES, $insert, array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid)) &&
                    $this->db->update_row(TABLE_PLACES, $insert_places, array(TABLE_PLACES_PLACE_ID => $placeid))){
                  return true;
                }
                else {
                  return false;
                }
              }
            }
            else{
                if($this->db->update_row(TABLE_VOTES, $insert, array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid))) {
                  return true;
                }else {
                  return false;
                }
            }
          }

    }

    //user has no existing vote of the place (first time)
    else {
       $insert = array(
            TABLE_VOTES_USERNAME => $username,
            TABLE_VOTES_PLACE_ID => $placeid,
            TABLE_VOTES_OVERALL_VOTE => $overall_vote,
            TABLE_VOTES_AMENITY_STAIRS => $amenity_stairs, 
            TABLE_VOTES_AMENITY_WALKWAYS => $amenity_walkways, 
            TABLE_VOTES_AMENITY_CORRIDORS => $amenity_corridors, 
            TABLE_VOTES_AMENITY_DOORS => $amenity_doors, 
            TABLE_VOTES_AMENITY_TOILETS => $amenity_toilets, 
            TABLE_VOTES_AMENITY_ELEVATORS => $amenity_elevators, 
            TABLE_VOTES_AMENITY_RAMPS => $amenity_ramps, 
            TABLE_VOTES_AMENITY_PARKING => $amenity_parking, 
            TABLE_VOTES_AMENITY_BUZZERS => $amenity_buzzers, 
            TABLE_VOTES_AMENITY_HANDRAILS => $amenity_handrails, 
            TABLE_VOTES_AMENITY_THRESHOLDS => $amenity_thresholds, 
            TABLE_VOTES_AMENITY_FLOOR => $amenity_floor, 
            TABLE_VOTES_AMENITY_FOUNTAINS => $amenity_fountains, 
            TABLE_VOTES_AMENITY_PHONES => $amenity_phones, 
            TABLE_VOTES_AMENITY_SEATING => $amenity_seating
          );

       $insert_places = array(
            TABLE_PLACES_PLACE_TYPE => $place_type,
            TABLE_PLACES_PLACE_CITY => $place_city,
            TABLE_PLACES_PLACE_NAME => $place_name,
            TABLE_PLACES_PLACE_ADDRESS => $place_address
          );

          //insert the data if the $insert variable is not empty
          if($this->db->insert_row(TABLE_VOTES, $insert)) {

            $filterarr = array(TABLE_PLACES_PLACE_ID => $placeid);

            if($this->db->has_row(TABLE_PLACES, $filterarr)){
             
              //UPVOTE
              if($overall_vote == 1){
                
                $arr = $this->db->get_value(TABLE_PLACES, TABLE_PLACES_TOTAL_UPVOTES, array(TABLE_PLACES_PLACE_ID => $placeid));
                $total_upvotes = $arr + 1;

                $update = array(TABLE_PLACES_TOTAL_UPVOTES => $total_upvotes);
                if($this->db->update_row(TABLE_PLACES, $update, array(TABLE_PLACES_PLACE_ID => $placeid))){
                  return true;
                }
              }

              //DOWNVOTE
              else{
                
                $arr = $this->db->get_value(TABLE_PLACES, TABLE_PLACES_TOTAL_DOWNVOTES, array(TABLE_PLACES_PLACE_ID => $placeid));
                $total_downvotes = $arr + 1;

                $update = array(TABLE_PLACES_TOTAL_DOWNVOTES => $total_downvotes);
                if($this->db->update_row(TABLE_PLACES, $update, array(TABLE_PLACES_PLACE_ID => $placeid))){
                  return true;
                }
                
              }
            }
            else {

              //UPVOTE
              if($overall_vote == 1){
                
                $insert_array = array(
                  TABLE_PLACES_PLACE_ID => $placeid, 
                  TABLE_PLACES_TOTAL_UPVOTES => 1, 
                  TABLE_PLACES_PLACE_TYPE => $place_type,
                  TABLE_PLACES_PLACE_CITY => $place_city, 
                  TABLE_PLACES_PLACE_ADDRESS => $place_address,
                  TABLE_PLACES_PLACE_NAME => $place_name 
                  );

                $this->db->insert_row(TABLE_PLACES, $insert_array);

                return true;
              }

              //DOWNVOTE
              else{
                
                $insert_array = array(TABLE_PLACES_PLACE_ID => $placeid, 
                  TABLE_PLACES_TOTAL_DOWNVOTES => 1,
                  TABLE_PLACES_PLACE_TYPE => $place_type,
                  TABLE_PLACES_PLACE_CITY => $place_city ,
                  TABLE_PLACES_PLACE_ADDRESS => $place_address,
                  TABLE_PLACES_PLACE_NAME => $place_name 
                  ) ;

                $this->db->insert_row(TABLE_PLACES, $insert_array);

                return true;
              }
            }  
          
          } else {
            return false;
          }
      }
    }

    public function view($placeid) {

      
      $filterarr = array(TABLE_PLACES_PLACE_ID => $placeid);
      
      // if the place has existing votes, the votes will be displayed to the user

      if($this->db->has_row(TABLE_PLACES, $filterarr)) {

        $results = $this->db->get_row(TABLE_PLACES, array(TABLE_PLACES_PLACE_ID => $placeid));    
        $results = $results[0];        
  
      }

      //if the place is voted for the first time, it will display 0 votes 
      else {
        $results = array(TABLE_PLACES_TOTAL_UPVOTES => 0, TABLE_PLACES_TOTAL_DOWNVOTES => 0, TABLE_PLACES_PLACE_ID => 0,
            TABLE_PLACES_AMENITY_STAIRS => 0, 
            TABLE_PLACES_AMENITY_WALKWAYS => 0, 
            TABLE_PLACES_AMENITY_CORRIDORS => 0, 
            TABLE_PLACES_AMENITY_DOORS => 0, 
            TABLE_PLACES_AMENITY_TOILETS => 0, 
            TABLE_PLACES_AMENITY_ELEVATORS => 0, 
            TABLE_PLACES_AMENITY_RAMPS => 0, 
            TABLE_PLACES_AMENITY_PARKING => 0, 
            TABLE_PLACES_AMENITY_BUZZERS => 0, 
            TABLE_PLACES_AMENITY_HANDRAILS =>0, 
            TABLE_PLACES_AMENITY_THRESHOLDS => 0, 
            TABLE_PLACES_AMENITY_FLOOR => 0, 
            TABLE_PLACES_AMENITY_FOUNTAINS => 0, 
            TABLE_PLACES_AMENITY_PHONES => 0, 
            TABLE_PLACES_AMENITY_SEATING => 0); 
      }
      
      return $results;
    }

    public function retrieve($username, $placeid){

      $filterarr = array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid);

      if($this->db->has_row(TABLE_VOTES, $filterarr)){
            $previousvote = $this->db->get_row(TABLE_VOTES, array(TABLE_VOTES_USERNAME => $username, TABLE_VOTES_PLACE_ID => $placeid));
      }
      else {

      }

      return $previousvote;

    }

    public function resetPassword($username, $secretKey, $newPassword) {

      $existingSecretKey = $this->db->get_value(TABLE_USERS, TABLE_USERS_SECRET_KEY_PASSWORD, array(TABLE_USERS_USERID => $username));

      if($existingSecretKey == "0") {
        return false;
      } else {

        $filterarr = array(TABLE_USERS_USERID => $username, TABLE_USERS_SECRET_KEY_PASSWORD => $secretKey);

        if($this->db->has_row(TABLE_USERS, $filterarr)){
            return $this->db->update_row(TABLE_USERS, array(TABLE_USERS_PASSWORD => $newPassword, TABLE_USERS_SECRET_KEY_PASSWORD => "0"), array(TABLE_USERS_USERID => $username));    
        }
        else{
            return false;
        }
      }
    }

    public function isPremium($username) {
      $isPremium = $this->db->get_value(TABLE_USERS, TABLE_USERS_PREMIUM, array(TABLE_USERS_USERID => $username));

      return $isPremium;
    }

    //THEORETICAL
    public function getLeaderBoards($placeFilter, $placeType, $orderOfPlaces) {

      if($orderOfPlaces == "Top-Rated") {

        if($placeFilter == "ALL" &&  $placeType == "ANY") {
          //return $this->db->get_row(TABLE_PLACES);
          return $this->db->exec("SELECT * from " . TABLE_PLACES . " WHERE " . TABLE_PLACES_PLACE_SCORE_UPVOTE . " > 0  ORDER BY " . TABLE_PLACES_PLACE_SCORE_UPVOTE . " DESC LIMIT 50");
        }

        else if($placeFilter == "ALL" && $placeType != "ANY") {
          return $this->db->exec("SELECT * from " . TABLE_PLACES . " WHERE " . TABLE_PLACES_PLACE_SCORE_UPVOTE . " > 0  AND " . TABLE_PLACES_PLACE_TYPE . " = '" . $placeType . "' ORDER BY " . TABLE_PLACES_PLACE_SCORE_UPVOTE . " DESC LIMIT 50");
        }

        else if($placeFilter != "ALL" && $placeType == "ANY") {
          return $this->db->exec("SELECT * from " . TABLE_PLACES . " WHERE " . TABLE_PLACES_PLACE_SCORE_UPVOTE . " > 0  AND " . TABLE_PLACES_PLACE_CITY . " = '" . $placeFilter . "' ORDER BY " . TABLE_PLACES_PLACE_SCORE_UPVOTE . " DESC LIMIT 50");
        }

        else if($placeFilter != "ALL" && $placeType != "ANY") {
          return $this->db->exec("SELECT * from " . TABLE_PLACES . " WHERE " . TABLE_PLACES_PLACE_SCORE_UPVOTE . " > 0  AND " . TABLE_PLACES_PLACE_TYPE . " = '" . $placeType . "'  AND " . TABLE_PLACES_PLACE_CITY . " = '" . $placeFilter . "' ORDER BY " . TABLE_PLACES_PLACE_SCORE_UPVOTE . " DESC LIMIT 50");
        }

      }
      else {

        if($placeFilter == "ALL" &&  $placeType == "ANY") {
          //return $this->db->get_row(TABLE_PLACES);
          return $this->db->exec("SELECT * from " . TABLE_PLACES . " WHERE " . TABLE_PLACES_PLACE_SCORE_DOWNVOTE . " > 0  ORDER BY " . TABLE_PLACES_PLACE_SCORE_DOWNVOTE . " DESC LIMIT 50");
        }

        else if($placeFilter == "ALL" && $placeType != "ANY") {
          return $this->db->exec("SELECT * from " . TABLE_PLACES . " WHERE " . TABLE_PLACES_PLACE_SCORE_DOWNVOTE . " > 0  AND " . TABLE_PLACES_PLACE_TYPE . " = '" . $placeType . "' ORDER BY " . TABLE_PLACES_PLACE_SCORE_DOWNVOTE . " ASC LIMIT 50");
        }

        else if($placeFilter != "ALL" && $placeType == "ANY") {
          return $this->db->exec("SELECT * from " . TABLE_PLACES . " WHERE " . TABLE_PLACES_PLACE_SCORE_DOWNVOTE . " > 0  AND " . TABLE_PLACES_PLACE_CITY . " = '" . $placeFilter . "' ORDER BY " . TABLE_PLACES_PLACE_SCORE_DOWNVOTE . " ASC LIMIT 50");
        }

        else if($placeFilter != "ALL" && $placeType != "ANY") {
          return $this->db->exec("SELECT * from " . TABLE_PLACES . " WHERE " . TABLE_PLACES_PLACE_SCORE_DOWNVOTE . " > 0  AND " . TABLE_PLACES_PLACE_TYPE . " = '" . $placeType . "'  AND " . TABLE_PLACES_PLACE_CITY . " = '" . $placeFilter . "' ORDER BY " . TABLE_PLACES_PLACE_SCORE_DOWNVOTE . " DESC LIMIT 50");
        }
      }
    }
      
    //THEORETICAL

    public function calculateScore($placeid) {

      $upvotes = $this->db ->get_value(TABLE_PLACES, TABLE_PLACES_TOTAL_UPVOTES, array(TABLE_PLACES_PLACE_ID => $placeid));
      $downvotes = $this->db ->get_value(TABLE_PLACES, TABLE_PLACES_TOTAL_DOWNVOTES, array(TABLE_PLACES_PLACE_ID => $placeid));
      $totalvotes = $upvotes + $downvotes;

      var_dump($upvotes);
      var_dump($downvotes);

      $upvote_score = $this->wilsoncalcu->getScore($upvotes, $totalvotes);
      $downvote_score = $this->wilsoncalcu->getScore($downvotes, $totalvotes);

      var_dump($upvote_score);
      var_dump($downvote_score);

      if($this->db->update_row(TABLE_PLACES, array(TABLE_PLACES_PLACE_SCORE_UPVOTE => $upvote_score, TABLE_PLACES_PLACE_SCORE_DOWNVOTE => $downvote_score), 
        array(TABLE_PLACES_PLACE_ID =>  $placeid))) {

          return true;
      }
      else {
          return false;
      }
     }

    public function checkConsecutive($placeid) {

      $votes_columns = array(TABLE_VOTES_AMENITY_STAIRS, TABLE_VOTES_AMENITY_WALKWAYS, TABLE_VOTES_AMENITY_CORRIDORS, TABLE_VOTES_AMENITY_DOORS, TABLE_VOTES_AMENITY_TOILETS, TABLE_VOTES_AMENITY_ELEVATORS, TABLE_VOTES_AMENITY_RAMPS, TABLE_VOTES_AMENITY_PARKING, TABLE_VOTES_AMENITY_BUZZERS, TABLE_VOTES_AMENITY_HANDRAILS, TABLE_VOTES_AMENITY_THRESHOLDS, TABLE_VOTES_AMENITY_FLOOR, TABLE_VOTES_AMENITY_FOUNTAINS, TABLE_VOTES_AMENITY_PHONES, TABLE_VOTES_AMENITY_SEATING);

      $places_columns = array(TABLE_PLACES_AMENITY_STAIRS, TABLE_PLACES_AMENITY_WALKWAYS, TABLE_PLACES_AMENITY_CORRIDORS, TABLE_PLACES_AMENITY_DOORS, TABLE_PLACES_AMENITY_TOILETS, TABLE_PLACES_AMENITY_ELEVATORS, TABLE_PLACES_AMENITY_RAMPS, TABLE_PLACES_AMENITY_PARKING, TABLE_PLACES_AMENITY_BUZZERS, TABLE_PLACES_AMENITY_HANDRAILS, TABLE_PLACES_AMENITY_THRESHOLDS, TABLE_PLACES_AMENITY_FLOOR, TABLE_PLACES_AMENITY_FOUNTAINS, TABLE_PLACES_AMENITY_PHONES, TABLE_PLACES_AMENITY_SEATING);

      for($i = 0 ; $i < count($votes_columns) ; $i++) {
         
         //if ctr reaches seven, then it is considered as consecutive
         $ctr = 0;

         //select records
         $records = $this->db->exec("SELECT " . $votes_columns[$i] . " FROM " . TABLE_VOTES . " WHERE " . TABLE_VOTES_PLACE_ID . " = '" . $placeid . "' AND " . $votes_columns[$i]. " <> '0' ORDER BY " . TABLE_VOTES_VOTE_ID . " DESC LIMIT 7");

         var_dump(count($records));

         //first element is base, this is where the next 6 values will be compared
         $base = 0;


         //check if the 7 records have consecutive 1's or -1's
         for($j = 0 ; $j < count($records) ; $j++) {

            //if 1 value is zero, or the first record is 0, then the counter will never reach value of 7
            if($j == 0) {
              $base = $records[$j][$votes_columns[$i]];

              //skip the entire set of records if base is zero
              if($base == 0) {
                break;
              }
              $ctr++;
            }

            else {
              //if values of the 2nd to 7th record are the same with the first (base), then the counter will increment up until 7
              if($records[$j][$votes_columns[$i]] == $base) { 
                $ctr++;
              }
            }
         }

         //hit the consecutive mark
         if($ctr == 7) {
             $this->db->update_row(TABLE_PLACES, array($places_columns[$i] => $base), array(TABLE_PLACES_PLACE_ID =>  $placeid));
         }

      }

      return true;

    }

    public function getDistinctTypes() {
      $records = $this->db->exec("SELECT DISTINCT " . TABLE_PLACES_PLACE_TYPE. " FROM " . TABLE_PLACES);

      return $records;
    }

    public function getDistinctCities() {
      $records = $this->db->exec("SELECT DISTINCT " . TABLE_PLACES_PLACE_CITY. " FROM " . TABLE_PLACES);

      return $records;
    }


    public function verifyMail($email_address, $secretKey, $username) {
      //message to be sent to
      $to = $email_address;
      //message of the mail to be sent1
      $message = "<html><body>";
      $message .= "<h1>Good day!</h1> \n <p>This is to verify that the email address associated with " . $username .  " is authentic.</p>\n\n <h2>Secret Key:
                  <b><font color='green'>" . $secretKey . "</font></b></h2>
                  \n\n <p>The text above will serve as your verification key. Upon launching of the application, Click on the 'Forgot Password' page, and click on 'Enter Secret Key'. Enter your username, your secret key, and your new password, to be entered twice for security measures.</p>

                  \n <p>If you do not remember requesting this, please ignore this message.</p>\n\n\n <br />
                  <br /> <p>Thank you!</p>\n<p><b>PWDirectory Support Team</b></p>";
      $message .= "</body></html>";

      //subject of the mail to be sent
      $subject = "PWDirectory Mobile Application - Email Address Verification";

      //headers of the mail to be sent
      $headers = 'From: PWDirectory Support Team <help.pwdirectory@gmail.com>' . "\r\n" .
                  'Reply-To: PWDirectory Support Team <help.pwdirectory@gmail.com>' . "\r\n" .
                  'Reply-Path: PWDirectory Support Team <help.pwdirectory@gmail.com>' . "\r\n" .
                  'MIME-Version: 1.0' . "\r\n" .
                  "Organization: PWDirectory Support Team" . "\r\n" .
                  "X-Priority: 3" . "\r\n" .
                  'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                  'X-Mailer: PHP/' . phpversion();

      return mail($to, $subject, $message, $headers);
    }

    public function confirmVerifyMail($secretKey, $username) {

        $filterarr = array(TABLE_USERS_USERID => $username, TABLE_USERS_SECRET_KEY_EMAIL => $secretKey);

        if($this->db->has_row(TABLE_USERS, $filterarr)){
            return $this->db->update_row(TABLE_USERS, array(TABLE_USERS_SECRET_KEY_EMAIL => "1"), array(TABLE_USERS_USERID => $username));    
        }
        else{
            return false;
        }
    }

    public function changeEmail($username, $new_email) {

        return $this->db->update_row(TABLE_USERS, array(TABLE_USERS_EMAIL => $new_email), array(TABLE_USERS_USERID => $username));
    }
}
