<?php

namespace CS\Models;

/**
 * Description of Limitation
 *
 * @author root
 */
class Limitation
{

    const SMS = 1;
    const CALL = 2;
    const GPS = 4;
    const BLOCK_NUMBER = 8;
    const BLOCK_WORDS = 16;
    const BROWSER_HISTORY = 32;
    const BROWSER_BOOKMARK = 64;
    const CONTACT = 128;
    const CALENDAR = 256;
    const PHOTOS = 512;
    const VIBER = 1024;
    const WHATSAPP = 2048;
    const VIDEO = 4096;
    const SKYPE = 8192;
    const FACEBOOK = 16384;
    const VK = 32768;
    const EMAILS = 65536;
    const APPLICATIONS = 131072;
    const KEYLOGGER = 262144;
    const OLD_DATA = 524288;
    const SMS_COMMANDS = 1048576;
    const INSTAGRAM = 2097152;
    const KIK = 4194304;
    const NOTES = 8388608;
    const SNAPCHAT = 16777216;
    const SMS_DAILY_LIMIT = 33554432;
    const APPLICATIONS_BLOCKING = 67108864;
    const APPLICATIONS_LIMITING = 134217728;
    const WEBSITES_BLOCKING = 268435456;
    const SIM_CHANGE_ALERT = 536870912;
    const REBOOT_DEVICE = 1073741824;
    const REBOOT_APPLICATION = 2147483648;
    const LOCK_DEVICE = 4294967296;
    const ICLOUD = 8589934592;
    
    private static $allowedOptions = array(
        self::SMS,
        self::CALL,
        self::GPS,
        self::BLOCK_NUMBER,
        self::BLOCK_WORDS,
        self::BROWSER_HISTORY,
        self::BROWSER_BOOKMARK,
        self::CONTACT,
        self::CALENDAR,
        self::PHOTOS,
        self::VIBER,
        self::WHATSAPP,
        self::VIDEO,
        self::SKYPE,
        self::FACEBOOK,
        self::VK,
        self::EMAILS,
        self::APPLICATIONS,
        self::KEYLOGGER,
        self::OLD_DATA,
        self::SMS_COMMANDS,
        self::INSTAGRAM,
        self::KIK,
        self::NOTES,
        self::SNAPCHAT,
        self::SMS_DAILY_LIMIT,
        self::APPLICATIONS_BLOCKING,
        self::APPLICATIONS_LIMITING,
        self::WEBSITES_BLOCKING,
        self::SIM_CHANGE_ALERT,
        self::REBOOT_DEVICE,
        self::REBOOT_APPLICATION,
        self::LOCK_DEVICE,
        self::ICLOUD
    );
    
    protected $value = 0;
    protected $sms = 0;
    protected $call = 0;

    public function __construct($sms = 0, $call = 0, $value = 0)
    {
        $this->sms = $sms;
        $this->call = $call;
        $this->value = $value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        
        return $this;
    }

    public function setOption($option, $value)
    {
        if (!in_array($option, self::$allowedOptions)) {
            throw new Limitation\InvalidOptionException("Invalid option!");
        }

        if ($value) {
            $this->value |= $option;
        } else {
            $this->value &= ~$option;
        }

        return $this;
    }

    public function hasOption($option)
    {
        if (!in_array($option, self::$allowedOptions)) {
            throw new Limitation\InvalidOptionException("Invalid option!");
        }

        return ($this->value & $option) == $option;
    }

    public static function hasValueOption($value, $option)
    {
        if (!in_array($option, self::$allowedOptions)) {
            throw new Limitation\InvalidOptionException("Invalid option!");
        }

        return ($value & $option) == $option;
    }

    public function setSms($value)
    {
        $this->sms = $value;
        
        return $this;
    }

    public function setCall($value)
    {
        $this->call = $value;
        
        return $this;
    }

    public function getSms()
    {
        return $this->sms;
    }

    public function getCall()
    {
        return $this->call;
    }

    public function merge(Limitation $limitation, $resetCount = false)
    {
        if ($resetCount) {
            $this->sms = max($this->sms, $limitation->getSms());
            $this->call = max($this->call, $limitation->getCall());
        }

        $this->value |= $limitation->getValue();

        return $this;
    }

    public function getValue()
    {
        $this->setOption(self::SMS, $this->sms > 0)
                ->setOption(self::CALL, $this->call > 0);

        return $this->value;
    }

}
