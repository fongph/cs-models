<?php

namespace CS\Models\Order\Product;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Product\ProductRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException;

/**
 * Description of OrderProductRecord
 *
 * @author root
 */
class OrderProductRecord extends AbstractRecord
{

    /**
     *
     * @var OrderRecord 
     */
    protected $order;

    /**
     *
     * @var ProductRecord 
     */
    protected $product;
    protected $orderId;
    protected $productId;
    protected $count = 1;
    protected $referenceNumber;
    protected $status = self::STATUS_ADDED;
    protected $keys = array(
        'id' => 'id',
        'orderId' => 'order_id',
        'productId' => 'product_id',
        'count' => 'count',
        'referenceNumber' => 'reference_number',
        'status' => 'status',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    /**
     * List of allowed statuses
     * 
     * @var array
     */
    protected static $allowedStatuses = array(self::STATUS_ADDED, self::STATUS_PAID);

    const STATUS_ADDED = 'added';
    const STATUS_PAID = 'paid';

    public function setOrder(OrderRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->orderId = $value->getId();

        $this->order = $value;

        return $this;
    }

    public function setOrderId($id)
    {
        $this->orderId = $id;

        return $this;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * 
     * @return OrderRecord
     */
    public function getOrder()
    {
        if ($this->order instanceof OrderRecord) {
            return $this->order;
        }

        if (!$this->isNew() && $this->orderId) {
            $orderRecord = new OrderRecord($this->db);
            $orderRecord->load($this->orderId);

            $this->setOrder($orderRecord);
            return $this->order;
        }

        return null;
    }

    public function setProduct(ProductRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        if ($this->getOrder() == null) {
            throw new OrderRequiredException("You must set order value before set product!");
        }
        
        $this->productId = $value->getId();
        $this->referenceNumber = $value->getReferenceCode($this->getOrder()->getPaymentMethod());

        $this->product = $value;

        return $this;
    }

    public function setProductId($id)
    {
        $this->productId = $id;

        return $this;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * 
     * @return ProductRecord
     */
    public function getProduct()
    {
        if ($this->product instanceof ProductRecord) {
            return $this->product;
        }

        if (!$this->isNew() && $this->productId) {
            $productRecord = new ProductRecord($this->db);
            $productRecord->load($this->productId);

            $this->setProduct($productRecord);
            return $this->product;
        }

        return null;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setCount($value)
    {
        $this->count = $value;

        return $this;
    }

    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber($value)
    {
        $this->referenceNumber = $value;

        return $this;
    }

    public function setStatus($value)
    {
        $this->status = $value;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    private function checkOrder()
    {
        if ($this->order instanceof OrderRecord && $this->orderId != $this->order->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function checkProduct()
    {
        if ($this->product instanceof ProductRecord && $this->productId != $this->product->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function check()
    {
        if (!in_array($this->status, self::getAllowedStatuses())) {
            throw new InvalidStatusException("Invalid status value");
        }

        $this->checkOrder();
        $this->checkProduct();
    }

    private function updateRecord($orderId, $productId, $referenceNumber, $count, $status)
    {
        $rows = $this->db->exec("UPDATE `orders_products` SET
                                        `order_id` = {$orderId},
                                        `product_id` = {$productId},
                                        `reference_number` = {$referenceNumber},
                                        `count` = {$count},
                                        `status` = {$status},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($orderId, $productId, $referenceNumber, $count, $status)
    {
        $this->db->exec("INSERT INTO `orders_products` SET
                            `order_id` = {$orderId},
                            `product_id` = {$productId},
                            `reference_number` = {$referenceNumber},
                            `count` = {$count},
                            `status` = {$status}
                        ");

        return $this->db->lastInsertId();
    }

    public function save()
    {
        $this->check();

        $orderId = $this->escape($this->orderId);
        $productId = $this->escape($this->productId);
        $count = $this->escape($this->count);
        $referenceNumber = $this->escape($this->referenceNumber);
        $status = $this->escape($this->status);

        if (!empty($this->id)) {
            return $this->updateRecord($orderId, $productId, $referenceNumber, $count, $status);
        } else {
            $this->id = $this->insertRecord($orderId, $productId, $referenceNumber, $count, $status);
        }

        return true;
    }

    public function loadReferenceNumber()
    {
        if ($this->getProduct() == null || $this->getOrder() == null) {
            throw new ReferenceNumberLoadException("Unable to load reference number without order or product record!");
        }
        
        $this->referenceNumber = $this->getProduct()->getReferenceCode($this->getOrder()->getPaymentMethod());
        
        return $this;
    }
    
    /**
     * 
     * @param int $id
     * @return OrderProductRecord
     * @throws OrderProductNotFoundException
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `orders_products` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new OrderProductNotFoundException('Unable to load order product record');
    }

    public static function getAllowedStatuses()
    {
        return self::$allowedStatuses;
    }

}
