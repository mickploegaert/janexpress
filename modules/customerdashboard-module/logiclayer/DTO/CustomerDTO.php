<?php

class CustomerDTO
{
    private string $customerId;
    private string $name;
    private string $email;
    private ?string $phone;

    public function __construct(
        string $customerId,
        string $name,
        string $email,
        ?string $phone = null
    ) {
        $this->customerId = $customerId;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
}