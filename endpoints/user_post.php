<?php 
///// -- ROUTE TO POST / CREATE NEW USERS -- /////


//function to fetch the DB content from the user request
function api_user_post($request) {

    //defining the variables     
    $email = sanitize_email($request['email']);
    $username = sanitize_text_field($request['username']);
    $password = $request['password'];


    //error treatment to verify if the user filled out every request
    if (empty($email) || empty($username) || empty($password)) {
        $response = new WP_Error('error', 'Incomplete data', ['status' => 406]);
        return rest_ensure_response($response);
    }
    

    //error treatment to check if the user already exists
    if (username_exists($username) || email_exists($email)) {
        $response = new WP_Error('error', 'Sorry, this user already has a register', ['status' => 403]);
        return rest_ensure_response($response);
    }
    

    //setting the infos required to create the new user in the DB
    $response = wp_insert_user([
        'user_login'=> $username,
        'user_email'=> $email,
        'user_pass' => $password,
        'role' => 'subscriber'
    ]);

    return rest_ensure_response($response);
}


function register_api_user_post(){
    register_rest_route('api', '/user', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_user_post',
    ]);
}

add_action('rest_api_init', 'register_api_user_post');


?>