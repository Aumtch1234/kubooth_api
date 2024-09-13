<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// get
$app->get('/booth', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From booth");
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
$app->get('/booth/{booth_id}', function (Request $request, Response $response, $args) {
    $eId = $args['booth_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From booth WHERE booth_id = ?");
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

$app->post('/booth/insert', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($bodyArr['booth_name']) || empty($bodyArr['size']) || empty($bodyArr['price']) || empty($bodyArr['img'])) {
        $response->getBody()->write(json_encode(['message' => "ข้อมูลไม่ครบถ้วน กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    } else if ($bodyArr['price'] < 0) {
        $response->getBody()->write(json_encode(['message' => "ราคาไม่ถูกต้อง กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่ามีชื่อบูธซ้ำหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM booth WHERE booth_name = ?");
    $stmt->bind_param("s", $bodyArr['booth_name']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // ถ้าชื่อบูธซ้ำ
        $response->getBody()->write(json_encode(['message' => "ชื่อบูธซ้ำ กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // เตรียม statement สำหรับการ INSERT
    $stmt = $conn->prepare("INSERT INTO booth (booth_name, size, price, img) 
        VALUES (?, ?, ?, ?)"
    );

    // Bind parameters
    $stmt->bind_param(
        "ssds",
        $bodyArr['booth_name'], // booth_name เป็น string (s)
        $bodyArr['size'],       // size เป็น string (s)
        $bodyArr['price'],      // price ควรเป็น double (d)
        $bodyArr['img']         // img เป็น string (s)
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
$app->put('/booth/update/{booth_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['booth_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("UPDATE booth SET booth_name = ?, size = ?, products = ?, zone_id = ? WHERE booth_id = ?");
    $stmt->bind_param(
        "sssii",
        $bodyArr['booth_name'],
        $bodyArr['size'],
        $bodyArr['products'],
        $bodyArr['zone_id'],
        $eId
    );
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result . "");
    return $response->withHeader('Content-type', 'application/json');
});

// delete
$app->delete('/booth/delete/{booth_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['booth_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("DELETE FROM booth WHERE booth_id = ?");
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
