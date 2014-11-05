<?php

use CS\Models\Site\SiteRecord,
    CS\Models\Limitation\LimitationRecord,
    CS\Models\Product\ProductRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\Product\ProductNotFoundException,
    CS\Models\Product\InvalidTypeException,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException;

/**
 * Description of ProductRecordTest
 *
 * @author root
 */
class ProductRecordTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \PDO
     */
    static $db;
    static $createdId;
    static $site;
    static $limitation;
    private $product;

    public static function setUpBeforeClass()
    {
        global $db;
        self::$db = $db;

        self::$site = new SiteRecord(self::$db);
        self::$site->setName('site.com')
                ->save();

        self::$limitation = new LimitationRecord(self::$db);
        self::$limitation->setName('name')
                ->setLifetime(20)
                ->setSms(100)
                ->save();
    }

    public function setUp()
    {
        $this->product = new ProductRecord(self::$db);
    }

    public function testCreate()
    {
        $this->product->setName('name')
                ->setSite(self::$site)
                ->setLimitation(self::$limitation)
                ->setNamespace('namespace')
                ->setGroup('group')
                ->setType(ProductRecord::TYPE_OPTION)
                ->setActive(true)
                ->setPrice(5)
                ->setCodeBlueSnap('bs')
                ->setCodeFastSpring('fs');

        $this->assertTrue($this->product->isNew());

        $this->product->save();

        $this->assertFalse($this->product->isNew());
        $this->assertNotNull($this->product->getId());
        $this->assertEquals('name', $this->product->getName());
        $this->assertEquals('namespace', $this->product->getNamespace());
        $this->assertEquals('group', $this->product->getGroup());
        $this->assertEquals(ProductRecord::TYPE_OPTION, $this->product->getType());
        $this->assertTrue($this->product->getActive());
        $this->assertEquals(5, $this->product->getPrice());
        $this->assertEquals('bs', $this->product->getCodeBlueSnap());
        $this->assertEquals('fs', $this->product->getCodeFastSpring());
        $this->assertEquals('fs', $this->product->getReferenceCode(OrderRecord::PAYMENT_METHOD_FASTSPRING));
        $this->assertNull($this->product->getCreatedAt());
        $this->assertNull($this->product->getUpdatedAt());

        self::$createdId = $this->product->getId();
    }

    public function testLoad()
    {
        $this->product->load(self::$createdId);

        $this->assertNotNull($this->product->getCreatedAt());
        $this->assertNull($this->product->getUpdatedAt());

        $this->assertFalse($this->product->isNew());
        $this->assertNotNull(self::$createdId, $this->product->getId());
        $this->assertEquals(self::$site->getId(), $this->product->getSiteId());
        $this->assertEquals(self::$limitation->getId(), $this->product->getLimitationId());
        $this->assertEquals('name', $this->product->getName());
        $this->assertEquals('namespace', $this->product->getNamespace());
        $this->assertEquals('group', $this->product->getGroup());
        $this->assertEquals(ProductRecord::TYPE_OPTION, $this->product->getType());
        $this->assertTrue($this->product->getActive());
        $this->assertEquals(5, $this->product->getPrice());
        $this->assertEquals('bs', $this->product->getCodeBlueSnap());
        $this->assertEquals('fs', $this->product->getCodeFastSpring());
    }

    public function testUpdate()
    {
        $this->product->load(self::$createdId)
                ->setActive(false)
                ->save();

        $this->product->load(self::$createdId);

        $this->assertNotNull($this->product->getUpdatedAt());
        $this->assertFalse($this->product->getActive());
    }

    public function testLoadError()
    {
        try {
            $this->product->load(-1);
        } catch (ProductNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckTypeError()
    {
        try {
            $this->product->setType('wrong-value')
                    ->save();
        } catch (InvalidTypeException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckSiteError()
    {
        try {
            $this->product->setSite(self::$site)
                    ->setSiteId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckLimitationError()
    {
        try {
            $this->product->setLimitation(self::$limitation)
                    ->setLimitationId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetSiteError()
    {
        try {
            $this->product->setSite(new SiteRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetLimitationError()
    {
        try {
            $this->product->setLimitation(new LimitationRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testGetSite()
    {
        $this->assertNull($this->product->getSite());

        $this->product->load(self::$createdId);

        $this->assertNotNull($this->product->getSite());
        $this->assertNotNull($this->product->getSite()); // check to return from object parameter
    }

    public function testGetLimitation()
    {
        $this->assertNull($this->product->getLimitation());

        $this->product->load(self::$createdId);

        $this->assertNotNull($this->product->getLimitation());
        $this->assertNotNull($this->product->getLimitation()); // check to return from object parameter
    }

}
