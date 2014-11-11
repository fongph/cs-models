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
    protected $login;
    protected $password;
    protected $locale = 'en-GB';
    protected $recordsPerPage = 10;
    protected $emailConfirmed = 0;
    protected $locked = 0;
    protected $unlockHash;
    protected $restoreHash;
    protected $emailConfirmHash;
    protected $keys = array(
        'id' => 'id',
        'siteId' => 'site_id',
        'login' => 'login',
        'password' => 'password',
        'locale' => 'locale',
        'recordsPerPage' => 'records_per_page',
        'emailConfirmed' => 'email_confirmed',
        'locked' => 'locked',
        'unlockHash' => 'unlock_hash',
        'restoreHash' => 'restore_hash',
        'emailConfirmHash' => 'email_confirm_hash',
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

    public function setLogin($value)
    {
        $this->login = $value;

        return $this;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setPassword($value)
    {
        $this->password = $value;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setLocale($value)
    {
        $this->locale = $value;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setRecordsPerPage($value)
    {
        $this->recordsPerPage = $value;

        return $this;
    }

    public function getRecordsPerPage()
    {
        return $this->recordsPerPage;
    }

    public function setEmailConfirmed($value = true)
    {
        $this->emailConfirmed = $this->boolToNum($value);

        return $this;
    }

    public function getEmailConfirmed()
    {
        return $this->emailConfirmed;
    }

    public function setLocked($value = true)
    {
        $this->locked = $this->boolToNum($value);

        return $this;
    }

    public function getLocked()
    {
        return $this->locked;
    }

    public function setUnlockHash($value)
    {
        $this->unlockHash = $value;

        return $this;
    }

    public function getUnlockHash()
    {
        return $this->unlockHash;
    }

    public function setRestoreHash($value)
    {
        $this->restoreHash = $value;

        return $this;
    }

    public function getRestoreHash()
    {
        return $this->restoreHash;
    }

    public function setEmailConfirmHash($value)
    {
        $this->emailConfirmHash = $value;

        return $this;
    }

    public function getEmailConfirmHash()
    {
        return $this->emailConfirmHash;
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

    private function updateRecord($siteId, $login, $password, $locale, $recordsPerPage, $emailConfirmed, $locked, $unlockHash, $restoreHash, $emailConfirmHash)
    {
        $rows = $this->db->exec("UPDATE `users` SET
                                        `site_id` = {$siteId},
                                        `login` = {$login},
                                        `password` = {$password},
                                        `locale` = {$locale},
                                        `records_per_page` = {$recordsPerPage},
                                        `email_confirmed` = {$emailConfirmed},
                                        `locked` = {$locked},
                                        `unlock_hash` = {$unlockHash},
                                        `restore_hash` = {$restoreHash},
                                        `email_confirm_hash` = {$emailConfirmHash},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($siteId, $login, $password, $locale, $recordsPerPage, $emailConfirmed, $locked, $unlockHash, $restoreHash, $emailConfirmHash)
    {
        $this->db->exec("INSERT INTO `users` SET 
                            `site_id` = {$siteId},
                            `login` = {$login},
                            `password` = {$password},
                            `locale` = {$locale},
                            `records_per_page` = {$recordsPerPage},
                            `email_confirmed` = {$emailConfirmed},
                            `locked` = {$locked},
                            `unlock_hash` = {$unlockHash},
                            `restore_hash` = {$restoreHash},
                            `email_confirm_hash` = {$emailConfirmHash}
                        ");

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
        $login = $this->escape($this->login);
        $password = $this->escape($this->password);
        $locale = $this->escape($this->locale);
        $recordsPerPage = $this->escape($this->recordsPerPage);
        $emailConfirmed = $this->escape($this->emailConfirmed);
        $locked = $this->escape($this->locked);
        $unlockHash = $this->escape($this->unlockHash);
        $restoreHash = $this->escape($this->restoreHash);
        $emailConfirmHash = $this->escape($this->emailConfirmHash);

        if (!empty($this->id)) {
            return $this->updateRecord($siteId, $login, $password, $locale, $recordsPerPage, $emailConfirmed, $locked, $unlockHash, $restoreHash, $emailConfirmHash);
        } else {
            $this->id = $this->insertRecord($siteId, $login, $password, $locale, $recordsPerPage, $emailConfirmed, $locked, $unlockHash, $restoreHash, $emailConfirmHash);
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
