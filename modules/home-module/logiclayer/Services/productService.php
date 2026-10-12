<?php

require_once __DIR__ . "/../../datalayer/dao/productDAO.php";
require_once __DIR__ . "/../dto/productDTO.php";

class ProductService
{
    private ProductDAO $productDAO;

    public function __construct()
    {
        $this->productDAO = new ProductDAO();
    }

    public function getAllProducts(): array
    {
        return $this->productDAO->getAllProducts();
    }

    public function getProductById(int $id): ?Product
    {
        return $this->productDAO->getProductById($id);
    }

    public function createProduct(ProductDTO $productDTO): bool
    {
        return $this->productDAO->createProduct($this->toValidProduct($productDTO));
    }

    public function updateProduct(ProductDTO $productDTO): bool
    {
        return $this->productDAO->updateProduct($this->toValidProduct($productDTO, true));
    }

    public function deleteProduct(int $id): bool
    {
        return $this->productDAO->deleteProduct($id);
    }

    private function toValidProduct(ProductDTO $productDTO, bool $requireId = false): Product
    {
        $error = $productDTO->getError();
        if ($error !== '') {
            throw new InvalidArgumentException($error);
        }

        if ($requireId && $productDTO->getId() === null) {
            throw new InvalidArgumentException('Product ID cannot be null for update.');
        }

        return $productDTO->toProduct();
    }
}
