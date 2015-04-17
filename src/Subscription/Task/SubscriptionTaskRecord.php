<?php

namespace CS\Models\Subscription\Task;

use CS\Models\AbstractRecord;
use CS\Models\Order\InvalidPaymentMethodException;
use CS\Models\Order\OrderRecord;
use CS\Models\Subscription\SubscriptionRecord;

class SubscriptionTaskRecord extends AbstractRecord {
    
    const TASK_AUTO_REBILL_START = 'auto-rebill-start';
    const TASK_AUTO_REBILL_STOP = 'auto-rebill-stop';

    protected $paymentMethod = OrderRecord::PAYMENT_METHOD_BLUESNAP;
    protected $referenceNumber;
    protected $task = 0;
    protected $keys = array(
        'id' => 'id',
        'paymentMethod' => 'payment_method',
        'referenceNumber' => 'reference_number',
        'task' => 'task',
    );
    
    protected static $allowedTasks = array(
        self::TASK_AUTO_REBILL_START,
        self::TASK_AUTO_REBILL_STOP,
    );
    
    public function setSubscription(SubscriptionRecord $subscription)
    {
        $this
            ->setPaymentMethod($subscription->getPaymentMethod())
            ->setReferenceNumber($subscription->getReferenceNumber());
        
        return $this;
    }

    public function setPaymentMethod($value)
    {
        $this->paymentMethod = $value;

        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setReferenceNumber($value)
    {
        $this->referenceNumber = $value;

        return $this;
    }

    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }
    
    public function setTask($value)
    {
        $this->task = $value;
        
        return $this;
    }
    
    public function getTask()
    {
        return $this->task;
    }

    private function updateRecord($paymentMethod, $referenceNumber, $task)
    {
        $rows = $this->db->exec("UPDATE `subscriptions_tasks` SET
                                        `payment_method` = {$paymentMethod},
                                        `reference_number` = {$referenceNumber},
                                        `task` = {$task}
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($paymentMethod, $referenceNumber, $task)
    {
        $this->db->exec("INSERT INTO `subscriptions_tasks` SET
                            `payment_method` = {$paymentMethod},
                            `reference_number` = {$referenceNumber},
                            `task` = {$task}
                        ");

        return $this->db->lastInsertId();
    }
    
    public function check()
    {
        if (!in_array($this->paymentMethod, OrderRecord::getAllowedPaymentMethods())) {
            throw new InvalidPaymentMethodException("Invalid payment method value!");
        }

        if (!in_array($this->task, self::getAllowedTasks())) {
            throw new InvalidTaskException("Invalid task value!");
        }
    }
    
    public static function getAllowedTasks()
    {
        return self::$allowedTasks;
    }

    public function save()
    {
        $this->check();

        $paymentMethod = $this->escape($this->paymentMethod);
        $referenceNumber = $this->escape($this->referenceNumber);
        $task = $this->escape($this->task);

        if (!empty($this->id)) {
            if (!$this->updateRecord($paymentMethod, $referenceNumber, $task)) {
                return false;
            }
        } else {
            $this->id = $this->insertRecord($paymentMethod, $referenceNumber, $task);
        }

        return true;
    }

    public function load($id)
    {
        if (($data = $this->db->query("SELECT * FROM `subscriptions_tasks` WHERE `id` = {$this->db->quote($id)} LIMIT 1")->fetch(\PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new SubscriptionTaskNotFoundException('Unable to load subscription task record');
    }
    
} 