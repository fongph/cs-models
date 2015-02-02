<?php

namespace CS\Models\Product;

use PDO,
    CS\Models\AbstractRecord,
    CS\Models\Site\SiteRecord,
    CS\Models\Limitation\LimitationRecord,
    CS\Models\Order\OrderRecord,
    CS\Models\RecordDifferencesException,
    CS\Models\RecordNotCreatedException;

/**
 * Description of ProductRecord
 *
 * @author root
 */
class ProductRecord extends AbstractRecord
{

    /**
     *
     * @var LimitationRecord
     */
    protected $limitation;

    /**
     *
     * @var SiteRecord
     */
    protected $site;
    protected $type = self::TYPE_PACKAGE;
    protected $siteId;
    protected $limitationId;
    protected $name;
    protected $namespace;
    protected $group;
    protected $active = 0;
    protected $trial = 0;
    protected $price = 0;
    protected $codeBluesnap;
    protected $codeFastspring;
    protected $keys = array(
        'id' => 'id',
        'siteId' => 'site_id',
        'limitationId' => 'limitation_id',
        'name' => 'name',
        'namespace' => 'namespace',
        'group' => 'group',
        'type' => 'type',
        'active' => 'active',
        'price' => 'price',
        'codeBluesnap' => 'code_bluesnap',
        'codeFastspring' => 'code_fastspring',
        'trial' => 'trial',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );
    protected static $allowedTypes = array(self::TYPE_PACKAGE, self::TYPE_BUNDLE, self::TYPE_OPTION);

