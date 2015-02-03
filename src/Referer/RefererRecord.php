<?php

namespace CS\Models\Referer;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Order\OrderRecord,
    IP,    
    CS\Models\RecordNotCreatedException,
    CS\Models\RecordDifferencesException;

/**
 * Description of RefererRecord
 *
 * @author Nsergey
 */
class RefererRecord extends AbstractRecord
{

    /**
     *
     * @var RefererRecord
     */
    protected $orderId;
    protected $referer;
    protected $ip;
   
    protected $keys = array(
        'id'        => 'id',
        'orderId'   => 'order_id',
        'referer'   => 'referer',
        'ip'        => 'ip',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    public function setOrderId($id)
    {
        $this->orderId = $id;

        return $this;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setOrder(OrderRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->orderId = $value->getId();

        $this->order = $value;

        return $this;
    }
    
    /**
     * 
     * @return OrderRecord
     */
    public function getOrder()
    {
        if ($this->order instanceof OrderRecord) {
            return $this->order;
        }

        if (!$this->isNew() && $this->orderId) {
            $orderRecord = new OrderRecord($this->db);
            $orderRecord->load($this->orderId);

            $this->setOrder($orderRecord);
            return $this->order;
        }

        return null;
    }

    
    public function setReferer($value)
    {
        $this->referer = $value;

        return $this;
    }

    public function getReferer()
    {
        return $this->referer;
    }
    
    
    public function setIp($value)
    {
        $this->ip = $value;

        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }
    
    
    
    private function updateRecord($orderId, $referer, $ip) 
    {
        $rows = $this->db->exec("UPDATE `orders_referers` SET
                                        `order_id` = {$orderId},
                                        `referer` = {$referer}, 
                                        `ip` = {$ip},            
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                "); 

        return ($rows > 0);
    }

    private function insertRecord( $orderId, $referer,  $ip ) 
    {
        $this->db->exec("INSERT INTO `orders_referers` SET 
                            `order_id` = {$orderId},
                            `referer` = {$referer},
                            `ip` = {$ip},
                            `created_at` = NOW()    
                        "); 

        return $this->db->lastInsertId();
    }


    public function save()
    {
        
        $orderId = $this->escape($this->orderId);
        $referer = ($this -> referer) ?  $this->escape($this->referer) : false;
        $ip = ($this->ip) ? $this->escape($this->ip) : $this->escape( IP::getRealIP() );
        
        if (!empty($this->id)) {
            return $this->updateRecord($orderId, $referer, $ip); 
        } else {
            $this->id = $this->insertRecord($orderId, $referer, $ip);
        }
    }

    /**
     * 
     * @param type $id
     * @return RefererRecord
     * @throws RefererNotFoundException
     */
    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `orders_referers` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new UserNotFoundException('Unable to load referers record');
    }

}
