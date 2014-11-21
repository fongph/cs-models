<?php

namespace CS\Models;

/**
 * Description of RecordsIterator
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

    static $allowedOptions = array(
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
        self::OLD_DATA
    );
    
    protected $value = 0;

    public function __construct($value = 0)
    {
        $this->value = $value;
    }

    public function setValue($value)
    {
        $this->value = $value;
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

    public function merge($value)
    {
        $this->value &= $value;
    }

    public function getValue()
    {
        return $this->value;
    }

}
