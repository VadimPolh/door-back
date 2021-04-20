<?php

// Модели дверей
register_rest_route( 'door/v1', '/get/models', array(
    'methods' => 'GET',
    'callback' => 'a_get_models',
));
register_rest_route( 'door/v1', '/set/models', array(
    'methods' => 'POST',
    'callback' => 'a_set_models',
));
register_rest_route( 'door/v1', '/edit/models', array(
    'methods' => 'POST',
    'callback' => 'a_edit_models',
) );
register_rest_route( 'door/v1', '/delete/models', array(
    'methods' => 'GET',
    'callback' => 'a_delete_models',
));


// Категории дверей
register_rest_route( 'door/v1', '/get/categorys', array(
    'methods' => 'GET',
    'callback' => 'a_get_categorys',
) );
register_rest_route( 'door/v1', '/get/dopserv', array(
    'methods' => 'GET',
    'callback' => 'a_get_dopserv',
) );
register_rest_route( 'door/v1', '/set/dopserv', array(
    'methods' => 'POST',
    'callback' => 'a_set_dop',
));
register_rest_route( 'door/v1', '/delete/dopserv', array(
    'methods' => 'GET',
    'callback' => 'a_delete_models',
));


function a_get_models(WP_REST_Request $request ){

    $return = array();
    $i = 0;


    if ($request->get_param( 'proizvoditel' )){
        $args     = array( 'post_type' => 'product', 'product_cat' => 'dveri', 'posts_per_page' => -1,
            'tax_query' => array( array('taxonomy' => 'pa_proizvoditel','field' => 'id','terms' => $request->get_param( 'proizvoditel' ))));
        $products = get_posts( $args );

    }else{
        $args     = array( 'post_type' => 'product', 'product_cat' => 'dveri', 'posts_per_page' => -1 );
        $products = get_posts( $args );
    }

    foreach ($products as $item){
        $product = wc_get_product( $item->ID );
        $p = new WC_Product( $item->ID);
        $category_values = wc_get_product_terms( $p->id, 'pa_proizvoditel', array( 'fields' => 'all' ) );

        $return[$i]['id'] = $item->ID;
        $return[$i]['name'] = $item->post_title;


        $prices = get_field('razmery',$item->ID);

        foreach ($prices as $itemp) {
            $priceform[$itemp['razmer']] = $itemp['stoimost'];
        }

        $return[$i]['price'] = $priceform;
        unset($priceform);
       

        $return[$i]['category']['id'] = $category_values[0]->term_id;
        $return[$i]['category']['name'] = $category_values[0]->name;

        $i++;
    }

    return $return;
}

//name
//manufacturer_id
//sizes - array
function a_set_models(WP_REST_Request $request){

    $data = $request->get_json_params();

    $return = array();

    $name              = $data['name'];
    $product           = new \WC_Product();

    $product->set_props( array(
        'name'               => $name,
        'featured'           => false,
        'catalog_visibility' => 'visible',
        'sku'                => sanitize_title( $name ) . '-' . rand(0, 100),
        'stock_status'       => 'instock'
    ) );

    $product->set_category_ids(array(20));

    $attribute = new WC_Product_Attribute();
    $attribute->set_id(wc_attribute_taxonomy_id_by_name('pa_proizvoditel')); //if passing the attribute name to get the ID
    $attribute->set_name('pa_proizvoditel'); //attribute name
    $attribute->set_options([$data['manufacturer_name']]); // attribute value
    $attribute->set_position(1); //attribute display order
    $attribute->set_visible(1); //attribute visiblity
    $attribute->set_variation(0);//to use this attribute as varint or not

    $raw_attributes[] = $attribute;

    $product->set_attributes($raw_attributes);

    $product->save();
   


    $return['status'] = 'Новая дверь добавлена';
    $return['door_id'] =  $product->id;


    $args     = array( 'post_type' => 'product', 'product_cat' => 'dveri', 'posts_per_page' => -1 );
    $products = get_posts( $args );

     foreach ($products as $item){
        $product = wc_get_product( $item->ID );
        $p = new WC_Product( $item->ID);
        $category_values = wc_get_product_terms( $p->id, 'pa_proizvoditel', array( 'fields' => 'all' ) );

        $return1[$i]['id'] = $item->ID;
        $return1[$i]['name'] = $item->post_title;

        $return1[$i]['price']['78'] = 1200;
        $return1[$i]['price']['80'] = '';
        $return1[$i]['price']['85'] = '';
        $return1[$i]['price']['86'] = '';
        $return1[$i]['price']['88'] = '';
        $return1[$i]['price']['90'] = '';
        $return1[$i]['price']['96'] = '';
        $return1[$i]['price']['98'] = '';
        $return1[$i]['price']['99'] = '';
        $return1[$i]['price']['105'] = '';

        $return1[$i]['category']['id'] = $category_values[0]->term_id;
        $return1[$i]['category']['name'] = $category_values[0]->name;

        $i++;
    }


    $return['doors'] = $return1;

    return $return;
}

