<?php

require_once __DIR__ . '/../../datalayer/dao/customerDAO.php';
require_once __DIR__ . '/../dto/customerDTO.php';
require_once __DIR__ . '/../dto/profileDTO.php';

class CustomerService
{
    private const DUMMY_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    private ?CustomerDAO $customerDAO = null;

    private function customers(): CustomerDAO
    {
        return $this->customerDAO ??= new CustomerDAO();
    }

    public function register(CustomerDTO $customerDTO): User
    {
        $error = $customerDTO->getError();
        if ($error !== '') {
            throw new InvalidArgumentException($error);
        }

        if ($this->customers()->findByEmail($customerDTO->getEmail()) !== null) {
            throw new InvalidArgumentException('Dit e-mailadres is al in gebruik.');
        }

        $passwordHash = password_hash($customerDTO->getPassword(), PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            throw new RuntimeException('Wachtwoord hashen is mislukt.');
        }

        try {
            return $this->customers()->create(new User(
                null,
                $customerDTO->getFirstName(),
                $customerDTO->getLastName(),
                $customerDTO->getEmail(),
                $customerDTO->getPhone(),
                $passwordHash,
                'customer'
            ), $customerDTO->getPassword());
        } catch (RuntimeException $exception) {
            if (str_contains($exception->getMessage(), '23505')) {
                throw new InvalidArgumentException('Dit e-mailadres is al in gebruik.');
            }

            throw $exception;
        }
    }

    public function login(string $email, string $password): User
    {
        $email = mb_strtolower(trim($email));
        $customer = null;

        if ($password !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $customer = $this->customers()->findByEmail($email);
        }

        $hash = $customer === null ? self::DUMMY_HASH : $customer->getPasswordHash();
        $passwordMatches = password_verify($password, $hash);

        if ($customer === null || !$passwordMatches) {
            throw new InvalidArgumentException('E-mailadres of wachtwoord is onjuist.');
        }

        return $customer;
    }

    public function getById(string $id): ?User
    {
        return $this->customers()->findById($id);
    }

    public function updateOwnProfile(string $userId, ProfileDTO $profile): User
    {
        $error = $profile->getError();
        if ($error !== '') {
            throw new InvalidArgumentException($error);
        }

        $current = $this->customers()->findById($userId);
        if ($current === null) {
            throw new InvalidArgumentException('Je account is niet gevonden.');
        }

        $existing = $this->customers()->findByEmail($profile->getEmail());
        if ($existing !== null && $existing->getId() !== $current->getId()) {
            throw new InvalidArgumentException('Dit e-mailadres is al in gebruik.');
        }

        try {
            return $this->customers()->updateProfile($current, new User(
                $current->getId(),
                $profile->getFirstName(),
                $profile->getLastName(),
                $profile->getEmail(),
                $profile->getPhone(),
                $current->getPasswordHash(),
                $current->getRole()
            ));
        } catch (RuntimeException $exception) {
            if (str_contains($exception->getMessage(), '23505')) {
                throw new InvalidArgumentException('Dit e-mailadres is al in gebruik.');
            }

            throw $exception;
        }
    }

    public static function publicError(RuntimeException $exception): string
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'SUPABASE_KEY_NOT_SECRET')) {
            return 'In .env staat de publishable key. Zet daar de Supabase secret key (sb_secret_...) neer en voer database/customers.sql uit in de SQL Editor.';
        }

        if (str_contains($message, 'PGRST204') || str_contains($message, '42703') || str_contains($message, '42501')) {
            return 'Accounts worden opgeslagen in de tabel users. Voer database/customers.sql uit in de Supabase SQL Editor.';
        }

        if (str_contains($message, 'niet ingesteld')) {
            return $message;
        }

        return 'Er ging iets mis. Probeer het later opnieuw.';
    }
}
