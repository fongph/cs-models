<?php

namespace CS\Models\Order;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\RecordsIterator,
    CS\Models\Site\SiteRecord,
    CS\Models\User\UserRecord,
    CS\Models\RecordNotCreatedException,
    CS\Models\RecordDifferencesException;

/**
 * Description of OrderRecord
 *
 * @author root
 */
class OrderRecord extends AbstractRecord
{

    /**
     * Database connection
     * 
     * @var PDO
     */
    protected $db;

    /**
     *
     * @var SiteRecord
     */
    protected $site;

    /**
     *
     * @var UserRecord
     */
    protected $user;
    protected $siteId;
    protected $userId;
    protected $status;
    protected $paymentMethod;
    protected $amount;
    protected $hash;
    protected $referenceNumber;
    protected $gatewayStatus;
    protected $gatewayData;
    protected $keys = array(
        'id' => 'id',
        'siteId' => 'site_id',
        'userId' => 'user_id',
        'status' => 'status',
        'paymentMethod' => 'payment_method',
        'amount' => 'amount',
        'hash' => 'hash',
        'referenceNumber' => 'reference_number',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    /**
     *
     * @var ProductsIterator
     */
    protected $productsIterator;

    /**
     * List of allowed statuses
     * 
     * @var array
     */
    protected static $allowedStatuses = array(self::STATUS_CREATED, self::STATUS_COMPLETED);

    /**
     * List of allowed payment methods
     * 
     * @var array
     */
    protected static $allowedPaymentMethods = array(self::PAYMENT_METHOD_BLUESNAP, self::PAYMENT_METHOD_FASTSPRING);

    const STATUS_CREATED = 'created';
    const STATUS_COMPLETED = 'completed';
    const PAYMENT_METHOD_BLUESNAP = 'bluesnap';
    const PAYMENT_METHOD_FASTSPRING = 'fastspring';

    public function getHash()
    {
        return $this->hash;
    }

    private function generateHash()
    {
        $this->hash = substr(md5(self::class . microtime()), 0, 10);

        return $this->hash;
    }

    public function setSiteId($id)
    {
        $this->siteId = $id;

        return $this;
    }

    public function getSiteId()
    {
        return $this->siteId;
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

    public function setStatus($value)
    {
        $this->status = $value;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
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

    public function setReferenceNumber($value)
    {
        $this->referenceNumber = $value;

        return $this;
    }

    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    public function setPaymentMethod($value)
    {
        $this->paymentMethod = $value;

        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setGatewayStatus($value)
    {
        $this->gatewayStatus = $value;

        return $this;
    }

    public function getGatewayStatus()
    {
        return $this->gatewayStatus;
    }

    public function setGatewayData($value)
    {
        $this->gatewayData = $value;

        return $this;
    }

    public function getGatewayData()
    {
        return $this->gatewayData;
    }

    public function setSite(SiteRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->siteId = $value->getId();

        $this->site = $value;

        return $this;
    }

    public function setUser(UserRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->userId = $value->getId();

        $this->user = $value;

        return $this;
    }

    /**
     * 
     * @return UserRecord
     */
    public function getUser()
    {
        if ($this->user instanceof UserRecord) {
            return $this->user;
        }

        if (!$this->isNew() && $this->userId) {
            $userRecord = new UserRecord($this->db);
            $userRecord->load($this->userId);

            $this->setUser($userRecord);
            return $this->user;
        }

        return null;
    }

    /**
     * 
     * @return SiteRecord
     */
    public function getSite()
    {
        if ($this->site instanceof SiteRecord) {
            return $this->site;
        }

        if (!$this->isNew() && $this->siteId) {
            $siteRecord = new SiteRecord($this->db);
            $siteRecord->load($this->siteId);

            $this->setSite($siteRecord);
            return $this->site;
        }

        return null;
    }

    private function insertHistoryRecord($paymentMethod, $status, $gatewayStatus, $gatewayData)
    {
        $this->db->exec("INSERT INTO `orders_history` SET 
                            `order_id` = {$this->id},
                            `payment_method` = {$paymentMethod},
                            `status` = {$status},
                            `gateway_status` = {$gatewayStatus},
                            `gateway_data` = {$gatewayData}
                        ");
    }

    private function updateRecord($siteId, $userId, $status, $paymentMethod, $amount, $hash, $referenceNumber)
    {
        $rows = $this->db->exec("UPDATE `orders` SET
                                        `site_id` = {$siteId},
                                        `user_id` = {$userId},
                                        `status` = {$status},
                                        `payment_method` = {$paymentMethod},
                                        `amount` = {$amount},
                                        `hash` = {$hash},
                                        `reference_number` = {$referenceNumber},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($siteId, $userId, $status, $paymentMethod, $amount, $hash, $referenceNumber)
    {
        $this->db->exec("INSERT INTO `orders` SET
                                    `site_id` = {$siteId},
                                    `user_id` = {$userId},
                                    `status` = {$status},
                                    `payment_method` = {$paymentMethod},
                                    `amount` = {$amount},
                                    `hash` = {$hash},
                                    `reference_number` = {$referenceNumber}
                                ");

        return $this->db->lastInsertId();
    }

    private function checkSite()
    {
        if ($this->site instanceof SiteRecord && $this->siteId != $this->site->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function checkUser()
    {
        if ($this->user instanceof UserRecord && $this->userId != $this->user->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }
    
    private function check()
    {
        if (!in_array($this->paymentMethod, self::getAllowedPaymentMethods())) {
            throw new InvalidPaymentMethodException("Invalid payment method value");
        }

        if (!in_array($this->status, self::getAllowedStatuses())) {
            throw new InvalidStatusException("Invalid status value");
        }
        
        $this->checkSite();
        $this->checkUser();
    }
    
    public function save()
    {
        $this->check();
        
        $siteId = $this->escape($this->siteId);
        $userId = $this->escape($this->userId);
        $status = $this->escape($this->status, self::STATUS_CREATED, true);
        $paymentMethod = $this->escape($this->paymentMethod);
        $amount = $this->escape($this->amount);
        $hash = $this->escape($this->generateHash());
        $referenceNumber = $this->escape($this->referenceNumber);

        if (!empty($this->id)) {
            $this->updateRecord($siteId, $userId, $status, $paymentMethod, $amount, $hash, $referenceNumber);
        } else {
            $this->id = $this->insertRecord($siteId, $userId, $status, $paymentMethod, $amount, $hash, $referenceNumber);
        }

        $gatewayStatus = $this->escape($this->gatewayStatus);
        $gatewayData = $this->escape($this->gatewayData);
        $this->insertHistoryRecord($paymentMethod, $status, $gatewayStatus, $gatewayData);

        return true;
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `orders` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new OrderNotFoundException('Unable to load order record');
    }

    public function getProductsIterator()
    {
        if ($this->isNew()) {
            return new RecordsIterator();
        }
        
        $data = array();
        $records = $this->db->query("SELECT * FROM `orders_products` WHERE `order_id` = {$this->id}")->fetchAll();
        foreach ($records as $value) {
            $product = new Product\Record($this->db);
            $product->loadFromArray($value);

            array_push($data, $product);
        }
        
        return new RecordsIterator($data);
    }

    public static function getAllowedStatuses()
    {
        return self::$allowedStatuses;
    }

    public static function getAllowedPaymentMethods()
    {
        return self::$allowedPaymentMethods;
    }

}
