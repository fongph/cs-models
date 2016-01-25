<?php

namespace CS\Models\Device;

use PDO,
    CS\Models\AbstractRecord;

/**
 * Description of DeviceBackupUploadTaskRecord
 *
 * @author root
 */
class DeviceBackupUploadTaskRecord extends AbstractRecord
{
    protected $deviceId;
    protected $priority = 0;
    protected $comments;
    protected $owner;
    protected $status = self::STATUS_OPEN;
    protected $keys = array(
        'id' => 'id',
        'deviceId' => 'device_id',
        'priority' => 'priority',
        'comments' => 'comments',
        'owner' => 'owner',
        'status' => 'status',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    private static $allowedStatuses = array(self::STATUS_OPEN, self::STATUS_ASSIGNED, self::STATUS_FAILED, self::STATUS_COMPLETED);

    const STATUS_OPEN = 'open';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETED = 'completed';

    public function setDeviceId($value)
    {
        $this->deviceId = $value;

        return $this;
    }

    public function getDeviceId()
    {
        return $this->deviceId;
    }

    public function setPriority($value)
    {
        $this->priority = $value;

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setComments($value)
    {
        $this->comments = $value;

        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setOwner($value)
    {
        $this->owner = $value;

        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setStatus($value)
    {
        $this->status = $value;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    private function updateRecord($deviceId, $priority, $comments, $owner, $status)
    {
        $rows = $this->db->exec("UPDATE `device_icloud_upload_task` SET
                                        `device_id` = {$deviceId},
                                        `priority` = {$priority},
                                        `comments` = {$comments},
                                        `owner` = {$owner},
                                        `status` = {$status},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($deviceId, $priority, $comments, $owner, $status)
    {
        $this->db->exec("INSERT INTO `device_icloud_upload_task` SET
                                    `device_id` = {$deviceId},
                                    `priority` = {$priority},
                                    `comments` = {$comments},
                                    `owner` = {$owner},
                                    `status` = {$status}");

        return $this->db->lastInsertId();
    }

    private function check()
    {
        return;
    }

    public function save()
    {
        $this->check();

        $deviceId = $this->escape($this->deviceId);
        $priority = $this->escape($this->priority);
        $comments = $this->escape($this->comments);
        $owner = $this->escape($this->owner);
        $status = $this->escape($this->status);

        if (!empty($this->id)) {
            return $this->updateRecord($deviceId, $priority, $comments, $owner, $status);
        }

        $this->id = $this->insertRecord($deviceId, $priority, $comments, $owner, $status);

        return true;
    }

    /**
     * 
     * @param type $id
     * @return DeviceBackupUploadTaskRecord
     * @throws Exception
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `device_icloud_upload_task` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new \Exception('Unable to load record');
    }

    public static function getAllowedStatuses()
    {
        return self::$allowedStatuses;
    }

}
