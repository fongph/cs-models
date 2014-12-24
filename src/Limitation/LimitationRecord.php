<?php

namespace CS\Models\Limitation;

use PDO,
    CS\Models\AbstractRecord;

/**
 * Description of LimitationRecord
 *
 * @author root
 */
class LimitationRecord extends AbstractRecord
{

    protected $name;
    protected $sms = 0;
    protected $call = 0;
    protected $value = 0;
    protected $lifetime = 0;
    protected $period = self::PERIOD_DAY;
    protected $recurrence = 0;
    protected $continuous = 0;
    protected $keys = array(
        'id' => 'id',
        'name' => 'name',
        'sms' => 'sms',
        'call' => 'call',
        'value' => 'value',
        'lifetime' => 'lifetime',
        'period' => 'period',
        'recurrence' => 'recurrence',
        'continuous' => 'continuous',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    const PERIOD_DAY = 'day';
    const PERIOD_WEEK = 'week';
    const PERIOD_MONTH = 'month';
    const PERIOD_YEAR = 'year';
    const UNLIMITED_VALUE = 65535;

    public function getRecurrence()
    {
        return $this->recurrence;
    }

    public function setRecurrence($value)
    {
        $this->recurrence = $value;

        return $this;
    }
    
    public function getContinuous()
    {
        return $this->continuous;
    }

    public function setContinuous($value)
    {
        $this->continuous = $value;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;

        return $this;
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
    
    public function getLifetime()
    {
        return $this->lifetime;
    }

    public function setLifetime($value)
    {
        $this->lifetime = $value;

        return $this;
    }
    
    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod($value)
    {
        $this->period = $value;

        return $this;
    }

    private function updateRecord($name, $lifetime, $period, $recurrence, $sms, $call, $value, $continuous)
    {
        $rows = $this->db->exec("UPDATE `limitations` SET
                                        `name` = {$name},
                                        `sms` = {$sms},
                                        `call` = {$call},
                                        `value` = {$value},
                                        `lifetime` = {$lifetime},
                                        `period` = {$period},
                                        `recurrence` = {$recurrence},
                                        `continuous` = {$continuous},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($name, $lifetime, $period, $recurrence, $sms, $call, $value, $continuous)
    {
        $this->db->exec("INSERT INTO `limitations` SET
                            `name` = {$name},
                            `sms` = {$sms},
                            `call` = {$call},
                            `value` = {$value},
                            `lifetime` = {$lifetime},
                            `period` = {$period},
                            `recurrence` = {$recurrence},
                            `continuous` = {$continuous}
                        ");

        return $this->db->lastInsertId();
    }

    public function save()
    {
        $name = $this->escape($this->name);
        $lifetime = $this->escape($this->lifetime);
        $period = $this->escape($this->period);
        $recurrence = $this->escape($this->recurrence);
        $sms = $this->escape($this->sms);
        $call = $this->escape($this->call);
        $value = $this->escape($this->value);
        $continuous = $this->escape($this->continuous);

        if (!empty($this->id)) {
            return $this->updateRecord($name, $lifetime, $period, $recurrence, $sms, $call, $value, $continuous);
        } else {
            $this->id = $this->insertRecord($name, $lifetime, $period, $recurrence, $sms, $call, $value, $continuous);
        }
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `limitations` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) == false) {
            throw new LimitationNotFoundException('Unable to load limitation record');
        }

        return $this->loadFromArray($data);
    }

}
