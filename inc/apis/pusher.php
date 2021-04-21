<?php

function pushTOS($type,$message,$code = 0,$user = 0){
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


if ($message){
    $data['message'] = $message;
}
if ($code != 0){
    $data['order_id'] = $code;
}
if ($user != 0){
    $data['user_id'] = $user;
}

$pusher->trigger('door-update', $type, $data);

}