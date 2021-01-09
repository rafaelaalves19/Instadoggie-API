<?php 

function api_stats_get($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    //checking if the user exists
    if ($user_id === 0) {
        $response = new WP_Error('error', 'This user does not have permission', ['status' => 401]);
        return rest_ensure_response($response);
    }


    //searching in all posts
    $args = [
        'post_type' => 'post',
        'author' => $user_id,
        'posts_per_page' => -1,
    ];
    
    $query = new WP_Query($args);
    $posts = $query->posts;

    //statistic list
    $stats = [];
    if ($posts) {
        foreach ($posts as $post) {
            $stats[] = [
                'id' => $post->ID,
                'title' => $post->post_title,
                'access' => get_post_meta($post->ID, 'access', true),
            ];
        }
    }

    return rest_ensure_response($stats);
}

function register_api_stats_get() {
    register_rest_route('api', '/stats', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'api_stats_get',
    ]);
}

add_action('rest_api_init', 'register_api_stats_get');

?>