<?php


// Заказы
register_rest_route( 'door/v1', '/get/sales', array(
    'methods' => 'GET',
    'callback' => 'get_sales',
));
register_rest_route( 'door/v1', '/add/sales', array(
    'methods' => 'POST',
    'callback' => 'addSales_callback',
));
register_rest_route( 'door/v1', '/edit/sales', array(
    'methods' => 'POST',
    'callback' => 'editSales_callback',
));
register_rest_route( 'door/v1', '/delete/sales', array(
    'methods' => 'GET',
    'callback' => 'deleteSales_callback',
));



function get_sales( $data ){

    $args = array('limit' => 1000);

    $curruser = get_user_by( 'id', $data->get_param( 'user_id' ));

    $orders = wc_get_orders( $args );
    $salses = array();
    $i=0;

    foreach ($orders as $order){


        $single = wc_get_order( $order );
        $data = $order->get_data();
        $order_items = $order->get_items();
        $order_author_id = get_post_field( 'post_author',$single->get_id() );
        $user = get_user_by( 'id', $order_author_id );

         if ($curruser->roles[0] == 'shop_manager'){
            if ($curruser->id != $order_author_id){
                continue;
            }
        }else if ($curruser->roles[0] == 'zamershik'){
            if ($curruser->id != get_field('zamershhik',$single->get_id())->data->ID){
                continue;
            }
        }

 
        foreach( $order_items as $item_id => $item ){

            $p = new WC_Product( $item->get_product_id());
            $item_name = $item->get_name();
            $product_id = $item->get_product_id();
            $category = $p->get_attribute( 'pa_proizvoditel' );
            $category_values = wc_get_product_terms( $p->id, 'pa_proizvoditel', array( 'fields' => 'all' ) );

            $category_values_ruk = wc_get_product_terms( get_field('model_dveri_rukovoditelya',$single->get_id())->ID, 'pa_proizvoditel', array( 'fields' => 'all' ) );

            $item_data = $item->get_data();

        }

        $brigada = get_field('montazhnaya_brigada',$single->get_id());

        //print_r($data);


        $salses[$i]['id'] = $single->get_id();
        $salses[$i]['date'] = $data['date_created']->date('Y-m-d H:i:s');
        //Персональная информация
        $salses[$i]['fio'] = $data['billing']['first_name'] .' '. $data['billing']['last_name'];
        $salses[$i]['phone'] = $data['billing']['phone'];
        $salses[$i]['dop_phone'] = get_field('dopolnitelnyj_telefon',$single->get_id());


        $adress = explode(';',$data['billing']['address_1']);
        $salses[$i]['adress'] = $adress[0];
        $salses[$i]['house'] = $adress[1];
        $salses[$i]['korpus'] = $adress[2];
        $salses[$i]['flat']= $adress[3];
        $salses[$i]['floor']= $adress[4];

        $salses[$i]['tochka'] = get_field('tochka',$single->get_id());
    


        $salses[$i]['part_city'] = get_field('chast_goroda',$single->get_id());

        //Информация о товаре продовца
        $salses[$i]['category_saler']['id'] = $category_values[0]->term_id;
        $salses[$i]['category_saler']['name'] = $category_values[0]->name;
        $salses[$i]['model_saler']['id'] = $product_id;
        $salses[$i]['model_saler']['name'] = $item_name;

        //Информация о товаре администратора
        $salses[$i]['category_ruk']['id'] = $category_values_ruk[0]->term_id;
        $salses[$i]['category_ruk']['name'] = $category_values_ruk[0]->name;;
        $salses[$i]['model_ruk']['id'] = get_field('model_dveri_rukovoditelya',$single->get_id())->ID;
        $salses[$i]['model_ruk']['name'] = get_field('model_dveri_rukovoditelya',$single->get_id())->post_title;

        $salses[$i]['door_size'] = get_field('razmer_dveri',$single->get_id());
        $salses[$i]['door_direction'] = get_field('napravlenie_otkrytiya_dveri',$single->get_id());
        $salses[$i]['proem_size'] = get_field('razmer_proema',$single->get_id());
        $salses[$i]['door_number'] = get_field('nomer_dveri',$single->get_id());
        $salses[$i]['prim_saler'] = get_field('primechanie_prodovcza',$single->get_id());
        $salses[$i]['prim_rukvod'] = get_field('primechanie_rukovoditelya',$single->get_id());

        //Дополнительные работы
        $salses[$i]['dopServ'] = get_field('dopolnitelnye_uslugi',$single->get_id());

        //Замер и монтаж
        $salses[$i]['data_zamera'] = get_field('data_zamera',$single->get_id());
        $salses[$i]['vremya_zamera'] = get_field('vremya_zamera',$single->get_id());
        $salses[$i]['zamershik']['id'] = get_field('zamershhik',$single->get_id())->data->ID;
        $salses[$i]['zamershik']['name'] = get_field('zamershhik',$single->get_id())->data->display_name;

        $salses[$i]['date_mont'] = get_field('data_montazha',$single->get_id());
        $salses[$i]['time_mont'] = get_field('vremya_montazha',$single->get_id());
        

        $salses[$i]['brigada_mont']['id'] = $brigada->ID;
        $salses[$i]['brigada_mont']['name'] = $brigada->post_title;
        $salses[$i]['team']['id'] = $brigada->ID;
        $salses[$i]['team']['name'] = $brigada->post_title;


        //Итого
        $salses[$i]['payments_metod'] = get_field('metod_platezha', $single->get_id());
        $salses[$i]['cost_saler'] = intval(get_field('czena_prodovcza', $single->get_id()));
        $salses[$i]['cost_diler'] = intval(get_field('czena_rukovoditelya', $single->get_id()));
        $salses[$i]['cost_zdi'] = intval(get_field('stoimost_zamera_dostavki_ustanovki', $single->get_id()));
        $salses[$i]['avans'] = intval(get_field('avans', $single->get_id()));
        $salses[$i]['discount'] = intval(get_field('skidka',$single->get_id()));
        $salses[$i]['total'] = intval(get_field('itog',$single->get_id()));



        //Заявка и премия
        $salses[$i]['status'] = $data['status'];

        $salses[$i]['sum_premia'] = get_field('summa_premiya',$single->get_id());
        $salses[$i]['status_premia'] = get_field('status_premii',$single->get_id());
        $salses[$i]['vdz_premia'] = '';

        $salses[$i]['saler']['id'] = $order_author_id;
        $salses[$i]['saler']['name'] = $user->data->display_name;
        $salses[$i]['city'] = get_field('gorod',$single->get_id());



        $i++;

        unset($order_items);
    }


    return $salses;
}

