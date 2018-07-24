<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 07.03.18
 * Time: 16:38
 */

namespace Phore\Dba;


use Phore\Dba\Driver\DbDriver;
use Phore\Dba\Driver\DbDriverResult;
use Phore\Dba\Driver\PdoDbDriver;
use Phore\Dba\Entity\Entity;
use Phore\Dba\Helper\EntityInstanceManager;
use Phore\Dba\Helper\EntityObjectAccessHelper;
use Phore\Dba\Helper\Result;

class PhoreDba
{
    /**
     * @var DbDriver
     */
    protected $driver;

    /**
     * Debugging: The last statement executed.
     *
     * @var string
     */
    public $lastStatement;

    /**
     * @var EntityInstanceManager
     */
    public $entityInstanceManager;

    protected function __construct(DbDriver $driver)
    {
        $this->driver = $driver;
        $this->entityInstanceManager = new EntityInstanceManager();
    }

    /**
     * @param $obj
     * @return DbDriverResult
     * @throws \Exception
     */
    public function insert($obj) : self
    {
        $meta = new EntityObjectAccessHelper($obj);
        $keys = [];
        $values = [];

        foreach ($meta->getProperties() as $col) {
            $keys[] = $col->colName;
            $colValue = $col->value;
            if ($colValue === null) {
                $values[] = "NULL";
                continue;
            }
            $values[] = $this->driver->escape((string)$colValue);
        }

        $stmt = "INSERT INTO " . $meta->getTableName() . " (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $values) . ");";
        $this->lastStatement = $stmt;
        $this->driver->query($stmt);

        $primKey = $meta->getPrimaryKey();
        $obj->$primKey = $this->driver->getLastInsertId();

        $this->entityInstanceManager->push(get_class($obj), $obj->$primKey, $meta->getDataAssoc());
        return $this;
    }

    /**
     * @param $obj
     * @return DbDriverResult
     * @throws \Exception
     */
    public function update($obj, $forceUpdate=false) : self
    {
        $meta = new EntityObjectAccessHelper($obj);
        $values = [];
        $primKey = $meta->getPrimaryKey();

        foreach ($meta->getProperties() as $col) {
            if ($col->colName === $primKey)
                continue;
            if ( ! $obj->isChanged($col->name) && ! $forceUpdate)
                continue;

            $colValue = $col->value;

            if ($colValue === null) {
                $colValue = "NULL";
            } else {
                $colValue = $this->driver->escape((string)$colValue);
            }
            $values[] = $col->colName . "=" . $colValue;
        }

        if (count($values) == 0) {
            $this->lastStatement = null;
            return $this;
        }
        $stmt = "UPDATE " . $meta->getTableName() . " SET " . implode(", ", $values) . " WHERE " . $primKey . "=" . $this->driver->escape($obj->$primKey) . ";";
        $this->lastStatement = $stmt;
        $this->driver->query($stmt);
        $this->entityInstanceManager->push($meta->getClassName(), $meta->getPrimaryKeyValue(), $meta->getDataAssoc());
        return $this;
    }

    /**
     * @param $obj
     * @return self
     * @throws \Exception
     */
    public function delete($obj) : self
    {

        $meta = new EntityObjectAccessHelper($obj);
        $primKey = $meta->getPrimaryKey();
        $stmt = "DELETE FROM " . $meta->getTableName() . " WHERE " . $primKey . "=" . $this->driver->escape($obj->$primKey);
        $this->lastStatement = $stmt;
        $this->driver->query($stmt);
        unset($obj);
        return $this;
    }

    /**
     * Load one dataset from Database
     *
     * Parameter1:
     *  - The class name of the entity to load
     *
     * Parameter 2:
     *  - PrimaryKey Value
     *  - Array with restrictions
     *
     * @param $obj
     * @return Entity
     * @throws \Exception
     */
    public function load(string $className, $restrictionsOrPkValue)
    {
        $meta = new EntityObjectAccessHelper($obj = new $className());
        $values = [];

        if (is_array($restrictionsOrPkValue)) {
            foreach ($restrictionsOrPkValue as $property => $restrictionValue) {

                $colValue = $this->driver->escape((string)$restrictionValue);
                $values[] = $property."=".$colValue;
            }
            $where = "";
        } else {
            $values[] = $meta->getPrimaryKey()."=".$this->driver->escape($restrictionsOrPkValue);
        }
        if (!empty($values)) {
            $where = " WHERE " . implode(" AND " , $values);
        }
        $stmt = "SELECT * FROM " . $meta->getTableName() . $where . ";";
        $this->lastStatement = $stmt;
        $ret = $this->query($stmt);
        $data = $ret->first();
        $this->entityInstanceManager->push($className, $data[$meta->getPrimaryKey()], $data);
        $meta->setDataAssoc($data);

        return $obj;
    }

    /**
     * @param string $input
     * @param array  $args
     *
     * @return Result
     */
    public function query(string $input, array $args = []) : Result
    {
        $argsCounter = 0;

        $stmt = preg_replace_callback(
            '/\?|\:[a-z0-9_\-\.]+/i',
            function ($match) use (&$argsCounter, &$args) {
                if ($match[0] === '?') {
                    if(empty($args)){
                        throw new \Exception("Index $argsCounter missing");
                    }
                    $argsCounter++;
                    return $this->driver->escape(array_shift($args));
                }
                $key = substr($match[0], 1);
                if (!isset($args[$key])){
                    throw new \Exception("Key '$key' not found");
                }
                return $this->driver->escape($args[$key]);
            },
            $input
        );
        $this->lastStatement = $stmt;
        return new Result($this->driver->query($stmt));
    }

    private static $instance = null;

    /**
     * Sqlite3
     *
     * sqlite:/path/to/sqlite.db
     *
     * mysqli:
     *
     * mysqli:user:pass@server1,server2/dbname
     *
     * @param string $conStr
     *
     * @return PhoreDba
     */
    public static function InitDSN (string $conStr) : PhoreDba
    {
        $parts = parse_url($conStr);
        //print_r($parts);

        switch ($parts["scheme"]) {
            case "sqlite":
                $pdo = new \PDO("sqlite:{$parts["path"]}");
                self::$instance = new PhoreDba(new PdoDbDriver($pdo));
                return self::$instance;

            default:
                throw new \InvalidArgumentException("Invalid scheme '{$parts["scheme"]}'");
        }
    }



    
    public static function Init(DbDriver $driver): PhoreDba
    {
        self::$instance = new self($driver);
        return self::$instance;
    }

    public static function Get(): PhoreDba
    {
        if (self::$instance === null) {
            throw new \Exception("OttoDb not initialized call Init() first");
        }
        return self::$instance;
    }
}