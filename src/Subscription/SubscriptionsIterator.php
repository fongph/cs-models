<?php

namespace CS\Models\Subscription;

/**
 * Description of SubscriptionsIterator
 *
 * @author root
 */
class SubscriptionsIterator implements \Iterator
{

    /**
     * Database connection
     * 
     * @var PDO
     */
    protected $db;
    protected $container = array();
    protected $position = 0;
    protected $referenceNumber;
    protected $paymentMethod;

    public function __construct(\PDO $db, $referenceNumber, $paymentMethod)
    {
        $this->db = $db;
        $this->referenceNumber = $referenceNumber;
        $this->paymentMethod = $paymentMethod;
        $this->reload();
    }

    /**
     * 
     * @return Product\Record
     */
    public function current()
    {
        return $this->container[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->container[$this->position]);
    }

    public function length()
    {
        return count($this->container);
    }

    public function reload()
    {
        $this->container = array();
        $this->position = 0;

        $escapedMethod = $this->db->quote($this->paymentMethod);
        $escapedReference = $this->db->quote($this->referenceNumber);
        $records = $this->db->query("SELECT *,  FROM `subscriptions` WHERE `payment_method` = `{$escapedMethod}` AND `reference_number` = {$escapedReference}")->fetchAll();
        foreach ($records as $value) {
            $product = new SubscriptionRecord($this->db);
            $product->loadFromArray($value);

            array_push($this->container, $product);
        }
    }

}