function addSales_callback(WP_REST_Request $request) {

    $data = $request->get_json_params();

    if (!$data['fio']){
        $return = 'Не указаны данные.';
    }else{
        global $woocommerce;
        $address = array();
        $billing = array();

        $name = explode(' ', $data['fio']);

        if ($name[1]){
            $address['first_name'] = $name[0];
            $address['last_name'] = $name[1];
            if ($name[2]){
                $address['last_name'] = $name[1] .' '. $name[2];
            }
        }else{
            $address['first_name'] = $name[0];
        }

        $address['phone'] = $data['phone'];

        $address['address_1'] = $data['street'] . ';'. $data['house'] .';'. $data['korpus'] . ';'. $data['flat'] . ';' . $data['floor'];



        $order = wc_create_order();
        $order->set_address( $address, 'billing' );
        
        if ($data['dop_phone']){
            update_field('dopolnitelnyj_telefon',$data['dop_phone'],$order->get_id());
        }
               
        update_field( 'korpus', $data['korpus'], $order->get_id());

        if ($data['part_city']){
            update_field( 'chast_goroda', $data['part_city'], $order->get_id());
        }

        //Дверь продовца
        $order->add_product(get_product($data['model_saler']), 1, [
            'subtotal'     => $data['prod_sale'],
            'total'        => $data['prod_sale'],
        ]);


        $dopi = array();
        $dop_count = 0;

        if (count($data['dopServ'])){
            foreach ($data['dopServ'] as $dop){
               
               if ($dop['name']){
                   $val_23 = array(
                        'name' => $dop['name'],
                        'price' => $dop['price'],
                        'count' => $dop['count']
                   );

                   array_push($dopi, $val_23);
                   unset($val_23);
                }
            }
        }

       

        update_field('dopolnitelnye_uslugi',$dopi,$order->get_id());
        update_field( 'model_dveri_rukovoditelya', $data['model_ruk'], $order->get_id());
    
        update_field( 'napravlenie_otkrytiya_dveri', $data['door_direction'], $order->get_id());
        update_field( 'razmer_dveri', $data['door_size'], $order->get_id());
        update_field( 'razmer_proema', $data['proem_size'], $order->get_id());
        update_field('nomer_dveri',$data['door_number'],$order->get_id());

        update_field( 'data_montazha', $data['date_mont'], $order->get_id());
        update_field( 'vremya_montazha', $data['time_mont'], $order->get_id());
           
        update_field( 'data_zamera', $data['data_zamera'], $order->get_id());
        update_field( 'vremya_zamera', $data['vremya_zamera'], $order->get_id());
        
        
        update_field( 'montazhnaya_brigada', $data['team']['id'], $order->get_id());
        update_field( 'zamershhik', $data['zamershik']['id'], $order->get_id());

        update_field( 'primechanie_rukovoditelya', $data['prim_rukvod'], $order->get_id());
        update_field( 'primechanie_prodovcza', $data['prim_saler'], $order->get_id());


        update_field( 'tochka', $data['tochka'], $order->get_id());

        
     

        if ($data['payments_metod'] == 'Терминал'){
            $order->set_payment_method('cheque');
        }else if($data['payments_metod'] == 'Наличными'){
            $order->set_payment_method('cod');
        }else if($data['payments_metod'] == 'Оплата по безналичному расчету'){
            $order->set_payment_method('bacs');
        }

        update_field( 'metod_platezha', $data['payments_metod'], $order->get_id());

        
        update_field('czena_prodovcza',$data['cost_saler'], $order->get_id());
        update_field('czena_rukovoditelya',$data['cost_diler'], $order->get_id());
        update_field('stoimost_zamera_dostavki_ustanovki',$data['cost_zdi'], $order->get_id());
        update_field('skidka',$data['discount'],$order->get_id());
        update_field('avans',$data['avans'],$order->get_id());     
        update_field('itog',$data['total'],$order->get_id());

        

        update_field('summa_premiya',$data['sum_premia'], $order->get_id());
        update_field('status_premii',$data['status_premia'],$order->get_id());

        


        if ($data['user_id']){
            $arg = array(
                'ID' => $order->get_id(),
                'post_author' => $data['user_id'],
            );
            update_field('gorod',get_field('gorod','user_'.$data['user_id']),$order->get_id());
            wp_update_post( $arg );
        }


        if ($data['status']){  
            $order->update_status($data['status']);
            
        }  

        $email = 'Заказ: '.$order->get_id().'<br>'.
                          'Адрес:'.$address['address_1'].'<br>'.
                          'Телефон: '.$address['phone'].'<br>'. 
                          'Перейдите в админ панель для детальной информации http://door.webink.site/edit_order/'.$order->get_id();
        
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        if ($data['part_city'] == 'Север'){
              update_field( 'zamershhik', 38, $order->get_id());
              $order->update_status('zamer');

              wp_mail('godzilafan@mail.ru', 'У вас новый заказ на замер', $email, $headers);
        }

        if ($data['part_city'] == 'Юг'){
              update_field( 'zamershhik', 39, $order->get_id());
              $order->update_status('zamer');

              wp_mail('cool.kyznecov1967@yandex.ru', 'У вас новый заказ на замер', $email, $headers);
        }

        $return['status'] = 'Заказ создан успешно';
        $return['order_id'] = $order->get_id();


    }

    return $return;
}

