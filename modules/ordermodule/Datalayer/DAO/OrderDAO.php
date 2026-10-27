<?php

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../entities/Order.php';

class OrderDAO
{
    private Database $database;
    private string $endpoint = '/rest/v1/orders';

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function getAllOrders(): iterable
    {
        $rows = $this->request('GET', '?select=*&order=id.asc');

        foreach ($rows as $row) {
            yield $this->createOrderFromRow($row);
        }
    }

    public function getOrderById(int $id): ?Order
    {
        $rows = $this->request(
            'GET',
            '?select=*&id=eq.' . $id . '&limit=1'
        );

        if (isset($rows[0])) {
            return $this->createOrderFromRow($rows[0]);
        }

        return null;
    }

    public function createOrder(Order $order): bool
    {
        $this->request('POST', '', $this->orderData($order));

        return true;
    }

    public function createOrderWithItems(Order $order): bool
    {
        $rows = $this->request('POST', '', $this->orderData($order), true);
        $orderId = $rows[0]['id'] ?? null;

        if (!is_numeric($orderId)) {
            throw new RuntimeException('De bestelling kreeg geen ID van Supabase.');
        }

        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = [
                'order_id' => (int) $orderId,
                'product_id' => $item->getProductId(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
            ];
        }

        if ($items !== []) {
            $this->requestAt('POST', '/rest/v1/order_items', '', $items);
        }

        return true;
    }

    public function updateOrder(Order $order): bool
    {
        if ($order->getId() === null) {
            throw new InvalidArgumentException('Order ID cannot be null for update.');
        }

        $this->request(
            'PATCH',
            '?id=eq.' . $order->getId(),
            $this->orderData($order)
        );

        return true;
    }

    public function deleteOrder(int $id): bool
    {
        $this->request('DELETE', '?id=eq.' . $id);

        return true;
    }

    private function createOrderFromRow(array $row): Order
    {
        return new Order(
            (int) $row['id'],
            (string) $row['customer_id'],
            (string) $row['order_date'],
            (float) $row['total_amount'],
            (string) $row['status']
        );
    }

    private function orderData(Order $order): array
    {
        return [
            'customer_id' => $order->getCustomerId(),
            'order_date' => $order->getOrderDate(),
            'total_amount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
        ];
    }

    private function request(string $method, string $query, ?array $data = null, bool $returnRepresentation = false): array
    {
        return $this->requestAt($method, $this->endpoint, $query, $data, $returnRepresentation);
    }

    private function requestAt(
        string $method,
        string $endpoint,
        string $query,
        ?array $data = null,
        bool $returnRepresentation = false
    ): array
    {
        $curl = curl_init(
            rtrim($this->database->getSupabaseUrl(), '/') . $endpoint . $query
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
                'Prefer: ' . ($returnRepresentation ? 'return=representation' : 'return=minimal'),
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
            throw new RuntimeException("Supabase HTTP {$status}: {$response}");
        }

        if ($response === '') {
            return [];
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
