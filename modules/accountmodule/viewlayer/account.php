<?php

require_once __DIR__ . '/../logiclayer/Services/customerSession.php';
require_once __DIR__ . '/../logiclayer/Services/customerService.php';

CustomerSession::start();
header('Cache-Control: no-store');

if (!CustomerSession::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$customer = null;
$error = null;
$customerId = CustomerSession::id();

try {
    if ($customerId !== null) {
        $customer = (new CustomerService())->getById($customerId);
    }
} catch (RuntimeException $exception) {
    $error = CustomerService::publicError($exception);
}

if ($customer === null && $error === null) {
    CustomerSession::logout();
    header('Location: login.php');
    exit;
}

$flash = CustomerSession::pullFlash();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$pageTitle = 'Je account - JanExpress';
require __DIR__ . '/siteHeader.php';
?>
        <div class="section-label">Account</div>
        <h1>Je account</h1>
        <p class="auth-lead">Bekijk je persoonlijke gegevens en pas ze aan als er iets verandert.</p>
        <section class="auth-card">
            <?php if ($flash !== null): ?>
                <div class="form-success" role="status"><?= e($flash) ?></div>
            <?php endif; ?>
            <?php if ($error !== null): ?>
                <div class="form-error" role="alert"><?= e($error) ?></div>
            <?php elseif ($customer !== null): ?>
                <dl class="account-details">
                    <div>
                        <dt>Naam</dt>
                        <dd><?= e($customer->getFullName()) ?></dd>
                    </div>
                    <div>
                        <dt>E-mailadres</dt>
                        <dd><?= e($customer->getEmail()) ?></dd>
                    </div>
                    <div>
                        <dt>Telefoonnummer</dt>
                        <dd><?= e($customer->getPhone() ?? 'Niet opgegeven') ?></dd>
                    </div>
                    <div>
                        <dt>Rol</dt>
                        <dd><?= e($customer->getRole() === 'administrator' ? 'Beheerder' : 'Klant') ?></dd>
                    </div>
                </dl>
                <div class="auth-actions">
                    <a class="button auth-submit" href="edit.php">Gegevens aanpassen</a>
                    <a class="button-secondary" href="<?= e($homeUrl) ?>">Verder winkelen</a>
                    <form method="post" action="logout.php">
                        <input type="hidden" name="csrf_token" value="<?= e(CustomerSession::token()) ?>">
                        <button class="button-secondary" type="submit">Uitloggen</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
<?php require __DIR__ . '/siteFooter.php'; ?>
