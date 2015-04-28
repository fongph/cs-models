<?php

namespace CS\Models\License;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Order\Product\OrderProductRecord,
    CS\Models\Product\ProductRecord,
    CS\Models\RecordNotCreatedException,
    CS\Models\RecordDifferencesException,
    CS\Models\Product\InvalidTypeException,
    CS\Models\Subscription\SubscriptionRecord;

/**
 * Description of LicenseRecord
 *
 * @author root
 */
class LicenseRecord extends AbstractRecord
{

    /**
     *
     * @var OrderProductRecord
     */
    protected $orderProduct;
    protected $product;
    protected $subscription;
    protected $userId;
    protected $productId;
    protected $orderProductId;
    protected $deviceId;
    protected $status = self::STATUS_PENDING;
    protected $productType = ProductRecord::TYPE_PACKAGE;
    protected $activationDate = 0;
    protected $expirationDate = 0;
    protected $lifetime = 0;
    protected $currency = 'USD';
    protected $amount = 0;
    protected $keys = array(
        'id' => 'id',
        'userId' => 'user_id',
        'orderProductId' => 'order_product_id',
        'productId' => 'product_id',
        'deviceId' => 'device_id',
        'productType' => 'product_type',
        'status' => 'status',
        'activationDate' => 'activation_date',
        'expirationDate' => 'expiration_date',
        'lifetime' => 'lifetime',
        'currency' => 'currency',
        'amount' => 'amount',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    /**
     * List of allowed statuses
     * 
     * @var array
     */
    protected $allowedStatuses = array(self::STATUS_PENDING, self::STATUS_PROMO, self::STATUS_AVAILABLE, self::STATUS_ACTIVE, self::STATUS_CANCELED, self::STATUS_INACTIVE);

    const STATUS_PENDING = 'pending';
    const STATUS_PROMO = 'trial';
    const STATUS_AVAILABLE = 'available';
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELED = 'canceled';
    const STATUS_INACTIVE = 'inactive';

    public function setOrderProduct(OrderProductRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->orderProductId = $value->getId();
        $this->productId = $value->getProductId();
        $this->userId = $value->getOrder()->getUserId();
        $this->productType = $value->getProduct()->getType();

        $this->orderProduct = $value;

        return $this;
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

    /**
     * @return ProductRecord
     */
    public function getProduct()
    {
        if (is_null($this->product)) {
            $productRecord = new ProductRecord($this->db);
            $productRecord->load($this->productId);
            $this->product = $productRecord;
        }
        return $this->product;
    }
    
    public function hasSubscription()
    {
        try {
            $this->getSubscription();
            return true;
        } catch (LicenseDoNotHaveSubscriptionException $e) {
            return false;
        }
    }
    
    public function getSubscription()
    {
        if ($this->subscription instanceof SubscriptionRecord) {
            return $this->subscription;
        }

        if ($this->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $subscription = new SubscriptionRecord($this->db);
        
        $this->subscription = $subscription->loadByLicenseId($this->getId());

        return $this->subscription;
    }

    public function setUserId($id)
    {
        $this->userId = $id;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
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

    public function setDeviceId($id)
    {
        $this->deviceId = $id;

        return $this;
    }

    public function getDeviceId()
    {
        return $this->deviceId;
    }

    public function getOrderProductId()
    {
        return $this->orderProductId;
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

    public function setProductType($value)
    {
        $this->productType = $value;

        return $this;
    }

    public function getProductType()
    {
        return $this->productType;
    }

    public function setActivationDate($value)
    {
        $this->activationDate = $value;

        return $this;
    }

    public function getActivationDate()
    {
        return $this->activationDate;
    }

    public function setExpirationDate($value)
    {
        $this->expirationDate = $value;

        return $this;
    }

    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    public function setLifetime($value)
    {
        $this->lifetime = $value;

        return $this;
    }

    public function getLifetime()
    {
        return $this->lifetime;
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
    
    public function setAmount($value)
    {
        $this->amount = $value;

        return $this;
    }
    
    public function getAmount()
    {
        return $this->amount;
    }

    private function updateRecord($userId, $productId, $deviceId, $orderProductId, $type, $status, $activationDate, $expirationDate, $lifetime, $currency, $amount)
    {
        $rows = $this->db->exec("UPDATE `licenses` SET
                                        `user_id` = {$userId},
                                        `product_id` = {$productId},
                                        `device_id` = {$deviceId},
                                        `order_product_id` = {$orderProductId},
                                        `product_type` = {$type},
                                        `status` = {$status},
                                        `activation_date` = {$activationDate},
                                        `expiration_date` = {$expirationDate},
                                        `lifetime` = {$lifetime},
                                        `currency` = {$currency},
                                        `amount` = {$amount},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($userId, $productId, $deviceId, $orderProductId, $type, $status, $activationDate, $expirationDate, $lifetime, $currency, $amount)
    {
        $this->db->exec("INSERT INTO `licenses` SET
                            `user_id` = {$userId},
                            `product_id` = {$productId},
                            `device_id` = {$deviceId},
                            `order_product_id` = {$orderProductId},
                            `product_type` = {$type},
                            `status` = {$status},
                            `activation_date` = {$activationDate},
                            `expiration_date` = {$expirationDate},
                            `lifetime` = {$lifetime},
                            `currency` = {$currency},
                            `amount` = {$amount}
                        ");

        return $this->db->lastInsertId();
    }

    private function checkOrderProduct()
    {
        if ($this->orderProduct instanceof OrderProductRecord) {

            $equal = $this->orderProductId == $this->orderProduct->getId() &&
                    $this->productId == $this->orderProduct->getProductId() &&
                    $this->userId == $this->orderProduct->getOrder()->getUserId() &&
                    $this->productType == $this->orderProduct->getProduct()->getType();

            if (!$equal) {
                throw new RecordDifferencesException("Invalid params");
            }
        }
    }

    private function check()
    {
        if (!in_array($this->status, $this->allowedStatuses)) {
            throw new InvalidStatusException("Invalid license status value!");
        }

        if (!in_array($this->productType, ProductRecord::getAllowedTypes())) {
            throw new InvalidTypeException("Invalid license product type value!");
        }

        $this->checkOrderProduct();
    }

    public function save()
    {
        $this->check();

        $userId = $this->escape($this->userId);
        $productId = $this->escape($this->productId);
        $deviceId = $this->escape($this->deviceId, 'NULL');
        $orderProductId = $this->escape($this->orderProductId, 'NULL');
        $productType = $this->escape($this->productType);
        $status = $this->escape($this->status);
        $activationDate = $this->escape($this->activationDate);
        $expirationDate = $this->escape($this->expirationDate);
        $lifetime = $this->escape($this->lifetime);
        $currency = $this->escape($this->currency);
        $amount = $this->escape($this->amount);

        if (!empty($this->id)) {
            if (!$this->updateRecord($userId, $productId, $deviceId, $orderProductId, $productType, $status, $activationDate, $expirationDate, $lifetime, $currency, $amount)) {
                return false;
            }
        } else {
            $this->id = $this->insertRecord($userId, $productId, $deviceId, $orderProductId, $productType, $status, $activationDate, $expirationDate, $lifetime, $currency, $amount);
        }

        return true;
    }

    /**
     * 
     * @param type $id
     * @return LicenseRecord
     * @throws LicenseNotFoundException
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT 
                            *,
                            UNIX_TIMESTAMP(`created_at`) as `created_at`, 
                            UNIX_TIMESTAMP(`updated_at`) as `updated_at` 
                        FROM `licenses` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new LicenseNotFoundException('Unable to load order product record');
    }

}
