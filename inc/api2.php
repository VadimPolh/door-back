<?php
/**
 * Door Crm Api
 *
 * @package door
 */

include 'apis/sales.php';
include 'apis/steps.php';
include 'apis/doors.php';
include 'apis/users.php';

add_action( 'rest_api_init', function () {

    //Служебное
  register_rest_route( 'door/v1', '/get/fields', array(
    'methods' => 'GET',
    'callback' => 'get_fields_acf',
  ) );


  register_rest_route( 'stars/v1', '/get/services', array(
    'methods' => 'GET',
    'callback' => 'get_services',
  ) );

} );




function get_fields_acf( $request_data ){
    $fields = get_fields('26');

    return $fields;
}






function get_services( $data ) {
	
	$args = array(
        'post_type'      => 'services',
        'posts_per_page' => -1
    );

    $loop = new WP_Query( $args );
    $services = array();
    $i=0;

    while ( $loop->have_posts() ) : $loop->the_post();

       $services[$i]['id'] = get_the_ID();
       $services[$i]['title'] = get_the_title();
       $services[$i]['img'] = get_field('ikonka',get_the_ID())['url'];
       $services[$i]['miniDescr'] = get_field('podzagolovok',get_the_ID());
       $services[$i]['fullDescr'] = get_the_content();

       $do = get_field('perechen',get_the_ID());
       $do_ar = array();

       foreach ($do as $item){
          array_push($do_ar, $item['tekst']);
       }

       $services[$i]['whatDo'] = $do_ar;
       $services[$i]['imgBig'] = get_field('zadnij_fon',get_the_ID())['url'];

	$i++;
    endwhile;

    wp_reset_query();	


    return $services;
}

