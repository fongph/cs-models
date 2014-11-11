<?php

namespace CS\Models;

/**
 * Description of AbstractRecord
 *
 * @author root
 */
abstract class AbstractRecord
{

    /**
     * Database connection
     * 
     * @var PDO
     */
    protected $db;
    protected $id;
    protected $keys = array();
    protected $createdAt;
    protected $updatedAt;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public abstract function save();

    public abstract function load($id);

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function isNew()
    {
        return !($this->id > 0);
    }

    protected function escape($value, $default = null, $quoteDefault = false)
    {
        if ($value === null && $default !== null) {
            if (!$quoteDefault) {
                return $default;
            }

            return $this->db->quote($default);
        }

        return $this->db->quote($value);
    }

    public function loadFromArray(array $data)
    {
        foreach ($this->keys as $attribute => $key) {
            if (isset($data[$key])) {
                $this->$attribute = $data[$key];
            }
        }

        return $this;
    }

    protected function boolToNum($value)
    {
        return ($value > 0) ? 1 : 0;
    }
    
    protected function numToBool($value)
    {
        return $value > 0;
    }

}
