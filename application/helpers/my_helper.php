<?php

function get_request(){
    $post_data = file_get_contents("php://input");
    $request = json_decode($post_data, true);
    return $request;
}

function send_response($type, $text = ""){
    echo json_encode(array(ERROR_NAME => $type, ERROR_MESSAGE_NAME => $text));
}

function unix_to_sql_date($date){
    return date("Y-m-d H:i:s", $date/1000);
}