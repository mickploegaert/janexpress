<?php

require_once __DIR__ . '/OrderItem.php';

class Order
{
    private ?int $id;
    private string $customerId;
    private string $orderDate;
    private float $totalAmount;
    private string $status;

    /** @var OrderItem[] */
    private array $items;

    /**
     * @param OrderItem[] $items
     */
    public function __construct(
        ?int $id,
        string $customerId,
        string $orderDate,
        float $totalAmount,
        string $status,
        array $items = []
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->orderDate = $orderDate;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
        $this->items = $items;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getOrderDate(): string
    {
        return $this->orderDate;
    }

    public function setOrderDate(string $orderDate): void
    {
        $this->orderDate = $orderDate;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param OrderItem[] $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
    }
}