<?php

$loginUrl ??= 'login.php';
$accountUrl ??= 'account.php';
$customerName = CustomerSession::firstName();
?>
<?php if ($customerName !== null): ?>
    <a class="account" href="<?= e($accountUrl) ?>" aria-label="Account van <?= e($customerName) ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="8" r="3.2"></circle>
            <path d="M5.5 20c.6-3.2 2.8-5 6.5-5s5.9 1.8 6.5 5"></path>
        </svg>
        <span class="account-name"><?= e($customerName) ?></span>
    </a>
<?php else: ?>
    <a class="account" href="<?= e($loginUrl) ?>" aria-label="Inloggen">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="8" r="3.2"></circle>
            <path d="M5.5 20c.6-3.2 2.8-5 6.5-5s5.9 1.8 6.5 5"></path>
        </svg>
    </a>
<?php endif; ?>
