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
    protected $origin = self::ORIGIN_INTERNAL;
    protected $price = 0;
    protected $priceRegular = 0;
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
        'priceRegular' => 'price_regular',
        'codeBluesnap' => 'code_bluesnap',
        'codeFastspring' => 'code_fastspring',
        'origin' => 'origin',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    );
    protected static $allowedTypes = array(self::TYPE_PACKAGE, self::TYPE_BUNDLE, self::TYPE_OPTION);
    protected static $allowedOrigins = array(self::ORIGIN_INTERNAL, self::ORIGIN_GATEWAY, self::ORIGIN_GATEWAY_TRIAL, self::ORIGIN_INTERNAL_TRIAL, self::ORIGIN_INTERNAL_GIFT);

    const TYPE_PACKAGE = 'package';
    const TYPE_BUNDLE = 'bundle';
    const TYPE_OPTION = 'option';
    const ORIGIN_INTERNAL = 'internal';
    const ORIGIN_GATEWAY = 'gateway';
    const ORIGIN_GATEWAY_TRIAL = 'gateway-trial';
    const ORIGIN_INTERNAL_TRIAL = 'internal-trial';
    const ORIGIN_INTERNAL_GIFT = 'internal-gift';

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    public function getOrigin()
    {
        return $this->origin;
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

    /**
     * @deprecated
     * @param type $value
     * @return \CS\Models\Product\ProductRecord
     */
    public function setTrial()
    {
        return $this;
    }

    /**
     * @deprecated
     * @return type
     */
    public function getTrial()
    {
        return false;
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

    public function setPriceRegular($value)
    {
        $this->priceRegular = $value;

        return $this;
    }

    public function getPriceRegular()
    {
        return $this->priceRegular;
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
        if ($paymentMethod == 'fastspring-contextual'){
            $paymentMethod = 'fastspring';
        }
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

    private function updateRecord($siteId, $limitationId, $name, $namespace, $group, $type, $active, $price, $codeBluesnap, $codeFastspring, $origin, $priceRegular)
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
                                        `price_regular` = {$priceRegular},
                                        `code_bluesnap` = {$codeBluesnap},
                                        `code_fastspring` = {$codeFastspring},
                                        `origin` = {$origin},
                                        `updated_at` = NOW()
                                    WHERE `id` = {$this->id}
                                ");

        return ($rows > 0);
    }

    private function insertRecord($siteId, $limitationId, $name, $namespace, $group, $type, $active, $price, $codeBluesnap, $codeFastspring, $origin, $priceRegular)
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
                            `price_regular` = {$priceRegular},
                            `code_bluesnap` = {$codeBluesnap},
                            `code_fastspring` = {$codeFastspring},
                            `origin` = {$origin}
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
        
        if ($this->origin !== null && !in_array($this->origin, self::getAllowedTypes())) {
            throw new InvalidTypeException("Invalid product origin value!");
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
        $priceRegular = $this->escape($this->priceRegular);
        $codeBluesnap = $this->escape($this->codeBluesnap);
        $codeFastspring = $this->escape($this->codeFastspring);
        $origin = $this->escape($this->origin);

        if (!empty($this->id)) {
            return $this->updateRecord($siteId, $limitationId, $name, $namespace, $group, $type, $active, $price, $codeBluesnap, $codeFastspring, $origin, $priceRegular);
        } else {
            $this->id = $this->insertRecord($siteId, $limitationId, $name, $namespace, $group, $type, $active, $price, $codeBluesnap, $codeFastspring, $origin, $priceRegular);
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
    
    public static function getAllowedOrigins()
    {
        return self::$allowedOrigins;
    }

}
