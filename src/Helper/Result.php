<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 08.03.18
 * Time: 11:22
 */

namespace Phore\Dba\Helper;


use Phore\Dba\Driver\DbDriverResult;
use Phore\Dba\Ex\NoDataException;

class Result
{
    public $driverResult;

    /**
     * Result constructor.
     * @param DbDriverResult $result
     */
    public function __construct(DbDriverResult $result)
    {
        $this->driverResult = $result;
    }


    public function rowCount() : int
    {
        return $this->driverResult->rowCount();
    }

    /**
     * @param $obj
     * @return mixed
     * @throws \Exception
     */
    public function first() : array
    {
        $data = $this->driverResult->fetch();
        if ($data === null)
            throw new NoDataException("first(): No data matched request.");
        return $data;
    }

    /**
     * @param string $classname
     * @return array
     * @throws \Exception
     */
    public function all(string $classname = null): array
    {
        if (empty ($this->driverResult)) {
            throw new \Exception("no result found");
        }
        $ret = [];
        while(($row = $this->driverResult->fetch()) !== null){
            if ($classname === null) {
                $ret[] = $row;
                continue;
            }
            $meta = new EntityObjectAccessHelper($obj = new $classname());
            $meta->pushDataIntoObject($row);
            $ret[] = $obj;
        }
        return $ret;
    }

    /**
     * @param $fn
     * @return self
     * @throws \Exception
     */
    public function each(callable $fn): self
    {
        $index = 0;
        while(($row = $this->driverResult->fetch()) !== null) {
            $ret = $fn($row, $index++);
            if ($ret === false){
                break;
            }
        }
        return $this;
    }

}