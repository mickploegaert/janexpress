<?php

require_once __DIR__ . '/../../accountmodule/logiclayer/Services/customerSession.php';
require_once __DIR__ . '/../logiclayer/Services/orderService.php';
require_once __DIR__ . '/../datalayer/entities/OrderItem.php';

CustomerSession::start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $payload = json_decode(file_get_contents('php://input'), true);
    $csrfToken = is_array($payload) ? ($payload['csrf_token'] ?? null) : null;

    if (!CustomerSession::isLoggedIn() || !CustomerSession::matchesToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'Je sessie is verlopen. Log opnieuw in.']);
        exit;
    }

    $cart = is_array($payload['cart'] ?? null) ? $payload['cart'] : [];
    $items = [];
    $total = 0.0;

    foreach ($cart as $cartItem) {
        $productId = filter_var($cartItem['id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($cartItem['quantity'] ?? null, FILTER_VALIDATE_INT);
        $price = filter_var($cartItem['price'] ?? null, FILTER_VALIDATE_FLOAT);

        if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1 || $price === false || $price < 0) {
            http_response_code(422);
            echo json_encode(['error' => 'De winkelmand bevat ongeldige gegevens.']);
            exit;
        }

        $items[] = new OrderItem(null, null, $productId, $quantity, $price);
        $total += $quantity * $price;
    }

    if ($items === []) {
        http_response_code(422);
        echo json_encode(['error' => 'Je winkelmand is leeg.']);
        exit;
    }

    try {
        $customerId = CustomerSession::id();
        if ($customerId === null) {
            http_response_code(403);
            echo json_encode(['error' => 'Je moet ingelogd zijn om een bestelling te plaatsen.']);
            exit;
        }

        $order = new Order(
            null,
            $customerId,
            date('Y-m-d H:i:s'),
            $total,
            'new',
            $items
        );
        (new OrderService())->createOrderWithItems(new OrderDTO(
            null,
            $order->getCustomerId(),
            $order->getOrderDate(),
            $order->getTotalAmount(),
            $order->getStatus(),
            $items
        ));
        CustomerSession::flash('Bestelling is opgeslagen.');
        echo json_encode([
            'success' => true,
            'redirect' => '../../accountmodule/viewlayer/account.php',
        ]);
    } catch (Throwable $exception) {
        error_log('Order creation failed: ' . $exception->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'De bestelling kon niet worden opgeslagen.']);
    }
    exit;
}

$publicPath = '../../../public';
$homeUrl = '../../../public/index.php';
$loginUrl = '../../accountmodule/viewlayer/login.php';
$accountUrl = '../../accountmodule/viewlayer/account.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelmand - JanExpress</title>
    <link rel="stylesheet" href="<?= e($publicPath) ?>/css/homepage.css">
    <link rel="stylesheet" href="<?= e($publicPath) ?>/css/orders.css">
    <script src="<?= e($publicPath) ?>/js/orders.js" defer></script>
</head>

<body>
    <header class="header">
        <a class="brand" href="<?= e($homeUrl) ?>"><img class="brand-logo" src="<?= e($publicPath) ?>/logo/janexpresslogo.png" alt=""><span class="brand-name">JanExpress</span></a>
        <label class="search">
            <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="11" cy="11" r="6.5"></circle>
                <path d="m16 16 4 4"></path>
            </svg>
            <input type="search" placeholder="Waar ben je naar op zoek?">
        </label>
        <a class="nav-link" href="<?= e($homeUrl) ?>">Producten</a>
        <?php require __DIR__ . '/../../accountmodule/viewlayer/accountLink.php'; ?>
        <a class="cart header-cart" href="#" aria-label="Winkelmand">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 7H7"></path>
                <circle cx="10" cy="19" r="1.2"></circle>
                <circle cx="18" cy="19" r="1.2"></circle>
            </svg>
        </a>
    </header>

    <main class="cart-page">
        <div class="section-label">Jouw bestelling</div>
        <h1>Winkelmand</h1>
        <div class="cart-layout">
            <section>
                <div id="cart-items" class="cart-items"></div>
                <div id="empty-cart" class="empty-cart" hidden>
                    Je winkelmand is nog leeg. <a href="<?= e($homeUrl) ?>"><u>Bekijk de producten</u></a>
                </div>
            </section>
            <aside id="cart-summary" class="cart-summary" hidden>
                <h2>Overzicht</h2>
                <div class="summary-line">
                    <span>Subtotaal</span>
                    <span id="cart-subtotal">€ 0,00</span>
                </div>
                <div class="summary-line">
                    <span>Verzending</span>
                    <strong>Gratis</strong>
                </div>
                <div class="summary-total">
                    <strong>Totaal</strong>
                    <strong id="cart-total">€ 0,00</strong>
                </div>
                <label class="order-check">
                    <input type="checkbox" id="order-confirmation">
                    <span>Ik heb mijn bestelling en bezorggegevens gecontroleerd.</span>
                </label>
                <button class="button order-button" id="confirm-order" type="button" data-csrf="<?= e(CustomerSession::token()) ?>" disabled>
                    Bestelling bevestigen <span>›</span>
                </button>
            </aside>
        </div>
    </main>
    <div id="cart-message" class="cart-message" role="status" aria-live="polite" hidden></div>

</body>

</html>