<?php

require_once __DIR__ . '/ShoppingCart.php';

class Customer
{
    private string $customerId;
    private string $name;
    private string $email;
    private ?string $phone;
    private ShoppingCart $shoppingCart;

    public function __construct(
        string $customerId,
        string $name,
        string $email,
        ?string $phone = null,
        ?ShoppingCart $shoppingCart = null
    ) {
        $this->customerId = $customerId;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->shoppingCart = $shoppingCart ?? new ShoppingCart();
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

    public function getShoppingCart(): ShoppingCart
    {
        return $this->shoppingCart;
    }
}
