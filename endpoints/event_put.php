<?php 
///// -- ROUTE TO PUT EVENT -- /////


//function to fetch the DB content from the user request
function api_event_put($request) {

    $body = json_decode($request->get_body(), true);

    $user = wp_get_current_user(); //to get the current loged user
    $user_id = $user->ID; //specific user to make the post
    $post_id = $body['id'];
    $post = get_post($post_id);
    $post_meta = get_post_meta($post->ID);

    //error treatment when the user is not verifyed   
    if ($user_id === 0) {
        $response = new WP_Error('error', 'This user does not have permission', ['status' => 401]);
        return rest_ensure_response($response);
    }

    //checking if the post exists or not
    if (!isset($post) || empty($post_id)) {
        $response = new WP_Error('error', 'Post not found', ['status' => 404]);
        return rest_ensure_response($response);
    }

    if($post_meta['joined_list'][0] != "") {
        $joined_list = explode(",", $post_meta['joined_list'][0]);
    } else {
        $joined_list = [];
    }

    if (in_array($user_id, $joined_list)) { 
        $joined_list = array_diff($joined_list, array($user_id));
    } else {
        array_push($joined_list, $user_id);
    }
    
    // update_post with the joined_list attribute
    update_post_meta($post_id, 'joined_list',  implode(",", $joined_list)); //to update joined list in DB post

    $post_meta = get_post_meta($post->ID);
    
    $response = [
        'id' => $post->ID,
        'author' => $user->user_login,
        'title' => $post->post_title,
        'description' => $post->post_content,
        'category' => $post->post_category,
        'date' => $post->post_date,
        'local' => $post_meta['local'][0],
        'date' => $post_meta['date'][0],
        'time' => $post_meta['time'][0],
        'joined_list' => explode(",", $post_meta['joined_list'][0]),
    ];

    return rest_ensure_response($response);
}

//defining the event post route
function register_api_event_put() {
    register_rest_route('api', '/event', [
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'api_event_put',
    ]);
}

add_action('rest_api_init', 'register_api_event_put');



?>