<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 07.03.18
 * Time: 17:43
 */

namespace Phore\Dba\Helper;




class EntityObjectAccessHelper
{

    private $meta = [];
    private $obj;
    /**
     * @var \ReflectionObject
     */
    private $refl;

    /**
     * MetaWrapper constructor.
     * @param mixed $obj
     * @throws \Exception
     */
    public function __construct($obj)
    {
        if (!is_object($obj)) {
            throw new \Exception("invalid Object in Parameter 1");
        }
        $this->refl = new \ReflectionObject($obj);
        if (!$this->refl->getConstant('__META__')) {
            throw new \Exception("__META__ - constant missing");
        }
        $this->meta = $obj::__META__;
        $this->obj = $obj;
    }


    public function getClassName() : string
    {
        return $this->refl->getName();
    }

    public function getTableName(): string
    {
        return $this->refl->getShortName();
    }

    public function getPrimaryKey(): string
    {
        if (isset($this->meta['primaryKey'])) {
            return $this->meta['primaryKey'];
        }
        return "id";
    }


    public function getPrimaryKeyValue() {
        if ( ! $this->refl->hasProperty($this->getPrimaryKey()))
            throw new \InvalidArgumentException("Entity class '{$this->getClassName()}'::{$this->getPrimaryKey()}: Primary key property missing.");
        return $this->refl->getProperty($this->getPrimaryKey())->getValue($this->obj);
    }

    /**
     * @return MetaPropertyWrapper[]
     */
    public function getProperties() : array
    {
        $metaProperties = [];
        foreach ($this->refl->getProperties() as $property) {
            $metaProperties[] = new MetaPropertyWrapper(
                $property->getName(),
                $property->getName(),
                $property->getValue($this->obj)
            );
        }

        return $metaProperties;
    }

    public function getDataAssoc() : array
    {
        $ret = [];
        foreach ($this->refl->getProperties() as $curProp) {
            $ret[$curProp->getName()] = $curProp->getValue($this->obj);
        }
        return $ret;
    }


    public function setData($propertyName, $newValue)
    {
        $this->refl->getProperty($propertyName)->setValue($this->obj, $newValue);
    }

    public function getData($propertyName, $newValue)
    {
        return $this->refl->getProperty($propertyName)->getValue($this->obj);
    }

    public function setDataAssoc(array $data)
    {
        foreach ($this->refl->getProperties() as $curProp) {
            $curProp->setValue($this->obj, $data[$curProp->getName()]);
        }
    }

    public function pushDataIntoObject(array $data){
        foreach ($data as $key => $value) {
            $this->refl->getProperty($key)->setValue($this->obj, $value);
        }
    }
}