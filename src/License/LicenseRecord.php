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
    protected $price = 0;
    protected $priceRegular = 0;
    protected $reason = self::REASON_NONE;
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
        'price' => 'price',
        'priceRegular' => 'price_regular',
        'reason' => 'reason',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    /**
     * List of allowed statuses
     * 
     * @var array
     */
    protected static $allowedStatuses = array(self::STATUS_PENDING, self::STATUS_PROMO, self::STATUS_AVAILABLE, self::STATUS_ACTIVE, self::STATUS_CANCELED, self::STATUS_INACTIVE);

    const STATUS_PENDING = 'pending';
    const STATUS_PROMO = 'promo';
    const STATUS_AVAILABLE = 'available';
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELED = 'canceled';
    const STATUS_INACTIVE = 'inactive';

    /**
     * List of allowed reasons
     * 
     * @var array
     */
    protected static $allowedReasons = array(self::REASON_NONE, self::REASON_EXPIRED, self::REASON_DEVICE_DELETED, self::REASON_BILLING_REFUND, self::REASON_BILLING_FRAUD, self::REASON_ADMINISTRATIONS);

    const REASON_NONE = 'none';
    const REASON_EXPIRED = 'expired';
    const REASON_DEVICE_DELETED = 'device-deleted';
    const REASON_BILLING_REFUND = 'billing-refund';
    const REASON_BILLING_FRAUD = 'billing-fraud';
    const REASON_ADMINISTRATIONS = 'administrations';

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
    
    public function setPrice($value)
    {
        $this->price = $value;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }
    
    public function setPriceRegular($value)
    {
        $this->priceRegular = $value;

        return $this;
    }

    public function getPriceRegular()
    {
        return $this->priceRegular;
    }

    public function setReason($value)
    {
        $this->reason = $value;

        return $this;
    }

    public function getReason()
    {
        return $this->reason;
    }

    private function updateRecord($userId, $productId, $deviceId, $orderProductId, $type, $status, $activationDate, $expirationDate, $lifetime, $currency, $amount, $price, $priceRegular, $reason)
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
                                        `price` = {$price},
                                        `price_regular` = {$priceRegular},
                                        `reason` = {$reason},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($userId, $productId, $deviceId, $orderProductId, $type, $status, $activationDate, $expirationDate, $lifetime, $currency, $amount, $price, $priceRegular, $reason)
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
                            `amount` = {$amount},
                            `price` = {$price},
                            `price_regular` = {$priceRegular},
                            `reason` = {$reason}
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
        if (!in_array($this->status, self::$allowedStatuses)) {
            throw new InvalidStatusException("Invalid license status value!");
        }

        if (!in_array($this->reason, self::$allowedReasons)) {
            throw new InvalidReasonException("Invalid license reason value!");
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
        $price = $this->escape($this->price);
        $priceRegular = $this->escape($this->priceRegular);
        $reason = $this->escape($this->reason);

        if (!empty($this->id)) {
            if (!$this->updateRecord($userId, $productId, $deviceId, $orderProductId, $productType, $status, $activationDate, $expirationDate, $lifetime, $currency, $amount, $price, $priceRegular, $reason)) {
                return false;
            }
        } else {
            $this->id = $this->insertRecord($userId, $productId, $deviceId, $orderProductId, $productType, $status, $activationDate, $expirationDate, $lifetime, $currency, $amount, $price, $priceRegular, $reason);
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

    public static function getAllowedReasons()
    {
        return self::$allowedReasons;
    }
    
    public static function getAllowedStatuses()
    {
        return self::$allowedStatuses;
    }

}
