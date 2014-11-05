<?php

use CS\Models\Site\SiteRecord,
    CS\Models\Limitation\LimitationRecord,
    CS\Models\User\UserRecord,
    CS\Models\User\UserNotFoundException,
    CS\Models\Product\InvalidTypeException,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException;

/**
 * Description of ProductRecordTest
 *
 * @author root
 */
class UserRecordTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \PDO
     */
    static $db;
    static $createdId;
    static $site;
    private $user;

    public static function setUpBeforeClass()
    {
        global $db;
        self::$db = $db;

        self::$site = new SiteRecord(self::$db);
        self::$site->setName('site.com')
                ->save();
    }

    public function setUp()
    {
        $this->user = new UserRecord(self::$db);
    }

    public function testCreate()
    {
        $this->user->setSite(self::$site);

        $this->assertTrue($this->user->isNew());

        $this->user->save();

        $this->assertFalse($this->user->isNew());
        $this->assertNotNull($this->user->getId());
        $this->assertNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());

        self::$createdId = $this->user->getId();
    }

    public function testLoad()
    {
        $this->user->load(self::$createdId);

        $this->assertNotNull($this->user->getCreatedAt());
        $this->assertNull($this->user->getUpdatedAt());

        $this->assertFalse($this->user->isNew());
        $this->assertNotNull(self::$createdId, $this->user->getId());
        $this->assertEquals(self::$site->getId(), $this->user->getSiteId());
    }

    public function testUpdate()
    {
        $this->user->load(self::$createdId)->save();

        $this->user->load(self::$createdId);

        $this->assertNotNull($this->user->getUpdatedAt());
    }

    public function testLoadError()
    {
        try {
            $this->user->load(-1);
        } catch (UserNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckSiteError()
    {
        try {
            $this->user->setSite(self::$site)
                    ->setSiteId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetSiteError()
    {
        try {
            $this->user->setSite(new SiteRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testGetSite()
    {
        $this->assertNull($this->user->getSite());

        $this->user->load(self::$createdId);

        $this->assertNotNull($this->user->getSite());
        $this->assertNotNull($this->user->getSite()); // check to return from object parameter
    }

}
