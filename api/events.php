<?php
ini_set("display_errors",1);
ini_set("display_startup_errors",1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// get
$app->get('/events', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From events");
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
$app->get('/events/{event_id}', function (Request $request, Response $response, $args) {
    $eId = $args['event_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From events WHERE event_id = ?");
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
$app->post('/events/insert', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($bodyArr['event_name']) || empty($bodyArr['start_at_date']) || empty($bodyArr['end_at_date'])) {
        $response->getBody()->write(json_encode(['message' => "ข้อมูลไม่ครบถ้วน กรุณาตรวจกรอกให้ครบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่า start_at_date ต้องไม่ต่ำกว่าวันปัจจุบัน
    $stmt = $conn->prepare("SELECT ? < CURDATE() AS is_past_date");
    $stmt->bind_param("s", $bodyArr['start_at_date']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['is_past_date'] == 1) {
        // ถ้าวันที่เริ่มต้นอยู่ในอดีต
        $response->getBody()->write(json_encode(['message' => "ไม่สามารถเพิ่มกิจกรรมได้ เนื่องจากวันที่เริ่มต้นต่ำกว่าวันปัจจุบัน!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่ามีชื่อกิจกรรมซ้ำหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE event_name = ?");
    $stmt->bind_param("s", $bodyArr['event_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // ถ้าชื่อกิจกรรมซ้ำ
        $response->getBody()->write(json_encode(['message' => "ชื่อกิจกรรมนี้มีอยู่แล้ว กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // เตรียม statement สำหรับการ INSERT
    $stmt = $conn->prepare("INSERT INTO events (event_name, start_at_date, end_at_date) 
        VALUES (?, ?, ?)"
    );

    // Bind parameters
    $stmt->bind_param(
        "sss",
        $bodyArr['event_name'], 
        $bodyArr['start_at_date'],
        $bodyArr['end_at_date']
    );

    // Execute statement
    if ($stmt->execute()) {
        // ถ้าเพิ่มข้อมูลสำเร็จ
        $response->getBody()->write(json_encode(["message" => "เพิ่มข้อมูลสำเร็จ!!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(200);
    } else {
        // ถ้าเพิ่มข้อมูลไม่สำเร็จ
        $response->getBody()->write(json_encode(["message" => "เพิ่มข้อมูลไม่สำเร็จ"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }
});


// put
$app->put('/events/update/{event_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['event_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("UPDATE events SET event_name = ?, start_at_date = ?, end_at_date = ? WHERE event_id = ?");
    $stmt->bind_param("sssi",
        $bodyArr['event_name'], 
        $bodyArr['start_at_date'], 
        $bodyArr['end_at_date'], 
        $eId);
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
    return $response->withHeader('Content-type', 'application/json');
});

// delete
$app->delete('/events/delete/{event_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['event_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $eId);
    $stmt->execute();
    $result = $stmt->affected_rows;

    if ($result > 0) {
        $response->getBody()->write(json_encode(["message" => "Event deleted successfully"]));
    } else {
        $response->getBody()->write(json_encode(["message" => "No event found with the specified ID"]));
    }
    
    return $response->withHeader('Content-type', 'application/json');
});

?>