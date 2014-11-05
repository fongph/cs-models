<?php

use CS\Models\Site\SiteRecord,
    CS\Models\User\UserRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\Order\Payment\OrderPaymentRecord,
    CS\Models\Order\Payment\OrderPaymentNotFoundException,
    CS\Models\Order\Payment\InvalidTypeException,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException;

/**
 * Description of OrderPaymentRecordTest
 *
 * @author root
 */
class OrderPaymentRecordTest extends \PHPUnit_Framework_TestCase
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
    private $orderPayment;

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
    }

    public function setUp()
    {
        $this->orderPayment = new OrderPaymentRecord(self::$db);
    }

    public function testCreate()
    {
        $this->orderPayment->setOrder(self::$order)
                ->setType(OrderPaymentRecord::TYPE_SALE)
                ->setAmount(10)
                ->setCommision(2)
                ->setDiscount(1)
                ->setDiscountCode('code')
                ->setRate(3)
                ->setCurrency('PHP');

        $this->assertTrue($this->orderPayment->isNew());

        $this->orderPayment->save();

        $this->assertFalse($this->orderPayment->isNew());
        $this->assertNotNull($this->orderPayment->getId());

        $this->assertEquals(OrderPaymentRecord::TYPE_SALE, $this->orderPayment->getType());
        $this->assertEquals(10, $this->orderPayment->getAmount());
        $this->assertEquals(2, $this->orderPayment->getCommission());
        $this->assertEquals(1, $this->orderPayment->getDiscount());
        $this->assertEquals('code', $this->orderPayment->getDiscountCode());
        $this->assertEquals(3, $this->orderPayment->getRate());
        $this->assertEquals('PHP', $this->orderPayment->getCurrency());

        $this->assertNull($this->orderPayment->getCreatedAt());

        self::$createdId = $this->orderPayment->getId();
    }

    public function testLoad()
    {
        $this->orderPayment->load(self::$createdId);

        $this->assertNotNull($this->orderPayment->getCreatedAt());

        $this->assertFalse($this->orderPayment->isNew());
        $this->assertNotNull(self::$createdId, $this->orderPayment->getId());
        $this->assertEquals(self::$order->getId(), $this->orderPayment->getOrderId());
        $this->assertEquals(self::$site->getId(), $this->orderPayment->getSiteId());
        $this->assertEquals(OrderPaymentRecord::TYPE_SALE, $this->orderPayment->getType());
        $this->assertEquals(10, $this->orderPayment->getAmount());
        $this->assertEquals(2, $this->orderPayment->getCommission());
        $this->assertEquals(1, $this->orderPayment->getDiscount());
        $this->assertEquals('code', $this->orderPayment->getDiscountCode());
        $this->assertEquals(3, $this->orderPayment->getRate());
        $this->assertEquals('PHP', $this->orderPayment->getCurrency());
    }

    public function testUpdateError()
    {
        $this->orderPayment->load(self::$createdId)
                ->setType(OrderPaymentRecord::TYPE_PROLONGATION)
                ->save();

        $this->orderPayment->load(self::$createdId);

        $this->assertNotNull($this->orderPayment->getUpdatedAt());
        $this->assertEquals(OrderPaymentRecord::TYPE_PROLONGATION, $this->orderPayment->getType());
    }

    public function testLoadError()
    {
        try {
            $this->orderPayment->load(-1);
        } catch (OrderPaymentNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckTypeError()
    {
        try {
            $this->orderPayment->setType('wrong-value')->save();
        } catch (InvalidTypeException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckOrderError()
    {
        try {
            $this->orderPayment->setOrder(self::$order)
                    ->setType(OrderPaymentRecord::TYPE_SALE)
                    ->setOrderId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckSiteError()
    {
        try {
            $this->orderPayment->setOrder(self::$order)
                    ->setSite(self::$site)
                    ->setType(OrderPaymentRecord::TYPE_SALE)
                    ->setSiteId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetOrderError()
    {
        try {
            $this->orderPayment->setOrder(new OrderRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetSiteError()
    {
        try {
            $this->orderPayment->setSite(new SiteRecord(self::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testGetOrder()
    {
        $this->assertNull($this->orderPayment->getOrder());

        $this->orderPayment->load(self::$createdId);

        $this->assertNotNull($this->orderPayment->getOrder());
        $this->assertNotNull($this->orderPayment->getOrder()); // check to return from object parameter
    }

    public function testGetSite()
    {
        $this->assertNull($this->orderPayment->getSite());

        $this->orderPayment->load(self::$createdId);

        $this->assertNotNull($this->orderPayment->getSite());
        $this->assertNotNull($this->orderPayment->getSite()); // check to return from object parameter
    }

}
