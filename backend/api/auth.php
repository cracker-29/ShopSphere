
<?php

// ShopSphere Authentication API

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

// Session configuration
session_set_cookie_params([
    "lifetime" => 0,
    "path" => "/",
    "secure" => false,
    "httponly" => true,
    "samesite" => "Lax"
]);

session_start();

// JSON response helper
function respond($success, $message, $data = null, $status = 200)
{
    http_response_code($status);

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);

    exit;
}

// Database connection
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
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    respond(
        false,
        "Database connection failed. Check XAMPP and database settings.",
        null,
        500
    );
}

// Read JSON request
$input = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($input)) {
    $input = [];
}

$action = $_GET["action"] ?? "";

// Get logged-in user
function getUser($pdo)
{
    if (!isset($_SESSION["user_id"])) {
        return null;
    }

    $stmt = $pdo->prepare(
        "SELECT id, name, email, created_at
         FROM users
         WHERE id = ?"
    );

    $stmt->execute([$_SESSION["user_id"]]);

    return $stmt->fetch() ?: null;
}

// REGISTER
if ($action === "register") {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        respond(false, "Invalid request method.", null, 405);
    }

    $name = trim($input["name"] ?? "");
    $email = strtolower(trim($input["email"] ?? ""));
    $password = $input["password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        respond(false, "All fields are required.", null, 422);
    }

    if (strlen($name) > 100) {
        respond(false, "Name is too long.", null, 422);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, "Please enter a valid email address.", null, 422);
    }

    if (strlen($email) > 150) {
        respond(false, "Email is too long.", null, 422);
    }

    if (strlen($password) < 8) {
        respond(
            false,
            "Password must contain at least 8 characters.",
            null,
            422
        );
    }

    // Check duplicate email
    $stmt = $pdo->prepare(
        "SELECT id FROM users WHERE email = ?"
    );

    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        respond(
            false,
            "This email is already registered. Please login.",
            null,
            409
        );
    }

    // Secure password hash
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password)
             VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $email,
            $passwordHash
        ]);

        $userId = $pdo->lastInsertId();

        session_regenerate_id(true);
        $_SESSION["user_id"] = (int) $userId;

        respond(
            true,
            "Account created successfully!",
            [
                "id" => (int) $userId,
                "name" => $name,
                "email" => $email
            ],
            201
        );

    } catch (PDOException $e) {
        respond(
            false,
            "Unable to create account. Please try again.",
            null,
            500
        );
    }
}

// LOGIN
if ($action === "login") {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        respond(false, "Invalid request method.", null, 405);
    }

    $email = strtolower(trim($input["email"] ?? ""));
    $password = $input["password"] ?? "";

    if ($email === "" || $password === "") {
        respond(
            false,
            "Email and password are required.",
            null,
            422
        );
    }

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password
         FROM users
         WHERE email = ?"
    );

    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user["password"])) {
        respond(
            false,
            "Invalid email or password.",
            null,
            401
        );
    }

    session_regenerate_id(true);

    $_SESSION["user_id"] = (int) $user["id"];

    respond(
        true,
        "Login successful!",
        [
            "id" => (int) $user["id"],
            "name" => $user["name"],
            "email" => $user["email"]
        ]
    );
}

// LOGOUT
if ($action === "logout") {

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        respond(false, "Invalid request method.", null, 405);
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            [
                "expires" => time() - 42000,
                "path" => $params["path"],
                "domain" => $params["domain"],
                "secure" => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => "Lax"
            ]
        );
    }

    session_destroy();

    respond(true, "Logged out successfully.");
}

// CURRENT USER
if ($action === "me") {

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        respond(false, "Invalid request method.", null, 405);
    }

    $user = getUser($pdo);

    if (!$user) {
        respond(
            false,
            "Please login to continue.",
            null,
            401
        );
    }

    respond(true, "User information loaded.", $user);
}

// Invalid action
respond(false, "Invalid API action.", null, 404);