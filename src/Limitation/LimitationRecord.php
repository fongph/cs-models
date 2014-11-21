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
    protected $recurrence = 0;
    protected $keys = array(
        'id' => 'id',
        'name' => 'name',
        'sms' => 'sms',
        'call' => 'call',
        'value' => 'value',
        'lifetime' => 'lifetime',
        'recurrence' => 'recurrence',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

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

    private function updateRecord($name, $lifetime, $recurrence, $sms, $call, $value)
    {
        $rows = $this->db->exec("UPDATE `limitations` SET
                                        `name` = {$name},
                                        `sms` = {$sms},
                                        `call` = {$call},
                                        `value` = {$value},
                                        `lifetime` = {$lifetime},
                                        `recurrence` = {$recurrence},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($name, $lifetime, $recurrence, $sms, $call, $value)
    {
        $this->db->exec("INSERT INTO `limitations` SET
                            `name` = {$name},
                            `sms` = {$sms},
                            `call` = {$call},
                            `value` = {$value},
                            `lifetime` = {$lifetime},
                            `recurrence` = {$recurrence}
                        ");

        return $this->db->lastInsertId();
    }

    public function save()
    {
        $name = $this->escape($this->name);
        $lifetime = $this->escape($this->lifetime);
        $recurrence = $this->escape($this->recurrence);
        $sms = $this->escape($this->sms);
        $call = $this->escape($this->call);
        $value = $this->escape($this->value);

        if (!empty($this->id)) {
            return $this->updateRecord($name, $lifetime, $recurrence, $sms, $call, $value);
        } else {
            $this->id = $this->insertRecord($name, $lifetime, $recurrence, $sms, $call, $value);
        }
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT * FROM `limitations` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) == false) {
            throw new LimitationNotFoundException('Unable to load limitation record');
        }

        return $this->loadFromArray($data);
    }

}
