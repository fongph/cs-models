<?php

namespace CS\Models\License;

use CS\Models\AbstractRecord,
    CS\Models\Order\Product\OrderProductRecord,
    CS\Models\Product\ProductRecord,
    CS\Models\RecordNotCreatedException,
    CS\Models\RecordDifferencesException,
    CS\Models\Product\InvalidTypeException;

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
    protected $userId;
    protected $productId;
    protected $orderProductId;
    protected $deviceId;
    protected $status;
    protected $productType;
    protected $activationDate;
    protected $expirationDate;
    protected $lifetime;
    protected $keys = array(
        'id' => 'id',
        'siteId' => 'site_id',
        'userId' => 'user_id',
        'status' => 'status',
        'product_type' => 'product_type',
        'paymentMethod' => 'payment_method',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    /**
     * List of allowed statuses
     * 
     * @var array
     */
    protected $allowedStatuses = array(self::STATUS_INACTIVE, self::STATUS_ACTIVE, self::STATUS_EXPIRED);

    const STATUS_INACTIVE = 'inactive';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';

    public function setOrderProduct(OrderProductRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }
        
        $this->orderProductId = $value->getId();
        $this->productId = $value->getProductId();
        $this->userId = $value->getOrder()->getUserId();        
        $this->productType = $value->getProduct()->getType();
        $this->lifetime = $value->getProduct()->getLimitation()->getLifetime();

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

    private function updateRecord($userId, $productId, $deviceId, $orderProductId, $type, $status, $activationDate, $expirationDate, $lifetime)
    {
        $rows = $this->db->exec("UPDATE `orders_products` SET
                                        `user_id` = {$userId},
                                        `product_id` = {$productId},
                                        `device_id` = {$deviceId},
                                        `order_product_id` = {$orderProductId},
                                        `product_type` = {$type},
                                        `status` = {$status},
                                        `activation_date` = {$activationDate},
                                        `expiration_date` = {$expirationDate},
                                        `lifetime` = {$lifetime}
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($userId, $productId, $deviceId, $orderProductId, $type, $status, $activationDate, $expirationDate, $lifetime)
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
                            `lifetime` = {$lifetime}
                        ");

        return $this->db->lastInsertId();
    }

    private function checkOrderProduct()
    {
        if ($this->orderProduct instanceof OrderProductRecord) {

            $equal = $this->orderProductId == $this->orderProduct->getId() &&
                    $this->productId == $this->orderProduct->getProductId() &&
                    $this->userId == $this->orderProduct->getOrder()->getUserId() &&
                    $this->productType == $this->orderProduct->getProduct()->getType() &&
                    $this->lifetime == $this->orderProduct->getProduct()->getLimitation()->getLifetime();

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
            throw new InvalidTypeException("Invalid license type value!");
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
        $lifetime = $this->escape($this->lifetime, 'NULL');

        if (!empty($this->id)) {
            if (!$this->updateRecord($userId, $productId, $deviceId, $orderProductId, $productType, $status, $activationDate, $expirationDate, $lifetime)) {
                return false;
            }
        } else {
            $this->id = $this->insertRecord($userId, $productId, $deviceId, $orderProductId, $productType, $status, $activationDate, $expirationDate, $lifetime);
        }

        return true;
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `licenses` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new LicenseNotFoundException('Unable to load order product record');
    }

}
