<?php 
///// -- ROUTE TO GET PHOTOS -- /////



//function photo_data to organize the data from $post
//this function will be used to fetch one post OR a list of posts
function photo_data($post) {
    $post_meta = get_post_meta($post->ID);
    $src = wp_get_attachment_image_src($post_meta['img'][0], 'large')[0];
    $user = get_userdata($post->post_author);
    $total_comments = get_comments_number($post->ID);

    return [
        'id' => $post->ID,
        'author' => $user->user_login,
        'title' => $post->post_title,
        'date' => $post->post_date,
        'src' => $src,
        'weight' => $post_meta['weight'][0],
        'age' => $post_meta['age'][0],
        'access' => $post_meta['access'][0],
        'total_comments' => $total_comments,
    ];
}


//function to fetch the DB content from the user request
function api_photo_get($request) {
    $post_id = $request['id'];
    $post = get_post($post_id);


    //checking if the post exists or not
    if (!isset($post) || empty($post_id)) {
        $response = new WP_Error('error', 'Post not found', ['status' => 404]);
        return rest_ensure_response($response);
    }


    $photo = photo_data($post);
    $photo['access'] = (int) $photo['access'] + 1; //adding 1 access each time the post is accessed
    update_post_meta($post_id, 'access', $photo['access']); //to save the access counting in the DB



    $comments = get_comments([
        'post_id' => $post_id,
        'order' => 'ASC',
    ]);

    //showing both (photo+comments) when a post is accessed
    $response = [
        'photo' => $photo,
        'comments' => $comments,
    ];


    return rest_ensure_response($response);
}

//function to define the delete route
function register_api_photo_get() {
    register_rest_route('api', '/photo/(?P<id>[0-9]+)', [ 
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_photo_get',
    ]);
}

add_action('rest_api_init', 'register_api_photo_get');



/////////////////// getting a LIST of photos ///////////////////////

function api_photos_get($request) {
    $_total = sanitize_text_field($request['_total']) ?: 6;
    $_page = sanitize_text_field($request['_page']) ?: 1; 
    $_user = sanitize_text_field($request['_user']) ?: 0;
    

    //to get the user by the name (not only by the id)
    if (!is_numeric($_user)) {
        $user = get_user_by('login', $_user);
        if(!user) {
            $response = new WP_Error('error', 'User not found', ['status' => 404]);
            return rest_ensure_response($response);
        }
        $_user = $user->ID;
    }


    //arguments for searching
    $args = [
        'post_type' => 'post',
        'author' => $user,
        'posts_per_page' => $_total,
        'paged' => $_page,
    ];


    //main WP query for searching posts
    $query = new WP_Query($args);
    $posts = $query->posts;

    
    //using the photo_data function to get the posts organized 
    $photos = [];
    if ($posts) {
      foreach ($posts as $post) {
        $photos[] = photo_data($post);
      }
    }

    return rest_ensure_response($photos);
}

//function to define the delete route
function register_api_photos_get() {
    register_rest_route('api', '/photo', [ 
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_photos_get',
    ]);
}

add_action('rest_api_init', 'register_api_photos_get');

?>

