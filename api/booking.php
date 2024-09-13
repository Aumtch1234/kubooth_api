<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// get
$app->get('/booking', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From booking");
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

$app->get('/booking/{booking_id}', function (Request $request, Response $response, $args) {
    $eId = $args['booking_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From booking WHERE booking_id = ?");
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

//booking
$app->post('/booking/booth_booking', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($bodyArr['booth_id']) || empty($bodyArr['products_data']) || empty($bodyArr['user_id']) || empty($bodyArr['event_id'])) {
        $response->getBody()->write(json_encode(['message' => "ข้อมูลไม่ครบถ้วน กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบสถานะของบูธ
    $status_stmt = $conn->prepare("SELECT status FROM booth WHERE booth_id = ?");
    $status_stmt->bind_param('i', $bodyArr['booth_id']);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    $status_result_use = $status_result->fetch_assoc();

    if (!$status_result_use || ($status_result_use['status'] === "อยู่ระหว่างการตรวจสอบ" || $status_result_use['status'] === "จองแล้ว")) {
        $response->getBody()->write(json_encode([
            "message" => "บูธนี้ไม่สามารถจองได้เนื่องจากอยู่ในสถานะ " . ($status_result_use['status'] ?? 'ไม่พบข้อมูลบูธ')
        ]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่าผู้ใช้จองเกิน 4 ครั้งหรือไม่
    $checkStmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM booking WHERE user_id = ? AND (booking_status = 'อยู่ระหว่างการตรวจสอบ' OR booking_status = 'ชำระเงินแล้ว')");
    $checkStmt->bind_param("i", $bodyArr['user_id']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $bookingCount = $result->fetch_assoc()['booking_count'];

    if ($bookingCount >= 4) {
        $response->getBody()->write(json_encode(["message" => "ผู้ใช้งานคนนี้จองเกิน 4 ครั้งแล้ว ไม่สามารถจองเพิ่มเติมได้!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // เริ่มต้น transaction เพื่อให้แน่ใจว่าข้อมูลทั้งสองถูกเพิ่มพร้อมกัน
    $conn->begin_transaction();

    try {
        // ดึงราคาจากตาราง booth
        $price_stmt = $conn->prepare("SELECT price FROM booth WHERE booth_id = ?");
        $price_stmt->bind_param("i", $bodyArr['booth_id']);
        $price_stmt->execute();
        $price_result = $price_stmt->get_result();
        $booth_price = $price_result->fetch_assoc()['price'];

        if (!$booth_price) {
            throw new Exception("ไม่สามารถดึงราคาของบูธได้");
        }

        // เตรียม statement สำหรับการเพิ่มข้อมูลลงใน booking พร้อมราคาที่ได้จากบูธ
        $stmt = $conn->prepare("INSERT INTO booking (booth_id, price, products_data, user_id, event_id) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "idsii",
            $bodyArr['booth_id'],
            $booth_price,  // ใช้ราคาที่ดึงจากบูธ
            $bodyArr['products_data'],
            $bodyArr['user_id'],
            $bodyArr['event_id']
        );

        // Execute booking insertion
        $stmt->execute();

        // เตรียม statement สำหรับการอัปเดตสถานะของ booth
        $stmt2 = $conn->prepare("UPDATE booth SET status = 'อยู่ระหว่างการตรวจสอบ' WHERE booth_id = ?");
        $stmt2->bind_param("i", $bodyArr['booth_id']);
        
        // Execute booth status update
        $stmt2->execute();

        // ถ้าทั้งสองคำสั่งสำเร็จ ให้ commit
        $conn->commit();

        // ส่ง response เมื่อการจองสำเร็จ
        $response->getBody()->write(json_encode(["message" => "คำขอในการจองพื้นที่สำเร็จ กรุณาชำระเงิน!!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        // ถ้ามีข้อผิดพลาด ให้ rollback
        $conn->rollback();
        $response->getBody()->write(json_encode(["message" => "คำขอในการจองพื้นที่ไม่สำเร็จ: " . $e->getMessage()]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }
});

//CANCEL
$app->put('/booking/cancel/{booking_id}', function (Request $request, Response $response, array $args) {
    $Bid = $args['booking_id'];
    $conn = $GLOBALS['conn'];

    // เริ่มต้น transaction
    $conn->begin_transaction();

    try {
        // ดึง booth_id จากการจองด้วย booking_id
        $stmt_get_booth = $conn->prepare("SELECT booth_id FROM booking WHERE booking_id = ?");
        $stmt_get_booth->bind_param('i', $Bid);
        $stmt_get_booth->execute();
        $result = $stmt_get_booth->get_result();
        $booth = $result->fetch_assoc();

        if (!$booth) {
            throw new Exception("ไม่พบข้อมูลการจองหรือบูธ");
        }

        $booth_id = $booth['booth_id'];

        $stmt_canceled = $conn->prepare("SELECT * FROM booking WHERE booking_id = ? ");
        $stmt_canceled->bind_param('i', $Bid);
        $stmt_canceled->execute();  
        $stmt_canceled_data = $stmt_canceled->get_result();
        $result = $stmt_canceled_data -> fetch_assoc(); 
        
        if ($result['booking_status'] === "ยกเลิกการจอง"){
            $response->getBody()->write(json_encode(["message" => "ได้ทำการยกเลิกการจองสำเร็จแล้ว!!!"]));
            return $response->withHeader('Content-type', 'application/json')->withStatus(200);
        }


        // อัปเดตสถานะการจองเป็น 'ยกเลิกการจอง'
        $stmt_booking = $conn->prepare("UPDATE booking SET booking_status = 'ยกเลิกการจอง' WHERE booking_id = ?");
        $stmt_booking->bind_param('i', $Bid);
        $stmt_booking->execute();

        // อัปเดตสถานะบูธเป็น 'ว่าง'
        $stmt_booth = $conn->prepare("UPDATE booth SET status = 'ว่าง' WHERE booth_id = ?");
        $stmt_booth->bind_param('i', $booth_id);
        $stmt_booth->execute();

        // Commit การเปลี่ยนแปลง
        $conn->commit();

        // ส่ง response เมื่อสำเร็จ
        $response->getBody()->write(json_encode(["message" => "ยกเลิกการจองสำเร็จ"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        // Rollback ถ้ามีข้อผิดพลาด
        $conn->rollback();
        $response->getBody()->write(json_encode(["message" => "ไม่สามารถยกเลิกการจองได้: " . $e->getMessage()]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }
});


//payments
$app->put('/booking/payment/{booking_id}', function (Request $request, Response $response, array $args) {
    $bookingId = $args['booking_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    if (empty($bodyArr['bill_img'])) {
        $response->getBody()->write(json_encode(["message" => "กรุณาส่งสลิป หรือ แนบหลักฐานการโอนมาด้วยครับ 😊"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบสถานะการจอง
    $stmtCheck_status = $conn->prepare("SELECT * FROM booking WHERE booking_id = ?");
    $stmtCheck_status->bind_param("i", $bookingId);
    $stmtCheck_status->execute();
    $result_status = $stmtCheck_status->get_result();
    $statusData = $result_status->fetch_assoc();
    
    if (!$statusData) {
        $response->getBody()->write(json_encode(["message" => "ไม่พบข้อมูลที่ตรงกับ ID"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(404);
    }

    if ($statusData['booking_status'] == "ชำระเงินแล้ว") {
        $response->getBody()->write(json_encode(["message" => "ท่านได้ทำการชำระเงินเรียบร้อยแล้ว กรุณารอการตรวจสอบ!! ✅"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(200);
    }

    // ตรวจสอบเวลาที่เหลือก่อนวันเริ่มงาน
    $stmt = $conn->prepare("SELECT 
            e.start_at_date, 
            DATEDIFF(e.start_at_date, current_timestamp()) AS days_until_event
        FROM booking b
        JOIN events e ON b.event_id = e.event_id
        WHERE b.booking_id = ?
    ");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $eventData = $result->fetch_assoc();

    if (!$eventData) {
        $response->getBody()->write(json_encode(["message" => "ไม่พบข้อมูลที่ตรงกับ ID"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(404);
    }

    $daysUntilEvent = $eventData['days_until_event'];

    if ($daysUntilEvent >= 5) {
        // อัปเดตการชำระเงิน
        $stmt = $conn->prepare("UPDATE booking 
            SET bill_img = ?, booking_pay = current_timestamp(), booking_status = 'ชำระเงินแล้ว'
            WHERE booking_id = ?");
        $stmt->bind_param("si", $bodyArr['bill_img'], $bookingId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response->getBody()->write(json_encode(["message" => "ชำระเงินเรียบร้อยแล้ว ขั้นตอนต่อไปโปรดรอการอนุมัติ!!", "เริ่มงาน/กิจกรรมในอีก" => "$daysUntilEvent วัน"]));
            return $response->withHeader('Content-type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(["message" => "ไม่สามารถอัปเดตข้อมูลได้"]));
            return $response->withHeader('Content-type', 'application/json')->withStatus(500);
        }
    } else {
        // ยกเลิกการจองและอัปเดตสถานะบูธ
        $stmt = $conn->prepare("UPDATE booking SET booking_status = 'ยกเลิกการจอง' WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();

        $stmt_booth = $conn->prepare("UPDATE booth b
            JOIN booking bk ON b.booth_id = bk.booth_id
            SET b.status = 'ว่าง'
            WHERE bk.booking_id = ?");
        $stmt_booth->bind_param("i", $bookingId);
        $stmt_booth->execute();

        $response->getBody()->write(json_encode([
            "message" => "ชำระเงินไม่สำเร็จ หรือ เกินเวลาที่กำหนด 5 วันเริ่มงาน/กิจกรรม!!",
            "วันที่เริ่มงาน" => $eventData['start_at_date'],
            "ผ่านมาแล้ว" => "$daysUntilEvent วัน"
        ]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }
});





// put
// $app->put('/booking/update/details/{booking_id}', function (Request $request, Response $response, array $args) {
//     $bookingId = $args['booking_id'];
//     $body = $request->getBody();
//     $bodyArr = json_decode($body, true);
//     $conn = $GLOBALS['conn'];

//     $stmt = $conn->prepare("UPDATE booking SET booking_name = ?, size = ?, products = ?, zone_id = ? WHERE booking_id = ?");
//     $stmt->bind_param(
//         "sssii",
//         $bodyArr['booking_name'],
//         $bodyArr['size'],
//         $bodyArr['products'],
//         $bodyArr['zone_id'],
//         $bookingId
//     );
//     $stmt->execute();
//     $result = $stmt->affected_rows;
//     $response->getBody()->write($result . "");
//     return $response->withHeader('Content-type', 'application/json');
// });


// delete
$app->delete('/booking/delete/{booking_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['booking_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("DELETE FROM booking WHERE booking_id = ?");
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
