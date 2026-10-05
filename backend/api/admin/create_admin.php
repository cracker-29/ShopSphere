<?php
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$dbname = "shopsphere_db";
$username = "root";
$password = "";

$adminName = "ShopSphere Admin";
$adminEmail = "admin@shopsphere.com";
$adminPassword = "Admin@12345";

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

    // Check whether admin already exists
    $check = $pdo->prepare(
        "SELECT id FROM admins WHERE email = ? LIMIT 1"
    );
    $check->execute([$adminEmail]);

    if ($check->fetch()) {
        echo json_encode([
            "success" => true,
            "message" => "Admin account already exists."
        ]);
        exit;
    }

    // Securely hash the password
    $passwordHash = password_hash(
        $adminPassword,
        PASSWORD_DEFAULT
    );

    $insert = $pdo->prepare(
        "INSERT INTO admins (name, email, password_hash)
         VALUES (?, ?, ?)"
    );

    $insert->execute([
        $adminName,
        $adminEmail,
        $passwordHash
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Admin account created successfully.",
        "email" => $adminEmail
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error. Check database settings."
    ]);
}