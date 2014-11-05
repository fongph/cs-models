<?php

use CS\Models\Site\SiteRecord,
    CS\Models\User\UserRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\Limitation\LimitationRecord,
    CS\Models\Product\ProductRecord,
    CS\Models\Order\Product\OrderProductRecord,
    CS\Models\Order\Product\OrderProductNotFoundException,
    CS\Models\Order\Product\InvalidStatusException,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException;

/**
 * Description of OrderProductRecordTest
 *
 * @author root
 */
class OrderProductRecordTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \PDO
     */
    static $db;
    static $createdId;
    static $site;
    static $user;
    static $order;
    static $product;
    static $limitation;
    private $orderProduct;

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

        self::$order = new OrderRecord(self::$db);
        self::$order->setSite(self::$site)
                ->setUser(self::$user)
                ->setStatus(OrderRecord::STATUS_CREATED)
                ->setPaymentMethod(OrderRecord::PAYMENT_METHOD_FASTSPRING)
                ->save();

        self::$limitation = new LimitationRecord(self::$db);
        self::$limitation->setName('name')
                ->setLifetime(10)
                ->save();

        self::$product = new ProductRecord(self::$db);
        self::$product->setName('product')
                ->setSite(self::$site)
                ->setLimitation(self::$limitation)
                ->save();
    }

    public function setUp()
    {
        $this->orderProduct = new OrderProductRecord(self::$db);
    }

    public function testCreate()
    {
        $this->orderProduct->setOrder(self::$order)
                ->setProduct(self::$product)
                ->setCount(2)
                ->setReferenceNumber('number')
                ->setStatus(OrderProductRecord::STATUS_ADDED);

        $this->assertTrue($this->orderProduct->isNew());

        $this->orderProduct->save();

        $this->assertFalse($this->orderProduct->isNew());
        $this->assertNotNull($this->orderProduct->getId());
        $this->assertEquals(2, $this->orderProduct->getCount());
        $this->assertEquals('number', $this->orderProduct->getReferenceNumber());
        $this->assertEquals(OrderProductRecord::STATUS_ADDED, $this->orderProduct->getStatus());
        $this->assertNull($this->orderProduct->getCreatedAt());
        $this->assertNull($this->orderProduct->getUpdatedAt());

        self::$createdId = $this->orderProduct->getId();
    }

    public function testLoad()
    {
        $this->orderProduct->load(self::$createdId);

        $this->assertNotNull($this->orderProduct->getCreatedAt());
        $this->assertNull($this->orderProduct->getUpdatedAt());

        $this->assertFalse($this->orderProduct->isNew());
        $this->assertNotNull(self::$createdId, $this->orderProduct->getId());
        $this->assertEquals(self::$order->getId(), $this->orderProduct->getOrderId());
        $this->assertEquals(self::$product->getId(), $this->orderProduct->getProductId());
        $this->assertEquals(2, $this->orderProduct->getCount());
        $this->assertEquals('number', $this->orderProduct->getReferenceNumber());
        $this->assertEquals(OrderProductRecord::STATUS_ADDED, $this->orderProduct->getStatus());
    }

    public function testUpdate()
    {
        $this->orderProduct->load(self::$createdId)
                ->setStatus(OrderProductRecord::STATUS_PAID)
                ->save();

        $this->orderProduct->load(self::$createdId);

        $this->assertNotNull($this->orderProduct->getUpdatedAt());
        $this->assertEquals(OrderProductRecord::STATUS_PAID, $this->orderProduct->getStatus());
    }

    public function testLoadError()
    {
        try {
            $this->orderProduct->load(-1);
        } catch (OrderProductNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckStatusError()
    {
        try {
            $this->orderProduct->setStatus('wrong-value')
                    ->save();
        } catch (InvalidStatusException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckOrderError()
    {
        try {
            $this->orderProduct->setOrder(self::$order)
                    ->setStatus(OrderProductRecord::STATUS_ADDED)
                    ->setOrderId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckLimitationError()
    {
        try {
            $this->orderProduct->setOrder(self::$order)
                    ->setProduct(self::$product)
                    ->setStatus(OrderProductRecord::STATUS_ADDED)
                    ->setProductId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetOrderError()
    {
        try {
            $this->orderProduct->setOrder(new OrderRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetProductError()
    {
        try {
            $this->orderProduct->setProduct(new ProductRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testGetOrder()
    {
        $this->assertNull($this->orderProduct->getOrder());

        $this->orderProduct->load(self::$createdId);

        $this->assertNotNull($this->orderProduct->getOrder());
        $this->assertNotNull($this->orderProduct->getOrder()); // check to return from object parameter
    }

    public function testGetProduct()
    {
        $this->assertNull($this->orderProduct->getProduct());

        $this->orderProduct->load(self::$createdId);

        $this->assertNotNull($this->orderProduct->getProduct());
        $this->assertNotNull($this->orderProduct->getProduct()); // check to return from object parameter
    }

}
