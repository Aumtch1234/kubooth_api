<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//usersData
$app->get('/admin/report_data/users', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("SELECT pname, fname, lname, phone, email From users");
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

//จอง ยังไม่ชำระเงิน
$app->get('/admin/report_data/no_payment', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];

    // SQL query ที่รวมข้อมูลจากตาราง users, booking, booth, และ zone โดยมีเงื่อนไข booking_status = 'อยู่ระหว่างการตรวจสอบ'
    $stmt = $conn->prepare("SELECT 
            u.pname, u.fname, u.lname, u.phone, bo.booth_name, z.zone_name,  b.booking_status
        FROM booking b
        JOIN users u ON u.user_id = b.user_id
        JOIN booth bo ON bo.booth_id = b.booth_id
        JOIN zone z ON z.zone_id = bo.zone_id
        WHERE b.booking_status = 'จอง'
    ");
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

//ชำระเงินแล้วรอตรวจสอบ
$app->get('/admin/report_data/payment', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];

    // SQL query ที่รวมข้อมูลจากตาราง users, booking, booth, และ zone โดยมีเงื่อนไข booking_status = 'อยู่ระหว่างการตรวจสอบ'
    $stmt = $conn->prepare("SELECT 
            u.pname, u.fname, u.lname, u.phone, bo.booth_name, z.zone_name, b.booking_status
        FROM booking b
        JOIN users u ON u.user_id = b.user_id
        JOIN booth bo ON bo.booth_id = b.booth_id
        JOIN zone z ON z.zone_id = bo.zone_id
        WHERE b.booking_status = 'ชำระเงินแล้ว'
    ");
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

//บูธที่รอการตรวจสอบ
$app->get('/admin/report_data/booth_waitng', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];

    // SQL query ที่รวมข้อมูลจากตาราง users, booking, booth, และ zone โดยมีเงื่อนไข booking_status = 'อยู่ระหว่างการตรวจสอบ'
    $stmt = $conn->prepare("SELECT 
            u.pname, u.fname, u.lname, u.phone, bo.booth_name, z.zone_name, bo.status
        FROM booth bo
        JOIN booking b ON bo.booth_id = b.booth_id
        JOIN users u ON u.user_id = b.user_id
        JOIN zone z ON z.zone_id = bo.zone_id
        WHERE bo.status = 'อยู่ระหว่างการตรวจสอบ'
    ");
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

//บูธที่จองแล้ว
$app->get('/admin/report_data/complete_booking', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("SELECT 
            u.pname, u.fname, u.lname, u.phone, bo.booth_name, z.zone_name, bo.status
        FROM booth bo
        JOIN booking b ON bo.booth_id = b.booth_id
        JOIN users u ON u.user_id = b.user_id
        JOIN zone z ON z.zone_id = bo.zone_id
        WHERE bo.status = 'จองแล้ว'
    ");
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