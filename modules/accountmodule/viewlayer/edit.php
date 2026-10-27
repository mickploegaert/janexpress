<?php

require_once __DIR__ . '/../logiclayer/Services/customerSession.php';
require_once __DIR__ . '/../logiclayer/Services/customerService.php';

CustomerSession::start();
header('Cache-Control: no-store');

if (!CustomerSession::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$customerId = CustomerSession::id();
$service = new CustomerService();
$customer = null;
$error = null;

try {
    if ($customerId !== null) {
        $customer = $service->getById($customerId);
    }
} catch (RuntimeException $exception) {
    $error = CustomerService::publicError($exception);
}

if ($customer === null && $error === null) {
    CustomerSession::logout();
    header('Location: login.php');
    exit;
}

$firstName = $customer?->getFirstName() ?? '';
$lastName = $customer?->getLastName() ?? '';
$email = $customer?->getEmail() ?? '';
$phone = $customer?->getPhone() ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $customerId !== null && $error === null) {
    $firstName = (string) ($_POST['first_name'] ?? '');
    $lastName = (string) ($_POST['last_name'] ?? '');
    $email = (string) ($_POST['email'] ?? '');
    $phone = (string) ($_POST['phone'] ?? '');
    $token = $_POST['csrf_token'] ?? null;

    if (!is_string($token) || !CustomerSession::matchesToken($token)) {
        $error = 'De sessie is verlopen. Probeer het opnieuw.';
    } else {
        try {
            $updated = $service->updateOwnProfile($customerId, new ProfileDTO(
                $firstName,
                $lastName,
                $email,
                $phone
            ));
            CustomerSession::rememberName($updated->getFirstName());
            CustomerSession::flash('Je gegevens zijn opgeslagen.');
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

$pageTitle = 'Gegevens aanpassen - JanExpress';
require __DIR__ . '/siteHeader.php';
?>
        <div class="section-label">Account</div>
        <h1>Gegevens aanpassen</h1>
        <p class="auth-lead">Wijzig je naam, e-mailadres of telefoonnummer. Je past alleen je eigen account aan.</p>
        <section class="auth-card">
            <?php if ($customer === null): ?>
                <div class="form-error" role="alert"><?= e($error ?? 'Je account is niet gevonden.') ?></div>
            <?php else: ?>
                <?php if ($error !== null): ?>
                    <div class="form-error" role="alert"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post" action="edit.php">
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
                    <button class="button auth-submit" type="submit">Opslaan</button>
                </form>
                <p class="auth-switch"><a href="account.php">Annuleren</a></p>
            <?php endif; ?>
        </section>
<?php require __DIR__ . '/siteFooter.php'; ?>
