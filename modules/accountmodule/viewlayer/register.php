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
$firstName = '';
$lastName = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = (string) ($_POST['first_name'] ?? '');
    $lastName = (string) ($_POST['last_name'] ?? '');
    $email = (string) ($_POST['email'] ?? '');
    $phone = (string) ($_POST['phone'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (!is_string($token) || !CustomerSession::matchesToken($token)) {
        $error = 'De sessie is verlopen. Probeer het opnieuw.';
    } else {
        try {
            $customer = (new CustomerService())->register(new CustomerDTO(
                $firstName,
                $lastName,
                $email,
                $phone,
                (string) ($_POST['password'] ?? ''),
                (string) ($_POST['password_confirmation'] ?? '')
            ));
            CustomerSession::login($customer);
            CustomerSession::flash('Je account is aangemaakt.');
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

$pageTitle = 'Account aanmaken - JanExpress';
require __DIR__ . '/siteHeader.php';
?>
        <div class="section-label">Account</div>
        <h1>Account aanmaken</h1>
        <p class="auth-lead">Maak een account aan met je persoonlijke gegevens.</p>
        <section class="auth-card">
            <?php if ($error !== null): ?>
                <div class="form-error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="register.php">
                <input type="hidden" name="csrf_token" value="<?= e(CustomerSession::token()) ?>">
                <div class="name-row">
                    <label class="field">
                        Voornaam
                        <input type="text" name="first_name" value="<?= e($firstName) ?>" autocomplete="given-name" maxlength="80" required>
                    </label>
                    <label class="field">
                        Achternaam
                        <input type="text" name="last_name" value="<?= e($lastName) ?>" autocomplete="family-name" maxlength="80" required>
                    </label>
                </div>
                <label class="field">
                    E-mailadres
                    <input type="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
                </label>
                <label class="field">
                    Telefoonnummer
                    <input type="tel" name="phone" value="<?= e($phone) ?>" autocomplete="tel" placeholder="Optioneel">
                </label>
                <label class="field">
                    Wachtwoord
                    <input type="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" required>
                    <span class="field-hint">Minimaal 8 tekens</span>
                </label>
                <label class="field">
                    Wachtwoord bevestigen
                    <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" maxlength="72" required>
                </label>
                <button class="button auth-submit" type="submit">Account aanmaken</button>
            </form>
            <p class="auth-switch">Al een account? <a href="login.php">Inloggen</a></p>
        </section>
<?php require __DIR__ . '/siteFooter.php'; ?>
