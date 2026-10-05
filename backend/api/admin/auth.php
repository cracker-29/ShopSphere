<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

session_start();

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

if ($action === "login" && $_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    $email = trim($input["email"] ?? "");
    $passwordInput = $input["password"] ?? "";

    if ($email === "" || $passwordInput === "") {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Email and password are required."
        ]);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password_hash
         FROM admins
         WHERE email = ?
         LIMIT 1"
    );
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($passwordInput, $admin["password_hash"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid admin email or password."
        ]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION["admin_id"] = (int)$admin["id"];
    $_SESSION["admin_name"] = $admin["name"];
    $_SESSION["admin_email"] = $admin["email"];

    echo json_encode([
        "success" => true,
        "message" => "Admin login successful.",
        "data" => [
            "id" => (int)$admin["id"],
            "name" => $admin["name"],
            "email" => $admin["email"]
        ]
    ]);
    exit;
}

if ($action === "me" && $_SERVER["REQUEST_METHOD"] === "GET") {
    if (!isset($_SESSION["admin_id"])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Admin is not logged in."
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => [
            "id" => $_SESSION["admin_id"],
            "name" => $_SESSION["admin_name"],
            "email" => $_SESSION["admin_email"]
        ]
    ]);
    exit;
}

if ($action === "logout" && $_SERVER["REQUEST_METHOD"] === "POST") {
    unset(
        $_SESSION["admin_id"],
        $_SESSION["admin_name"],
        $_SESSION["admin_email"]
    );

    echo json_encode([
        "success" => true,
        "message" => "Admin logged out."
    ]);
    exit;
}

http_response_code(400);
echo json_encode([
    "success" => false,
    "message" => "Invalid action."
]);