<?php

require_once __DIR__ . '/../../datalayer/DAO/CustomerDAO.php';

class CustomerService
{
    private CustomerDAO $customerDAO;

    public function __construct()
    {
        $this->customerDAO = new CustomerDAO();
    }

    public function getCustomerById(string $customerId): ?Customer
    {
        return $this->customerDAO->getCustomerById($customerId);
    }

    /** @return Order[] */
    public function getOrdersByCustomerId(string $customerId): array
    {
        return $this->customerDAO->getOrdersByCustomerId($customerId);
    }
}