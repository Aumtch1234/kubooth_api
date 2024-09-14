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
// $app->get('/booth/{booth_id}', function (Request $request, Response $response, $args) {
//     $bId = $args['booth_id'];
//     $conn = $GLOBALS['conn'];
//     $stmt = $conn->prepare("select * From booth WHERE booth_id = ?");
//     $stmt->bind_param("i", $bId);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $data = array();
//     while ($row = $result->fetch_assoc()) {
//         array_push($data, $row);
//     }
//     $json = json_encode($data);
//     $response->getBody()->write($json);
//     return $response->withHeader('Content-type', 'application/json');
// });

//insert
$app->post('/admin/booth/insert', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($bodyArr['booth_name']) || empty($bodyArr['size']) || empty($bodyArr['price']) || empty($bodyArr['zone_id']) || empty($bodyArr['img'])) {
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

    // ตรวจสอบว่า zone_id มีอยู่ในตาราง zone หรือไม่
    $stmtBooth = $conn->prepare("SELECT COUNT(*) as count FROM zone WHERE zone_id = ?");
    $stmtBooth->bind_param('i', $bodyArr['zone_id']);
    $stmtBooth->execute();
    $resultBooth = $stmtBooth->get_result();

    if ($resultBooth->fetch_assoc()['count'] == 0) {
        $response->getBody()->write(json_encode(['message' => "ไม่พบโซนที่คุณเลือก กรุณาตรวจสอบอีกครั้ง!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }
    // เตรียม statement สำหรับการ INSERT
    $stmt = $conn->prepare("INSERT INTO booth (booth_name, size, price, zone_id, img) 
        VALUES (?, ?, ?, ?, ?)"
    );
    // Bind parameters
    $stmt->bind_param(
        "ssdis",
        $bodyArr['booth_name'], 
        $bodyArr['size'],       
        $bodyArr['price'], 
        $bodyArr['zone_id'],   
        $bodyArr['img']         
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
$app->put('/admin/booth/update/{booth_id}', function (Request $request, Response $response, array $args) {
    $bId = $args['booth_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($bodyArr['booth_name']) || empty($bodyArr['size']) || empty($bodyArr['price']) || empty($bodyArr['zone_id'])) {
        $response->getBody()->write(json_encode(['message' => "ข้อมูลไม่ครบถ้วน กรุณาตรวจกรอกให้ครบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่าชื่อบูธซ้ำหรือไม่ (ยกเว้นบูธที่กำลังแก้ไขอยู่)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM booth WHERE booth_name = ? AND booth_id != ?");
    $stmt->bind_param("si", $bodyArr['booth_name'], $bId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // ถ้าชื่อบูธซ้ำ
        $response->getBody()->write(json_encode(['message' => "ชื่อบูธนี้มีอยู่แล้ว กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

     // ตรวจสอบว่า zone_id มีอยู่ในตาราง zone หรือไม่
     $stmtUser = $conn->prepare("SELECT COUNT(*) as count FROM zone WHERE zone_id = ?");
     $stmtUser->bind_param('i', $bodyArr['zone_id']);
     $stmtUser->execute();
     $resultUser = $stmtUser->get_result();
     if ($resultUser->fetch_assoc()['count'] == 0) {
         $response->getBody()->write(json_encode(['message' => "ไม่พบกิจกรรมที่คุณเลือก กรุณาตรวจสอบอีกครั้ง!!"]));
         return $response->withHeader('Content-type', 'application/json')->withStatus(400);
     }

     // เตรียม statement สำหรับการ UPDATE
     $stmt = $conn->prepare("UPDATE booth SET booth_name = ?, size = ?, price = ?, zone_id =? WHERE booth_id = ?");
     $stmt->bind_param(
         "ssdii",
         $bodyArr['booth_name'],
         $bodyArr['size'],
         $bodyArr['price'],
         $bodyArr['zone_id'],
         $bId
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
$app->delete('/admin/booth/delete/{booth_id}', function (Request $request, Response $response, array $args) {
    $bId = $args['booth_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("DELETE FROM booth WHERE booth_id = ?");
    $stmt->bind_param("i", $bId);
    $stmt->execute();
    $result = $stmt->affected_rows;

    if ($result > 0) {
        $response->getBody()->write(json_encode(["message" => "ลบข้อมูลบูธ สำเร็จ!!"]));
    } else {
        $response->getBody()->write(json_encode(["message" => "ลบบูธข้อมูล ไม่สำเร็จ!!"]));
    }

    return $response->withHeader('Content-type', 'application/json');
});
