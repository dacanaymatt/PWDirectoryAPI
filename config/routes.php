<?php

/**
 * Configure manually all the routes here
 *
 * use .+ when expecting a parameter
 *
 * @author Vince Urag
 */

$route['/'] = "index";
$route['/users'] = "users";
$route['/about'] = "test";
$route['/users/edit'] = "users/edit"; //change password
$route['/users/signin'] = "users/signin"; //login
$route['/users/getusers'] = "users/getusers"; //sample lang to
$route['/users/create'] = "users/create"; //register 
$route['/users/reset'] = "users/forgotpassword"; //forgot password step 1
$route['/users/vote'] = "users/vote"; //vote / rate place
$route['/users/view'] = "users/view"; //view ratings of place
$route['/users/retrieve'] = "users/retrieve"; //retrieve previous vote of user if available
$route['/users/confirmreset'] = "users/confirmforgotpassword"; //forgot password step 2
$route['/users/leaderboards'] = "users/getLeaderBoards"; //forgot password step 2
$route['/users/testScore'] = "users/testScore"; //forgot password step 2
$route['/users/selectDistinctTypes'] = "users/selectDistinctTypes"; //forgot password step 2
$route['/users/selectDistinctCities'] = "users/selectDistinctCities"; //forgot password step 2

//TESTING PHASE
$route['/users/CheckConsecutive'] = "users/checkConsecutive"; //view ratings of place
$route['about/name/:param/age/:param'] = "test/nameage"; //testing
