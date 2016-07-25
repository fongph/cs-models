<?php

namespace CS\Models\Device;

use PDO,
    CS\Models\AbstractRecord;

/**
 * Class DeviceICloudRecord
 * @package CS\Models\Device
 *
 * @property integer $id
 * @property integer $devId
 * @property string $appleId
 * @property string $applePassword
 * @property string $deviceHash
 * @property string $serialNumber
 * @property integer $processing
 * @property integer $lastError
 * @property integer $lastBackup
 * @property integer $lastSync
 * @property integer $quotaUsed
 * @property string $lastSnapshot
 * @property integer $duration
 * @property string $worker
 * @property string $lastPhoto
 * @property integer $photoSync
 * @property integer $photoDuration
 * @property integer $processingStartTime
 *
 * @method DeviceICloudRecord setId (integer $value)
 * @method DeviceICloudRecord setDevId (integer $value)
 * @method DeviceICloudRecord setAppleId (string $value)
 * @method DeviceICloudRecord setApplePassword (string $value)
 * @method DeviceICloudRecord setDeviceHash (string $value)
 * @method DeviceICloudRecord setSerialNumber (string $value)
 * @method DeviceICloudRecord setProcessing (integer $value)
 * @method DeviceICloudRecord setLastError (integer $value)
 * @method DeviceICloudRecord setLastSync (integer $value)
 * @method DeviceICloudRecord setLastBackup (integer $value)
 * @method DeviceICloudRecord setQuotaUsed (integer $value)
 * @method DeviceICloudRecord setLastSnapshot (string $value)
 * @method DeviceICloudRecord setDuration (integer $value)
 * @method DeviceICloudRecord setWorker (string $value)
 * @method DeviceICloudRecord setLastPhoto (string $value)
 * @method DeviceICloudRecord setPhotoSync (string $value)
 * @method DeviceICloudRecord setPhotoDuration (string $value)
 * @method DeviceICloudRecord setProcessingStartTime (integer $value)
 * @method DeviceICloudRecord setLastCommited (integer $value)
 *
 * @method integer getId ()
 * @method integer getDevId ()
 * @method string getAppleId ()
 * @method string getApplePassword ()
 * @method string getSerialNumber ()
 * @method string getDeviceHash ()
 * @method integer getProcessing ()
 * @method integer getLastError ()
 * @method integer getLastSync ()
 * @method integer getLastBackup ()
 * @method integer getQuotaUsed ()
 * @method integer getDuration ()
 * @method string getWorker ()
 * @method string getLastPhoto ()
 * @method integer getPhotoSync ()
 * @method integer getPhotoDuration ()
 * @method integer getProcessingStartTime ()
 * @method integer getLastCommited ()
 * 
 */
class DeviceICloudRecord extends AbstractRecord
{
    const PROCESS_AWAITING = 0;
    const PROCESS_IMPORT = 1;
    const PROCESS_FIRST_COMMIT = 2;

    const ERROR_NONE = 0;
    const ERROR_AUTHENTICATION = 1;

    const ERROR_FIRST_NOT_COMMITTED = 2;
    const ERROR_DIRECTORY_EXIST = 3;
    const ERROR_INVALID_OUTPUT_DIR = 4;
    const ERROR_UNDEFINED_ON_DOWNLOAD = 5;
    const ERROR_TWO_STEP_VERIFICATION = 6;
    const ERROR_INVALID_PYTHON_RESULT = 8;
    const ERROR_UNDEFINED_PYTHON = 9;

    const ERROR_DEVICE_NOT_FOUND_ON_ICLOUD = 11;
    const ERROR_INVALID_QUEUE_DATA = 12;
    const ERROR_PARSE = 13;

    const ERROR_ADD_QUEUE_TASK = 14;
    const ERROR_UNDEFINED = 15;
    const ERROR_UNDEFINED_ON_CRON = 16;
    const ERROR_REQUEST_TIMEOUT = 17;

    //not fatal errors with 3 digits
    const ERROR_BACKUP_DECODING = 100;

    const ERROR_LOADING_CHUNK_TIMEOUT = 110;
    const ERROR_TIMEOUT = 111;

    const ERROR_INVALID_RESPONSE_CODE = 113;

    const ERROR_EMPTY_DB_FILE = 160;
    const ERROR_NO_DATA = 161;
    const ERROR_SQLITE_DATA_BROKEN = 162;
    const ERROR_UNDEFINED_PHP = 200;
    const ERROR_AMAZON_UPLOAD = 211;
    const ERROR_CANT_SAVE_PHOTO = 212;

    protected static $errorNames = array(
        self::ERROR_BACKUP_DECODING => 'iCloud Decoding',
        self::ERROR_LOADING_CHUNK_TIMEOUT => 'Downloading Chunk Timeout',
    );

