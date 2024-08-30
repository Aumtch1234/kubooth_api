<?php
ini_set("display_errors",1);
ini_set("display_startup_errors",1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// get
$app->get('/users', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From users");
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
$app->get('/users/{users_id}', function (Request $request, Response $response, $args) {
    $eId = $args['users_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From users WHERE id = ?");
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
$app->post('/users/insert', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];
    $hashPassword = password_hash($bodyArr['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users" . 
        "(fname, lname, email, password, phone)".
        "VALUES (?,?,?,?,?)");
    $stmt->bind_param("ssssi",
        $bodyArr['fname'], $bodyArr['lname'], $bodyArr['email'], $hashPassword, $bodyArr['phone']
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
$app->put('/users/update/{users_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['users_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("UPDATE users SET users_name = ?, amount = ?, event_id = ? WHERE users_id = ?");
    $stmt->bind_param("siii",
        $bodyArr['users_name'], 
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
$app->delete('/users/delete/{users_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['users_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("DELETE FROM users WHERE users_id = ?");
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