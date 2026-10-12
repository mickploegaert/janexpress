<?php

require_once __DIR__ . '/../../datalayer/entities/product.php';

class ProductDTO
{
    private ?int $id;
    private int $categoryId;
    private string $name;
    private ?string $image;
    private ?string $description;
    private float $price;
    private int $stock;
    private string $status;
    private string $error = '';

    public function __construct(
        ?int $id,
        int $categoryId,
        string $name,
        ?string $image,
        ?string $description,
        float $price,
        int $stock,
        string $status
    ) {
        $name = trim($name);
        $image = $image !== null ? trim($image) : null;
        $description = $description !== null ? trim($description) : null;
        $status = trim($status);

        $this->validate($id, $categoryId, $name, $price, $stock, $status);

        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->image = $image;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->status = $status;
    }

    private function validate(?int $id, int $categoryId, string $name, float $price, int $stock, string $status): void
    {
        $this->error = '';

        if ($id !== null && $id <= 0) {
            $this->addError('Product ID must be null or a positive number.');
        }

        if ($categoryId <= 0) {
            $this->addError('Category ID must be a positive number.');
        }

        if ($name === '') {
            $this->addError('Product name is required.');
        }

        if ($price < 0) {
            $this->addError('Price cannot be negative.');
        }

        if ($stock < 0) {
            $this->addError('Stock cannot be negative.');
        }

        if ($status === '') {
            $this->addError('Status is required.');
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

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function toProduct(): Product
    {
        return new Product(
            $this->id,
            $this->categoryId,
            $this->name,
            $this->image,
            $this->description,
            $this->price,
            $this->stock,
            $this->status
        );
    }
}
