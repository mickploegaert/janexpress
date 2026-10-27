<?php

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../entities/Customer.php';
require_once __DIR__ . '/../../../ordermodule/datalayer/entities/Order.php';
require_once __DIR__ . '/../../../ordermodule/datalayer/entities/OrderItem.php';

class CustomerDAO
{
    private Database $database;
    private string $customerEndpoint = '/rest/v1/users';
    private string $orderEndpoint = '/rest/v1/orders';
    private string $orderItemEndpoint = '/rest/v1/order_items';

    public function __construct()
    {
        $this->database = Database::getInstance();
    }

    public function getCustomerById(string $customerId): ?Customer
    {
        if (preg_match('/^[0-9a-fA-F-]{36}$/', $customerId) !== 1) {
            return null;
        }

        $rows = $this->request('GET', $this->customerEndpoint . '?' . http_build_query([
            'select' => 'id,name,email,phone_number',
            'id' => 'eq.' . $customerId,
            'limit' => 1,
        ]));

        if (!isset($rows[0])) {
            return null;
        }

        return new Customer(
            (string) $rows[0]['id'],
            (string) ($rows[0]['name'] ?? ''),
            (string) $rows[0]['email'],
            isset($rows[0]['phone_number']) ? (string) $rows[0]['phone_number'] : null
        );
    }

    /** @return Order[] */
    public function getOrdersByCustomerId(string $customerId): array
    {
        if (preg_match('/^[0-9a-fA-F-]{36}$/', $customerId) !== 1) {
            return [];
        }

        $rows = $this->request('GET', $this->orderEndpoint . '?' . http_build_query([
            'select' => '*',
            'customer_id' => 'eq.' . $customerId,
            'order' => 'order_date.desc,id.desc',
        ]));

        $orders = [];
        foreach ($rows as $row) {
            $order = new Order(
                (int) $row['id'],
                (string) $row['customer_id'],
                (string) $row['order_date'],
                (float) $row['total_amount'],
                (string) $row['status']
            );

            foreach ($this->getOrderItems((int) $row['id']) as $item) {
                $order->addItem($item);
            }

            $orders[] = $order;
        }

        return $orders;
    }

    /** @return OrderItem[] */
    private function getOrderItems(int $orderId): array
    {
        $rows = $this->request('GET', $this->orderItemEndpoint . '?' . http_build_query([
            'select' => 'id,order_id,product_id,quantity,price',
            'order_id' => 'eq.' . $orderId,
            'order' => 'id.asc',
        ]));

        $items = [];
        foreach ($rows as $row) {
            $items[] = new OrderItem(
                (int) $row['id'],
                (int) $row['order_id'],
                (int) $row['product_id'],
                (int) $row['quantity'],
                (float) $row['price']
            );
        }

        return $items;
    }

    private function request(string $method, string $endpoint): array
    {
        $curl = curl_init(rtrim($this->database->getSupabaseUrl(), '/') . $endpoint);

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
            ],
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

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('Supabase gaf een ongeldig antwoord terug.');
        }

        return $decoded;
    }
}