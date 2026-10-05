
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond(bool $success, string $message, $data = null, int $status = 200): void
{
    http_response_code($status);

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);

    exit;
}

// Login session check
$userId = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? null);

if (!$userId) {
    respond(false, 'Please login to continue.', null, 401);
}

try {
    // XAMPP default MySQL settings
    $pdo = new PDO(
        'mysql:host=localhost;dbname=shopsphere_db;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    respond(false, 'Database connection failed.', null, 500);
}

$action = $_GET['action'] ?? '';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    switch ($action) {

        // Get logged-in user's cart
        case 'list':
            $stmt = $pdo->prepare(
                'SELECT product_id, quantity
                 FROM cart_items
                 WHERE user_id = ?
                 ORDER BY id DESC'
            );

            $stmt->execute([$userId]);

            respond(true, 'Cart loaded.', $stmt->fetchAll());

        // Add product to cart
        case 'add':
            $productId = filter_var(
                $input['product_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$productId || $productId < 1) {
                respond(false, 'Invalid product.', null, 400);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO cart_items (user_id, product_id, quantity)
                 VALUES (?, ?, 1)
                 ON DUPLICATE KEY UPDATE quantity = quantity + 1'
            );

            $stmt->execute([$userId, $productId]);

            respond(true, 'Product added to cart.');

        // Update quantity
        case 'update':
            $productId = filter_var(
                $input['product_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            $quantity = filter_var(
                $input['quantity'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$productId || $productId < 1 ||
                $quantity === false || $quantity < 1 || $quantity > 99) {
                respond(false, 'Invalid product or quantity.', null, 400);
            }

            $stmt = $pdo->prepare(
                'UPDATE cart_items
                 SET quantity = ?
                 WHERE user_id = ? AND product_id = ?'
            );

            $stmt->execute([$quantity, $userId, $productId]);

            respond(true, 'Cart quantity updated.');

        // Remove product
        case 'remove':
            $productId = filter_var(
                $input['product_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$productId || $productId < 1) {
                respond(false, 'Invalid product.', null, 400);
            }

            $stmt = $pdo->prepare(
                'DELETE FROM cart_items
                 WHERE user_id = ? AND product_id = ?'
            );

            $stmt->execute([$userId, $productId]);

            respond(true, 'Product removed from cart.');

        // Empty cart
        case 'clear':
            $stmt = $pdo->prepare(
                'DELETE FROM cart_items WHERE user_id = ?'
            );

            $stmt->execute([$userId]);

            respond(true, 'Cart cleared.');

        default:
            respond(false, 'Invalid cart action.', null, 400);
    }

} catch (PDOException $e) {
    respond(false, 'Cart operation failed.', null, 500);
}