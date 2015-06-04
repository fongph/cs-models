<?php namespace CS\Models\Device;

use CS\Models\AbstractRecord;

class DeviceModules extends AbstractRecord {

    const MODULE_CALLS = 'calls';
    const MODULE_SMS = 'sms';
    const MODULE_BOOKMARKS = 'bookmarks';
    const MODULE_BROWSER_HISTORY = 'browserHistory';
    const MODULE_CALENDAR = 'calendar';
    const MODULE_CONTACTS = 'contacts';
    const MODULE_PHOTOS = 'photos';
    const MODULE_SKYPE = 'skype';
    const MODULE_WHATSAPP = 'whatsapp';
    const MODULE_NOTES = 'notes';

    protected $devId;
    protected $calls;
    protected $sms;
    protected $bookmarks;
    protected $browserHistory;
    protected $calendar;
    protected $contacts;
    protected $photos;
    protected $skype;
    protected $whatsapp;
    protected $notes;

    public static function isAllowedModule($moduleName)
    {
        return in_array($moduleName, array(
            self::MODULE_CALLS,
            self::MODULE_SMS,
            self::MODULE_BOOKMARKS,
            self::MODULE_BROWSER_HISTORY,
            self::MODULE_CALENDAR,
            self::MODULE_CONTACTS,
            self::MODULE_PHOTOS,
            self::MODULE_SKYPE,
            self::MODULE_WHATSAPP,
            self::MODULE_NOTES,
        ));
    }

    protected $keys = array(
        'devId' => 'dev_id',
        'calls' => 'calls',
        'sms' => 'sms',
        'bookmarks' => 'bookmarks',
        'browserHistory' => 'browser_history',
        'calendar' => 'calendar',
        'contacts' => 'contacts',
        'photos' => 'photos',
        'skype' => 'skype',
        'whatsapp' => 'whatsapp',
        'notes' => 'notes',
    );

    public function setDevId($devId)
    {
        $this->devId = (int) $devId;
        return $this;
    }

    public function getDevId()
    {
        return $this->devId;
    }

    public function isActive($moduleName)
    {
        if (self::isAllowedModule($moduleName)) {
            return $this->$moduleName;
        }
        return false;
    }

    public function setStatus($moduleName, $status)
    {
        if (self::isAllowedModule($moduleName)) {
            $this->$moduleName = (int) (bool) $status;
        }
        return $this;
    }

    public function save()
    {
        $this->check();

        if ($this->isNew()) {
            $this->id = $this->insertRecord();
            return true;
        }

        return $this->id = $this->updateRecord();
    }

    protected function check()
    {
        if (!($this->devId = (int)$this->devId))
            throw new \Exception('Invalid Device ID!');
    }

    public function insertRecord()
    {
        $this->db->exec("
            INSERT INTO `dev_modules`
            SET dev_id = {$this->db->quote($this->devId)},
                calls = {$this->db->quote($this->calls)},
                sms = {$this->db->quote($this->sms)},
                bookmarks = {$this->db->quote($this->bookmarks)},
                browser_history = {$this->db->quote($this->browserHistory)},
                calendar = {$this->db->quote($this->calendar)},
                contacts = {$this->db->quote($this->contacts)},
                photos = {$this->db->quote($this->photos)},
                skype = {$this->db->quote($this->skype)},
                whatsapp = {$this->db->quote($this->whatsapp)},
                notes = {$this->db->quote($this->notes)}");

        return $this->db->lastInsertId();
    }

    public function updateRecord()
    {
        return $this->db->exec("
            UPDATE `dev_modules`
            SET dev_id = {$this->db->quote($this->devId)},
                calls = {$this->calls},
                sms = {$this->sms},
                bookmarks = {$this->bookmarks},
                browser_history = {$this->browserHistory},
                calendar = {$this->calendar},
                contacts = {$this->contacts},
                photos = {$this->photos},
                skype = {$this->skype},
                whatsapp = {$this->whatsapp},
                notes = {$this->notes}
            WHERE id = {$this->id}");
    }

    public function load($id)
    {
        if (($data = $this->db->query("
                SELECT *
                FROM `dev_modules`
                WHERE `id` = {$this->db->quote($id)} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new DeviceNotFoundException('Unable to load order record');
    }

    public function loadByDevId($id)
    {
        if (($data = $this->db->query("
                SELECT *
                FROM `dev_modules`
                WHERE `dev_id` = {$this->db->quote($id)} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new DeviceNotFoundException('Unable to load order record');
    }
} 