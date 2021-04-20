<?php

function pushTOS(){
$options = array(
    'cluster' => 'us2',
    'useTLS' => true
);

$pusher = new Pusher\Pusher(
    '0cecb9eb64fb72e940ee',
    '3f2c3733f72b1d3a5fb7',
    '1191583',
    $options
);

$data['message'] = 'hello world';

$pusher->trigger('door-update', 'createOrder', $data);
$pusher->trigger('door-update', 'updateOrder', $data);



}