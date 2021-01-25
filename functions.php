<?php

// Disabling the internal APIs from WP 
//remove_action('rest_api_init', 'create_initial_rest_routes', 99);


//Disabling the access to the users json 
add_filter('rest_endpoints', function ($endpoints) {
    unset($endpoints['/wp/v2/users']);
    unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    return $endpoints;
});



// importing the endpoints routes
$dirbase = get_template_directory();

require_once $dirbase . '/endpoints/user_post.php';
require_once $dirbase . '/endpoints/user_get.php';

require_once $dirbase . '/endpoints/photo_post.php';
require_once $dirbase . '/endpoints/photo_delete.php';
require_once $dirbase . '/endpoints/photo_get.php';

require_once $dirbase . '/endpoints/comment_post.php';
require_once $dirbase . '/endpoints/comment_get.php';

require_once $dirbase . '/endpoints/statistics_get.php';

require_once $dirbase . '/endpoints/password.php';

require_once $dirbase . '/endpoints/event_post.php';
require_once $dirbase . '/endpoints/event_get.php';
require_once $dirbase . '/endpoints/event_put.php';
require_once $dirbase . '/endpoints/event_delete.php';

//update the WP photo standard size
update_option('large_size_w', 1000);
update_option('large_size_h', 1000);
update_option('large_crop', 1);


//changing the routes name just to facilitate the json searches
function change_api(){
    return 'json';
}
add_filter('rest_url_prefix', 'change_api');


//function to determinate how long the JWT toke will be valid
function expire_token() {
    return time() + (60 * 60 * 24);
}
add_action('jwt_auth_expire', 'expire_token');


?>