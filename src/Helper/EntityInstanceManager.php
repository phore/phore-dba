<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 17.07.18
 * Time: 19:43
 */

namespace Phore\Dba\Helper;




class EntityInstanceManager
{

    private $entities = [];


    public function push(string $clasName, $pkVal, array $data)
    {
        if ($pkVal == null)
            throw new \InvalidArgumentException("primary key value must not be null.");
        $this->entities[$clasName . "#" . $pkVal] = $data;
    }


    public function destroy(string $className, $pkVal)
    {
        if ($pkVal == null)
            throw new \InvalidArgumentException("primary key value must not be null.");
        unset ($this->entities[$className . "#" . $pkVal]);
    }

    public function getObservedEntityCount ()
    {
        return count($this->entities);
    }

    public function get (string $className, $pkVal)
    {
        if ($pkVal == null)
            throw new \InvalidArgumentException("primary key value must not be null.");
        $key = $className . "#" . $pkVal;
        if ( ! isset ($this->entities[$key]))
            throw new \InvalidArgumentException("This entity is not observed by entityManager. Assure to load the entity using ::load() method. (StorKey: '$key')");
        return $this->entities[$key];
    }

    public function isChanged ($entity, string $propertyName) : bool
    {
        if ( ! $entity->isPersistent())
            return true;
        $metaWrapper = new EntityObjectAccessHelper($entity);
        return $entity->$propertyName !== $this->get(get_class($entity), $entity->{$metaWrapper->getPrimaryKey()})[$propertyName];
    }

    public function getChangedProperties($entity) : array
    {
        $changedProperties = [];
        $metaWrapper = new EntityObjectAccessHelper($entity);
        foreach ($metaWrapper->getProperties() as $property) {
            if ($this->isChanged($entity, $property->name)) {
                $changedProperties[] = $property->name;
            }
        }
        return $changedProperties;
    }


}