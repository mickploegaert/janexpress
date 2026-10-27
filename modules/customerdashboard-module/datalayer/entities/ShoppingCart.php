<?php

require_once __DIR__ . '/CartItem.php';

class ShoppingCart
{
    private ?int $shoppingCartId;

    /** @var CartItem[] */
    private array $items;

    /** @param CartItem[] $items */
    public function __construct(?int $shoppingCartId = null, array $items = [])
    {
        foreach ($items as $item) {
            if (!$item instanceof CartItem) {
                throw new InvalidArgumentException('Shopping cart items must be CartItem instances.');
            }
        }

        $this->shoppingCartId = $shoppingCartId;
        $this->items = array_values($items);
    }

    public function getShoppingCartId(): ?int
    {
        return $this->shoppingCartId;
    }

    /** @return CartItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addProduct(CartItem $item): void
    {
        foreach ($this->items as $existingItem) {
            if ($existingItem->getProductId() === $item->getProductId()) {
                $existingItem->setQuantity($existingItem->getQuantity() + $item->getQuantity());
                return;
            }
        }

        $this->items[] = $item;
    }

    public function removeProduct(int $productId): void
    {
        $this->items = array_values(array_filter(
            $this->items,
            static fn (CartItem $item): bool => $item->getProductId() !== $productId
        ));
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        foreach ($this->items as $item) {
            if ($item->getProductId() === $productId) {
                if ($quantity < 1) {
                    $this->removeProduct($productId);
                } else {
                    $item->setQuantity($quantity);
                }
                return;
            }
        }
    }

    public function calculateTotal(): float
    {
        return array_reduce(
            $this->items,
            static fn (float $total, CartItem $item): float => $total + $item->getTotal(),
            0.0
        );
    }
}
