<?php
ini_set("display_errors",1);
ini_set("display_startup_errors",1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// get
$app->get('/booth', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From booth");
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
$app->get('/booth/{booth_id}', function (Request $request, Response $response, $args) {
    $eId = $args['booth_id'];
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("select * From booth WHERE booth_id = ?");
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
$app->post('/booth/insert', function (Request $request, Response $response, array $args) {
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("INSERT INTO booth" . 
        "(booth_name, size, products, zone_id)".
        "VALUES (?,?,?,?)");
    $stmt->bind_param("sssi",
        $bodyArr['booth_name'], $bodyArr['size'], $bodyArr['products'], $bodyArr['zone_id']
    );
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
    return $response->withHeader('Content-type', 'application/json');
});

// put
$app->put('/booth/update/{booth_id}', function (Request $request, Response $response, array $args) {
    $eId = $args['booth_id'];
    $body = $request->getBody();
    $bodyArr = json_decode($body, true);
    $conn = $GLOBALS['conn'];
    $stmt = $conn->prepare("UPDATE booth SET booth_name = ?, size = ?, products = ?, zone_id = ? WHERE booth_id = ?");
    $stmt->bind_param("sssii",
        $bodyArr['booth_name'], 
        $bodyArr['size'], 
        $bodyArr['products'], 
        $bodyArr['zone_id'],
        $eId);
    $stmt->execute();
    $result = $stmt->affected_rows;
    $response->getBody()->write($result."");
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

?>