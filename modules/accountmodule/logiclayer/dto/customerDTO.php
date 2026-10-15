<?php

require_once __DIR__ . '/../../datalayer/entities/user.php';

class CustomerDTO
{
    private string $firstName;
    private string $lastName;
    private string $email;
    private ?string $phone;
    private string $password;
    private string $error = '';

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $password,
        string $passwordConfirmation
    ) {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $email = mb_strtolower(trim($email));
        $phone = trim($phone);

        $this->validate($firstName, $lastName, $email, $phone, $password, $passwordConfirmation);

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone === '' ? null : $phone;
        $this->password = $password;
    }

    private function validate(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $password,
        string $passwordConfirmation
    ): void {
        if ($firstName === '') {
            $this->addError('Voornaam is verplicht.');
        } elseif (mb_strlen($firstName) > 80) {
            $this->addError('Voornaam mag maximaal 80 tekens zijn.');
        }

        if ($lastName === '') {
            $this->addError('Achternaam is verplicht.');
        } elseif (mb_strlen($lastName) > 80) {
            $this->addError('Achternaam mag maximaal 80 tekens zijn.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError('Vul een geldig e-mailadres in.');
        } elseif (mb_strlen($email) > 254) {
            $this->addError('Het e-mailadres is te lang.');
        }

        if ($phone !== '' && preg_match('/^[0-9+\s().-]{8,20}$/', $phone) !== 1) {
            $this->addError('Vul een geldig telefoonnummer in.');
        }

        if ($password === '') {
            $this->addError('Wachtwoord is verplicht.');
        } elseif (strlen($password) < 8) {
            $this->addError('Het wachtwoord moet minimaal 8 tekens zijn.');
        } elseif (strlen($password) > 72) {
            $this->addError('Het wachtwoord mag maximaal 72 tekens zijn.');
        } elseif ($password !== $passwordConfirmation) {
            $this->addError('De wachtwoorden komen niet overeen.');
        }
    }

    private function addError(string $message): void
    {
        $this->error .= ($this->error === '' ? '' : "\n") . $message;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getError(): string
    {
        return $this->error;
    }
}
