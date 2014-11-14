<?php

use 
    CS\Models\Site\SiteRecord,
    CS\Models\User\UserRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\Limitation\LimitationRecord,
    CS\Models\License\LicenseRecord,
    CS\Models\Product\ProductRecord,
    CS\Models\Order\Product\OrderProductRecord,
    CS\Models\Order\Product\OrderProductNotFoundException,
    CS\Models\Order\Product\InvalidStatusException,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException,
    CS\Models\TestHelper;

/**
 * Description of LicenseTest
 *
 * @author root
 */
class LicenseTest extends \PHPUnit_Framework_TestCase
{

    private static $createdId;
    private $license;

    public function setUp()
    {
        $this->license = new LicenseRecord(TestHelper::$db);
    }

    public function testCreate()
    {
        $this->license->setOrderProduct(TestHelper::$orderProduct)
                ->setStatus(LicenseRecord::STATUS_INACTIVE);

        $this->assertTrue($this->license->isNew());

        $this->license->save();

        $this->assertFalse($this->license->isNew());
        $this->assertNotNull($this->license->getId());
        $this->assertEquals(LicenseRecord::STATUS_INACTIVE, $this->license->getStatus());
        $this->assertNull($this->license->getCreatedAt());
        $this->assertNull($this->license->getUpdatedAt());

        self::$createdId = $this->license->getId();
    }

//    public function testLoad()
//    {
//        $this->license->load(self::$createdId);
//
//        $this->assertNotNull($this->license->getCreatedAt());
//        $this->assertNull($this->license->getUpdatedAt());
//
//        $this->assertFalse($this->license->isNew());
//        $this->assertNotNull(self::$createdId, $this->license->getId());
//        $this->assertEquals(TestHelper::$orderProduct->getProductId(), $this->license->getProductId());
//        $this->assertEquals(2, $this->license->getCount());
//        $this->assertEquals('number', $this->license->getReferenceNumber());
//        $this->assertEquals(OrderProductRecord::STATUS_ADDED, $this->license->getStatus());
//    }

//    public function testUpdate()
//    {
//        $this->license->load(self::$createdId)
//                ->setStatus(OrderProductRecord::STATUS_PAID)
//                ->save();
//
//        $this->license->load(self::$createdId);
//
//        $this->assertNotNull($this->license->getUpdatedAt());
//        $this->assertEquals(OrderProductRecord::STATUS_PAID, $this->license->getStatus());
//    }
//
//    public function testLoadError()
//    {
//        try {
//            $this->license->load(-1);
//        } catch (OrderProductNotFoundException $e) {
//            return;
//        }
//
//        $this->fail('An expected exception has not been raised.');
//    }
//
//    public function testCheckStatusError()
//    {
//        try {
//            $this->license->setStatus('wrong-value')
//                    ->save();
//        } catch (InvalidStatusException $e) {
//            return;
//        }
//
//        $this->fail('An expected exception has not been raised.');
//    }
//
//    public function testCheckOrderError()
//    {
//        try {
//            $this->license->setOrder(self::$order)
//                    ->setStatus(OrderProductRecord::STATUS_ADDED)
//                    ->setOrderId(0)
//                    ->save();
//        } catch (RecordDifferencesException $e) {
//            return;
//        }
//
//        $this->fail('An expected exception has not been raised.');
//    }
//
//    public function testCheckLimitationError()
//    {
//        try {
//            $this->license->setOrder(self::$order)
//                    ->setProduct(self::$product)
//                    ->setStatus(OrderProductRecord::STATUS_ADDED)
//                    ->setProductId(0)
//                    ->save();
//        } catch (RecordDifferencesException $e) {
//            return;
//        }
//
//        $this->fail('An expected exception has not been raised.');
//    }
//
//    public function testSetOrderError()
//    {
//        try {
//            $this->license->setOrder(new OrderRecord(self::$db));
//        } catch (RecordNotCreatedException $e) {
//            return;
//        }
//
//        $this->fail('An expected exception has not been raised.');
//    }
//
//    public function testSetProductError()
//    {
//        try {
//            $this->license->setProduct(new ProductRecord(self::$db));
//        } catch (RecordNotCreatedException $e) {
//            return;
//        }
//
//        $this->fail('An expected exception has not been raised.');
//    }
//
//    public function testGetOrder()
//    {
//        $this->assertNull($this->license->getOrder());
//
//        $this->license->load(self::$createdId);
//
//        $this->assertNotNull($this->license->getOrder());
//        $this->assertNotNull($this->license->getOrder()); // check to return from object parameter
//    }
//
//    public function testGetProduct()
//    {
//        $this->assertNull($this->license->getProduct());
//
//        $this->license->load(self::$createdId);
//
//        $this->assertNotNull($this->license->getProduct());
//        $this->assertNotNull($this->license->getProduct()); // check to return from object parameter
//    }
}
