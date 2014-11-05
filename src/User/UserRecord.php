<?php

namespace CS\Models\User;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Site\SiteRecord,
    CS\Models\RecordNotCreatedException,
    CS\Models\RecordDifferencesException;

/**
 * Description of UserRecord
 *
 * @author root
 */
class UserRecord extends AbstractRecord
{

    /**
     *
     * @var SiteRecord
     */
    protected $site;
    protected $siteId;
    protected $keys = array(
        'id' => 'id',
        'siteId' => 'site_id',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    public function setSiteId($id)
    {
        $this->siteId = $id;

        return $this;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function setSite(SiteRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->siteId = $value->getId();

        $this->site = $value;

        return $this;
    }

    /**
     * 
     * @return SiteRecord
     */
    public function getSite()
    {
        if ($this->site instanceof SiteRecord) {
            return $this->site;
        }

        if (!$this->isNew() && $this->siteId) {
            $siteRecord = new SiteRecord($this->db);
            $siteRecord->load($this->siteId);

            $this->setSite($siteRecord);
            return $this->site;
        }

        return null;
    }

    private function updateRecord($siteId)
    {
        $rows = $this->db->exec("UPDATE `users` SET
                                        `site_id` = {$siteId},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($siteId)
    {
        $this->db->exec("INSERT INTO `users` SET `site_id` = {$siteId}");

        return $this->db->lastInsertId();
    }

    private function checkSite()
    {
        if ($this->site instanceof SiteRecord && $this->siteId != $this->site->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function check()
    {
        $this->checkSite();
    }

    public function save()
    {
        $this->check();
        
        $siteId = $this->escape($this->siteId);

        if (!empty($this->id)) {
            return $this->updateRecord($siteId);
        } else {
            $this->id = $this->insertRecord($siteId);
        }
    }

    /**
     * 
     * @param type $id
     * @return UserRecord
     * @throws UserNotFoundException
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `users` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new UserNotFoundException('Unable to load user record');
    }

}
