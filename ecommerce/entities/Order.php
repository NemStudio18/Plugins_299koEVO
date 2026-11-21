<?php

namespace Ecommerce\Entities;

defined('ROOT') or exit('Access denied!');

class Order extends \Core\Storage\JsonActiveRecord
{
    protected static string $filePath = DATA_PLUGIN . 'ecommerce/orders.json';
    protected static string $tableName = 'orders';
    protected static string $primaryKey = 'id';
    protected static bool $autoIncrementId = true;

    public function getId()
    {
        if (!isset($this->attributes['id'])) {
            return null;
        }
        return is_numeric($this->attributes['id']) ? (int)$this->attributes['id'] : (string)$this->attributes['id'];
    }

    public function getOrderNumber(): string
    {
        return $this->attributes['order_number'] ?? '';
    }

    public function getUserId(): ?int
    {
        return isset($this->attributes['user_id']) ? (int)$this->attributes['user_id'] : null;
    }

    public function getCustomerName(): string
    {
        return $this->attributes['customer_name'] ?? '';
    }

    public function getCustomerEmail(): string
    {
        return $this->attributes['customer_email'] ?? '';
    }

    public function getCustomerAddress(): string
    {
        return $this->attributes['customer_address'] ?? '';
    }

    public function getCustomerPhone(): string
    {
        return $this->attributes['customer_phone'] ?? '';
    }

    public function getTotal(): float
    {
        return (float)($this->attributes['total'] ?? 0);
    }

    public function getStatus(): string
    {
        return $this->attributes['status'] ?? 'pending';
    }

    public function getStatusLabel(): string
    {
        $map = [
            'pending_payment' => 'En attente de paiement',
            'pending'         => 'En attente de paiement',
            'paid'            => 'Payée',
            'processing'      => 'En préparation',
            'shipped'         => 'Expédiée',
            'delivered'       => 'Livrée',
            'completed'       => 'Terminée',
            'cancelled'       => 'Annulée',
        ];
        $st = $this->getStatus();
        return $map[$st] ?? $st;
    }

    public function getPaymentMethod(): string
    {
        return $this->attributes['payment_method'] ?? '';
    }

    public function getPaymentMethodLabel(): string
    {
        $map = [
            'stripe'           => 'Carte bancaire (Stripe)',
            'paypal'           => 'PayPal',
            'bank_transfer'    => 'Virement bancaire',
            'check'            => 'Chèque',
            'cash_on_delivery' => 'Paiement à la livraison',
        ];
        $m = $this->getPaymentMethod();
        return $map[$m] ?? $m;
    }

    public function getTrackingNumber(): string
    {
        return $this->attributes['tracking_number'] ?? '';
    }

    public function getCreatedAt(): string
    {
        return $this->attributes['created_at'] ?? date('d/m/Y H:i');
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array
    {
        return OrderItem::queryBuilder()
            ->where('order_id', '=', $this->getId())
            ->get();
    }
}
