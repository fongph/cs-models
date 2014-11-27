<?php

namespace CS\Models\Subscription;

use CS\Models\AbstractRecord,
    CS\Models\License\LicenseRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\Order\InvalidPaymentMethodException;

/**
 * Description of SubscriptionRecord
 *
 * @author root
 */
class SubscriptionRecord extends AbstractRecord
{

    /**
     *
     * @var LicenseRecord
     */
    protected $license;
    protected $licenseId;
    protected $paymentMethod;
    protected $referenceNumber;
    protected $keys = array(
        'id' => 'id',
        'licenseId' => 'license_id',
        'referenceNumber' => 'reference_number',
        'paymentMethod' => 'payment_method',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    public function setLicense(LicenseRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->licenseId = $value->getId();

        $this->license = $value;

        return $this;
    }

    /*
     * return LicenseRecord
     */

    public function getLicense()
    {
        if ($this->license instanceof OrderProductRecord) {
            return $this->license;
        }

        if (!$this->isNew()) {
            $licenseRecord = new OrderProductRecord($this->db);
            $licenseRecord->load($this->licenseId);

            $this->setLicense($licenseRecord);
            return $this->license;
        }

        return null;
    }

    public function setPaymentMethod($value)
    {
        $this->paymentMethod = $value;

        return $this;
    }

    public function getPaymentMethod()
    {
        return $this;
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
    
    public function setLicenseId($value)
    {
        $this->licenseId = $value;

        return $this;
    }

    public function getLicenseId()
    {
        return $this;
    }

    private function updateRecord($licenseId, $paymentMethod, $referenceNumber)
    {
        $rows = $this->db->exec("UPDATE `subscriptions` SET
                                        `license_id` = {$licenseId},
                                        `payment_method` = {$paymentMethod},
                                        `reference_number` = {$referenceNumber},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($licenseId, $paymentMethod, $referenceNumber)
    {
        $this->db->exec("INSERT INTO `subscriptions` SET
                            `license_id` = {$licenseId},
                            `payment_method` = {$paymentMethod},
                            `reference_number` = {$referenceNumber}
                        ");

        return $this->db->lastInsertId();
    }

    public function check()
    {
        if (!in_array($this->paymentMethod, OrderRecord::getAllowedPaymentMethods())) {
            throw new InvalidPaymentMethodException("Invalid payment method value!");
        }
    }

    public function save()
    {
        $this->check();
        
        $licenseId = $this->escape($this->licenseId);
        $paymentMethod = $this->escape($this->paymentMethod);
        $referenceNumber = $this->escape($this->referenceNumber);

        if (!empty($this->licenseId)) {
            if (!$this->updateRecord($licenseId, $paymentMethod, $referenceNumber)) {
                return false;
            }
        } else {
            $this->id = $this->insertRecord($licenseId, $paymentMethod, $referenceNumber);
        }

        return true;
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `subscriptions` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new SubscriptionNotFoundException('Unable to load subscription record');
    }

}