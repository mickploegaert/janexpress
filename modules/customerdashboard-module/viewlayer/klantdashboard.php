<?php

require_once __DIR__ . '/../../accountmodule/logiclayer/Services/customerSession.php';
require_once __DIR__ . '/../logiclayer/Services/CustomerService.php';

CustomerSession::start();
header('Cache-Control: no-store');

if (!CustomerSession::isLoggedIn()) {
    header('Location: ../../accountmodule/viewlayer/login.php');
    exit;
}

$customerId = CustomerSession::id();
$customer = null;
$orders = [];
$error = null;

try {
    if ($customerId !== null) {
        $service = new CustomerService();
        $customer = $service->getCustomerById($customerId);
        $orders = $service->getOrdersByCustomerId($customerId);
    }
} catch (RuntimeException $exception) {
    $error = 'De bestelgeschiedenis kon niet worden geladen.';
}

if ($customer === null && $error === null) {
    CustomerSession::logout();
    header('Location: ../../accountmodule/viewlayer/login.php');
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatOrderDate(string $date): string
{
    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return $date;
    }

    $months = [
        1 => 'januari', 2 => 'februari', 3 => 'maart', 4 => 'april',
        5 => 'mei', 6 => 'juni', 7 => 'juli', 8 => 'augustus',
        9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'december',
    ];

    return date('j', $timestamp) . ' ' . $months[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp);
}

function orderStatus(string $status): string
{
    return match (strtolower($status)) {
        'new' => 'In behandeling',
        'pending' => 'In behandeling',
        'processing' => 'Wordt verwerkt',
        'shipped' => 'Verzonden',
        'delivered' => 'Bezorgd',
        'cancelled', 'canceled' => 'Geannuleerd',
        default => ucfirst($status),
    };
}

$publicPath = '../../../public';
$homeUrl = '../../../public/index.php';
$ordersUrl = '../../ordermodule/viewlayer/Orders.php';
$pageTitle = 'Mijn bestellingen - JanExpress';
$extraStylesheet = 'customer-dashboard.css';
require __DIR__ . '/../../accountmodule/viewlayer/siteHeader.php';
?>
        <div class="dashboard-heading">
            <div>
                <div class="section-label">Mijn account</div>
                <h1>Hoi, <?= e($customer?->getName() ?? 'klant') ?></h1>
            </div>
            <form method="post" action="../../accountmodule/viewlayer/logout.php">
                <input type="hidden" name="csrf_token" value="<?= e(CustomerSession::token()) ?>">
                <button class="dashboard-logout" type="submit">Uitloggen</button>
            </form>
        </div>

        <?php if ($error !== null): ?>
            <div class="form-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <div class="dashboard-layout">
            <section class="profile-card">
                <h2>Persoonlijke gegevens</h2>
                <div class="profile-field">
                    <span>Naam</span>
                    <div><?= e($customer?->getName() ?? '') ?></div>
                </div>
                <div class="profile-field">
                    <span>E-mailadres</span>
                    <div><?= e($customer?->getEmail() ?? '') ?></div>
                </div>
                <?php if ($customer?->getPhone() !== null): ?>
                    <div class="profile-field">
                        <span>Telefoonnummer</span>
                        <div><?= e($customer->getPhone()) ?></div>
                    </div>
                <?php endif; ?>
            </section>
            <section class="orders-history" aria-label="Bestelgeschiedenis">
                <h2>Bestelgeschiedenis</h2>
                <?php if ($orders === []): ?>
                    <div class="dashboard-empty">
                        <p>Je hebt nog geen bestelling geplaatst.</p>
                        <a class="button" href="<?= e($homeUrl) ?>">Bekijk de producten <span>›</span></a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <details class="order-card">
                            <summary>
                                <span>
                                    <strong>OR-<?= e((string) $order->getId()) ?></strong>
                                    <small><?= e(formatOrderDate($order->getOrderDate())) ?></small>
                                </span>
                                <span class="order-summary-right">
                                    <strong>€ <?= e(number_format($order->getTotalAmount(), 2, ',', '.')) ?></strong>
                                    <span class="order-status"><?= e(orderStatus($order->getStatus())) ?></span>
                                </span>
                            </summary>
                            <div class="order-details">
                                <?php foreach ($order->getItems() as $item): ?>
                                    <div class="order-item">
                                        <span>Product #<?= e((string) $item->getProductId()) ?></span>
                                        <span><?= e((string) $item->getQuantity()) ?> × € <?= e(number_format($item->getPrice(), 2, ',', '.')) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
<?php require __DIR__ . '/../../accountmodule/viewlayer/siteFooter.php'; ?>
