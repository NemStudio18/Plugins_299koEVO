<?php

namespace Ecommerce\Entities;

defined('ROOT') or exit('Access denied!');

class OrderManager
{
    public function getAll(): array
    {
        return Order::queryBuilder()->orderBy('created_at', 'DESC')->get();
    }

    public function getById($id): ?Order
    {
        if (empty($id)) {
            return null;
        }
        $order = Order::findPK($id);
        if (!$order && is_numeric($id)) {
            $order = Order::findPK((int)$id) ?? Order::findPK((string)$id);
        }
        return $order;
    }

    public function findById(int $id): ?Order
    {
        return $this->getById($id);
    }

    public function getByOrderNumber(string $orderNumber): ?Order
    {
        return Order::find('order_number', $orderNumber);
    }

    public function getByUser(int $userId, string $email = ''): array
    {
        $orders = [];
        foreach ($this->getAll() as $order) {
            $uid = $order->getUserId();
            $uEmail = $order->getCustomerEmail();
            if (($userId > 0 && $uid === $userId) || ($email !== '' && strcasecmp($uEmail, $email) === 0)) {
                $orders[] = $order;
            }
        }
        return $orders;
    }

    public function getOrderItems(int $orderId): array
    {
        return OrderItem::queryBuilder()
            ->where('order_id', '=', $orderId)
            ->get();
    }

    public function create(array $data, array $items): ?Order
    {
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $data['order_number'] = $orderNumber;
        
        $order = new Order($data);
        
        if ($order->save()) {
            $orderId = $order->getId();
            
            // Insert order items
            foreach ($items as $item) {
                $orderItem = new OrderItem([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
                $orderItem->save();
            }
            
            return $order;
        }
        return null;
    }

    public function updateStatus(int $id, string $status, ?string $trackingNumber = null): bool
    {
        $order = $this->getById($id);
        if (!$order) {
            return false;
        }
        
        $order->status = $status;
        if ($trackingNumber !== null) {
            $order->tracking_number = trim($trackingNumber);
        }
        $order->updated_at = date('Y-m-d H:i:s');
        return $order->save();
    }
}
