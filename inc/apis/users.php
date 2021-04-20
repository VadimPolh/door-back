<?php


// Пользователи
register_rest_route( 'door/v1', '/get/users', array(
    'methods' => 'GET',
    'callback' => 'a_get_users',
) );
register_rest_route( 'door/v1', '/create/user', array(
    'methods' => 'POST',
    'callback' => 'a_set_users',
) );

register_rest_route( 'door/v1', '/delete/user', array(
    'methods' => 'GET',
    'callback' => 'a_delete_user',
) );

register_rest_route( 'door/v1', '/get/teams', array(
    'methods' => 'GET',
    'callback' => 'a_get_teams',
) );

register_rest_route( 'door/v1', '/delete/teams', array(
    'methods' => 'GET',
    'callback' => 'a_delete_teams',
) );

register_rest_route( 'door/v1', '/create/teams', array(
    'methods' => 'POST',
    'callback' => 'a_create_teams',
) );



function a_get_users(WP_REST_Request $request ){


    $return = array();
    $args = array();
    $i = 0;

    if ($request->get_param( 'type' )){
        $args['role'] = $request->get_param( 'type' );
    }

    $blogusers = get_users($args);

    foreach ( $blogusers as $user ) {

        $args = [
            'author' => $user->ID,
            'post_status' => 'any',
            'post_type' => 'shop_order'
        ];

        $query = new WP_Query($args);

        $return[$i]['id'] = $user->ID;
        $return[$i]['name'] = $user->user_nicename;
        $return[$i]['fname'] = $user->user_firstname . ' ' . $user->user_lastname;
        $return[$i]['role'] = implode($user->roles);
        $return[$i]['email'] = $user->user_email;
        $return[$i]['zakaz'] = $query->found_posts;
        $return[$i]['premia'] = '';
        $return[$i]['lenter'] = $user->user_registered;
        $return[$i]['city'] = '';


        $i++;
    }


    return $return;
}

function a_set_users(WP_REST_Request $request){

    $data = $request->get_json_params();

    if (username_exists($data['nickname']) == null && email_exists($data['email']) == false) {

        $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );

        $user_id = wp_create_user($data['nickname'], $random_password, $data['email']);

        $user = get_user_by('id', $user_id);
        $name = explode(' ', $data['fio']);

        update_user_meta( $user_id, "first_name", $name[0]);
        update_user_meta( $user_id, "last_name", $name[1] );
        update_field('gorod',$data['city'],'user_'.$user_id);
        
        if ($data['premia']){
            update_field('premia',$data['city'],'user_'.$user_id);
        }
        
        $user->remove_role('subscriber');

        if ($data['role'] == 'Менеджер'){
            $user->add_role('shop_manager'); 
        }else if ($data['role'] == 'Замерщик'){
            $user->add_role('zamershik');
        }else{
            $user->add_role('administrator');
        }

        $result['status'] = '200';
        $result['data'] = 'Пользователь успешно создан';

        wp_new_user_notification( $user_id, $random_password);

       
    }else{
        $result['status'] = '403';
        $result['error'] = 'Пользователь с таким ником или емейлом существует.';
    }


    $i = 0;
    $args = array(); 
    $blogusers = get_users($args);

    foreach ( $blogusers as $user ) {

        $args = [
            'author' => $user->ID,
            'post_status' => 'any',
            'post_type' => 'shop_order'
        ];

        $query = new WP_Query($args);

        $return[$i]['id'] = $user->ID;
        $return[$i]['name'] = $user->user_nicename;
        $return[$i]['fname'] = $user->user_firstname . ' ' . $user->user_lastname;
        $return[$i]['role'] = implode($user->roles);
        $return[$i]['email'] = $user->user_email;
        $return[$i]['zakaz'] = $query->found_posts;
        $return[$i]['premia'] = '';
        $return[$i]['lenter'] = $user->user_registered;
        $return[$i]['city'] = get_field('gorod','user_'.$user->ID);

        $i++;
    }

    $result['users'] = $return;

    
    //print_r($request);

    return $result;
}

function a_delete_user(WP_REST_Request $request ){
    require_once(ABSPATH.'wp-admin/includes/user.php');

    if( wp_delete_user( intval($request->get_param( 'user_id' ) ) )){
        $result['status'] = '200';
        $result['data'] = 'Пользователь успешно удален';
    } else {
        $result['status'] = '403';
        $result['error'] = 'Пользователь с таким ид не существует существует.';      
    }
    
    $i = 0;
    $args = array(); 
    $blogusers = get_users($args);

    foreach ( $blogusers as $user ) {

        $args = [
            'author' => $user->ID,
            'post_status' => 'any',
            'post_type' => 'shop_order'
        ];

        $query = new WP_Query($args);

        $return[$i]['id'] = $user->ID;
        $return[$i]['name'] = $user->user_nicename;
        $return[$i]['fname'] = $user->user_firstname . ' ' . $user->user_lastname;
        $return[$i]['role'] = implode($user->roles);
        $return[$i]['email'] = $user->user_email;
        $return[$i]['zakaz'] = $query->found_posts;
        $return[$i]['premia'] = '';
        $return[$i]['lenter'] = $user->user_registered;
        $return[$i]['city'] = get_field('gorod','user_'.$user->ID);

        $i++;
    }

    $result['users'] = $return;
    return $result;

}

function a_delete_teams(WP_REST_Request $request ){
    $data = $request->get_json_params();

     if ($request->get_param( 'id' )){
        
       wp_delete_post( $request->get_param( 'id' ), true);
            
            $result['status'] = 200;
            $result['text'] = 'Бригада с id:' . $request->get_param( 'id' ) . ' удалена';
        
    }else{

        $result['status'] = 500;
        $result['text'] = 'Не указан id';
    }

    return $result;
}

function a_create_teams(WP_REST_Request $request){

    $data = $request->get_json_params();

    $return = array();

    $name              = $data['name'];
   
    $team = wp_insert_post(array (
        'post_type' => 'brigadi',
        'post_title' =>  $name,
        'post_status' => 'publish',
    ));

    $return['status'] = 200;
    $return['team_id'] =  $team;

    return $return;
}

function a_get_teams(WP_REST_Request $request ){

    $args = array(
        'post_type'      => 'brigadi',
        'posts_per_page' => -1
    );

    $loop = new WP_Query( $args );
    $return = array();
    $i=0;

    while ( $loop->have_posts() ) : $loop->the_post();

        $return[$i]['id'] = get_the_ID();
        $return[$i]['title'] = get_the_title();

        $orders = get_posts(
                                array(  'post_type' => 'shop_order',
                                        'numberposts'   => -1,
                                        'meta_key'      => 'montazhnaya_brigada',
                                        'meta_value'    => get_the_ID()
                                )
                            );

        $return[$i]['kolZakazov'] = count($orders);
        $i++;
    endwhile;

    wp_reset_query();


    return $return;
}

function a_get_sheluder(WP_REST_Request $request ){
    
}