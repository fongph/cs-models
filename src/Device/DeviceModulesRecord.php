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
    protected static $validModules = array(
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
    );

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
    protected $keys = array(
        'id' => 'id',
        'devId' => 'dev_id',
        self::MODULE_CALLS => 'calls',
        self::MODULE_SMS => 'sms',
        self::MODULE_BOOKMARKS => 'bookmarks',
        self::MODULE_BROWSER_HISTORY => 'browser_history',
        self::MODULE_CALENDAR => 'calendar',
        self::MODULE_CONTACTS => 'contacts',
        self::MODULE_PHOTOS => 'photos',
        self::MODULE_SKYPE => 'skype',
        self::MODULE_WHATSAPP => 'whatsapp',
        self::MODULE_NOTES => 'notes',
    );
    protected $prevValues = array();

    public function setDevId($devId)
    {
        $this->devId = (int) $devId;
        return $this;
    }

    public function getDevId()
    {
        return $this->devId;
    }
    
    public static function getValidModules()
    {
        return self::$validModules;
    }
    
    public function isModuleChecked($moduleName)
    {
        return in_array($moduleName, self::$validModules) && !is_null($this->$moduleName);
    }

    public function isModuleFound($moduleName)
    {
        return in_array($moduleName, self::$validModules) && $this->$moduleName > 0;
    }

    public function hasModuleErrorsCode($code) {
        if(empty($code)) return false;
        foreach (self::$validModules as $moduleName) {
            if ($this->$moduleName < 0 && -$this->$moduleName != $code)
                return true;
        }
        
        return false;
    }
    
    public function getModuleParams($moduleName)
    {
        if(in_array($moduleName, self::$validModules)) {
            return $this->$moduleName;
        }
        
        return null;
    }
    
    public function hasModuleError($moduleName)
    {
        return in_array($moduleName, self::$validModules) && $this->$moduleName < 0;
    }
    
    public function hasErrors()
    {
        foreach (self::$validModules as $moduleName) {
            if ($this->$moduleName < 0)
                return true;
        }
        return false;
    }

    public function getModuleErrorCode($moduleName)
    {
        if (in_array($moduleName, self::$validModules)) {
            if ($this->$moduleName < 0) 
                return -$this->$moduleName;
        }
        
        return false;
    }
    
    public function setModuleStatus($moduleName, $status)
    {
        if (in_array($moduleName, self::$validModules)) {
            $status = (int) $status;
            if ($this->$moduleName !== $status) {
                $this->prevValues[$moduleName] = $this->$moduleName;
                $this->$moduleName = $status;
            }
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

    public function loadByDevId($id)
    {
        if (($data = $this->db->query("
                SELECT *
                FROM `devices_modules`
                WHERE `dev_id` = {$this->db->quote($id)} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new \Exception("Unable to load device modules record dev_id #$id");
    }

    public function loadFromArray(array $data)
    {
        foreach ($this->keys as $classPropName => $dbFieldName) {
            if (isset($data[$dbFieldName])) {
                if (in_array($classPropName, self::$validModules)) {
                    $this->$classPropName = (int) $data[$dbFieldName];
                } else {
                    $this->$classPropName = $data[$dbFieldName]; 
                }
            }
        }
        return $this;
    }

    public function getNewModules()
    {
        $modules = array();
        foreach ($this->prevValues as $attr => $prevValue) {
            if ($this->$attr > 0 && $prevValue !== $this->$attr) {
                $modules[] = $attr;
            }
        }
        return $modules;
    }

    public function getNewModuleErrors()
    {
        $errors = array();
        foreach ($this->prevValues as $attr => $prevValue) {
            if ($this->$attr < 0 && $prevValue !== $this->$attr) {
                $errors[$attr] = $this->getModuleErrorCode($attr);
            }
        }
        return $errors;
    }

    public function getNewFixedModules()
    {
        $fixes = array();
        foreach ($this->prevValues as $attr => $prevValue) {
            if ($prevValue < 0 && $this->$attr > 0) {
                $fixes[] = $attr;
            }
        }
        return $fixes;
    }
} 