<?php 
///// -- ROUTE TO DELETE PICTURES -- /////


//function to fetch the DB content from the user request
function api_photo_delete($request) {
    $post_id = $request['id'];
    $user = wp_get_current_user(); //the user can delete his own photo, not deleting others users photos
    $post = get_post($post_id); //defining wich post will be deleted
    $author_id = (int) $post->post_author;
    $user_id = (int) $user->ID;


    //checking if the user and the author are the same, and if the post still exists
    if ($user_id !== $author_id || !isset($post)) {
        $response = new WP_Error('error', 'No permition', ['status' => 401]);
        return rest_ensure_response($response);
    }
    

    //deleting the post + attachment  
    $attachment_id = get_post_meta($post_id, 'img', true);
    wp_delete_attachment($attachment_id, true);
    wp_delete_post($post_id, true);
    

    return rest_ensure_response('Your post was deleted!');
}

//function to define the delete route
function register_api_photo_delete() {
    register_rest_route('api', '/photo/(?P<id>[0-9]+)', [ //passing the photo id
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'api_photo_delete',
    ]);
}

add_action('rest_api_init', 'register_api_photo_delete');



?>