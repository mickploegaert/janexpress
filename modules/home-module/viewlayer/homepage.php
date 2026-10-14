<?php

require_once __DIR__ . '/../logiclayer/Services/productService.php';

$publicPath ??= '../../../public';
$homeUrl ??= 'homepage.php';
$ordersUrl ??= '../modules/ordermodule/viewlayer/Orders.php';

$products = [];
$error = null;

try {
    foreach ((new ProductService())->getAllProducts() as $product) {
        $products[] = $product;
    }
} catch (RuntimeException $exception) {
    $error = $exception->getMessage();
}

$images = [

];

$categories = [
    'Alles',
    'Elektronica',
    'Sport',
    'Audio',
    'Mode',
];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function categoryName(int $categoryId): string
{
    return match ($categoryId) {
        2 => 'Sport',
        3 => 'Audio',
        4 => 'Mode',
        default => 'Elektronica',
    };
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JanExpress - Slim shoppen</title>
    <link rel="stylesheet" href="<?= e($publicPath) ?>/css/homepage.css">
    <script src="<?= e($publicPath) ?>/js/homepage.js" defer></script>
    <script src="<?= e($publicPath) ?>/js/orders.js" defer></script>
</head>

<body>
    <div id="cart-message" class="cart-message" role="status" aria-live="polite" hidden>
        Product toegevoegd aan je winkelmand.
    </div>

    <header class="header">
        <a class="brand" href="<?= e($homeUrl) ?>"><span class="brand-mark">J</span>JanExpress</a>
        <label class="search">
            <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="11" cy="11" r="6.5"></circle>
                <path d="m16 16 4 4"></path>
            </svg>
            <input type="search" placeholder="Waar ben je naar op zoek?">
        </label>
        <a class="nav-link" href="#producten">Producten</a>
        <a class="account" href="#" aria-label="Account">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8" r="3.2"></circle>
                <path d="M5.5 20c.6-3.2 2.8-5 6.5-5s5.9 1.8 6.5 5"></path>
            </svg>
        </a>
        <a class="cart header-cart" href="<?= e($ordersUrl) ?>" aria-label="Winkelmand">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 7H7"></path>
                <circle cx="10" cy="19" r="1.2"></circle>
                <circle cx="18" cy="19" r="1.2"></circle>
            </svg>
        </a>
    </header>

    <main>
        <section class="hero">
            <span class="eyebrow">Voorjaarsdeals</span>
            <h1>Grote ideeën.<br><span>Scherpe prijzen.</span></h1>
            <p>Ontdek slimme favorieten voor thuis, onderweg en alles daartussenin.</p>
            <a class="button" href="#producten">Bekijk alle deals <span>›</span></a>
        </section>

        <section class="selection" id="producten">
            <div class="section-label">Onze selectie</div>
            <h2>Vind jouw volgende favoriet</h2>
            <nav class="filters" aria-label="Productcategorieën">
                <?php foreach ($categories as $index => $category): ?>
                    <button class="filter<?= $index === 0 ? ' active' : '' ?>" type="button">
                        <?= e($category) ?>
                    </button>
                <?php endforeach; ?>
            </nav>

            <?php if ($error !== null): ?>
                <div class="notice"><?= e($error) ?></div>
            <?php elseif ($products === []): ?>
                <div class="notice">Er zijn momenteel geen producten beschikbaar.</div>
            <?php else: ?>
                <div class="products">
                    <?php foreach ($products as $index => $product): ?>
                        <article class="card" data-id="<?= (int) $product->getId() ?>"
                            data-category="<?= e(categoryName($product->getCategoryId())) ?>"
                            data-name="<?= e($product->getName()) ?>"
                            data-description="<?= e($product->getDescription() ?? 'Geen beschrijving beschikbaar.') ?>"
                            data-price="€ <?= number_format($product->getPrice(), 2, ',', '.') ?>"
                            data-price-value="<?= $product->getPrice() ?>"
                            data-stock="<?= $product->getStock() ?>"
                            data-image="<?= e($product->getImage() ?: $images[$index % count($images)]) ?>">
                            <div class="card-image">
                                <button class="product-image-button" type="button"
                                    aria-label="Bekijk <?= e($product->getName()) ?>">
                                    <img src="<?= e($product->getImage() ?: $images[$index % count($images)]) ?>"
                                        alt="<?= e($product->getName()) ?>">
                                </button>
                                <?php if ($index === 0): ?><span class="badge">Bestseller</span><?php endif; ?>
                            </div>
                            <div class="card-info">
                                <div class="category"><?= e(categoryName($product->getCategoryId())) ?></div>
                                <h3><?= e($product->getName()) ?></h3>
                                <div class="card-bottom">
                                    <span class="price">€ <?= number_format($product->getPrice(), 2, ',', '.') ?></span>
                                    <button class="add<?= $product->getStock() < 1 ? ' disabled' : '' ?>" type="button"
                                        <?= $product->getStock() < 1 ? 'disabled' : '' ?>>+</button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <div class="product-modal" role="dialog" aria-modal="true" aria-labelledby="modal-name" hidden>
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-content">
            <button class="modal-close" type="button" aria-label="Sluiten" data-modal-close>×</button>
            <div class="modal-image">
                <img id="modal-image" src="" alt="">
            </div>
            <div class="modal-details">
                <div class="category" id="modal-category"></div>
                <h2 id="modal-name"></h2>
                <p id="modal-description"></p>
                <div class="stock-status" id="modal-stock"></div>
                <div class="modal-bottom">
                    <strong class="modal-price" id="modal-price"></strong>
                    <button class="button modal-cart" type="button">
                        <svg class="cart-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 7H7" />
                            <circle cx="10" cy="19" r="1.2" />
                            <circle cx="18" cy="19" r="1.2" />
                        </svg>
                        <span>In winkelmand</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-grid">
            <div>
                <div class="footer-brand"><span class="brand-mark">J</span>JanExpress</div>
                <p class="footer-description">Slim shoppen, zonder gedoe. Alles wat je nodig hebt op één plek.</p>
            </div>
            <div>
                <h3>Klantenservice</h3>
                <a href="#">Bestellen & betalen</a>
                <a href="#">Bezorgen & ophalen</a>
                <a href="#">Retourneren</a>
            </div>
            <div>
                <h3>Over JanExpress</h3>
                <a href="#">Over ons</a>
                <a href="#">Werken bij</a>
                <a href="#">Privacy</a>
            </div>
        </div>
    </footer>
</body>

</html>