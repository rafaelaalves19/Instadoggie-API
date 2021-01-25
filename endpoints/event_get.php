<?php 
///// -- ROUTE TO GET EVENT -- /////



//function event_data to organize the data from $post
//this function will be used to fetch one post OR a list of posts
function event_data($post) {
    $post_meta = get_post_meta($post->ID);
    $user = get_userdata($post->post_author);
    $total_comments = get_comments_number($post->ID);

    return [
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
        'total_comments' => $total_comments,
    ];
}


//function to fetch the DB content from the user request
function api_event_get($request) {
    $post_id = $request['id'];
    $post = get_post($post_id);


    //checking if the post exists or not
    if (!isset($post) || empty($post_id)) {
        $response = new WP_Error('error', 'Post not found', ['status' => 404]);
        return rest_ensure_response($response);
    }


    $event = event_data($post);
    $event['access'] = (int) $photo['access'] + 1; //adding 1 access each time the post is accessed
    update_post_meta($post_id, 'access', $event['access']); //to save the access counting in the DB



    $comments = get_comments([
        'post_id' => $post_id,
        'order' => 'ASC',
    ]);

    //showing both (photo+comments) when a post is accessed
    $response = [
        'event' => $event,
        'comments' => $comments,
    ];


    return rest_ensure_response($response);
}

//function to define the delete route
function register_api_event_get() {
    register_rest_route('api', '/event/(?P<id>[0-9]+)', [ 
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_event_get',
    ]);
}

add_action('rest_api_init', 'register_api_event_get');



/////////////////// getting a LIST of event ///////////////////////

function api_event_list_get($request) {
    $_total = sanitize_text_field($request['_total']) ?: 6;
    $_page = sanitize_text_field($request['_page']) ?: 1; 
    $_user = sanitize_text_field($request['_user']) ?: 0;
    

    //to get the user by the name (not only by the id)
    if (!is_numeric($_user)) {
        $user = get_user_by('login', $_user);
        $_user = $user->ID;
    }


    //arguments for searching
    $args = [
        'post_type' => 'post',
        'category_name' => 'event',     
        'posts_per_page' => $_total,
        'paged' => $_page,
    ];

    if($_user) {
        $args['author'] = $_user;
    }

    //main WP query for searching posts
    $query = new WP_Query($args);
    $posts = $query->posts;

    
    //using the photo_data function to get the posts organized 
    $event_list = [];
    if ($posts) {
      foreach ($posts as $post) {
        $event_list[] = event_data($post);
      }
    }

    return rest_ensure_response($event_list);
}

//function to define the delete route
function register_api_event_list_get() {
    register_rest_route('api', '/event', [ 
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_event_list_get',
    ]);
}

add_action('rest_api_init', 'register_api_event_list_get');

?>

