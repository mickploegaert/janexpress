<?php

require_once __DIR__ . '/../logiclayer/Services/customerSession.php';

CustomerSession::start();

$token = $_POST['csrf_token'] ?? null;
$isValidPost = $_SERVER['REQUEST_METHOD'] === 'POST'
    && is_string($token)
    && CustomerSession::matchesToken($token);

if ($isValidPost) {
    CustomerSession::logout();
    header('Location: ../../../public/index.php');
    exit;
}

header('Location: ' . (CustomerSession::isLoggedIn() ? 'account.php' : 'login.php'));
exit;
