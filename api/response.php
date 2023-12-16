<?php  

function Ok($message = 'Response successful', $data = []){
    $response = [];
    if(count($data) > 0){ 
        $response['data'] = $data;
    }
    $response['message'] = $message;
    return json_response($response);
}
    
function Error($status,$message = '', $data = []){
    $haveContent = false;
    $response = [];
    if (count($data) > 0) {
        $response['data'] = $data;
        $haveContent = true;
    }
    if ($message != '') {
        $response['message'] = $message;
        $haveContent = true;
    }
    http_response_code($status);
    if(!$haveContent){
        return;
    }
    return json_response($response);
}

function json_response($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
}