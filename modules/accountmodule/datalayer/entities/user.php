<?php

class User
{
    private ?string $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private ?string $phone;
    private string $passwordHash;
    private string $role;

    public function __construct(
        ?string $id,
        string $firstName,
        string $lastName,
        string $email,
        ?string $phone,
        string $passwordHash,
        string $role = 'customer'
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
