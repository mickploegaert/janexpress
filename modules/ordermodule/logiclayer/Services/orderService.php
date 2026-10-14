<?php

require_once __DIR__ . '/../../datalayer/DAO/OrderDAO.php';
require_once __DIR__ . '/../dto/orderDTO.php';

class OrderService
{
    private OrderDAO $orderDAO;

    public function __construct()
    {
        $this->orderDAO = new OrderDAO();
    }

    public function getAllOrders(): iterable
    {
        return $this->orderDAO->getAllOrders();
    }

    public function getOrderById(int $id): ?Order
    {
        return $this->orderDAO->getOrderById($id);
    }

    public function createOrder(OrderDTO $orderDTO): bool
    {
        return $this->orderDAO->createOrder($this->toValidOrder($orderDTO));
    }

    public function updateOrder(OrderDTO $orderDTO): bool
    {
        return $this->orderDAO->updateOrder($this->toValidOrder($orderDTO, true));
    }

    public function deleteOrder(int $id): bool
    {
        return $this->orderDAO->deleteOrder($id);
    }

    private function toValidOrder(OrderDTO $orderDTO, bool $requireId = false): Order
    {
        $error = $orderDTO->getError();
        if ($error !== '') {
            throw new InvalidArgumentException($error);
        }

        if ($requireId && $orderDTO->getId() === null) {
            throw new InvalidArgumentException('Order ID cannot be null for update.');
        }

        return $orderDTO->toOrder();
    }
}