<?php

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../entities/product.php';

class ProductDAO
{
    private Database $database;
    private string $endpoint = '/rest/v1/products';

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function getAllProducts(): iterable
    {
        $rows = $this->request('GET', '?select=*&order=id.asc');

        foreach ($rows as $row) {
            yield $this->createProductFromRow($row);
        }
    }

    public function getProductById(int $id): ?Product
    {
        $rows = $this->request('GET', '?select=*&id=eq.' . $id . '&limit=1');

        if (isset($rows[0])) {
            return $this->createProductFromRow($rows[0]);
        }

        return null;
    }

    public function createProduct(Product $product): bool
    {
        $this->request('POST', '', $this->productData($product));

        return true;
    }

    public function updateProduct(Product $product): bool
    {
        if ($product->getId() === null) {
            throw new InvalidArgumentException('Product ID cannot be null for update.');
        }

        $this->request(
            'PATCH',
            '?id=eq.' . $product->getId(),
            $this->productData($product)
        );

        return true;
    }

    public function deleteProduct(int $id): bool
    {
        $this->request('DELETE', '?id=eq.' . $id);

        return true;
    }

    private function createProductFromRow(array $row): Product
    {
        return new Product(
            (int) $row['id'],
            (int) $row['category_id'],
            (string) $row['name'],
            $row['image'] ?? null,
            $row['description'] ?? null,
            (float) $row['price'],
            (int) $row['stock'],
            (string) $row['status']
        );
    }

    private function productData(Product $product): array
    {
        return [
            'category_id' => $product->getCategoryId(),
            'name' => $product->getName(),
            'image' => $product->getImage(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'status' => $product->getStatus(),
        ];
    }

    private function request(string $method, string $query, ?array $data = null): array
    {
        $curl = curl_init(
            rtrim($this->database->getSupabaseUrl(), '/') . $this->endpoint . $query
        );

        if ($curl === false) {
            throw new RuntimeException('Supabase-request kon niet worden gestart.');
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->database->getSupabaseKey(),
                'Authorization: Bearer ' . $this->database->getSupabaseKey(),
                'Content-Type: application/json',
                'Prefer: return=minimal',
            ],
            CURLOPT_POSTFIELDS => $data === null
                ? null
                : json_encode($data, JSON_THROW_ON_ERROR),
        ]);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException('Supabase-request mislukt: ' . $error);
        }

        if ($status < 200 || $status >= 300) {
            if ($status === 401 || ($status === 403 && str_contains($response, '42501'))) {
                throw new RuntimeException(
                    'Geen toegang tot de producten. Voer database/customers.sql opnieuw uit in de Supabase SQL Editor.'
                );
            }

            throw new RuntimeException("Supabase HTTP {$status}: {$response}");
        }

        if ($response === '') {
            return [];
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
