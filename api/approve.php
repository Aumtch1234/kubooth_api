<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


$app->put('/admin/booking/approve/{booking_id}', function (Request $request, Response $response, array $args) {
    $Bid = $args['booking_id'];
    $conn = $GLOBALS['conn'];

    // เริ่มต้น transaction
    $conn->begin_transaction();

    try {
        // ดึงข้อมูลการจองด้วย booking_id
        $stmt_get_booking = $conn->prepare("SELECT * FROM booking WHERE booking_id = ?");
        $stmt_get_booking->bind_param('i', $Bid);
        $stmt_get_booking->execute();
        $result = $stmt_get_booking->get_result();
        $booking = $result->fetch_assoc();

        if (!$booking) {
            throw new Exception("ไม่พบข้อมูลการจอง!!");
        }

        // ตรวจสอบสถานะการจองว่าได้ชำระเงินแล้วหรือไม่
        if ($booking['booking_status'] === "อยู่ระหว่างการตรวจสอบ") {
            $response->getBody()->write(json_encode(["message" => "การจองนี้ยังไม่ได้ชำระเงิน!!"]));
            return $response->withHeader('Content-type', 'application/json')->withStatus(400);
        }else if($booking['booking_status'] === "ยกเลิกการจอง"){
            $response->getBody()->write(json_encode(["message" => "การจองนี้ได้ทำการยกเลิกแล้ว!!"]));
            return $response->withHeader('Content-type', 'application/json')->withStatus(400);
        }

        // อัปเดตสถานะการจองเป็น 'อนุมัติแล้ว'
        $stmt_update_booking = $conn->prepare("UPDATE booking SET booking_status = 'อนุมัติแล้ว' WHERE booking_id = ?");
        $stmt_update_booking->bind_param('i', $Bid);
        $stmt_update_booking->execute();

        // อัปเดตสถานะบูธเป็น 'จองแล้ว'
        $stmt_update_booth = $conn->prepare("UPDATE booth SET status = 'จองแล้ว' WHERE booth_id = ?");
        $stmt_update_booth->bind_param('i', $booking['booth_id']);
        $stmt_update_booth->execute();

        // Commit การเปลี่ยนแปลง
        $conn->commit();

        // ส่ง response เมื่อสำเร็จ
        $response->getBody()->write(json_encode(["message" => "อนุมัติการจองสำเร็จ!!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        // Rollback ถ้ามีข้อผิดพลาด
        $conn->rollback();
        $response->getBody()->write(json_encode(["message" => "ไม่สามารถอนุมัติการจองได้: " . $e->getMessage()]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }
});

?>