<?php

namespace CS\Models\Device;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\User\UserRecord,
    CS\Models\RecordNotCreatedException;

/**
 * Description of OrderRecord
 *
 * @author root
 */
class DeviceRecord extends AbstractRecord
{
    /**
     * @var UserRecord
     */
    protected $user;
    protected $iCloudDevice;
    protected $userId;
    protected $name;
    protected $uniqueId;
    protected $os = self::NETWORK_UNKNOWN;
    protected $osVersion;
    protected $model;
    protected $appVersion = 0;
    protected $time = 0;
    protected $lastVisit = 0;
    protected $token;
    protected $network = self::NETWORK_UNKNOWN;
    protected $rooted = 0;
    protected $rootAccess = 0;
    protected $power = 0;
    protected $deleted = 0;
    protected $keys = array(
        'id' => 'id',
        'userId' => 'user_id',
        'name' => 'name',
        'uniqueId' => 'unique_id',
        'os' => 'os',
        'osVersion' => 'os_version',
        'model' => 'model',
        'appVersion' => 'app_version',
        'time' => 'time',
        'lastVisit' => 'last_visit',
        'token' => 'token',
        'network' => 'network',
        'rooted' => 'rooted',
        'rootAccess' => 'rootAccess',
        'power' => 'power',
        'deleted' => 'deleted',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    private static $allowedOS = array(self::OS_UNKNOWN, self::OS_ANDROID, self:: OS_BLACKBERRY, self::OS_IOS, self::OS_ICLOUD);
    private static $allowedNetworks = array(self::NETWORK_UNKNOWN, self::NETWORK_MOBILE, self::NETWORK_WIFI);


    const OS_UNKNOWN = 'unknown';
    const OS_ANDROID = 'android';
    const OS_BLACKBERRY = 'blackberry';
    const OS_IOS = 'ios';
    const OS_ICLOUD = 'icloud';
    
    const NETWORK_UNKNOWN = 'unknown';
    const NETWORK_MOBILE = 'mobile';
    const NETWORK_WIFI = 'wifi';    
    

    public function setUserId($id)
    {
        $this->userId = $id;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setOS($value)
    {
        $this->os = $value;

        return $this;
    }

    public function getOS()
    {
        return $this->os;
    }

    public function setOSVersion($value)
    {
        $this->osVersion = $value;

        return $this;
    }

    public function getOSVersion()
    {
        return $this->osVersion;
    }

    public function setModel($value)
    {
        $this->model = $value;

        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setAppVersion($value)
    {
        $this->appVersion = $value;

        return $this;
    }

    public function getAppVersion()
    {
        return $this->appVersion;
    }

    public function setTime($value)
    {
        $this->time = $value;

        return $this;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setLastVisit($value)
    {
        $this->lastVisit = $value;

        return $this;
    }

    public function getLastVisit()
    {
        return $this->lastVisit;
    }

    public function setToken($value)
    {
        $this->token = $value;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }
    
    public function setNetwork($value)
    {
        $this->network = $value;

        return $this;
    }

    public function getNetwork()
    {
        return $this->network;
    }

    public function setRooted($value)
    {
        $this->rooted = $value;

        return $this;
    }

    public function getRooted()
    {
        return $this->rooted;
    }
    
    public function setRootAccess($value)
    {
        $this->rootAccess = $value;

        return $this;
    }

    public function getRootAccess()
    {
        return $this->rootAccess;
    }

    public function setPower($value)
    {
        $this->power = $value;

        return $this;
    }

    public function getPower()
    {
        return $this->power;
    }

    public function setDeleted($value = true)
    {
        $this->deleted = $this->boolToNum($value);

        return $this;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setUniqueId($value)
    {
        $this->uniqueId = $value;

        return $this;
    }

    public function getUniqueId()
    {
        return $this->uniqueId;
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
    
    /** @return DeviceICloudRecord */
    public function getICloudDevice()
    {
        if(is_null($this->iCloudDevice)){
            $iCloudDevice = new DeviceICloudRecord($this->db);
            $this->iCloudDevice = $iCloudDevice->loadByDevId($this->getId());
        }
        return $this->iCloudDevice;
    }

    private function updateRecord($userId, $name, $uniqueId, $os, $osVersion, $model, $appVersion, $time, $lastVisit, $token, $network, $rooted, $rootAccess, $power, $deleted)
    {
        $rows = $this->db->exec("UPDATE `devices` SET
                                        `user_id` = {$userId},
                                        `name` = {$name},
                                        `unique_id` = {$uniqueId},
                                        `os` = {$os},
                                        `os_version` = {$osVersion},
                                        `model` = {$model},
                                        `app_version` = {$appVersion},
                                        `time` = {$time},
                                        `last_visit` = {$lastVisit},
                                        `token` = {$token},
                                        `network` = {$network},
                                        `rooted` = {$rooted},
                                        `root_access` = {$rootAccess},
                                        `power` = {$power},
                                        `deleted` = {$deleted},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($userId, $name, $uniqueId, $os, $osVersion, $model, $appVersion, $time, $lastVisit, $token, $network, $rooted, $rootAccess, $power, $deleted)
    {
        $this->db->exec("INSERT INTO `devices` SET
                                    `user_id` = {$userId},
                                    `name` = {$name},
                                    `unique_id` = {$uniqueId},
                                    `os` = {$os},
                                    `os_version` = {$osVersion},
                                    `model` = {$model},
                                    `app_version` = {$appVersion},
                                    `time` = {$time},
                                    `last_visit` = {$lastVisit},
                                    `token` = {$token},
                                    `network` = {$network},
                                    `rooted` = {$rooted},
                                    `root_access` = {$rootAccess},
                                    `power` = {$power},
                                    `deleted` = {$deleted}
                                ");

        return $this->db->lastInsertId();
    }

    private function checkUser()
    {
        if (!in_array($this->network, self::getAllowedNetworks())) {
            throw new InvalidNetworkException("Invalid payment method value");
        }
        
        if (!in_array($this->os, self::getAllowedOS())) {
            throw new InvalidOSException("Invalid payment method value");
        }
        
        if ($this->user instanceof UserRecord && $this->userId != $this->user->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function check()
    {
        $this->checkUser();
    }

    public function save()
    {
        $this->check();

        $userId = $this->escape($this->userId);
        $name = $this->escape($this->name);
        $uniqueId = $this->escape($this->uniqueId);
        $os = $this->escape($this->os);
        $osVersion = $this->escape($this->osVersion);
        $model = $this->escape($this->model);
        $appVersion = $this->escape($this->appVersion);
        $time = $this->escape($this->time);
        $lastVisit = $this->escape($this->lastVisit);
        $token = $this->escape($this->token);
        $network = $this->escape($this->network);
        $rooted = $this->escape($this->rooted);
        $rootAccess = $this->escape($this->rootAccess);
        $power = $this->escape($this->power);
        $deleted = $this->escape($this->deleted);

        if (!empty($this->id)) {
            return $this->updateRecord($userId, $name, $uniqueId, $os, $osVersion, $model, $appVersion, $time, $lastVisit, $token, $network, $rooted, $rootAccess, $power, $deleted);
        }

        $this->id = $this->insertRecord($userId, $name, $uniqueId, $os, $osVersion, $model, $appVersion, $time, $lastVisit, $token, $network, $rooted, $rootAccess, $power, $deleted);

        return true;
    }

    /**
     * 
     * @param type $id
     * @return DeviceRecord
     * @throws OrderNotFoundException
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `devices` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new DeviceNotFoundException('Unable to load order record');
    }
    
    public static function getAllowedNetworks()
    {
        return self::$allowedNetworks;
    }

    public static function getAllowedOS()
    {
        return self::$allowedOS;
    }

}
