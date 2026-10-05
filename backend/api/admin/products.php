
<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

session_start();

if (!isset($_SESSION["admin_id"])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Admin login required."
    ]);
    exit;
}

$host = "localhost";
$dbname = "shopsphere_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);
    exit;
}

$action = $_GET["action"] ?? "";

function getJsonInput(): array {
    $data = json_decode(file_get_contents("php://input"), true);
    return is_array($data) ? $data : [];
}

function sendResponse(
    bool $success,
    string $message,
    $data = null,
    int $status = 200
): void {
    http_response_code($status);

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);

    exit;
}

/* LIST PRODUCTS */
if ($action === "list" && $_SERVER["REQUEST_METHOD"] === "GET") {
    $stmt = $pdo->query(
        "SELECT id, name, description, price, category, image, stock,
                created_at, updated_at
         FROM products
         ORDER BY id DESC"
    );

    sendResponse(true, "Products loaded.", $stmt->fetchAll());
}

/* ADD PRODUCT */
if ($action === "add" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $input = getJsonInput();

    $name = trim($input["name"] ?? "");
    $description = trim($input["description"] ?? "");
    $price = $input["price"] ?? null;
    $category = trim($input["category"] ?? "");
    $image = trim($input["image"] ?? "");
    $stock = $input["stock"] ?? 0;

    if ($name === "" || !is_numeric($price) || $price < 0) {
        sendResponse(
            false,
            "Product name and valid price are required.",
            null,
            400
        );
    }

    if (!is_numeric($stock) || (int)$stock < 0) {
        sendResponse(false, "Stock must be 0 or greater.", null, 400);
    }

    $stmt = $pdo->prepare(
        "INSERT INTO products
         (name, description, price, category, image, stock)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $name,
        $description,
        $price,
        $category,
        $image,
        (int)$stock
    ]);

    sendResponse(
        true,
        "Product added successfully.",
        ["id" => (int)$pdo->lastInsertId()]
    );
}

/* EDIT PRODUCT */
if ($action === "update" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $input = getJsonInput();

    $id = $input["id"] ?? null;
    $name = trim($input["name"] ?? "");
    $description = trim($input["description"] ?? "");
    $price = $input["price"] ?? null;
    $category = trim($input["category"] ?? "");
    $image = trim($input["image"] ?? "");
    $stock = $input["stock"] ?? 0;

    if (!filter_var($id, FILTER_VALIDATE_INT) || (int)$id < 1) {
        sendResponse(false, "Valid product ID is required.", null, 400);
    }

    if ($name === "" || !is_numeric($price) || $price < 0) {
        sendResponse(
            false,
            "Product name and valid price are required.",
            null,
            400
        );
    }

    if (!is_numeric($stock) || (int)$stock < 0) {
        sendResponse(false, "Stock must be 0 or greater.", null, 400);
    }

    $stmt = $pdo->prepare(
        "UPDATE products
         SET name = ?, description = ?, price = ?,
             category = ?, image = ?, stock = ?
         WHERE id = ?"
    );

    $stmt->execute([
        $name,
        $description,
        $price,
        $category,
        $image,
        (int)$stock,
        (int)$id
    ]);

    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $check->execute([(int)$id]);

        if (!$check->fetch()) {
            sendResponse(false, "Product not found.", null, 404);
        }
    }

    sendResponse(true, "Product updated successfully.");
}

/* DELETE PRODUCT */
if ($action === "delete" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $input = getJsonInput();
    $id = $input["id"] ?? null;

    if (!filter_var($id, FILTER_VALIDATE_INT) || (int)$id < 1) {
        sendResponse(false, "Valid product ID is required.", null, 400);
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([(int)$id]);

    if ($stmt->rowCount() === 0) {
        sendResponse(false, "Product not found.", null, 404);
    }

    sendResponse(true, "Product deleted successfully.");
}

sendResponse(false, "Invalid action.", null, 400);