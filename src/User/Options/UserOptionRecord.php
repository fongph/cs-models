<?php

namespace CS\Models\User\Options;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\User\UserRecord;

/**
 * Description of UserOptionRecord
 *
 * @author root
 */
class UserOptionRecord extends AbstractRecord
{

    /**
     *
     * @var UserRecord
     */
    protected $user;
    protected $userId;
    protected $option;
    protected $value;
    protected $keys = array(
        'id' => 'id',
        'userId' => 'user_id',
        'option' => 'option',
        'value' => 'value',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
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
    
    public function setOption($value)
    {
        $this->option = $value;

        return $this;
    }

    public function getOption()
    {
        return $this->option;
    }
    
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
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

    private function updateRecord($userId, $option, $value)
    {
        $rows = $this->db->exec("UPDATE `users_options` SET
                                        `user_id` = {$userId},
                                        `option` = {$option},
                                        `value` = {$value},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }
    
    private function insertRecord($userId, $option, $value)
    {
        $this->db->exec("INSERT INTO `users_options` SET
                                    `user_id` = {$userId},
                                    `option` = {$option},
                                    `value` = {$value}
                                ");

        return $this->db->lastInsertId();
    }
    
    public function save()
    {
        $this->check();
        
        $userId = $this->escape($this->userId);
        $option = $this->escape($this->option);
        $value = $this->escape($this->value);
        
        if (!empty($this->id)) {
            return $this->updateRecord($userId, $option, $value);
        } else {
            $this->id = $this->insertRecord($userId, $option, $value);
        }
        
        return true;
    }
    
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `users_options` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new OrderNotFoundException('Unable to load user option record');
    }

}
