<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 18.04.18
 * Time: 11:55
 */

namespace Phore\Dba\Entity;


use Phore\Dba\Ex\NoDataException;
use Phore\Dba\Helper\EntityObjectAccessHelper;
use Phore\Dba\PhoreDba;

trait Entity
{
    /**
     * Entity constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->push($data);
    }

    /**
     * Load a entity from database
     *
     * SomeEntity::Load(1234); // Load by Primary Key
     * SomeEntity::Load(["prop1"=>"val1", "prop2"=>"val2"])
     *
     *
     * @param $restrictionsOrPkValue
     *
     *
     *
     * @return Entity
     * @throws \Exception
     */
    public static function Load($restrictionsOrPkValue) : self
    {
        try {
            return PhoreDba::Get()->load(
                get_called_class(),
                $restrictionsOrPkValue
            );
        } catch (NoDataException $e) {
            throw new NoDataException(get_called_class() . "::Load(): No matching data found in database.", 0, $e);
        }
    }


    public function isPersistent() : bool
    {
        $eoa = new EntityObjectAccessHelper($this);
        return $eoa->getPrimaryKeyValue() !== null;
    }


    public static function Cast($input) : self
    {
        $class = get_called_class();
        if ($input instanceof $class)
            return $input; // Cast only
        $entity = new $class();
        $wrapper = new EntityObjectAccessHelper($entity);
        $wrapper->pushDataIntoObject($input);
        return $entity;
    }

    /**
     * Set the data from array,
     *
     * Retuns: Array with changed property names
     *
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function push(array $data) : array
    {
        $eoa = new EntityObjectAccessHelper($this);
        $changed = [];
        foreach ($eoa->getProperties() as $property) {
            if (isset($data[$property->name]) && $eoa->getPrimaryKey() !== $property->name) {
                $eoa->setData($property->name, $data[$property->name]);
                if ($this->isChanged($property->name))
                    $changed[] = $property->name;
            }
        }
        return $changed;
    }


    public function isChanged(string $propertyName) : bool
    {
        return PhoreDba::Get()->entityInstanceManager->isChanged($this, $propertyName);
    }

    public function getChangedProperties() : array
    {
        return PhoreDba::Get()->entityInstanceManager->getChangedProperties($this);
    }

    public function __destruct()
    {
        $wrapper = new EntityObjectAccessHelper($this);
        PhoreDba::Get()->entityInstanceManager->destroy(get_class($this), $wrapper->getPrimaryKeyValue());
    }
}