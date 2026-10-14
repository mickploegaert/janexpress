<?php

require_once __DIR__ . '/../../datalayer/entities/Order.php';

class OrderDTO
{
    private ?int $id;
    private string $customerId;
    private string $orderDate;
    private float $totalAmount;
    private string $status;
    /** @var OrderItem[] */
    private array $items;
    private string $error = '';

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
        $customerId = trim($customerId);
        $orderDate = trim($orderDate);
        $status = trim($status);

        $this->validate($id, $customerId, $orderDate, $totalAmount, $status, $items);

        $this->id = $id;
        $this->customerId = $customerId;
        $this->orderDate = $orderDate;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
        $this->items = $items;
    }

    private function validate(
        ?int $id,
        string $customerId,
        string $orderDate,
        float $totalAmount,
        string $status,
        array $items
    ): void {
        $this->error = '';

        if ($id !== null && $id <= 0) {
            $this->addError('Order ID must be null or a positive number.');
        }

        if ($customerId === '') {
            $this->addError('Customer ID is required.');
        }

        if ($orderDate === '') {
            $this->addError('Order date is required.');
        }

        if ($totalAmount < 0) {
            $this->addError('Total amount cannot be negative.');
        }

        if ($status === '') {
            $this->addError('Status is required.');
        }

        foreach ($items as $item) {
            if (!$item instanceof OrderItem) {
                $this->addError('Order items must be instances of OrderItem.');
                break;
            }
        }
    }

    private function addError(string $message): void
    {
        $this->error .= ($this->error === '' ? '' : ' ') . $message;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getOrderDate(): string
    {
        return $this->orderDate;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function toOrder(): Order
    {
        return new Order(
            $this->id,
            $this->customerId,
            $this->orderDate,
            $this->totalAmount,
            $this->status,
            $this->items
        );
    }
}