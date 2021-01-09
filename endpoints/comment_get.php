<?php 
///// -- ROUTE TO POST COMMENTS -- /////


//function to fetch the DB content from the user request
function api_comment_get($request) {
    $post_id = $request['id'];

    $comments = get_comments([
    'post_id' => $post_id,
  ]);

    return rest_ensure_response($comments);
}

//function to define the delete route
function register_api_comment_get() {
    register_rest_route('api', '/comment/(?P<id>[0-9]+)', [ //passing the comment id
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_comment_get',
    ]);
}

add_action('rest_api_init', 'register_api_comment_get');

?>