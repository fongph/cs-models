<?php

namespace CS\Models\Subscription;

use CS\Models\AbstractRecord,
    CS\Models\License\LicenseRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\Order\InvalidPaymentMethodException,
    CS\Models\License\LicenseDoNotHaveSubscriptionException,
    CS\Models\RecordNotCreatedException;

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
    protected $paymentMethod = OrderRecord::PAYMENT_METHOD_BLUESNAP;
    protected $referenceNumber;
    protected $auto = 0;
    protected $reason = self::REASON_NONE;
    protected $nextDunningStep = self::NEXT_DUNNING_STEP_FIRST_FOLLOWUP;
    protected $keys = array(
        'id' => 'id',
        'licenseId' => 'license_id',
        'paymentMethod' => 'payment_method',
        'referenceNumber' => 'reference_number',
        'auto' => 'auto',
        'reason' => 'reason',
        'nextDunningStep' => 'next_dunning_step',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );
    protected static $allowedReasons = array(self::REASON_NONE, self::REASON_CANCELED_NON_PAYMENT, self::REASON_COMPLETED, self::REASON_CANCELED);
    protected static $allowedNextDunningSteps = array(
        self::NEXT_DUNNING_STEP_FIRST_FOLLOWUP,
        self::NEXT_DUNNING_STEP_THREE_DAY_CANCEL,
        self::NEXT_DUNNING_STEP_FIVE_DAY_CANCEL,
        self::NEXT_DUNNING_STEP_SEVEN_DAY_CANCEL,
        self::NEXT_DUNNING_STEP_FOURTEEN_DAY_CANCEL,
        self::NEXT_DUNNING_STEP_TWENTY_ONE_DAY_CANCEL,
        self::NEXT_DUNNING_STEP_THREE_DAY_REMINDER,
        self::NEXT_DUNNING_STEP_FIVE_DAY_REMINDER,
        self::NEXT_DUNNING_STEP_SEVEN_DAY_REMINDER,
        self::NEXT_DUNNING_STEP_FOURTEEN_DAY_REMINDER,
        self::NEXT_DUNNING_STEP_TWENTY_ONE_DAY_REMINDER
    );

    const REASON_NONE = 'none';
    const REASON_CANCELED_NON_PAYMENT = 'canceled-non-payment';
    const REASON_COMPLETED = 'completed';
    const REASON_CANCELED = 'canceled';
    const NEXT_DUNNING_STEP_FIRST_FOLLOWUP = 'first-followup';
    const NEXT_DUNNING_STEP_THREE_DAY_CANCEL = 'three-day-cancel';
    const NEXT_DUNNING_STEP_FIVE_DAY_CANCEL = 'five-day-cancel';
    const NEXT_DUNNING_STEP_SEVEN_DAY_CANCEL = 'seven-day-cancel';
    const NEXT_DUNNING_STEP_FOURTEEN_DAY_CANCEL = 'fourteen-day-cancel';
    const NEXT_DUNNING_STEP_TWENTY_ONE_DAY_CANCEL = 'twenty-one-day-cancel';
    const NEXT_DUNNING_STEP_THREE_DAY_REMINDER = 'three-day-reminder';
    const NEXT_DUNNING_STEP_FIVE_DAY_REMINDER = 'five-day-reminder';
    const NEXT_DUNNING_STEP_SEVEN_DAY_REMINDER = 'seven-day-reminder';
    const NEXT_DUNNING_STEP_FOURTEEN_DAY_REMINDER = 'fourteen-day-reminder';
    const NEXT_DUNNING_STEP_TWENTY_ONE_DAY_REMINDER = 'twenty-one-day-reminder';

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
        if ($this->license instanceof LicenseRecord) {
            return $this->license;
        }

        if (!$this->isNew()) {
            $licenseRecord = new LicenseRecord($this->db);
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
        return $this->paymentMethod;
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
        return $this->licenseId;
    }

    public function setAuto($value)
    {
        $this->auto = $value;

        return $this;
    }

    public function getAuto()
    {
        return $this->auto;
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
    
    public function setNextDunningStep($value)
    {
        $this->nextDunningStep = $value;

        return $this;
    }

    public function getNextDunningStep()
    {
        return $this->nextDunningStep;
    }

    private function updateRecord($licenseId, $paymentMethod, $referenceNumber, $auto, $reason, $nextDunningStep)
    {
        $rows = $this->db->exec("UPDATE `subscriptions` SET
                                        `license_id` = {$licenseId},
                                        `payment_method` = {$paymentMethod},
                                        `reference_number` = {$referenceNumber},
                                        `auto` = {$auto},
                                        `reason` = {$reason},
                                        `next_dunning_step` = {$nextDunningStep},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($licenseId, $paymentMethod, $referenceNumber, $auto, $reason, $nextDunningStep)
    {
        $this->db->exec("INSERT INTO `subscriptions` SET
                            `license_id` = {$licenseId},
                            `payment_method` = {$paymentMethod},
                            `reference_number` = {$referenceNumber},
                            `auto` = {$auto},
                            `reason` = {$reason},
                            `next_dunning_step` = {$nextDunningStep},
                        ");

        return $this->db->lastInsertId();
    }

    public function check()
    {
        if (!in_array($this->paymentMethod, OrderRecord::getAllowedPaymentMethods())) {
            throw new InvalidPaymentMethodException("Invalid payment method value!");
        }

        if (!in_array($this->reason, self::getAllowedReasons())) {
            throw new InvalidReasonException("Invalid reason value!");
        }

        if (!in_array($this->nextDunningStep, self::getAllowedNextDunningSteps())) {
            throw new InvalidReasonException("Invalid nex dunning step value!");
        }
    }

    public function save()
    {
        $this->check();

        $licenseId = $this->escape($this->licenseId);
        $paymentMethod = $this->escape($this->paymentMethod);
        $referenceNumber = $this->escape($this->referenceNumber);
        $auto = $this->escape($this->auto);
        $reason = $this->escape($this->reason);
        $nextDunningStep = $this->escape($this->nextDunningStep);

        if (!empty($this->id)) {
            if (!$this->updateRecord($licenseId, $paymentMethod, $referenceNumber, $auto, $reason, $nextDunningStep)) {
                return false;
            }
        } else {
            $this->id = $this->insertRecord($licenseId, $paymentMethod, $referenceNumber, $auto, $reason, $nextDunningStep);
        }

        return true;
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `subscriptions` WHERE `id` = {$escapedId} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new SubscriptionNotFoundException('Unable to load subscription record');
    }

    public function loadByLicenseId($licenseId)
    {
        $escapedId = $this->db->quote($licenseId);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `subscriptions` WHERE `license_id` = {$escapedId} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new LicenseDoNotHaveSubscriptionException('Unable to load subscription record');
    }

    public static function getAllowedReasons()
    {
        return self::$allowedReasons;
    }

    public static function getAllowedNextDunningSteps()
    {
        return self::$allowedNextDunningSteps;
    }

}