    const TYPE_PACKAGE = 'package';
    const TYPE_BUNDLE = 'bundle';
    const TYPE_OPTION = 'option';

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setSiteId($id)
    {
        $this->siteId = $id;

        return $this;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function setLimitationId($id)
    {
        $this->limitationId = $id;

        return $this;
    }

    public function getLimitationId()
    {
        return $this->limitationId;
    }

    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setNamespace($value)
    {
        $this->namespace = $value;

        return $this;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setGroup($value)
    {
        $this->group = $value;

        return $this;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setActive($value = true)
    {
        $this->active = intval($value > 0);

        return $this;
    }

    public function getActive()
    {
        return $this->active > 0;
    }
    
    public function setTrial($value = true)
    {
        $this->trial = intval($value > 0);

        return $this;
    }

    public function getTrial()
    {
        return $this->trial > 0;
    }

    public function setPrice($value)
    {
        $this->price = $value;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setCodeBlueSnap($value)
    {
        $this->codeBluesnap = $value;

        return $this;
    }

    public function setCodeFastSpring($value)
    {
        $this->codeFastspring = $value;

        return $this;
    }

    public function getCodeBlueSnap()
    {
        return $this->codeBluesnap;
    }

    public function getCodeFastSpring()
    {
        return $this->codeFastspring;
    }

    public function getReferenceCode($paymentMethod)
    {
        return $this->{'code' . ucfirst($paymentMethod)};
    }
    
    public static function getReferenceCodeColumn($paymentMethod)
    {
        if (!in_array($paymentMethod, OrderRecord::getAllowedPaymentMethods())) {
            throw new InvalidPaymentMethodException("Invalid payment method value");
        }

        return 'code_' . $paymentMethod;
    }

    public function setSite(SiteRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->siteId = $value->getId();

        $this->site = $value;

        return $this;
    }

    public function setLimitation(LimitationRecord $value)
    {
        if ($value->isNew()) {
            throw new RecordNotCreatedException("Record must be created!");
        }

        $this->limitationId = $value->getId();

        $this->limitation = $value;

        return $this;
    }

    /**
     * 
     * @return LimitationRecord
     */
    public function getLimitation()
    {
        if ($this->limitation instanceof LimitationRecord) {
            return $this->limitation;
        }

        if (!$this->isNew() && $this->limitationId) {
            $limitationRecord = new LimitationRecord($this->db);
            $limitationRecord->load($this->limitationId);

            $this->setLimitation($limitationRecord);
            return $this->limitation;
        }

        return null;
    }

    /**
     * 
     * @return SiteRecord
     */
    public function getSite()
    {
        if ($this->site instanceof SiteRecord) {
            return $this->site;
        }

        if (!$this->isNew() && $this->siteId) {
            $siteRecord = new SiteRecord($this->db);
            $siteRecord->load($this->siteId);

            $this->setSite($siteRecord);
            return $this->site;
        }

        return null;
    }

    private function updateRecord($siteId, $limitationId, $name, $namespace, $group, $type, $active, $price, $codeBluesnap, $codeFastspring, $trial)
    {
        $rows = $this->db->exec("UPDATE `products` SET
                                        `site_id` = {$siteId},
                                        `limitation_id` = {$limitationId},
                                        `name` = {$name},
                                        `namespace` = {$namespace},
                                        `group` = {$group},
                                        `type` = {$type},
                                        `active` = {$active},
                                        `price` = {$price},
                                        `code_bluesnap` = {$codeBluesnap},
                                        `code_fastspring` = {$codeFastspring},
                                        `trial` = {$trial},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($siteId, $limitationId, $name, $namespace, $group, $type, $active, $price, $codeBluesnap, $codeFastspring, $trial)
    {
        $this->db->exec("INSERT INTO `products` SET 
                            `site_id` = {$siteId},
                            `limitation_id` = {$limitationId},
                            `name` = {$name},
                            `namespace` = {$namespace},
                            `group` = {$group},
                            `type` = {$type},
                            `active` = {$active},
                            `price` = {$price},
                            `code_bluesnap` = {$codeBluesnap},
                            `code_fastspring` = {$codeFastspring},
                            `trial` = {$trial}
                        ");

        return $this->db->lastInsertId();
    }

    private function checkSite()
    {
        if ($this->site instanceof SiteRecord && $this->siteId != $this->site->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    private function checkLimitation()
    {
        if ($this->limitation instanceof LimitationRecord && $this->limitationId != $this->limitation->getId()) {
            throw new RecordDifferencesException("Invalid params");
        }
    }

    public function check()
    {
        if ($this->type !== null && !in_array($this->type, self::getAllowedTypes())) {
            throw new InvalidTypeException("Invalid product type value!");
        }

        $this->checkSite();
        $this->checkLimitation();
    }

    public function save()
    {
        $this->check();

        $siteId = $this->escape($this->siteId);
        $limitationId = $this->escape($this->limitationId);
        $name = $this->escape($this->name);
        $namespace = $this->escape($this->namespace);
        $group = $this->escape($this->group);
        $type = $this->escape($this->type);
        $active = $this->escape($this->active);
        $price = $this->escape($this->price);
        $codeBluesnap = $this->escape($this->codeBluesnap);
        $codeFastspring = $this->escape($this->codeFastspring);
        $trial = $this->escape($this->trial);

        if (!empty($this->id)) {
            return $this->updateRecord($siteId, $limitationId, $name, $namespace, $group, $type, $active, $price, $codeBluesnap, $codeFastspring, $trial);
        } else {
            $this->id = $this->insertRecord($siteId, $limitationId, $name, $namespace, $group, $type, $active, $price, $codeBluesnap, $codeFastspring, $trial);
        }
    }

    public function load($id)
    {
        $escapedId = $this->db->quote($id);
        if (($data = $this->db->query("SELECT *, UNIX_TIMESTAMP(`created_at`) as `created_at`, UNIX_TIMESTAMP(`updated_at`) as `updated_at` FROM `products` WHERE `id` = {$escapedId} LIMIT 1")->fetch(PDO::FETCH_ASSOC)) != false) {
            return $this->loadFromArray($data);
        }

        throw new ProductNotFoundException('Unable to load product');
    }

    public static function getAllowedTypes()
    {
        return self::$allowedTypes;
    }

}
