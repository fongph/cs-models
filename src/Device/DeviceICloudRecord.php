<?php

namespace CS\Models\Device;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Device\DeviceRecord;

/**
 * Class DeviceICloudRecord
 * @package CS\Models\Device
 *
 * @property integer $id
 * @property integer $devId
 * @property string $appleId
 * @property string $applePassword
 * @property string $deviceHash
 * @property integer $processing
 * @property integer $lastError
 * @property integer $lastBackup
 * @property integer $lastSync
 * @property integer $quotaUsed
 *
 * @method DeviceICloudRecord setId (integer $value)
 * @method DeviceICloudRecord setDevId (integer $value)
 * @method DeviceICloudRecord setAppleId (string $value)
 * @method DeviceICloudRecord setApplePassword (string $value)
 * @method DeviceICloudRecord setDeviceHash (string $value)
 * @method DeviceICloudRecord setProcessing (integer $value)
 * @method DeviceICloudRecord setLastError (integer $value)
 * @method DeviceICloudRecord setLastSync (integer $value)
 * @method DeviceICloudRecord setLastBackup (integer $value)
 * @method DeviceICloudRecord setQuotaUsed (integer $value)
 *
 */
class DeviceICloudRecord extends AbstractRecord
{
    protected $keys = array(
        'id' => 'id',
        'devId' => 'dev_id',
        'appleId' => 'apple_id',
        'applePassword' => 'apple_password',
        'deviceHash' => 'device_hash',
        'processing' => 'processing',
        'quotaUsed' => 'quota_used',
        'lastError' => 'last_error',
        'lastBackup' => 'last_backup',
        'lastSync' => 'last_sync',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    );
    protected $recordProperties = array(
        'id' => null,
        'devId' => null,
        'appleId' => null,
        'applePassword' => null,
        'deviceHash' => null,
        'processing' => 0,
        'lastBackup' => null,
        'lastError' => 0,
        'lastSync' => 0,
        'quotaUsed' => null,
    );

    public function __isset($name)
    {
        return array_key_exists($name, $this->recordProperties);
    }

    public function __call($name, $params)
    {
        switch(substr($name, 0, 3)) {
            case 'get':
                $propName = substr($name, 3);
                if(array_key_exists($propName, $this->recordProperties))
                    return $this->recordProperties[$propName];
                break;
            
            case 'set':
                $propName = lcfirst(substr($name, 3));
                if(array_key_exists($propName, $this->recordProperties)){
                    list($propValue) = $params;
                    $this->recordProperties[$propName] = $propValue;
                }
                return $this;
                break;
        }
        return null;
    }

    public function __set($name, $value)
    {

        if(method_exists($this, $method = "set" . ucfirst($name)))
            $this->$method($value);
            
        elseif(array_key_exists($name, $this->recordProperties))
            $this->recordProperties[$name] = $value;
    }

    public function __get($name)
    {
        if(method_exists($this, $method = "get" . ucfirst($name)))
            return $this->$method();
        
        if(array_key_exists($name, $this->recordProperties))
            return $this->recordProperties[$name];

        else return null;
    }

    private function updateRecord()
    {
        return (bool)$this->db->exec("
            UPDATE `devices_icloud` 
            SET `dev_id` = {$this->devId},
                `apple_id` = {$this->db->quote($this->appleId)},
                `apple_password` = {$this->db->quote($this->applePassword)},
                `device_hash` = {$this->db->quote($this->deviceHash)},
                `processing` = {$this->processing},
                `quota_used` = {$this->quotaUsed},
                `last_error` = {$this->lastError},
                `last_backup` = IF(UNIX_TIMESTAMP({$this->db->quote($this->lastBackup)}), {$this->db->quote($this->lastBackup)}, NULL),
                `last_sync` = {$this->db->quote($this->lastSync)},
                `updated_at` = NOW()
            WHERE `id` = {$this->id}"
        );
    }

    private function insertRecord()
    {
        $this->db->exec("
            INSERT INTO `devices_icloud` 
            SET `dev_id` = {$this->devId},
                `apple_id` = {$this->db->quote($this->appleId)},
                `apple_password` = {$this->db->quote($this->applePassword)},
                `device_hash` = {$this->db->quote($this->deviceHash)},
                `processing` = {$this->processing},
                `quota_used` = {$this->quotaUsed},
                `last_error` = {$this->lastError},
                `last_backup` = IF(UNIX_TIMESTAMP({$this->db->quote($this->lastBackup)}), {$this->db->quote($this->lastBackup)}, NULL),
                `last_sync` = IF(UNIX_TIMESTAMP({$this->db->quote($this->lastSync)}), {$this->db->quote($this->lastSync)}, NULL),
                `created_at` = NOW()"
        );

        return $this->db->lastInsertId();
    }
    
    public function check()
    {
        $checkStatus = is_numeric($this->devId)
            && !empty($this->appleId)
            && !empty($this->applePassword)
            && !empty($this->deviceHash);
        if($checkStatus) 
            return true;
        else throw new InvalidICloudParamsException; 
    }
    
    public function save()
    {
        $this->processing = $this->processing ? 1 : 0;
        $this->devId      = (int)$this->devId;
        $this->quotaUsed  = (int)$this->quotaUsed;
        $this->lastError  = (int)$this->lastError;
        $this->check();
        
        if (!empty($this->id)) {
            return $this->updateRecord();
        }

        $this->id = $this->insertRecord();

        return true;
    }

    /**
     * @param $id
     * @return $this
     * @throws DeviceNotFoundException
     */
    function load($id)
    {
        $data = $this->db->query("
            SELECT *, 
                UNIX_TIMESTAMP(`last_sync`) as `last_sync`,
                UNIX_TIMESTAMP(`created_at`) as `created_at`,
                UNIX_TIMESTAMP(`updated_at`) as `updated_at`
            FROM `devices_icloud` 
            WHERE `id` = {$this->db->quote($id)} LIMIT 1")->fetch(PDO::FETCH_ASSOC);

        if ($data === false)
            throw new DeviceNotFoundException('Unable to load order record');

        return $this->loadFromArray($data);
    }

    function loadByDevId($devId)
    {
        $data = $this->db->query("
            SELECT *, 
                UNIX_TIMESTAMP(`last_sync`) as `last_sync`,
                UNIX_TIMESTAMP(`created_at`) as `created_at`,
                UNIX_TIMESTAMP(`updated_at`) as `updated_at`
            FROM `devices_icloud` 
            WHERE `dev_id` = {$this->db->quote($devId)}
            LIMIT 1")->fetch(PDO::FETCH_ASSOC);

        if ($data === false)
            throw new DeviceNotFoundException('Unable to load order record');

        return $this->loadFromArray($data);
    }
    
    public function getDeviceRecord()
    {
        if($this->isNew() || !$this->devId)
            return null;
        
        
        return (new DeviceRecord($this->db))
            ->load($this->devId);
        
    }

}


class InvalidICloudParamsException extends \Exception { }