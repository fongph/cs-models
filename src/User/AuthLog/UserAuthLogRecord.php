<?php

namespace CS\Models\User\AuthLog;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\User\UserRecord;

/**
 * Description of UserAuthLogRecord
 *
 * @author root
 */
class UserAuthLogRecord extends AbstractRecord
{

    /**
     *
     * @var UserRecord
     */
    protected $user;
    protected $userId;
    protected $ip;
    protected $country;
    protected $browser;
    protected $browserVersion;
    protected $platform;
    protected $platformVersion;
    protected $mobile = 0;
    protected $tablet = 0;
    protected $userAgent;
    protected $fullInfo;
    protected $keys = array(
        'id' => 'id',
        'userId' => 'user_id',
        'ip' => 'ip',
        'country' => 'country',
        'browser' => 'browser',
        'browserVersion' => 'browser_version',
        'platform' => 'platform',
        'platformVersion' => 'platform_version',
        'mobile' => 'mobile',
        'tablet' => 'tablet',
        'userAgent' => 'user_agent',
        'fullInfo' => 'full_info',
        'createdAt' => 'created_at'
    );

    public function setUserId($id)
    {
        $this->userId = $id;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setIp($value)
    {
        $this->ip = $value;

        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }
    
    public function setCountry($value)
    {
        $this->country = $value;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }
    
    public function setBrowser($value)
    {
        $this->browser = $value;

        return $this;
    }

    public function getBrowser()
    {
        return $this->browser;
    }
    
    public function setBrowserVersion($value)
    {
        $this->browserVersion = $value;

        return $this;
    }

    public function getBrowserVersion()
    {
        return $this->browserVersion;
    }
    
    public function setPlatform($value)
    {
        $this->platform = $value;

        return $this;
    }

    public function getPlatform()
    {
        return $this->platform;
    }
    
    public function setPlatformVersion($value)
    {
        $this->platformVersion = $value;

        return $this;
    }

    public function getPlatformVersion()
    {
        return $this->platformVersion;
    }
    
    public function setMobile($value = true)
    {
        $this->mobile = $this->boolToNum($value);

        return $this;
    }

    public function getMobile()
    {
        return $this->mobile > 0;
    }
    
    public function setTablet($value = true)
    {
        $this->tablet = $this->boolToNum($value);

        return $this;
    }

    public function getTablet()
    {
        return $this->tablet > 0;
    }
    
    public function setUserAgent($value)
    {
        $this->userAgent = $value;

        return $this;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }
    
    public function setFullInfo($value)
    {
        $this->fullInfo = $value;

        return $this;
    }

    public function getFullInfo()
    {
        return $this->fullInfo;
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

    public function setUser(UserRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->userId = $value->getId();

        $this->user = $value;

        return $this;
    }

    private function checkUser()
    {
        if ($this->user instanceof UserRecord && $this->userId != $this->user->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }
    
    private function check()
    {
        $this->checkUser();
    }

    private function insertRecord($userId, $ip, $country, $browser, $browserVersion, $platform, $platformVersion, $mobile, $tablet, $userAgent, $fullInfo)
    {
        $this->db->exec("INSERT INTO `users_auth_log` SET
                                    `user_id` = {$userId},
                                    `ip` = {$ip},
                                    `country` = {$country},
                                    `browser` = {$browser},
                                    `browser_version` = {$browserVersion},
                                    `platform` = {$platform},
                                    `platform_version` = {$platformVersion},
                                    `mobile` = {$mobile},
                                    `tablet` = {$tablet},
                                    `user_agent` = {$userAgent},
                                    `full_info` = {$fullInfo}
                                ");

        return $this->db->lastInsertId();
    }
    
    public function save()
    {
        $this->check();
        
        $userId = $this->escape($this->userId);
        $ip = $this->escape($this->ip);
        $country = $this->escape($this->country);
        $browser = $this->escape($this->browser);
        $browserVersion = $this->escape($this->browserVersion);
        $platform = $this->escape($this->platform);
        $platformVersion = $this->escape($this->platformVersion);
        $mobile = $this->escape($this->mobile);
        $tablet = $this->escape($this->tablet);
        $userAgent = $this->escape($this->userAgent);
        $fullInfo = $this->escape($this->fullInfo);
        
        if (!empty($this->id)) {
            throw new RecordNotUpdatableException("You can`t update this record!");
        } else {
            $this->id = $this->insertRecord($userId, $ip, $country, $browser, $browserVersion, $platform, $platformVersion, $mobile, $tablet, $userAgent, $fullInfo);
        }
        
        return true;
    }
    
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at` FROM `full_info` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new UserAuthLogNotFoundException('Unable to load user auth record');
    }

}
