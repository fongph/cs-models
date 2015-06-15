<?php namespace CS\Models\Device;

use CS\Models\AbstractRecord;

class DeviceModulesRecord extends AbstractRecord {

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

    protected $devId = 0;
    protected $calls  = null;
    protected $sms  = null;
    protected $bookmarks  = null;
    protected $browserHistory  = null;
    protected $calendar  = null;
    protected $contacts  = null;
    protected $photos  = null;
    protected $skype  = null;
    protected $whatsapp  = null;
    protected $notes  = null;

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
        'id' => 'id',
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

    public function isFound($moduleName)
    {
        return self::isAllowedModule($moduleName) && $this->$moduleName != 0;
    }

    public function hasError($moduleName)
    {
        return self::isAllowedModule($moduleName) && $this->$moduleName < 0;
    }

    public function setStatus($moduleName, $status)
    {
        if (self::isAllowedModule($moduleName)) {
            $this->$moduleName = $status;
        } else {
            throw new \Exception("Invalid Module Name '{$moduleName}'");
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
            INSERT INTO `devices_modules`
            SET dev_id = {$this->db->quote($this->devId)},
                calls = {$this->quoteOrNull($this->calls)},
                sms = {$this->quoteOrNull($this->sms)},
                bookmarks = {$this->quoteOrNull($this->bookmarks)},
                browser_history = {$this->quoteOrNull($this->browserHistory)},
                calendar = {$this->quoteOrNull($this->calendar)},
                contacts = {$this->quoteOrNull($this->contacts)},
                photos = {$this->quoteOrNull($this->photos)},
                skype = {$this->quoteOrNull($this->skype)},
                whatsapp = {$this->quoteOrNull($this->whatsapp)},
                notes = {$this->quoteOrNull($this->notes)}");

        return $this->db->lastInsertId();
    }

    protected function quoteOrNull($value, $parameter_type = \PDO::PARAM_STR)
    {
        if (is_null($value)) {
            return 'NULL';
        } else return $this->db->quote($value, $parameter_type);
    }


    public function updateRecord()
    {
        return $this->db->exec("
            UPDATE `devices_modules`
            SET dev_id = {$this->db->quote($this->devId)},
                calls = {$this->quoteOrNull($this->calls)},
                sms = {$this->quoteOrNull($this->sms)},
                bookmarks = {$this->quoteOrNull($this->bookmarks)},
                browser_history = {$this->quoteOrNull($this->browserHistory)},
                calendar = {$this->quoteOrNull($this->calendar)},
                contacts = {$this->quoteOrNull($this->contacts)},
                photos = {$this->quoteOrNull($this->photos)},
                skype = {$this->quoteOrNull($this->skype)},
                whatsapp = {$this->quoteOrNull($this->whatsapp)},
                notes = {$this->quoteOrNull($this->notes)}
            WHERE id = {$this->id}");
    }

    public function load($id)
    {
        if (($data = $this->db->query("
                SELECT *
                FROM `devices_modules`
                WHERE `id` = {$this->db->quote($id)} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new \Exception('Unable to load device modules record');
    }

    public function loadDevId($id)
    {
        if (($data = $this->db->query("
                SELECT *
                FROM `devices_modules`
                WHERE `dev_id` = {$this->db->quote($id)} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new \Exception('Unable to load device modules record');
    }
} 