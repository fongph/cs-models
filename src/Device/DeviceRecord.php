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
     *
     * @var UserRecord
     */
    protected $user;
    protected $userId;
    protected $name;
    protected $uniqueId;
    protected $deleted = 0;
    protected $keys = array(
        'id' => 'id',
        'userId' => 'user_id',
        'name' => 'name',
        'uniqueId' => 'unique_id',
        'deleted' => 'deleted',
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

    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function setDeleted($value)
    {
        $this->deleted = $value;

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

    private function updateRecord($userId, $name, $uniqueId, $deleted)
    {
        $rows = $this->db->exec("UPDATE `devices` SET
                                        `user_id` = {$userId},
                                        `name` = {$name},
                                        `unique_id` = {$uniqueId},
                                        `deleted` = {$deleted}
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($userId, $name, $uniqueId, $deleted)
    {
        $this->db->exec("INSERT INTO `devices` SET
                                    `user_id` = {$userId},
                                    `name` = {$name},
                                    `unique_id` = {$uniqueId},
                                    `deleted` = {$deleted}
                                ");

        return $this->db->lastInsertId();
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

    public function save()
    {
        $this->check();

        $userId = $this->escape($this->userId);
        $name = $this->escape($this->name);
        $uniqueId = $this->escape($this->uniqueId);
        $deleted = $this->escape($this->deleted);

        if (!empty($this->id)) {
            return $this->updateRecord($userId, $name, $uniqueId, $deleted);
        }

        $this->id = $this->insertRecord($userId, $name, $uniqueId, $deleted);

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

}
