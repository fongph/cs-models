<?php

use CS\Models\Limitation\LimitationRecord,
    CS\Models\Limitation\LimitationNotFoundException;

/**
 * Description of LimitationRecordTest
 *
 * @author root
 */
class LimitationRecordTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \PDO
     */
    static $db;
    static $createdId;
    public $limitation;

    public static function setUpBeforeClass()
    {
        global $db;
        self::$db = $db;
    }

    public function setUp()
    {
        $this->limitation = new LimitationRecord(self::$db);
    }

    public function testCreate()
    {
        $this->limitation->setName('name')
                ->setLifetime(20)
                ->setSms(100);
        
        $this->assertTrue($this->limitation->isNew());
        
        $this->limitation->save();

        $this->assertFalse($this->limitation->isNew());
        $this->assertNotNull($this->limitation->getId());
        $this->assertEquals('name', $this->limitation->getName());
        $this->assertEquals(20, $this->limitation->getLifetime());
        $this->assertFalse($this->limitation->getRecurrence());
        $this->assertEquals(100, $this->limitation->getSms());
        $this->assertNull($this->limitation->getCreatedAt());
        $this->assertNull($this->limitation->getUpdatedAt());

        self::$createdId = $this->limitation->getId();
    }

    public function testLoad()
    {
        $this->limitation->load(self::$createdId);

        $this->assertNotNull($this->limitation->getCreatedAt());
        $this->assertNull($this->limitation->getUpdatedAt());
    }

    public function testUpdate()
    {
        $this->limitation->load(self::$createdId)
                ->setSms(99)
                ->setRecurrence()
                ->save();

        $this->limitation->load(self::$createdId);

        $this->assertNotNull($this->limitation->getUpdatedAt());
        $this->assertTrue($this->limitation->getRecurrence());
        $this->assertEquals(99, $this->limitation->getSms());
    }

    public function testLoadError()
    {
        try {
            $this->limitation->load(-1);
        } catch (LimitationNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

}
