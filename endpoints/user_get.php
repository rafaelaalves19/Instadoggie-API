<?php 
///// -- ROUTE TO GET / RETRIEVE THE USERS INFORMATION -- /////


//function to fetch the DB content from the user request
function api_user_get($request) {

    $user = wp_get_current_user(); //to get the current loged user
    $user_id = $user->ID;

    //error treatment when there is no token  
    if ($user_id === 0) {
       $response = new WP_Error('error', 'This user does not have permission', ['status' => 401]);
       return rest_ensure_response($response);
    }
    
    //setting the relevant data I want to get
    $response = [
        'id' => $user_id,
        'username' => $user->user_login,
        'name' => $user->display_name,
        'email' => $user->user_email,
    ];
    
    return rest_ensure_response($response);
    
}

//defining the get route
function register_api_user_get() {
    register_rest_route('api', '/user', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_user_get',
    ]);
}

add_action('rest_api_init', 'register_api_user_get');


?>