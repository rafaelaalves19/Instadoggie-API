<?php 
///// -- ROUTE TO POST COMMENTS -- /////


//function to fetch the DB content from the user request
function api_comment_post($request) {
    $user = wp_get_current_user(); //the user can delete his own photo only
    $user_id = $user->ID;


    // checking if there is a user loged in
    if ($user_id === 0) {
        $response = new WP_Error('error', 'This user does not have permition', ['status' => 401]);
        return rest_ensure_response($response);
    }


    $comment = sanitize_text_field($request['comment']);
    $post_id = $request['id'];


    if (empty($comment)) {
        $response = new WP_Error('error', 'Incomplete data', ['status' => 422]);
        return rest_ensure_response($response);
    }

    //data needed to post a comment
    $response = [
        'comment_author' => $user->user_login,
        'comment_content' => $comment,
        'comment_post_ID' => $post_id,
        'user_id' => $user_id,
    ];
    

    //inserting a comment with wp_insert_comment
    $comment_id = wp_insert_comment($response);
    $comment = get_comment($comment_id);
    

    return rest_ensure_response($comment);
}

//function to define the delete route
function register_api_comment_post() {
    register_rest_route('api', '/comment/(?P<id>[0-9]+)', [ //passing the comment id
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_comment_post',
    ]);
}

add_action('rest_api_init', 'register_api_comment_post');

?>