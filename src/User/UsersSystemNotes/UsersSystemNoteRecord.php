<?php

namespace CS\Models\User\UsersSystemNotes;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\RecordNotUpdatableException;

/**
 * Description of UsersSystemNoteRecord
 *
 * @author root
 */
class UsersSystemNoteRecord extends AbstractRecord
{

    /**
     *
     * @var UserRecord
     */
    protected $user;
    protected $userId;
    protected $adminId;
    protected $type = self::TYPE_SYSTEM;
    protected $joinId;
    protected $content;
    protected $keys = array(
        'id' => 'id',
        'userId' => 'user_id',
        'adminId' => 'admin_id',
        'type' => 'type',
        'joinId' => 'join_id',
        'content' => 'content'
    );

    /**
     * List of allowed types
     * 
     * @var array
     */
    protected static $allowedTypes = array(self::TYPE_SYSTEM, self::TYPE_AUTH, self::TYPE_APP);
    
    const TYPE_SYSTEM = 'sys';
    const TYPE_AUTH = 'auth';
    const TYPE_APP = 'app';

    public function setUserId($id)
    {
        $this->userId = $id;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setAdminId($value)
    {
        $this->adminId = $value;

        return $this;
    }

    public function getAdminId()
    {
        return $this->adminId;
    }
    
    public function setType($value)
    {
        $this->type = $value;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function setJoinId($value)
    {
        $this->joinId = $value;

        return $this;
    }

    public function getJoinId()
    {
        return $this->joinId;
    }
    
    public function setContent($value)
    {
        $this->content = $value;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }
    
    private function check()
    {
        if (!in_array($this->type, self::getAllowedTypes())) {
            throw new InvalidTypeException("Invalid status value");
        }
    }

    private function insertRecord($userId, $adminId, $type, $joinId, $content)
    {
        $this->db->exec("INSERT INTO `users_system_notes` SET
                                    `user_id` = {$userId},
                                    `admin_id` = {$adminId},
                                    `type` = {$type},
                                    `join_id` = {$joinId},
                                    `content` = {$content}
                                ");

        return $this->db->lastInsertId();
    }
    
    public function save()
    {
        $this->check();
        
        $userId = $this->escape($this->userId, 'NULL');
        $adminId = $this->escape($this->adminId, 'NULL');
        $type = $this->escape($this->type);
        $joinId = $this->escape($this->joinId, 'NULL');
        $content = $this->escape($this->content);
        
        if (!empty($this->id)) {
            throw new RecordNotUpdatableException("You can`t update this record!");
        } else {
            $this->id = $this->insertRecord($userId, $adminId, $type, $joinId, $content);
        }
        
        return true;
    }
    
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT * FROM `users_system_notes` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new UsersSystemNoteNotFoundException('Unable to load user auth record');
    }

    public static function getAllowedTypes()
    {
        return self::$allowedTypes;
    }
}
