<?php

namespace CS\Models\Order\Payment\Product;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Order\Payment\OrderPaymentRecord,
    CS\Models\Order\Product\OrderProductRecord,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException;

/**
 * Description of OrderPaymentProductRecord
 *
 * @author root
 */
class OrderPaymentProductRecord extends AbstractRecord
{

    /**
     *
     * @var OrderPaymentRecord 
     */
    protected $orderPayment;

    /**
     *
     * @var OrderProductRecord 
     */
    protected $orderProduct;
    protected $orderPaymentId;
    protected $orderProductId;
    protected $currency;
    protected $price;
    protected $keys = array(
        'id' => 'id',
        'orderPaymentId' => 'order_payment_id',
        'orderProductId' => 'order_product_id',
        'currency' => 'currency',
        'price' => 'price',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    public function setOrderPayment(OrderPaymentRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->orderPaymentId = $value->getId();
        $this->setCurrency($value->getCurrency());

        $this->orderPayment = $value;

        return $this;
    }

    public function setOrderPaymentId($id)
    {
        $this->orderPaymentId = $id;

        return $this;
    }

    public function getOrderPaymentId()
    {
        return $this->orderPaymentId;
    }

    /**
     * 
     * @return OrderPaymentRecord
     */
    public function getOrderPayment()
    {
        if ($this->orderPayment instanceof OrderPaymentRecord) {
            return $this->orderPayment;
        }

        if (!$this->isNew() && $this->orderPaymentId) {
            $orderPaymentRecord = new OrderPaymentRecord($this->db);
            $orderPaymentRecord->load($this->orderPaymentId);

            $this->setOrderPayment($orderPaymentRecord);
            return $this->orderPayment;
        }

        return null;
    }

    public function setOrderProduct(OrderProductRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->orderProductId = $value->getId();

        $this->orderProduct = $value;

        return $this;
    }

    public function setOrderProductId($id)
    {
        $this->orderProductId = $id;

        return $this;
    }

    public function getOrderProductId()
    {
        return $this->orderProductId;
    }

    /**
     * 
     * @return OrderProductRecord
     */
    public function getOrderProduct()
    {
        if ($this->orderProduct instanceof OrderProductRecord) {
            return $this->orderProduct;
        }

        if (!$this->isNew() && $this->orderProductId) {
            $orderProductRecord = new OrderProductRecord($this->db);
            $orderProductRecord->load($this->orderProductId);

            $this->setOrderProduct($orderProductRecord);
            return $this->orderProduct;
        }

        return null;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency($value)
    {
        $this->currency = $value;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($value)
    {
        $this->price = $value;

        return $this;
    }

    private function checkOrderPayment()
    {
        if ($this->orderPayment instanceof OrderPaymentRecord) {

            $equal = $this->orderPaymentId == $this->orderPayment->getId() &&
                    $this->currency == $this->orderPayment->getCurrency();

            if (!$equal) {
                throw new RecordDifferencesException("Invalid params");
            }
        }
    }

    private function checkOrderProduct()
    {
        if ($this->orderProduct instanceof OrderProductRecord && $this->orderProductId != $this->orderProduct->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function check()
    {
        $this->checkOrderPayment();
        $this->checkOrderProduct();
    }

    private function updateRecord($orderPaymentId, $orderProductId, $currency, $price)
    {
        $rows = $this->db->exec("UPDATE `orders_payments_products` SET
                                        `order_payment_id` = {$orderPaymentId},
                                        `order_product_id` = {$orderProductId},
                                        `currency` = {$currency},
                                        `price` = {$price},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($orderPaymentId, $orderProductId, $currency, $price)
    {
        $this->db->exec("INSERT INTO `orders_payments_products` SET
                            `order_payment_id` = {$orderPaymentId},
                            `order_product_id` = {$orderProductId},
                            `currency` = {$currency},
                            `price` = {$price}
                        ");

        return $this->db->lastInsertId();
    }

    public function save()
    {
        $this->check();

        $orderPaymentId = $this->escape($this->orderPaymentId);
        $orderProductId = $this->escape($this->orderProductId);
        $currency = $this->escape($this->currency);
        $price = $this->escape($this->price);

        if (!empty($this->id)) {
            return $this->updateRecord($orderPaymentId, $orderProductId, $currency, $price);
        } else {
            $this->id = $this->insertRecord($orderPaymentId, $orderProductId, $currency, $price);
        }

        return true;
    }

    /**
     * 
     * @param int $id
     * @return OrderPaymentProductRecord
     * @throws OrderPaymentProductNotFoundException
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `orders_payments_products` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new OrderPaymentProductNotFoundException('Unable to load order payment product record');
    }

}
