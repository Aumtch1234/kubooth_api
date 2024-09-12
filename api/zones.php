<?php
ini_set("display_errors",1);
ini_set("display_startup_errors",1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// get
$app->get('/zone', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From zone");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    while( $row = $result->fetch_assoc() ) {
        array_push($data, $row);
    }
    $json = json_encode( $data );
    $response->getBody()->write($json);
    return $response->withHeader('Content-type', 'application/json');
});
$app->get('/zone/{zone_id}', function (Request $request, Response $response, $args) {
    $eId = $args['zone_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From zone WHERE zone_id = ?");
    $stmt->bind_param("i", $eId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    while( $row = $result->fetch_assoc() ) {
        array_push($data, $row);
    }
    $json = json_encode( $data );
    $response->getBody()->write($json);
    return $response->withHeader('Content-type', 'application/json');
});

// insert
$app->post('/zone/insert', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("INSERT INTO zone" . 
        "(zone_name, amount_booth, event_id)".
        "VALUES (?,?,?)");
    $stmt->bind_param("sii",
        $bodyArr['zone_name'], $bodyArr['amount_booth'], $bodyArr['event_id'],
    );
    $stmt->execute();
    $result = $stmt->affected_rows;
    
    if($result > 0){
        $response->getBody()->write(json_encode(["message" => "เพิ่มข้อมูล สำเร็จ!!"]));
    }else{
        $response->getBody()->write(json_encode(["message" => "เพิ่มข้อมูล ไม่สำเร็จ!! หรือ ไม่พบ ID."]));
    }
    return $response->withHeader('Content-type', 'application/json');
});

// put
$app->put('/zone/update/{zone_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['zone_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("UPDATE zone SET zone_name = ?, amount = ?, event_id = ? WHERE zone_id = ?");
    $stmt->bind_param("siii",
        $bodyArr['zone_name'], 
        $bodyArr['amount'], 
        $bodyArr['event_id'], 
        $eId);
    $stmt->execute();
    $result = $stmt->affected_rows;

    if($result > 0){
        $response->getBody()->write(json_encode(["message" => "อัพเดทข้อมูล สำเร็จ!!"]));
    }else{
        $response->getBody()->write(json_encode(["message" => "อัพเดทข้อมูล ไม่สำเร็จ!! หรือ ไม่พบ ID."]));
    }
    
    return $response->withHeader('Content-type', 'application/json');
});

// delete
$app->delete('/zone/delete/{zone_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['zone_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("DELETE FROM zone WHERE zone_id = ?");
    $stmt->bind_param("i", $eId);
    $stmt->execute();
    $result = $stmt->affected_rows;

    if ($result > 0) {
        $response->getBody()->write(json_encode(["message" => "ลบกิจกรรม สำเร็จ!!"]));
    } else {
        $response->getBody()->write(json_encode(["message" => "ลบกิจกรรม ไม่สำเร็จ!! หรือ ไม่พบ ID."]));
    }
    
    return $response->withHeader('Content-type', 'application/json');
});

?>