<?php

namespace CS\Models\Discount;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Site\SiteRecord,   
    CS\Models\RecordNotCreatedException,
    CS\Models\RecordDifferencesException;

/**
 * Description of RefererRecord
 *
 * @author Nsergey
 */
class DiscountRecord extends AbstractRecord
{

    /**
     *
     * @var SiteRecord
     */
    protected $site;
    protected $licenseId;
    protected $userId;
    protected $status = self::DISCOUNT_APPLE;

    protected $keys = array(
        'id'        => 'id',
        'userId'    => 'user_id',
        'licenseId' => 'license_id',
        'status'    => 'status',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );

    const DISCOUNT_50 = 'rP3DBSVh';
    
    const DISCOUNT_APPLE = 'apple';
    const DISCOUNT_DELETE = 'delete';
    const DISCOUNT_COMPLETED = 'completed';

     public function setId($value)
    {
        $this->id = $value;

        return $this;
    }
    
    public function setUserId($value)
    {
        $this->userId = $value;

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }
    
    public function setLicenseId($value)
    {
        $this->licenseId = $value;

        return $this;
    }

    public function getLicenseId()
    {
        return $this->licenseId;
    }
    
    public function setStatus($value)
    {
        $this->status = $value;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    
    private function updateRecord($status) 
    {
        $rows = $this->db->exec("UPDATE `discounts` SET
                                        `status` = {$status},       
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                "); 

        return ($rows > 0);
    }

    private function insertRecord( $userId, $licenseId, $status ) 
    {
        $this->db->exec("INSERT INTO `discounts` SET 
                            `user_id` = {$userId},
                            `status` = {$status},    
                            `license_id` = {$licenseId},
                            `created_at` = NOW()    
                        "); 

        return $this->db->lastInsertId();
    }

    public function save()
    {
        $userId = (int)$this->userId;
        $licenseId = ($this ->licenseId) ?  $this->escape($this->licenseId) : false;
        $status = $this->escape($this->status);
        
        if (!empty($this->id)) {
            return $this->updateRecord($status); 
        } else {
            $this->id = $this->insertRecord($userId, $licenseId, $status);
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
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `discounts` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new DiscountNotFoundException('Unable to load discounts record');
    }

}