function a_delete_models(WP_REST_Request $request){
    $data = $request->get_json_params();

    if ($request->get_param( 'id' )){
        
        $product = wc_get_product($request->get_param( 'id' ));

        if ($product){

            $product->delete();

            $result['status'] = 200;
            $result['text'] = 'Дверь с id:' . $request->get_param( 'id' ) . ' удалена';
        }else{
            $result['status'] = 500;
            $result['text'] = 'Дверь с id:' . $request->get_param( 'id' ) . ' не найдена';
        }

        
    }else{

        $result['status'] = 500;
        $result['text'] = 'Не указан id';
    }

    return $result;
}

//door_id
//name
//manufacturer_id
//sizes - array
function a_edit_models(WP_REST_Request $request){
    $data = $request->get_json_params();

    $return = array();

    if ($request->get_param('door_id')){
        $product = wc_get_product($request->get_param( 'door_id' ));
    }else{

    }


    $return['status'] = 'Дверь №'.$product->id.' обновлена';
    $return['door_id'] = $product->id;

    $args     = array( 'post_type' => 'product', 'product_cat' => 'dveri', 'posts_per_page' => -1 );
    $products = get_posts( $args );

     foreach ($products as $item){
        $product = wc_get_product( $item->ID );
        $p = new WC_Product( $item->ID);
        $category_values = wc_get_product_terms( $p->id, 'pa_proizvoditel', array( 'fields' => 'all' ) );

        $return1[$i]['id'] = $item->ID;
        $return1[$i]['name'] = $item->post_title;

        $return1[$i]['price']['78'] = 1200;
        $return1[$i]['price']['80'] = '';
        $return1[$i]['price']['85'] = '';
        $return1[$i]['price']['86'] = '';
        $return1[$i]['price']['88'] = '';
        $return1[$i]['price']['90'] = '';
        $return1[$i]['price']['96'] = '';
        $return1[$i]['price']['98'] = '';
        $return1[$i]['price']['99'] = '';
        $return1[$i]['price']['105'] = '';

        $return1[$i]['category']['id'] = $category_values[0]->term_id;
        $return1[$i]['category']['name'] = $category_values[0]->name;

        $i++;
    }


    $return['doors'] = $return1;


    return $return;
}

function a_get_categorys( $data ) {

    $attr_terms = get_terms( 'pa_proizvoditel' );

    return $attr_terms;
}


function a_get_dopserv(WP_REST_Request $request): array
{

    $return = array();
    $i = 0;

    if ($request->get_param( 'proizvoditel' )){
        $args     = array( 'post_type' => 'product', 'product_cat' => 'dop-raboty', 'posts_per_page' => -1,
            'tax_query' => array( array('taxonomy' => 'pa_proizvoditel','field' => 'id','terms' => $request->get_param( 'proizvoditel' ))));
        $products = get_posts( $args );

    }else{
        $args     = array( 'post_type' => 'product', 'product_cat' => 'dop-raboty', 'posts_per_page' => -1 );
        $products = get_posts( $args );
    }

    foreach ($products as $item){

        $product = wc_get_product( $item->ID );
        $p = new WC_Product( $item->ID);
        $category_values = wc_get_product_terms( $p->id, 'pa_proizvoditel', array( 'fields' => 'all' ) );

        $return[$i]['id'] = $item->ID;
        $return[$i]['name'] = $item->post_title;
        $return[$i]['price'] = $product->get_price();

        $return[$i]['manufacturer']['id'] = $category_values[0]->term_id;
        $return[$i]['manufacturer']['name'] = $category_values[0]->name;
        $i++;
    }

    return $return;
}

add_filter('woocommerce_is_purchasable', '__return_TRUE'); 
function a_set_dop(WP_REST_Request $request){

    $data = $request->get_json_params();

    $return = array();

    $name              = $data['name'];
    $product           = new \WC_Product();

    $product->set_props( array(
        'name'               => $name,
        'featured'           => false,
        'catalog_visibility' => 'visible',
        'sku'                => sanitize_title( $name ) . '-' . rand(0, 100),
        'stock_status'       => 'instock'
    ) );

    $product->set_category_ids(array(21));

    $attribute = new WC_Product_Attribute();
    $attribute->set_id(wc_attribute_taxonomy_id_by_name('pa_proizvoditel')); //if passing the attribute name to get the ID
    $attribute->set_name('pa_proizvoditel'); //attribute name
    $attribute->set_options([$data['manufacturer_name']]); // attribute value
    $attribute->set_position(1); //attribute display order
    $attribute->set_visible(1); //attribute visiblity
    $attribute->set_variation(0);//to use this attribute as varint or not
   

    $raw_attributes[] = $attribute;

    $product->set_attributes($raw_attributes);
    $product->set_price( intval($data['price']) );
$product->set_regular_price( intval($data['price']) );

    $product->save();

    update_post_meta( $product->get_id(), '_regular_price', intval($data['price']));


   


    $return['status'] = 'Новая услуга добавлена';
    $return['dop_id'] =  $product->get_id();

    return $return;
}