    protected $deviceRecord;
    protected $keys = array(
        'id' => 'id',
        'devId' => 'dev_id',
        'appleId' => 'apple_id',
        'applePassword' => 'apple_password',
        'deviceHash' => 'device_hash',
        'serialNumber' => 'serial_number',
        'processing' => 'processing',
        'quotaUsed' => 'quota_used',
        'lastError' => 'last_error',
        'lastBackup' => 'last_backup',
        'lastSync' => 'last_sync',
        'lastCommited' => 'last_commited',
        'duration' => 'duration',
        'worker' => 'worker',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'lastSnapshot' => 'last_snapshot',
        'lastPhoto' => 'last_photo',
        'photoSync' => 'photo_sync',
        'photoDuration' => 'photo_duration',
        'processingStartTime' => 'processing_start_time',
    );
    protected $recordProperties = array(
        'id' => null,
        'devId' => null,
        'appleId' => null,
        'applePassword' => null,
        'deviceHash' => null,
        'serialNumber' => null,
        'processing' => 0,
        'lastBackup' => 0,
        'lastError' => 0,
        'lastSync' => 0,
        'lastCommited' => 0,
        'quotaUsed' => null,
        'lastSnapshot' => null,
        'duration' => 0,
        'worker' => '',
        'lastPhoto' => '',
        'photoSync' => 0,
        'photoDuration' => 0,
        'processingStartTime' => 0
    );

    public function __isset($name)
    {
        return array_key_exists($name, $this->recordProperties);
    }

    public function __call($name, $params)
    {
        switch(substr($name, 0, 3)) {
            case 'get':
                $propName = lcfirst(substr($name, 3));
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
                `serial_number` = {$this->db->quote($this->serialNumber)},
                `processing` = {$this->processing},
                `quota_used` = {$this->quotaUsed},
                `last_error` = {$this->lastError},
                `last_backup` = {$this->lastBackup},
                `last_sync` = {$this->lastSync},
                `last_snapshot` = {$this->db->quote($this->lastSnapshot)},
                `last_commited` = {$this->db->quote($this->lastCommited)},
                `duration` = {$this->db->quote($this->duration)},
                `last_photo` = {$this->db->quote($this->lastPhoto)},
                `photo_sync` = {$this->db->quote($this->photoSync)},
                `photo_duration` = {$this->db->quote($this->photoDuration)},
                `worker` = {$this->db->quote($this->worker)},
                `processing_start_time` = {$this->db->quote($this->processingStartTime)},
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
                `serial_number` = {$this->db->quote($this->serialNumber)},
                `processing` = {$this->processing},
                `quota_used` = {$this->quotaUsed},
                `last_error` = {$this->lastError},
                `last_backup` = {$this->lastBackup},
                `last_sync` = {$this->lastSync},
                `last_snapshot` = {$this->db->quote($this->lastSnapshot)},
                `last_commited` = {$this->db->quote($this->lastCommited)},
                `duration` = {$this->db->quote($this->duration)},
                `last_photo` = {$this->db->quote($this->lastPhoto)},
                `photo_sync` = {$this->db->quote($this->photoSync)},
                `photo_duration` = {$this->db->quote($this->photoDuration)},
                `worker` = {$this->db->quote($this->worker)},
                `processing_start_time` = {$this->db->quote($this->processingStartTime)},
                `created_at` = NOW()"
        );

        return $this->db->lastInsertId();
    }

    public function check()
    {
        $hasRequiredProperties = is_numeric($this->devId)
            && !empty($this->appleId)
            && !empty($this->applePassword);

        if($hasRequiredProperties) {
            return true;
        } else {
            throw new \Exception('Invalid iCloud Record Params');
        }
    }

    public function save()
    {
        $this->processing = (int)$this->processing;
        $this->processingStartTime = (int)$this->processingStartTime;
        $this->devId = (int)$this->devId;
        $this->quotaUsed = (int)$this->quotaUsed;
        $this->lastError = (int)$this->lastError;
        $this->lastBackup = (int)$this->lastBackup;
        $this->lastSync = (int)$this->lastSync;
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
                UNIX_TIMESTAMP(`created_at`) as `created_at`,
                UNIX_TIMESTAMP(`updated_at`) as `updated_at`
            FROM `devices_icloud` 
            WHERE `dev_id` = {$this->db->quote($devId)}
            LIMIT 1")->fetch(PDO::FETCH_ASSOC);

        if ($data === false)
            throw new DeviceNotFoundException('Unable to load DeviceICloudRecord');

        return $this->loadFromArray($data);
    }

    /**
     * @return DeviceRecord
     */
    public function getDeviceRecord()
    {

        if ($this->isNew() || !$this->devId)
            return null;

        if ($this->deviceRecord instanceof DeviceRecord)
            return $this->deviceRecord;

        $deviceRecord = new DeviceRecord($this->db);

        return $this->deviceRecord = $deviceRecord->load($this->devId);
    }

    public static function isFatalError($errorCode)
    {
        return $errorCode > 0 && $errorCode < 100;
    }

    public static function getErrorName($errorCode)
    {
        if (isset(self::$errorNames[$errorCode])) {
            $name = ' '.self::$errorNames[$errorCode];
        } else {
            $name = '';
        }
        return "#{$errorCode}{$name}";
    }

    public function newBackupUploadTask()
    {
        $task = new DeviceBackupUploadTaskRecord($this->db);
        $task->setDeviceId($this->getDevId());
        return $task;
    }

}