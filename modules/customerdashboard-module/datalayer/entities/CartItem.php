<?php

class CartItem
{
    private int $productId;
    private int $quantity;
    private float $price;

    public function __construct(int $productId, int $quantity, float $price)
    {
        if ($quantity < 1 || $price < 0) {
            throw new InvalidArgumentException('Cart item values are invalid.');
        }

        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->price = $price;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Cart item quantity must be at least 1.');
        }

        $this->quantity = $quantity;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTotal(): float
    {
        return $this->price * $this->quantity;
    }
}