function editSales_callback(WP_REST_Request $request){

    $data = $request->get_json_params();
    $return = array();

    $order = wc_update_order( array ( 'order_id' => intval($data['id']) ) );

    if ($order){
        $return['order_id'] = $data['id'];

        $name = explode(' ', $data['fio']);

        if ($name[1]){
            $address['first_name'] = $name[0];
            $address['last_name'] = $name[1];
            if ($name[2]){
                $address['last_name'] = $name[1] .' '. $name[2];
            }
        }else{
            $address['first_name'] = $name[0];
        }

        $address['phone'] = $data['phone'];

        if ($data['dop_phone']){
            $billing['phone'] = $data['dop_phone'];
        }

        $address['address_1'] = $data['adress'] . ';'. $data['house'] .';'. $data['korpus'].';'. $data['flat'] . ';' . $data['floor'];

        $order->set_address( $address, 'billing' );

        if ($data['part_city']){
            update_field( 'chast_goroda', $data['part_city'], $order->get_id());
        }


        //Дверь продовца
        $order->add_product(get_product($data['model_saler']), 1, [
            'subtotal'     => $data['prod_sale'],
            'total'        => $data['prod_sale'],
        ]);

        $dopi = array();
        $dop_count = 0;

        if (count($data['dopServ'])){
            foreach ($data['dopServ'] as $dop){
               
               if ($dop['name']){
                   $val_23 = array(
                        'name' => $dop['name'],
                        'price' => $dop['price'],
                        'count' => $dop['count']
                   );

                   array_push($dopi, $val_23);
                   unset($val_23);
                }
            }
        }

       

        update_field('dopolnitelnye_uslugi',$dopi,$order->get_id());

         //Дверь руководителя
        update_field( 'model_dveri_rukovoditelya', $data['model_ruk'], $order->get_id());


        update_field( 'napravlenie_otkrytiya_dveri', $data['door_direction'], $order->get_id());

        update_field( 'razmer_dveri', $data['door_size'], $order->get_id());
        update_field( 'razmer_proema', $data['proem_size'], $order->get_id());
        update_field( 'nomer_dveri', $data['door_number'],$order->get_id());

        update_field( 'primechanie_rukovoditelya', $data['prim_rukvod'], $order->get_id());
        update_field( 'primechanie_prodovcza', $data['prim_saler'], $order->get_id());

        update_field( 'tochka', $data['tochka'], $order->get_id());


        // установить дату монтажа
        if ($data['date_mont']){
            $datem = DateTime::createFromFormat('d/m/Y', $data['date_mont']);
            $datem = $datem->format('Ymd');

            update_field( 'data_montazha',  $datem, $order->get_id());
            update_field( 'vremya_montazha', $data['time_mont'], $order->get_id());
        }
        
        if ($data['data_zamera']){
            $date = DateTime::createFromFormat('d/m/Y', $data['data_zamera']);
            $date = $date->format('Ymd');
        
            update_field( 'data_zamera', $date, $order->get_id());
            update_field( 'vremya_zamera', $data['vremya_zamera'], $order->get_id());
        }
      
        
        update_field( 'metod_platezha', $data['payments_metod'], $order->get_id());

        update_field( 'montazhnaya_brigada', $data['team']['id'], $order->get_id());
        update_field( 'zamershhik', $data['zamershik']['id'], $order->get_id());

        

        update_field('czena_prodovcza',$data['cost_saler'], $order->get_id());
        update_field('czena_rukovoditelya',$data['cost_diler'], $order->get_id());
        update_field('stoimost_zamera_dostavki_ustanovki',$data['cost_zdi'], $order->get_id());
        update_field('skidka',$data['discount'],$order->get_id());
        update_field('avans',$data['avans'],$order->get_id());     
        update_field('itog',$data['total'],$order->get_id());

        
        update_field('summa_premiya',$data['sum_premia'], $order->get_id());
        update_field('status_premii',$data['status_premia'],$order->get_id());

        if ($data['status']){  
            $order->update_status($data['status']);
        }    

    }

    $return['status'] = 'Заказ № '.$data['id'].' обновлен';

    return $return;
}

function deleteSales_callback(WP_REST_Request $request){
    if($request->get_param( 'order_id' )){
        if(wp_delete_post($request->get_param( 'order_id' ),true)){
            $result['status'] = 'OK';
            $result['text'] = 'Заказ удален успешно';
        }else{
            $result['status'] = 'FAIL';
            $result['text'] = 'Ошибка при удалении заказа';
        }
    }else{
        $result['status'] = 'FAIL';
        $result['text'] = 'Не указан номер заказа';
    }

    return $result;
}

function a_change_status(WP_REST_Request $request){

}