<?php

require_once __DIR__ . '/../logiclayer/Services/customerSession.php';
require_once __DIR__ . '/../logiclayer/Services/customerService.php';

CustomerSession::start();
header('Cache-Control: no-store');

if (CustomerSession::isLoggedIn()) {
    header('Location: account.php');
    exit;
}

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (!is_string($token) || !CustomerSession::matchesToken($token)) {
        $error = 'De sessie is verlopen. Probeer het opnieuw.';
    } else {
        try {
            $customer = (new CustomerService())->login($email, $password);
            CustomerSession::login($customer);
            header('Location: account.php');
            exit;
        } catch (InvalidArgumentException $exception) {
            $error = $exception->getMessage();
        } catch (RuntimeException $exception) {
            $error = CustomerService::publicError($exception);
        }
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$pageTitle = 'Inloggen - JanExpress';
require __DIR__ . '/siteHeader.php';
?>
        <div class="section-label">Account</div>
        <h1>Inloggen</h1>
        <p class="auth-lead">Log in met je e-mailadres en wachtwoord om je gegevens te bekijken.</p>
        <section class="auth-card">
            <?php if ($error !== null): ?>
                <div class="form-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= e(CustomerSession::token()) ?>">
                <label class="field">
                    E-mailadres
                    <input type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required>
                </label>
                <label class="field">
                    Wachtwoord
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <button class="button auth-submit" type="submit">Inloggen</button>
            </form>
            <p class="auth-switch">Nog geen account? <a href="register.php">Account aanmaken</a></p>
        </section>
<?php require __DIR__ . '/siteFooter.php'; ?>
