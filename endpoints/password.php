<?php

//////// CREATING THE EMAIL TO SEND THE LINK TO RESET THE PASSWORD

function api_password_lost($request) {
    $login = $request['login'];
    $url = $request['url'];

    //checking if the login is empty
    if (empty($login)) {
        $response = new WP_Error('error', 'Please fill out email or login', ['status' => 406]);
        return rest_ensure_response($response);
    }

    //getting the user by email OR by login, in case the user fill out just one data
    $user = get_user_by('email', $login);
    if (empty($user)) {
        $user = get_user_by('login', $login);
    }

    //checking if there is a user
    if (empty($user)) {
        $response = new WP_Error('error', 'This user does not exists', ['status' => 401]);
        return rest_ensure_response($response);
    }

    //creating the infos must contain within the url to reset the password
    $user_login = $user->user_login;
    $user_email = $user->user_email;
    $key = get_password_reset_key($user); 


    //creating the message will be sent by email + url to reset the password
    $message = "Please use the link below to reset your password: \r\n";
    $url = esc_url_raw($url . "/?key=$key&login=" . rawurlencode($user_login) . "\r\n");
    $body = $message . $url;

    wp_mail($user_email, 'Password Reset', $body);

    return rest_ensure_response('Email has sent');
}

//function to define the password route
function register_api_password_lost() {
    register_rest_route('api', '/password/lost', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_password_lost',
    ]);
}

add_action('rest_api_init', 'register_api_password_lost');


//////// RESETING THE PASSWORD ///////////

function api_password_reset($request) {
    $login = $request['login'];
    $password = $request['password'];
    $key = $request['key'];
    $user = get_user_by('login', $login);

    //checking if there is a user
    if (empty($user)) {
        $response = new WP_Error('error', 'This user does not exists', ['status' => 401]);
        return rest_ensure_response($response);
    }

    //checking if the key is valid
    $check_key = check_password_reset_key($key, $login);

    if (is_wp_error($check_key)) {
        $response = new WP_Error('error', 'Expired token', ['status' => 401]);
        return rest_ensure_response($response);
    }

    reset_password($user, $password);

    return rest_ensure_response('Password modified successfully');
}


function register_api_password_reset() {
    register_rest_route('api', '/password/reset', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'api_password_reset',
    ]);
}

add_action('rest_api_init', 'register_api_password_reset');


?>