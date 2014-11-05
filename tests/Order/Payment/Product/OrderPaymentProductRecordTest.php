<?php

use CS\Models\Order\Payment\Product\OrderPaymentProductRecord,
    CS\Models\Order\Product\OrderProductRecord,
    CS\Models\Order\Payment\OrderPaymentRecord,
    CS\Models\Order\Payment\Product\OrderPaymentProductNotFoundException,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException;

/**
 * Description of OrderPaymentProductRecordTest
 *
 * @author root
 */
class OrderPaymentProductRecordTest extends \PHPUnit_Framework_TestCase
{

    static $createdId;

    /**
     *
     * @var OrderPaymentProductRecord
     */
    private $orderPaymentProduct;

    public function setUp()
    {
        $this->orderPaymentProduct = new OrderPaymentProductRecord(TestHelper::$db);
    }

    public function testCreate()
    {
        $this->orderPaymentProduct->setOrderPayment(TestHelper::$orderPayment)
                ->setOrderProduct(TestHelper::$orderProduct)
                ->setPrice(12);

        $this->assertTrue($this->orderPaymentProduct->isNew());

        $this->orderPaymentProduct->save();

        $this->assertFalse($this->orderPaymentProduct->isNew());
        $this->assertNotNull($this->orderPaymentProduct->getId());
        $this->assertEquals(12, $this->orderPaymentProduct->getPrice());
        $this->assertNull($this->orderPaymentProduct->getCreatedAt());
        $this->assertNull($this->orderPaymentProduct->getUpdatedAt());

        self::$createdId = $this->orderPaymentProduct->getId();
    }

    public function testLoad()
    {
        $this->orderPaymentProduct->load(self::$createdId);

        $this->assertNotNull($this->orderPaymentProduct->getCreatedAt());
        $this->assertNull($this->orderPaymentProduct->getUpdatedAt());

        $this->assertFalse($this->orderPaymentProduct->isNew());
        $this->assertNotNull(self::$createdId, $this->orderPaymentProduct->getId());
        $this->assertEquals(TestHelper::$orderProduct->getId(), $this->orderPaymentProduct->getOrderProductId());
        $this->assertEquals(TestHelper::$orderPayment->getId(), $this->orderPaymentProduct->getOrderPaymentId());
        $this->assertEquals(TestHelper::$orderPayment->getCurrency(), $this->orderPaymentProduct->getCurrency());

        $this->assertEquals(12, $this->orderPaymentProduct->getPrice());
    }

    public function testUpdate()
    {
        $this->orderPaymentProduct->load(self::$createdId)
                ->setPrice(11)
                ->save();

        $this->orderPaymentProduct->load(self::$createdId);

        $this->assertNotNull($this->orderPaymentProduct->getUpdatedAt());
        $this->assertEquals(11, $this->orderPaymentProduct->getPrice());
    }

    public function testLoadError()
    {
        try {
            $this->orderPaymentProduct->load(-1);
        } catch (OrderPaymentProductNotFoundException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckOrderError()
    {
        try {
            $this->orderPaymentProduct->setOrderPayment(TestHelper::$orderPayment)
                    ->setOrderPaymentId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testCheckOrderProductError()
    {
        try {
            $this->orderPaymentProduct->setOrderPayment(TestHelper::$orderPayment)
                    ->setOrderProduct(TestHelper::$orderProduct)
                    ->setOrderProductId(0)
                    ->save();
        } catch (RecordDifferencesException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetOrderProductError()
    {
        try {
            $this->orderPaymentProduct->setOrderProduct(new OrderProductRecord(TestHelper::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testSetOrderPaymentError()
    {
        try {
            $this->orderPaymentProduct->setOrderPayment(new OrderPaymentRecord(TestHelper::$db));
        } catch (RecordNotCreatedException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testGetOrderProduct()
    {
        $this->assertNull($this->orderPaymentProduct->getOrderProduct());

        $this->orderPaymentProduct->load(self::$createdId);

        $this->assertNotNull($this->orderPaymentProduct->getOrderProduct());
        $this->assertNotNull($this->orderPaymentProduct->getOrderProduct()); // check to return from object parameter
    }
    
    public function testGetOrderPayment()
    {
        $this->assertNull($this->orderPaymentProduct->getOrderPayment());

        $this->orderPaymentProduct->load(self::$createdId);

        $this->assertNotNull($this->orderPaymentProduct->getOrderPayment());
        $this->assertNotNull($this->orderPaymentProduct->getOrderPayment()); // check to return from object parameter
    }

}
