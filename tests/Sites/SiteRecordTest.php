<?php

use CS\Models\Site\SiteRecord,
    CS\Models\Site\SiteNotFoundException;

class SiteRecordTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \PDO
     */
    static $db;
    static $createdId;

    /**
     *
     * @var SiteRecord 
     */
    private $site;

    public static function setUpBeforeClass()
    {
        global $db;
        self::$db = $db;
    }

    public function setUp()
    {
        $this->site = new SiteRecord(self::$db);
    }

    public function testCreate()
    {
        $this->site->setName('site.com');

        $this->assertTrue($this->site->isNew());

        $this->site->save();

        $this->assertFalse($this->site->isNew());
        $this->assertSame('site.com', $this->site->getName());
        $this->assertNull($this->site->getCreatedAt());
        $this->assertNull($this->site->getUpdatedAt());

        self::$createdId = $this->site->getId();
    }

    public function testLoad()
    {
        $this->site->load(self::$createdId);

        $this->assertNotNull($this->site->getCreatedAt());
        $this->assertNull($this->site->getUpdatedAt());
    }

    public function testUpdate()
    {
        $this->site->load(self::$createdId)
                ->setName('site.net')
                ->save();

        $this->site->load(self::$createdId);

        $this->assertNotNull($this->site->getUpdatedAt());
        $this->assertSame('site.net', $this->site->getName());
    }

    public function testLoadError()
    {
        try {
            $this->site->load(-1);
        } catch (SiteNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

}
