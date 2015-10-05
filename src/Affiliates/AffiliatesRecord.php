<?php

namespace CS\Models\Affiliates;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Order\OrderRecord,
    IP,    
    CS\Models\RecordNotCreatedException,
    CS\Models\Affiliates\AffiliatesNotFoundException as AffiliatesNotFoundException,     
    CS\Models\RecordDifferencesException;

/**
 * Description of AffiliatesRecord
 *
 * @author Nsergey
 */
class AffiliatesRecord extends AbstractRecord
{

    /**
     *
     * @var AffiliatesRecord
     */
    protected $aff_id;
    protected $utm_source = self::UTM_SOURCE_DEFAULT;
    protected $utm_medium = self::UTM_UTM_MEDIUM;
    protected $nick_name;
   
    protected $keys = array(
        'id'            => 'id',
        'utm_source'    => 'utm_source',
        'utm_medium'    => 'utm_medium',
        'nick_name'     => 'nick_name',
        'createdAt'     => 'created_at',
        'updatedAt'     => 'updated_at'
    );

    const UTM_SOURCE_DEFAULT = 'monitorphones';
    const UTM_UTM_MEDIUM = 'affiliate';
    
    public function setAffId($value)
    {
        $this->aff_id = $value;

        return $this;
    }

    public function getAffId()
    {
        return $this->aff_id;
    }
    
    public function setUtmSource($value)
    {
        $this->utm_source = $value;

        return $this;
    }

    public function getUtmSource()
    {
        return $this->utm_source;
    }
    
    
    public function setUtmMedium($value)
    {
        $this->utm_medium = $value;

        return $this;
    }

    public function getUtmMedium()
    {
        return $this->utm_medium;
    }
    
    public function setNickName($value)
    {
        $this->nick_name = $value;

        return $this;
    }

    public function getNickName()
    {
        return $this->nick_name;
    }
    
    private function updateRecord($utm_source, $utm_medium, $nick_name) 
    {
        $rows = $this->db->exec("UPDATE `affiliates` SET
                                        `utm_source` = {$utm_source},
                                        `utm_medium` = {$utm_medium}, 
                                        `nick_name` = {$nick_name},           
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                "); 

        return ($rows > 0);
    }

    private function insertRecord( $aff_id, $utm_source, $utm_medium, $nick_name ) 
    {
        $this->db->exec("INSERT INTO `affiliates` SET `id` = {$aff_id}, 
                            `utm_source` = {$utm_source},
                            `utm_medium` = {$utm_medium},
                            `nick_name` = {$nick_name},  
                            `created_at` = NOW()    
                        "); 

        return $this->db->lastInsertId();
    }


    public function save()
    {
        $aff_id = $this->escape($this->aff_id);
        $nick_name = $this->escape($this->nick_name);
        $utm_source = ($this -> utm_source) ?  $this->escape($this->utm_source) : false;
        $utm_medium = ($this -> utm_medium) ?  $this->escape($this->utm_medium) : false;
        
        if (!empty($this->id)) {
            return $this->updateRecord($utm_source, $utm_medium, $nick_name); 
        } else {
            $this->id = $this->insertRecord($aff_id, $utm_source, $utm_medium, $nick_name);
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
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `affiliates` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new AffiliatesNotFoundException('Unable to load affiliates record');
    }

}
