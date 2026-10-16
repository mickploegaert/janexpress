<?php

require_once __DIR__ . '/../../accountmodule/logiclayer/Services/customerSession.php';

CustomerSession::start();

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
                <button class="button order-button" id="confirm-order" type="button" disabled>
                    Bestelling bevestigen <span>›</span>
                </button>
            </aside>
        </div>
    </main>

</body>

</html>