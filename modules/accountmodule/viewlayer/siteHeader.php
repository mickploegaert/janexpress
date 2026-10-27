<?php

$publicPath ??= '../../../public';
$homeUrl ??= '../../../public/index.php';
$ordersUrl ??= '../../ordermodule/viewlayer/Orders.php';
$loginUrl ??= 'login.php';
$accountUrl ??= 'account.php';
$pageTitle ??= 'JanExpress';
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e($publicPath) ?>/css/homepage.css">
    <link rel="stylesheet" href="<?= e($publicPath) ?>/css/auth.css">
</head>

<body class="page-auth">
    <header class="header">
        <a class="brand" href="<?= e($homeUrl) ?>"><img class="brand-logo" src="<?= e($publicPath) ?>/logo/janexpresslogo.png" alt=""><span class="brand-name">JanExpress</span></a>
        <form class="search" action="<?= e($homeUrl) ?>#producten" method="get">
            <svg class="search-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="11" cy="11" r="6.5"></circle>
                <path d="m16 16 4 4"></path>
            </svg>
            <input type="search" name="zoek" placeholder="Waar ben je naar op zoek?" aria-label="Zoeken">
        </form>
        <a class="nav-link" href="<?= e($homeUrl) ?>#producten">Producten</a>
        <?php require __DIR__ . '/accountLink.php'; ?>
        <a class="cart header-cart" href="<?= e($ordersUrl) ?>" aria-label="Winkelmand">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4L21 7H7"></path>
                <circle cx="10" cy="19" r="1.2"></circle>
                <circle cx="18" cy="19" r="1.2"></circle>
            </svg>
        </a>
    </header>
    <main class="auth-page">
