<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// get
$app->get('/zone', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From zone");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
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
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $json = json_encode($data);
    $response->getBody()->write($json);
    return $response->withHeader('Content-type', 'application/json');
});

// insert
$app->post('/zone/insert', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($bodyArr['zone_name']) || empty($bodyArr['amount_booth']) || empty($bodyArr['event_id'])) {
        $response->getBody()->write(json_encode(['message' => "ข้อมูลไม่ครบถ้วน กรุณาตรวจกรอกให้ครบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่ามีชื่อโซนซ้ำหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM zone WHERE zone_name = ?");
    $stmt->bind_param("s", $bodyArr['zone_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // ถ้าชื่อโซนซ้ำ
        $response->getBody()->write(json_encode(['message' => "ชื่อโซนนี้มีอยู่แล้ว กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่า zone_id มีอยู่ในตาราง zone หรือไม่
    $stmtEvent = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE event_id = ?");
    $stmtEvent->bind_param("i", $bodyArr['event_id']);
    $stmtEvent->execute();
    $resultEvent = $stmtEvent->get_result();
    $rowEvent = $resultEvent->fetch_assoc();

    if ($rowEvent['count'] == 0) {
        // ถ้า event_id ไม่พบในตาราง events
        $response->getBody()->write(json_encode(['message' => "ไม่พบงาน/กิจกรรมที่คุณเลือก กรุณาตรวจสอบรหัสงานอีกครั้ง!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // เตรียม statement สำหรับการ INSERT
    $stmt = $conn->prepare(
        "INSERT INTO zone (zone_name, amount_booth, event_id) 
        VALUES (?, ?, ?)"
    );

    // Bind parameters
    $stmt->bind_param(
        "sii",
        $bodyArr['zone_name'],
        $bodyArr['amount_booth'],
        $bodyArr['event_id']
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


// Data Edit
$app->put('/zone/update/{zone_id}', function (Request $request, Response $response, array $args) {
    $zId = $args['zone_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($bodyArr['zone_name']) || empty($bodyArr['amount_booth']) || empty($bodyArr['event_id'])) {
        $response->getBody()->write(json_encode(['message' => "ข้อมูลไม่ครบถ้วน กรุณาตรวจกรอกให้ครบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่า event_id มีอยู่ในตาราง events หรือไม่
    $stmtUser = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE event_id = ?");
    $stmtUser->bind_param('i', $bodyArr['event_id']);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    if ($resultUser->fetch_assoc()['count'] == 0) {
        $response->getBody()->write(json_encode(['message' => "ไม่พบกิจกรรมที่คุณเลือก กรุณาตรวจสอบอีกครั้ง!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่าชื่อโซนซ้ำหรือไม่ (ยกเว้นโซนที่กำลังแก้ไขอยู่)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM zone WHERE zone_name = ? AND zone_id != ?");
    $stmt->bind_param("si", $bodyArr['zone_name'], $zId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // ถ้าชื่อโซนซ้ำ
        $response->getBody()->write(json_encode(['message' => "ชื่อโซนนี้มีอยู่แล้ว กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // เตรียม statement สำหรับการ UPDATE
    $stmt = $conn->prepare("UPDATE zone SET zone_name = ?, amount_booth = ?, event_id = ? WHERE zone_id = ?");
    $stmt->bind_param(
        "siii",
        $bodyArr['zone_name'],
        $bodyArr['amount_booth'],
        $bodyArr['event_id'],
        $zId
    );

    // Execute statement
    if ($stmt->execute()) {
        $response->getBody()->write(json_encode(["message" => "อัปเดตข้อมูลสำเร็จ!!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(200);
    } else {
        // ถ้าอัปเดตข้อมูลไม่สำเร็จ
        $response->getBody()->write(json_encode(["message" => "อัปเดตข้อมูลไม่สำเร็จ"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }
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
