<?php

namespace CS\Models\Order;

use PDO,
    CS\Models\AbstractRecord,
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
    protected $status = self::STATUS_CREATED;
    protected $paymentMethod = self::PAYMENT_METHOD_BLUESNAP;
    protected $amount = 0;
    protected $location;
    protected $hash;
    protected $referenceNumber;
    protected $person;
    protected $phone;
    protected $test = 0;
    protected $gatewayStatus;
    protected $gatewayData;
    protected $keys = array(
        'id' => 'id',
        'siteId' => 'site_id',
        'userId' => 'user_id',
        'status' => 'status',
        'paymentMethod' => 'payment_method',
        'amount' => 'amount',
        'location' => 'location',
        'hash' => 'hash',
        'referenceNumber' => 'reference_number',
        'person' => 'person',
        'phone' => 'phone',
        'test' => 'test',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    /**
     * List of allowed statuses
     * 
     * @var array
     */
    protected static $allowedStatuses = array(self::STATUS_CREATED, self::STATUS_PENDING, self::STATUS_COMPLETED);

    /**
     * List of allowed payment methods
     * 
     * @var array
     */
    protected static $allowedPaymentMethods = array(self::PAYMENT_METHOD_BLUESNAP, self::PAYMENT_METHOD_FASTSPRING);

    const STATUS_CREATED = 'created';
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const PAYMENT_METHOD_BLUESNAP = 'bluesnap';
    const PAYMENT_METHOD_FASTSPRING = 'fastspring';

    public function getHash()
    {
        return $this->hash;
    }

    private function generateHash()
    {
        $this->hash = substr(md5(__CLASS__ . microtime()), 0, 10);

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

    public function setLocation($value)
    {
        $this->location = $value;

        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setPerson($value)
    {
        $this->person = $value;

        return $this;
    }

    public function getPerson()
    {
        return $this->person;
    }
    
    public function setPhone($value)
    {
        $this->phone = $value;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }
    
    public function setTest($value = true)
    {
        $this->test = $this->boolToNum($value);

        return $this;
    }

    public function getTest()
    {
        return $this->test;
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

    private function updateRecord($siteId, $userId, $status, $paymentMethod, $amount, $location, $hash, $referenceNumber, $person, $phone, $test)
    {
        $rows = $this->db->exec("UPDATE `orders` SET
                                        `site_id` = {$siteId},
                                        `user_id` = {$userId},
                                        `status` = {$status},
                                        `payment_method` = {$paymentMethod},
                                        `amount` = {$amount},
                                        `location` = {$location},
                                        `hash` = {$hash},
                                        `reference_number` = {$referenceNumber},
                                        `person` = {$person},
                                        `phone` = {$phone},
                                        `test` = {$test},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($siteId, $userId, $status, $paymentMethod, $amount, $location, $hash, $referenceNumber, $person, $phone, $test)
    {
        $this->db->exec("INSERT INTO `orders` SET
                                    `site_id` = {$siteId},
                                    `user_id` = {$userId},
                                    `status` = {$status},
                                    `payment_method` = {$paymentMethod},
                                    `amount` = {$amount},
                                    `location` = {$location},
                                    `hash` = {$hash},
                                    `reference_number` = {$referenceNumber},
                                    `person` = {$person},
                                    `phone` = {$phone},
                                    `test` = {$test}
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
        $location = $this->escape($this->location);
        $referenceNumber = $this->escape($this->referenceNumber);
        $person = $this->escape($this->person);
        $phone = $this->escape($this->phone);
        $test = $this->escape($this->test);
        $hash = $this->escape($this->isNew() ? $this->generateHash() : $this->hash);

        if (!empty($this->id)) {
            $this->updateRecord($siteId, $userId, $status, $paymentMethod, $amount, $location, $hash, $referenceNumber, $person, $phone, $test);
        } else {
            $this->id = $this->insertRecord($siteId, $userId, $status, $paymentMethod, $amount, $location, $hash, $referenceNumber, $person, $phone, $test);
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

    public static function getAllowedStatuses()
    {
        return self::$allowedStatuses;
    }

    public static function getAllowedPaymentMethods()
    {
        return self::$allowedPaymentMethods;
    }

}
