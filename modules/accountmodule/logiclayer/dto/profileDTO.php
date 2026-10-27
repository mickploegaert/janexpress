<?php

class ProfileDTO
{
    private string $firstName;
    private string $lastName;
    private string $email;
    private ?string $phone;
    private string $error = '';

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $phone
    ) {
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        $email = mb_strtolower(trim($email));
        $phone = trim($phone);

        $this->validate($firstName, $lastName, $email, $phone);

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone === '' ? null : $phone;
    }

    private function validate(string $firstName, string $lastName, string $email, string $phone): void
    {
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

    public function getError(): string
    {
        return $this->error;
    }
}
