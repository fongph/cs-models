<?php

namespace CS\Models\Order\Payment;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Site\SiteRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\RecordNotCreatedException,
    CS\Models\RecordAlreadyCreatedException,
    CS\Models\RecordDifferencesException;

/**
 * Description of OrderPaymentRecord
 *
 * @author root
 */
class OrderPaymentRecord extends AbstractRecord
{

    /**
     *
     * @var SiteRecord
     */
    protected $site;

    /**
     *
     * @var OrderRecord
     */
    protected $order;
    protected $siteId;
    protected $orderId;
    protected $type = self::TYPE_SALE;
    protected $amount = 0;
    protected $commission = 0;
    protected $vat = 0;
    protected $discount = 0;
    protected $discountCode;
    protected $currency = 'USD';
    protected $rate = 1;
    protected $test = 0;
    protected $keys = array(
        'id' => 'id',
        'siteId' => 'site_id',
        'orderId' => 'order_id',
        'type' => 'type',
        'amount' => 'amount',
        'commission' => 'commission',
        'vat' => 'vat',
        'discount' => 'discount',
        'discountCode' => 'discount_code',
        'currency' => 'currency',
        'rate' => 'rate',
        'test' => 'test',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    /**
     * List of allowed statuses
     * 
     * @var array
     */
    protected static $allowedTypes = array(self::TYPE_SALE, self::TYPE_PROLONGATION, self::TYPE_REFUND, self::TYPE_CHARGEBACK, self::TYPE_FRAUD);

    const TYPE_SALE = 'sale';
    const TYPE_PROLONGATION = 'prolongation';
    const TYPE_REFUND = 'refund';
    const TYPE_CHARGEBACK = 'chargeback';
    const TYPE_FRAUD = 'fraud';

    public function setSiteId($id)
    {
        $this->siteId = $id;

        return $this;
    }

    public function getSiteId()
    {
        return $this->siteId;
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

    public function setSite(SiteRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->siteId = $value->getId();

        $this->site = $value;

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

    public function setOrder(OrderRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->orderId = $value->getId();
        $this->siteId = $value->getSiteId();
        $this->test = $value->getTest();

        $this->order = $value;

        return $this;
    }

    public function setType($value)
    {
        $this->type = $value;

        return $this;
    }

    public function getType()
    {
        return $this->type;
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

    public function setAmount($value)
    {
        $this->amount = $value;

        return $this;
    }
    
    public function getAmount()
    {
        return $this->amount;
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

    public function getRate()
    {
        return $this->rate;
    }

    public function setRate($value)
    {
        $this->rate = $value;

        return $this;
    }
    
    public function getVat()
    {
        return $this->vat;
    }

    public function setVat($value)
    {
        $this->vat = $value;

        return $this;
    }

    public function getCommission()
    {
        return $this->commission;
    }

    public function setCommision($value)
    {
        $this->commission = $value;

        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setDiscount($value)
    {
        $this->discount = $value;

        return $this;
    }

    public function getDiscountCode()
    {
        return $this->discountCode;
    }

    public function setDiscountCode($value)
    {
        $this->discountCode = $value;

        return $this;
    }

    private function checkSite()
    {
        if ($this->site instanceof SiteRecord && $this->siteId != $this->site->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function checkOrder()
    {
        if ($this->order instanceof OrderRecord && $this->orderId != $this->order->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function check()
    {
        if (!in_array($this->type, self::getAllowedTypes())) {
            throw new InvalidTypeException("Invalid status value");
        }

        $this->checkSite();
        $this->checkOrder();
    }

    private function updateRecord($siteId, $orderId, $type, $amount, $commission, $vat, $discount, $discountCode, $currency, $rate, $test)
    {
        $rows = $this->db->exec("UPDATE `orders_payments` SET
                                        `site_id` = {$siteId},
                                        `order_id` = {$orderId},
                                        `type` = {$type},
                                        `amount` = {$amount},
                                        `commission` = {$commission},
                                        `vat` = {$vat},
                                        `discount` = {$discount},
                                        `discount_code` = {$discountCode},
                                        `currency` = {$currency},
                                        `rate` = {$rate},
                                        `test` = {$test},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }
    
    private function insertRecord($siteId, $orderId, $type, $amount, $commission, $vat, $discount, $discountCode, $currency, $rate, $test)
    {
        $this->db->exec("INSERT INTO `orders_payments` SET
                            `site_id` = {$siteId},
                            `order_id` = {$orderId},
                            `type` = {$type},
                            `amount` = {$amount},
                            `commission` = {$commission},
                            `vat` = {$vat},
                            `discount` = {$discount},
                            `discount_code` = {$discountCode},
                            `currency` = {$currency},
                            `rate` = {$rate},
                            `test` = {$test}
                        ");

        return $this->db->lastInsertId();
    }

    public function save()
    {
        $this->check();

        $siteId = $this->escape($this->siteId);
        $orderId = $this->escape($this->orderId);
        $type = $this->escape($this->type);
        $amount = $this->escape($this->amount);
        $commission = $this->escape($this->commission);
        $vat = $this->escape($this->vat);
        $discount = $this->escape($this->discount);
        $discountCode = $this->escape($this->discountCode);
        $currency = $this->escape($this->currency);
        $rate = $this->escape($this->rate);
        $test = $this->escape($this->test);

        if (!empty($this->id)) {
            return $this->updateRecord($siteId, $orderId, $type, $amount, $commission, $vat, $discount, $discountCode, $currency, $rate, $test);
        } else {
            $this->id = $this->insertRecord($siteId, $orderId, $type, $amount, $commission, $vat, $discount, $discountCode, $currency, $rate, $test);
        }

        return true;
    }

    /**
     * 
     * @param type $id
     * @return OrderPaymentRecord
     * @throws OrderPaymentNotFoundException
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT * FROM `orders_payments` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new OrderPaymentNotFoundException('Unable to load order payment record');
    }

    public static function getAllowedTypes()
    {
        return self::$allowedTypes;
    }

}
