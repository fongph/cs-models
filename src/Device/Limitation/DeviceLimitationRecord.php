<?php

namespace CS\Models\Device\Limitation;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Device\DeviceRecord;

/**
 * Description of DeviceLimitationRecord
 *
 * @author root
 */
class DeviceLimitationRecord extends AbstractRecord
{

    /**
     *
     * @var DeviceRecord
     */
    protected $device;
    protected $deviceId;
    protected $sms = 0;
    protected $call = 0;
    protected $value = 0;
    protected $savedSms;
    protected $savedCall;
    protected $keys = array(
        'id' => 'id',
        'deviceId' => 'device_id',
        'sms' => 'sms',
        'call' => 'call',
        'value' => 'value',
        'savedSms' => 'saved_sms',
        'savedCall' => 'saved_call',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    const UNLIMITED_VALUE = 65535;

    public function setDeviceId($id)
    {
        $this->deviceId = $id;

        return $this;
    }

    public function getDeviceId()
    {
        return $this->deviceId;
    }

    public function getSms()
    {
        return $this->sms;
    }

    public function setSms($value)
    {
        $this->sms = $value;

        return $this;
    }

    public function getCall()
    {
        return $this->call;
    }

    public function setCall($value)
    {
        $this->call = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
    
    public function getSavedSms()
    {
        return $this->savedSms;
    }

    public function setSavedSms($value)
    {
        $this->savedSms = $value;

        return $this;
    }

    public function getSavedCall()
    {
        return $this->savedCall;
    }

    public function setSavedCall($value)
    {
        $this->savedCall = $value;

        return $this;
    }

    public function setDevice(DeviceRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->deviceId = $value->getId();
        $this->device = $value;

        return $this;
    }

    /**
     * 
     * @return UserRecord
     */
    public function getUser()
    {
        if ($this->device instanceof DeviceRecord) {
            return $this->device;
        }

        if (!$this->isNew() && $this->deviceId) {
            $deviceRecord = new DeviceRecord($this->db);
            $deviceRecord->load($this->deviceId);

            $this->setDevice($deviceRecord);
            return $this->device;
        }

        return null;
    }

    private function updateRecord($deviceId, $sms, $call, $value, $savedSms, $savedCall)
    {
        $rows = $this->db->exec("UPDATE `devices_limitations` SET
                                        `device_id` = {$deviceId},
                                        `sms` = {$sms},
                                        `call` = {$call},
                                        `value` = {$value},
                                        `saved_sms` = {$savedSms},
                                        `saved_call` = {$savedCall},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($deviceId, $sms, $call, $value, $savedSms, $savedCall)
    {
        $this->db->exec("INSERT INTO `devices_limitations` SET
                            `device_id` = {$deviceId},
                            `sms` = {$sms},
                            `call` = {$call},
                            `value` = {$value},
                            `saved_sms` = {$savedSms},
                            `saved_call` = {$savedCall}
                        ");

        return $this->db->lastInsertId();
    }
    
    private function checkDevice()
    {
        if ($this->device instanceof DeviceRecord && $this->deviceId != $this->device->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function check()
    {
        $this->checkDevice();
    }

    public function save()
    {
        $this->check();

        $deviceId = $this->escape($this->deviceId);
        $sms = $this->escape($this->sms);
        $call = $this->escape($this->call);
        $value = $this->escape($this->value);
        $savedSms = $this->escape($this->savedSms, 'NULL');
        $savedCall = $this->escape($this->savedCall, 'NULL');

        if (!empty($this->id)) {
            return $this->updateRecord($deviceId, $sms, $call, $value, $savedSms, $savedCall);
        } else {
            $this->id = $this->insertRecord($deviceId, $sms, $call, $value, $savedSms, $savedCall);
        }
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT * FROM `devices_limitations` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) == false) {
            throw new DeviceLimitationNotFoundException('Unable to load limitation record');
        }

        return $this->loadFromArray($data);
    }

    public function loadByDeviceId($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT * FROM `devices_limitations` WHERE `device_id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) == false) {
            throw new DeviceLimitationNotFoundException('Unable to load limitation record');
        }

        return $this->loadFromArray($data);
    }

}
