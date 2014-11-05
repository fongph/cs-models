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
    protected $sms;
    protected $lifetime;
    protected $recurrence;
    protected $keys = array(
        'id' => 'id',
        'name' => 'name',
        'sms' => 'sms',
        'lifetime' => 'lifetime',
        'recurrence' => 'recurrence',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    const UNLIMITED_VALUE = 65535;

    public function setLifetime($value)
    {
        $this->lifetime = $value;

        return $this;
    }

    public function getLifetime()
    {
        return $this->lifetime;
    }

    public function getRecurrence()
    {
        return $this->recurrence > 0;
    }

    public function setRecurrence($value = true)
    {
        $this->recurrence = intval($value);

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

    private function updateRecord($name, $lifetime, $recurrence, $sms)
    {
        $rows = $this->db->exec("UPDATE `limitations` SET
                                        `name` = {$name},
                                        `lifetime` = {$lifetime},
                                        `recurrence` = {$recurrence},
                                        `sms` = {$sms},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($name, $lifetime, $recurrence, $sms)
    {
        $this->db->exec("INSERT INTO `limitations` SET
                            `name` = {$name},
                            `lifetime` = {$lifetime},
                            `recurrence` = {$recurrence},
                            `sms` = {$sms}
                        ");

        return $this->db->lastInsertId();
    }

    public function save()
    {
        $name = $this->escape($this->name);
        $lifetime = $this->escape($this->lifetime, 0);
        $recurrence = $this->escape($this->recurrence, 0);
        $sms = $this->escape($this->sms, 0);

        if (!empty($this->id)) {
            return $this->updateRecord($name, $lifetime, $recurrence, $sms);
        } else {
            $this->id = $this->insertRecord($name, $lifetime, $recurrence, $sms);
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
