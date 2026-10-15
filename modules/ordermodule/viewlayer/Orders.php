<?php

$publicPath = '../../../public';
$homeUrl = '../../../public/index.php';

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
        <a class="brand" href="<?= e($homeUrl) ?>"><span class="brand-mark">J</span>JanExpress</a>
        <label class="search">
            <input type="search" placeholder="Waar ben je naar op zoek?">
        </label>
        <a class="nav-link" href="<?= e($homeUrl) ?>">Producten</a>
        <a class="cart header-cart" href="#" aria-label="Winkelmand">🛒</a>
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