<?php

use CS\Models\Site\SiteRecord,
    CS\Models\User\UserRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\Order\Product\OrderProductRecord,
    CS\Models\Order\OrderNotFoundException,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException,
    CS\Models\Order\InvalidStatusException,
    CS\Models\Order\InvalidPaymentMethodException;

/**
 * Description of ProductRecordTest
 *
 * @author root
 */
class OrderRecordTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \PDO
     */
    static $db;
    static $createdId;
    static $site;
    static $user;

    /**
     *
     * @var OrderRecord
     */
    private $order;

    public static function setUpBeforeClass()
    {
        global $db;
        self::$db = $db;

        self::$site = new SiteRecord(self::$db);
        self::$site->setName('site.com')
                ->save();

        self::$user = new UserRecord(self::$db);
        self::$user->setSite(self::$site)
                ->save();
    }

    public function setUp()
    {
        $this->order = new OrderRecord(self::$db);
    }

    public function testCreate()
    {
        $this->order->setSite(self::$site)
                ->setUser(self::$user)
                ->setStatus(OrderRecord::STATUS_CREATED)
                ->setPaymentMethod(OrderRecord::PAYMENT_METHOD_FASTSPRING)
                ->setAmount(5)
                ->setReferenceNumber('aabb')
                ->setGatewayStatus('status')
                ->setGatewayData('data');

        $this->assertTrue($this->order->isNew());

        $this->order->save();

        $this->assertFalse($this->order->isNew());
        $this->assertNotNull($this->order->getId());
        $this->assertEquals(self::$site->getId(), $this->order->getSiteId());
        $this->assertEquals(self::$user->getId(), $this->order->getUserId());
        $this->assertNotNull($this->order->getHash());
        $this->assertEquals(5, $this->order->getAmount());
        $this->assertEquals('aabb', $this->order->getReferenceNumber());
        $this->assertEquals(OrderRecord::STATUS_CREATED, $this->order->getStatus());
        $this->assertEquals(OrderRecord::PAYMENT_METHOD_FASTSPRING, $this->order->getPaymentMethod());
        $this->assertEquals('status', $this->order->getGatewayStatus());
        $this->assertEquals('data', $this->order->getGatewayData());

        $this->assertNull($this->order->getCreatedAt());
        $this->assertNull($this->order->getUpdatedAt());

        self::$createdId = $this->order->getId();
    }

    public function testLoad()
    {
        $this->order->load(self::$createdId);

        $this->assertNotNull($this->order->getCreatedAt());
        $this->assertNull($this->order->getUpdatedAt());

        $this->assertFalse($this->order->isNew());
        $this->assertNotNull(self::$createdId, $this->order->getId());
        $this->assertEquals(self::$site->getId(), $this->order->getSiteId());
        $this->assertEquals(self::$user->getId(), $this->order->getUserId());
        $this->assertNotNull($this->order->getHash());
        $this->assertEquals(5, $this->order->getAmount());
        $this->assertEquals('aabb', $this->order->getReferenceNumber());
        $this->assertEquals(OrderRecord::STATUS_CREATED, $this->order->getStatus());
        $this->assertEquals(OrderRecord::PAYMENT_METHOD_FASTSPRING, $this->order->getPaymentMethod());
    }

    public function testUpdate()
    {
        $this->order->load(self::$createdId)
                ->setStatus(OrderRecord::STATUS_COMPLETED)
                ->save();

        $this->order->load(self::$createdId);

        $this->assertNotNull($this->order->getUpdatedAt());
        $this->assertEquals(OrderRecord::STATUS_COMPLETED, $this->order->getStatus());
    }

    public function testLoadError()
    {
        try {
            $this->order->load(-1);
        } catch (OrderNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckSiteError()
    {
        try {
            $this->order->setSite(self::$site)
                    ->setSiteId(0)
                    ->setPaymentMethod(OrderRecord::PAYMENT_METHOD_FASTSPRING)
                    ->setStatus(OrderRecord::STATUS_CREATED)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckLimitationError()
    {
        try {
            $this->order->setSite(self::$site)
                    ->setUser(self::$user)
                    ->setUserId(0)
                    ->setPaymentMethod(OrderRecord::PAYMENT_METHOD_FASTSPRING)
                    ->setStatus(OrderRecord::STATUS_CREATED)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetSiteError()
    {
        try {
            $this->order->setSite(new SiteRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetLimitationError()
    {
        try {
            $this->order->setUser(new UserRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testGetSite()
    {
        $this->assertNull($this->order->getSite());

        $this->order->load(self::$createdId);

        $this->assertNotNull($this->order->getSite());
        $this->assertNotNull($this->order->getSite()); // check to return from object parameter
    }

    public function testGetUser()
    {
        $this->assertNull($this->order->getUser());

        $this->order->load(self::$createdId);

        $this->assertNotNull($this->order->getUser());
        $this->assertNotNull($this->order->getUser()); // check to return from object parameter
    }

    public function testCheckStatusError()
    {
        try {
            $this->order->setPaymentMethod(OrderRecord::PAYMENT_METHOD_FASTSPRING)
                    ->setStatus('wrong-value')
                    ->save();
        } catch (InvalidStatusException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckPaymentMethodError()
    {
        try {
            $this->order->setPaymentMethod('wrong-value')
                    ->save();
        } catch (InvalidPaymentMethodException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testProductsIterator()
    {
        $this->assertNotNull($this->order->getProductsIterator());

        $this->order->load(self::$createdId);
        $this->assertNotNull($this->order->getProductsIterator());

        $productRecord = new OrderProductRecord(self::$db);
        
        /**
         * @TODO: complete test
         */
        
        /**
        
        $productRecord->setOrder($this->order)
                ->setStatus(OrderProductRecord::STATUS_ADDED)
                ->save();
        
        $this->assertNotNull($this->order->getProductsIterator());
         * 
         */
    }

}
