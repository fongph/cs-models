<?php

namespace CS\Models;

/**
 * Description of RecordsIterator
 *
 * @author root
 */
class RecordsIterator implements \Iterator
{

    protected $container = array();
    protected $position = 0;

    public function __construct($data = array())
    {
        $this->container = $data;
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

}
