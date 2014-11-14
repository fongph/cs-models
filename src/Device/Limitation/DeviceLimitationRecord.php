<?php

namespace CS\Models\Device\Limitation;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Device\DeviceRecord;

/**
 * Description of DeviceLimitationRecord
 *
 * @author root
 */
class DeviceLimitationRecord extends AbstractRecord
{

    /**
     *
     * @var DeviceRecord
     */
    protected $device;
    protected $deviceId;
    protected $sms = 0;
    protected $call = 0;
    protected $gps = 0;
    protected $blockNumber = 0;
    protected $blockWords = 0;
    protected $browserHistory = 0;
    protected $browserBookmark = 0;
    protected $contact = 0;
    protected $calendar = 0;
    protected $photos = 0;
    protected $viber = 0;
    protected $whatsapp = 0;
    protected $video = 0;
    protected $skype = 0;
    protected $facebook = 0;
    protected $vk = 0;
    protected $emails = 0;
    protected $applications = 0;
    protected $keylogger = 0;
    protected $keys = array(
        'id' => 'id',
        'name' => 'name',
        'sms' => 'sms',
        'call' => 'call',
        'gps' => 'gps',
        'blockNumber' => 'block_number',
        'blockWords' => 'block_words',
        'browserHistory' => 'browser_history',
        'browserBookmark' => 'browser_bookmark',
        'contact' => 'contact',
        'calendar' => 'calendar',
        'photos' => 'photos',
        'viber' => 'viber',
        'whatsapp' => 'whatsapp',
        'video' => 'video',
        'skype' => 'skype',
        'facebook' => 'facebook',
        'vk' => 'vk',
        'emails' => 'emails',
        'applications' => 'applications',
        'keylogger' => 'keylogger',
        'lifetime' => 'lifetime',
        'recurrence' => 'recurrence',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    const UNLIMITED_VALUE = 65535;

    public function setDeviceId($id)
    {
        $this->deviceId = $id;

        return $this;
    }

    public function getDeviceId()
    {
        return $this->deviceId;
    }

    public function getSms()
    {
        return $this->sms;
    }

    public function setSms($value)
    {
        $this->sms = $value;

        return $this;
    }

    public function getCall()
    {
        return $this->call;
    }

    public function setCall($value)
    {
        $this->call = $value;

        return $this;
    }

    public function getGps()
    {
        return $this->gps;
    }

    public function setGps($value)
    {
        $this->gps = $value;

        return $this;
    }

    public function getBlockNumber()
    {
        return $this->blockNumber;
    }

    public function setBlockNumber($value)
    {
        $this->blockNumber = $value;

        return $this;
    }

    public function getBlockWords()
    {
        return $this->blockWords;
    }

    public function setBlockWords($value)
    {
        $this->blockWords = $value;

        return $this;
    }

    public function getBrowserHistory()
    {
        return $this->browserHistory;
    }

    public function setBrowserHistory($value)
    {
        $this->browserHistory = $value;

        return $this;
    }

    public function getBrowserBookmark()
    {
        return $this->browserBookmark;
    }

    public function setBrowserBookmark($value)
    {
        $this->browserBookmark = $value;

        return $this;
    }

    public function getContact()
    {
        return $this->contact;
    }

    public function setContact($value)
    {
        $this->contact = $value;

        return $this;
    }

    public function getCalendar()
    {
        return $this->calendar;
    }

    public function setCalendar($value)
    {
        $this->calendar = $value;

        return $this;
    }

    public function getPhotos()
    {
        return $this->photos;
    }

    public function setPhotos($value)
    {
        $this->photos = $value;

        return $this;
    }

    public function getViber()
    {
        return $this->viber;
    }

    public function setViber($value)
    {
        $this->viber = $value;

        return $this;
    }

    public function getWhatsapp()
    {
        return $this->whatsapp;
    }

    public function setWhatsapp($value)
    {
        $this->whatsapp = $value;

        return $this;
    }

    public function getVideo()
    {
        return $this->video;
    }

    public function setVideo($value)
    {
        $this->video = $value;

        return $this;
    }

    public function getSkype()
    {
        return $this->skype;
    }

    public function setSkype($value)
    {
        $this->skype = $value;

        return $this;
    }

    public function getFacebook()
    {
        return $this->facebook;
    }

    public function setFacebook($value)
    {
        $this->facebook = $value;

        return $this;
    }

    public function getVk()
    {
        return $this->vk;
    }

    public function setVk($value)
    {
        $this->vk = $value;

        return $this;
    }

    public function getEmails()
    {
        return $this->emails;
    }

    public function setEmails($value)
    {
        $this->emails = $value;

        return $this;
    }

    public function getApplications()
    {
        return $this->applications;
    }

    public function setApplications($value)
    {
        $this->applications = $value;

        return $this;
    }

    public function getKeylogger()
    {
        return $this->keylogger;
    }

    public function setKeylogger($value)
    {
        $this->keylogger = $value;

        return $this;
    }

    public function setDevice(DeviceRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->deviceId = $value->getId();
        $this->device = $value;

        return $this;
    }

    /**
     * 
     * @return UserRecord
     */
    public function getUser()
    {
        if ($this->device instanceof DeviceRecord) {
            return $this->device;
        }

        if (!$this->isNew() && $this->deviceId) {
            $deviceRecord = new DeviceRecord($this->db);
            $deviceRecord->load($this->deviceId);

            $this->setDevice($deviceRecord);
            return $this->device;
        }

        return null;
    }

    private function updateRecord($deviceId, $limitations)
    {
        $rows = $this->db->exec("UPDATE `limitations` SET
                                        `device_id` = {$deviceId},
                                        `sms` = {$limitations['sms']},
                                        `call` = {$limitations['call']},
                                        `gps` = {$limitations['gps']},
                                        `block_number` = {$limitations['blockNumber']},
                                        `block_words` = {$limitations['blockWords']},
                                        `browser_history` = {$limitations['browserHistory']},
                                        `browser_bookmark` = {$limitations['browserBookmark']},
                                        `contact` = {$limitations['contact']},
                                        `calendar` = {$limitations['calendar']},
                                        `photos` = {$limitations['photos']},
                                        `viber` = {$limitations['viber']},
                                        `whatsapp` = {$limitations['whatsapp']},
                                        `video` = {$limitations['video']},
                                        `skype` = {$limitations['skype']},
                                        `facebook` = {$limitations['facebook']},
                                        `vk` = {$limitations['vk']},
                                        `emails` = {$limitations['email']},
                                        `applications` = {$limitations['applications']},
                                        `keylogger` = {$limitations['keylogger']},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($deviceId, $limitations)
    {
        $this->db->exec("INSERT INTO `limitations` SET
                            `device_id` = {$deviceId},
                            `sms` = {$limitations['sms']},
                            `call` = {$limitations['call']},
                            `gps` = {$limitations['gps']},
                            `block_number` = {$limitations['blockNumber']},
                            `block_words` = {$limitations['blockWords']},
                            `browser_history` = {$limitations['browserHistory']},
                            `browser_bookmark` = {$limitations['browserBookmark']},
                            `contact` = {$limitations['contact']},
                            `calendar` = {$limitations['calendar']},
                            `photos` = {$limitations['photos']},
                            `viber` = {$limitations['viber']},
                            `whatsapp` = {$limitations['whatsapp']},
                            `video` = {$limitations['video']},
                            `skype` = {$limitations['skype']},
                            `facebook` = {$limitations['facebook']},
                            `vk` = {$limitations['vk']},
                            `emails` = {$limitations['email']},
                            `applications` = {$limitations['applications']},
                            `keylogger` = {$limitations['keylogger']}
                        ");

        return $this->db->lastInsertId();
    }

    private function escapeLimitations()
    {
        return array(
            'sms' => $this->escape($this->sms),
            'call' => $this->escape($this->call),
            'gps' => $this->escape($this->gps),
            'blockNumber' => $this->escape($this->blockNumber),
            'blockWords' => $this->escape($this->blockWords),
            'browserHistory' => $this->escape($this->browserHistory),
            'browserBookmark' => $this->escape($this->browserBookmark),
            'contact' => $this->escape($this->contact),
            'calendar' => $this->escape($this->calendar),
            'photos' => $this->escape($this->photos),
            'viber' => $this->escape($this->viber),
            'whatsapp' => $this->escape($this->whatsapp),
            'video' => $this->escape($this->video),
            'skype' => $this->escape($this->skype),
            'facebook' => $this->escape($this->facebook),
            'vk' => $this->escape($this->vk),
            'emails' => $this->escape($this->emails),
            'applications' => $this->escape($this->applications),
            'keylogger' => $this->escape($this->keylogger)
        );
    }

    private function checkDevice()
    {
        if ($this->device instanceof DeviceRecord && $this->deviceId != $this->device->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function check()
    {
        $this->checkDevice();
    }

    public function save()
    {
        $this->check();

        $deviceId = $this->escape($this->deviceId);
        $limitations = $this->escapeLimitations();

        if (!empty($this->id)) {
            return $this->updateRecord($deviceId, $limitations);
        } else {
            $this->id = $this->insertRecord($deviceId, $limitations);
        }
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT * FROM `devices_limitations` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) == false) {
            throw new DeviceLimitationNotFoundException('Unable to load limitation record');
        }

        return $this->loadFromArray($data);
    }

    public function loadByDeviceId($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT * FROM `devices_limitations` WHERE `device_id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) == false) {
            throw new DeviceLimitationNotFoundException('Unable to load limitation record');
        }

        return $this->loadFromArray($data);
    }

}
