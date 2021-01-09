<?php 
///// -- ROUTE TO POST PICTURES -- /////


//function to fetch the DB content from the user request
function api_photo_post($request) {
    $user = wp_get_current_user(); //to get the current loged user
    $user_id = $user->ID; //specific user to make the post


    //error treatment when the user is not verifyed   
    if ($user_id === 0) {
        $response = new WP_Error('error', 'This user does not have permission', ['status' => 401]);
        return rest_ensure_response($response);
    }


    //defining the variables that I want to show in the post
    $name = sanitize_text_field($request['name']);
    $weight = sanitize_text_field($request['weight']);
    $age = sanitize_text_field($request['age']);
    $subtitle = sanitize_text_field($request['subtitle']);
    $files = $request->get_file_params();


    //error treatment if one of the fields is empty
    if (empty($name) || empty($weight) || empty($age) || empty($subtitle) || empty($files)) {
        $response = new WP_Error('error', 'Incomplete data. Please fill out every field required', ['status' => 422]);
        return rest_ensure_response($response);
    }


    //listing the arguments / post settings
    $response = [
        'post_author' => $user_id,
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_title' => $name,
        'post_content' => $subtitle,
        'files' => $files,
        'meta_input' => [ //customized fields on WP
            'weight' => $weight,
            'age' => $age,
            'access' => 0,
        ],
    ];


    //to make posts in WordPress, each post = one photo
    $post_id = wp_insert_post($response); 

    //requiring specific parts from WP cor, to make the media_handle_upload easier
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $photo_id = media_handle_upload('img', $post_id);
    update_post_meta($post_id, 'img', $photo_id);


    return rest_ensure_response($response);
}

//defining the photo post route
function register_api_photo_post() {
    register_rest_route('api', '/photo', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_photo_post',
    ]);
}

add_action('rest_api_init', 'register_api_photo_post');



?>