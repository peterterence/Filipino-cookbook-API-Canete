<?php

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->setBasePath('/filipino-cookbook-api/public');

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, true, true);



$host = "localhost";
$dbname = "filipino_cookbook_api";
$port = "3307";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch(PDOException $e){

    die(json_encode([
        "status"=>"error",
        "message"=>$e->getMessage()
    ]));
}




$validToken = "dmmmsu-cookbook-token-2026";




$tokenMiddleware = function (Request $request, $handler) use ($validToken){

    $header = $request->getHeaderLine("Authorization");

    if($header != "Bearer ".$validToken){

        $response = new Slim\Psr7\Response();

        $response->getBody()->write(json_encode([
            "status"=>"error",
            "message"=>"Unauthorized access. Valid API token is required."
        ]));

        return $response
            ->withHeader("Content-Type","application/json")
            ->withStatus(401);
    }

    return $handler->handle($request);

};



function sendJson(Response $response, $data, int $status = 200): Response {

    $response->getBody()->write(json_encode($data));

    return $response
        ->withHeader("Content-Type", "application/json")
        ->withStatus($status);
}

function fetchFoods(PDO $pdo, string $whereSql = "", array $params = []): array {

    $sql = "SELECT
                f.food_id,
                f.food_name,
                c.category_name,
                o.origin_name,
                f.instructions,
                GROUP_CONCAT(i.ingredient_name ORDER BY i.ingredient_name SEPARATOR '||') AS ingredients_concat
            FROM foods f
            JOIN categories c ON f.category_id = c.category_id
            JOIN origins o ON f.origin_id = o.origin_id
            LEFT JOIN food_ingredients fi ON f.food_id = fi.food_id
            LEFT JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
            $whereSql
            GROUP BY f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
            ORDER BY f.food_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row["ingredients"] = $row["ingredients_concat"] ? explode("||", $row["ingredients_concat"]) : [];
        unset($row["ingredients_concat"]);
    }
    unset($row);

    return $rows;
}




$app->get('/', function(Request $request, Response $response){

    return sendJson($response, [
        "message"=>"Welcome to the Secured Filipino Cookbook API",
        "note"=>"Use a valid Bearer token to access /api endpoints."
    ]);

});



$app->get('/api/foods', function(Request $request, Response $response) use ($pdo){

    $foods = fetchFoods($pdo);

    return sendJson($response, $foods);

})->add($tokenMiddleware);


$app->get('/api/foods/search/{name}', function(Request $request, Response $response, array $args) use ($pdo){

    $foods = fetchFoods($pdo, "WHERE f.food_name LIKE :name", [
        "name" => "%" . $args['name'] . "%"
    ]);

    return sendJson($response, $foods);

})->add($tokenMiddleware);


$app->get('/api/foods/{id:[0-9]+}', function(Request $request, Response $response, array $args) use ($pdo){

    $foods = fetchFoods($pdo, "WHERE f.food_id = :id", ["id" => $args['id']]);

    if (count($foods) === 0) {
        return sendJson($response, [
            "status"=>"error",
            "message"=>"Food not found"
        ], 404);
    }

    return sendJson($response, $foods[0]);

})->add($tokenMiddleware);


$app->get('/api/categories', function(Request $request, Response $response) use ($pdo){

    $stmt = $pdo->query("SELECT * FROM categories ORDER BY category_id");

    return sendJson($response, $stmt->fetchAll());

})->add($tokenMiddleware);


$app->get('/api/ingredients', function(Request $request, Response $response) use ($pdo){

    $stmt = $pdo->query("SELECT * FROM ingredients ORDER BY ingredient_id");

    return sendJson($response, $stmt->fetchAll());

})->add($tokenMiddleware);


$app->post('/api/foods', function(Request $request, Response $response) use ($pdo){

    $body = $request->getParsedBody();

    if (empty($body['food_name']) || empty($body['category_id'])
        || empty($body['origin_id']) || empty($body['instructions'])) {

        return sendJson($response, [
            "status"=>"error",
            "message"=>"food_name, category_id, origin_id, and instructions are required."
        ], 400);
    }

    $ingredientIds = $body['ingredient_ids'] ?? [];

    try {
        $pdo->beginTransaction();

        $nextId = $pdo->query("SELECT COALESCE(MAX(food_id), 0) + 1 AS next_id FROM foods")->fetch();
        $newFoodId = $nextId['next_id'];

        $stmt = $pdo->prepare(
            "INSERT INTO foods (food_id, food_name, category_id, origin_id, instructions)
             VALUES (:food_id, :food_name, :category_id, :origin_id, :instructions)"
        );
        $stmt->execute([
            "food_id"=>$newFoodId,
            "food_name"=>$body['food_name'],
            "category_id"=>$body['category_id'],
            "origin_id"=>$body['origin_id'],
            "instructions"=>$body['instructions']
        ]);

        if (is_array($ingredientIds) && count($ingredientIds) > 0) {

            $linkStmt = $pdo->prepare(
                "INSERT INTO food_ingredients (food_id, ingredient_id) VALUES (:food_id, :ingredient_id)"
            );

            foreach ($ingredientIds as $ingredientId) {
                $linkStmt->execute([
                    "food_id"=>$newFoodId,
                    "ingredient_id"=>$ingredientId
                ]);
            }
        }

        $pdo->commit();

        return sendJson($response, [
            "status"=>"success",
            "message"=>"Food added successfully."
        ], 201);

    } catch (PDOException $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return sendJson($response, [
            "status"=>"error",
            "message"=>"Database error: " . $e->getMessage()
        ], 500);
    }

})->add($tokenMiddleware);



$app->run();