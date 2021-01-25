<?php 
///// -- ROUTE TO POST Event -- /////


//function to fetch the DB content from the user request
function api_event_post($request) {
    $user = wp_get_current_user(); //to get the current loged user
    $user_id = $user->ID; //specific user to make the post

    //error treatment when the user is not verifyed   
    if ($user_id === 0) {
        $response = new WP_Error('error', 'This user does not have permission', ['status' => 401]);
        return rest_ensure_response($response);
    }

    //defining the variables that I want to show in the post
    $local = sanitize_text_field($request['local']);
    $date = sanitize_text_field($request['date']);
    $time = sanitize_text_field($request['time']);
    $name = sanitize_text_field($request['name']);
    $description = sanitize_text_field($request['description']);

    //error treatment if one of the fields is empty
    if (empty($name) || empty($description) || empty($local) || empty($date) || empty($time)) {
        $response = new WP_Error('error', 'Incomplete data. Please fill out every field required', ['status' => 422]);
        return rest_ensure_response($response);
    }

    //listing the arguments / post settings
    $response = [
        'post_author' => $user_id,
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_title' => $name,
        'post_content' => $description,
        'post_category' => [2],        
        'meta_input' => [ //customized fields on WP
            'local' => $local,
            'date' => $date,
            'time' => $time,
            'joined_list' => '', // Users's list that joined the playdate
        ],
    ];


    //to make posts in WordPress, each post = one schedule
    $post_id = wp_insert_post($response); 
    
    return rest_ensure_response($response);
}

//defining the event post route
function register_api_event_post() {
    register_rest_route('api', '/event', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_event_post',
    ]);
}

add_action('rest_api_init', 'register_api_event_post');

?>