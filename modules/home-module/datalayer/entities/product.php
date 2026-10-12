<?php

class Product
{
    private ?int $id;
    private int $category_id;
    private string $name;
    private ?string $image;
    private ?string $description;
    private float $price;
    private int $stock;
    private string $status;


    public function __construct(?int $id, int $category_id, string $name, ?string $image, ?string $description, float $price, int $stock, string $status)
    {
        $this->id = $id;
        $this->category_id = $category_id;
        $this->name = $name;
        $this->image = $image;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->status = $status;
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategoryId(): int
    {
        return $this->category_id;
    }
}