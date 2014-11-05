<?php

namespace CS\Models\Site;

use CS\Models\AbstractRecord;

/**
 * Description of SiteRecord
 *
 * @author root
 */
class SiteRecord extends AbstractRecord
{

    protected $name;
    protected $keys = array(
        'id' => 'id',
        'name' => 'name',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    private function updateRecord($name)
    {
        $rows = $this->db->exec("UPDATE `sites` SET
                                        `name` = {$name},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($name)
    {
        $this->db->exec("INSERT INTO `sites` SET `name` = {$name}");

        return $this->db->lastInsertId();
    }
    
    public function save()
    {
        $name = $this->escape($this->name);

        if (!empty($this->id)) {
            return $this->updateRecord($name);
        } else {
            $this->id = $this->insertRecord($name);
        }
    }

    /**
     * 
     * @param type $id
     * @return SiteRecord
     * @throws SiteNotFoundException
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `sites` WHERE `id` = {$escapedId} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new SiteNotFoundException('Unable to load site record');
    }

}
