<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 18.04.18
 * Time: 11:55
 */

namespace Phore\Dba\Entity;


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
        foreach ($data as $colName => $value){
            if(property_exists($class = __TRAIT__, $colName)){
                throw new \InvalidArgumentException("Property does not exists in $class");
            }
            $this->$colName = $value;
        }
    }
    
    
    public static function Cast($input, $pkField="") : self
    {
        $class = get_called_class();
        if ($input instanceof $class)
            return $input; // Cast only
        $entity = new $class();
        $wrapper = new EntityObjectAccessHelper($entity);
        $wrapper->pushDataIntoObject($input);
        return $entity;
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