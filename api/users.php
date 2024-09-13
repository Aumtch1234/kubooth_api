<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT; // Import Firebase JWT

$app->get('/users/{users_id}', function (Request $request, Response $response, $args) {
    $eId = $args['users_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From users WHERE id = ?");
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

// register
$app->post('/users/register', function (Request $request, Response $response, array $args) {
    $body = $request->getBody()->getContents();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    if (!$conn) {
        $response->getBody()->write(json_encode(['message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้']));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }

    if (empty($bodyArr['email']) || empty($bodyArr['password']) || empty($bodyArr['fname']) || empty($bodyArr['lname']) || empty($bodyArr['phone'])) {
        $response->getBody()->write(json_encode(['message' => "ข้อมูลไม่ครบถ้วน กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }else if(strlen($bodyArr['phone']) > 10) {
        $response->getBody()->write(json_encode(['message' => "เบอร์โทรศัพท์เกิน 10 ตัว กรุณาตรวจสอบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }else if(strlen($bodyArr['password']) <= 6) {
        $response->getBody()->write(json_encode(['message' => "สร้างรหัสผ่านอย่างน้อย 6 ตัวขึ้นไป"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่าอีเมลมีอยู่ในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $count =  $bodyArr['email'];
    $stmt->bind_param("s", $bodyArr['email']);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // ใช้ตัวแปร $count เพื่อตรวจสอบจำนวนผู้ใช้ที่มีอีเมลนี้
    if ($count > 0) {
        $response->getBody()->write(json_encode(["message" => "อีเมลนี้มีอยู่แล้วในระบบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ถ้าอีเมลไม่ซ้ำให้ทำการเพิ่มผู้ใช้
    $hashPassword = password_hash($bodyArr['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (fname, lname, email, password, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssss", // เปลี่ยน 'i' เป็น 's' เนื่องจากเบอร์โทรศัพท์อาจมีเครื่องหมายพิเศษ
        $bodyArr['fname'],
        $bodyArr['lname'],
        $bodyArr['email'],
        $hashPassword,
        $bodyArr['phone']
    );

    $stmt->execute();
    $result = $stmt->affected_rows;
    $stmt->close();

    if ($result > 0) {
        $response->getBody()->write(json_encode(["message" => "เพิ่มข้อมูล สำเร็จ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(201);
    } else {
        $response->getBody()->write(json_encode(["message" => "เพิ่มข้อมูล ไม่สำเร็จ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }
});


// Edit Name
$app->put('/users/name/{users_id}', function (Request $request, Response $response, array $args) {
    $userId = $args['users_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    // ตรวจสอบการเชื่อมต่อฐานข้อมูล
    if (!$conn) {
        $response->getBody()->write(json_encode(['message' => "การเชื่อมต่อฐานข้อมูลล้มเหลว"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }

    // ตรวจสอบว่าข้อมูลครบถ้วนหรือไม่
    if (empty($bodyArr['pname']) || empty($bodyArr['fname']) || empty($bodyArr['lname']) || empty($bodyArr['phone']) || empty($bodyArr['email']) || empty($bodyArr['password'])) {
        $response->getBody()->write(json_encode(['message' => "ข้อมูลไม่ครบถ้วน กรุณาตรวจกรอกให้ครบ!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบความยาวรหัสผ่าน
    if (strlen($bodyArr['password']) < 6) {
        $response->getBody()->write(json_encode(['message' => "กรุณาใส่รหัสผ่าน 6 ตัวขึ้นไป!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบว่าอีเมลมีอยู่ในฐานข้อมูลหรือไม่
    $stmtCheckEmail = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND user_id != ?");
    $stmtCheckEmail->bind_param("si", $bodyArr['email'], $userId);
    $stmtCheckEmail->execute();
    $resultCheckEmail = $stmtCheckEmail->get_result();
    $emailCount = $resultCheckEmail->fetch_assoc()['count'];

    if ($emailCount > 0) {
        $response->getBody()->write(json_encode(['message' => "อีเมลนี้ถูกใช้งานแล้ว กรุณาเลือกอีเมลอื่น!!"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(400);
    }

    // ตรวจสอบสถานะการอัปเดต
    $stmtUpdate = $conn->prepare("UPDATE users 
        SET pname = ?, fname = ?, lname = ?, phone = ?, email = ?, password = ?, update_at = NOW()
        WHERE user_id = ?"
    );

    // Hash password
    $hashPassword = password_hash($bodyArr['password'], PASSWORD_DEFAULT);

    // Bind parameters
    $stmtUpdate->bind_param(
        "ssssssi",
        $bodyArr['pname'], 
        $bodyArr['fname'],
        $bodyArr['lname'],
        $bodyArr['phone'],
        $bodyArr['email'],
        $hashPassword,
        $userId
    );

    // Execute statement
    if ($stmtUpdate->execute()) {
        $response->getBody()->write(json_encode(["message" => "แก้ไขข้อมูลสำเร็จแล้ว ✅"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(["message" => "แก้ไขข้อมูลไม่สำเร็จ"]));
        return $response->withHeader('Content-type', 'application/json')->withStatus(500);
    }
});




// delete
// $app->delete('/users/delete/{users_id}', function (Request $request, Response $response, array $args) {
//     $eId = $args['users_id'];
//     $conn = $GLOBALS['conn'];
//     $stmt = $conn->prepare("DELETE FROM users WHERE users_id = ?");
//     $stmt->bind_param("i", $eId);
//     $stmt->execute();
//     $result = $stmt->affected_rows;

//     if ($result > 0) {
//         $response->getBody()->write(json_encode(["message" => "ลบกิจกรรม สำเร็จ!!"]));
//     } else {
//         $response->getBody()->write(json_encode(["message" => "ลบกิจกรรม ไม่สำเร็จ!! หรือ ไม่พบ ID."]));
//     }

//     return $response->withHeader('Content-type', 'application/json');
// });

//login
$app->post('/users/login', function (Request $request, Response $response, array $args) {
    $body = $request->getBody()->getContents();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];

    try {
        // ตรวจสอบ JSON ที่รับเข้ามาว่าเป็นข้อมูลที่ถูกต้อง
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("ข้อมูล JSON ไม่ถูกต้อง!!");
        }

        // ตรวจสอบว่า email และ password ไม่ว่างเปล่า
        if (empty($bodyArr['email']) || empty($bodyArr['password'])) {
            throw new Exception("ข้อมูลไม่ครบถ้วน กรุณาตรวจสอบ!!");
        }

        // ตรวจสอบรูปแบบของอีเมล
        if (!filter_var($bodyArr['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("รูปแบบอีเมลไม่ถูกต้อง!!");
        }

        // ค้นหาผู้ใช้จากฐานข้อมูลตามอีเมล
        $userId = '';
        $hashedPassword = '';

        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("การเตรียมคำสั่ง SQL ผิดพลาด");
        }
        $stmt->bind_param("s", $bodyArr['email']);
        $stmt->execute();
        $stmt->bind_result($userId, $hashedPassword);
        $stmt->fetch();
        $stmt->close();

        // ตรวจสอบว่าพบผู้ใช้หรือไม่
        if ($userId) {
            // ตรวจสอบรหัสผ่านว่าตรงกันหรือไม่
            if (password_verify($bodyArr['password'], $hashedPassword)) {
                // สร้าง JWT token
                $jwt = generate_jwt($userId);

                // ค้นหาข้อมูลของผู้ใช้ที่ล็อกอิน
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                if (!$stmt) {
                    throw new Exception("การเตรียมคำสั่ง SQL ผิดพลาด");
                }
                $stmt->bind_param("s", $bodyArr['email']);
                $stmt->execute();
                $result = $stmt->get_result();
                $data_users = array();
                while ($row = $result->fetch_assoc()) {
                    array_push($data_users, $row);
                }

                // ค้นหาการจองที่เกี่ยวข้องกับผู้ใช้ที่ล็อกอินเข้ามา
                $stmt_booking = $conn->prepare("SELECT * FROM booking WHERE user_id = ?");
                if (!$stmt_booking) {
                    throw new Exception("การเตรียมคำสั่ง SQL ผิดพลาด");
                }
                $stmt_booking->bind_param("i", $userId); // bind ค่า user_id ที่ได้จากการล็อกอิน
                $stmt_booking->execute();
                $result_booking = $stmt_booking->get_result();
                $data_booking = array();
                while ($row = $result_booking->fetch_assoc()) {
                    array_push($data_booking, $row);
                }

                // ส่ง response กลับไปยัง client พร้อมกับ JWT token และข้อมูลการจอง
                $response->getBody()->write(json_encode([
                    "message" => "ล็อกอินสำเร็จ!!",
                    "result" => true,
                    "data" => [
                        "token" => $jwt,
                        "user_data" => $data_users,
                        "user_bookings" => $data_booking
                    ]
                ]));
                return $response->withHeader('Content-type', 'application/json')
                                ->withStatus(200);
            } else {
                throw new Exception("รหัสผ่านไม่ถูกต้อง!!");
            }
        } else {
            throw new Exception("ไม่พบผู้ใช้ที่มีอีเมลนี้อยู่ในระบบ!!");
        }
    } catch (Exception $e) {
        // ส่ง response ข้อผิดพลาด
        $response->getBody()->write(json_encode([
            "message" => $e->getMessage(),
            "result" => false,
            "data" => null
        ]));
        return $response->withHeader('Content-type', 'application/json')
                        ->withStatus(400);
    }
});



// ฟังก์ชันสร้าง JWT
function generate_jwt($userId) {
    $key = "Arms_Cpe65_allsystem"; // ใช้ key ลับในการเข้ารหัส
    $payload = [
        'iss' => 'your_iss', // issuer
        'sub' => $userId, // subject (user id)
        'iat' => time(), // issued at
        'exp' => time() + (1 * 60) // หมดอายุใน 1 ชั่วโมง
    ];

    $alg = 'HS256'; // อัลกอริธึมที่ใช้เข้ารหัส (HS256)
    
    return JWT::encode($payload, $key, $alg);
}
?>