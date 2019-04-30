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
    /**
     * @var DbDriverResult
     */
    public $driverResult;

    /**
     * @var string|null
     */
    public $query;

    /**
     * Result constructor.
     * @param DbDriverResult $result
     */
    public function __construct(DbDriverResult $result, string $query = null)
    {
        $this->driverResult = $result;
        $this->query = $query;
    }


    /**
     * Returns the count of affected rows
     *
     * !! This is not the number of results !!
     * !! Use $db->query("SELECT COUNT(*) FROM Table")->first(0) to select the count !!
     *
     * @return int
     */
    public function rowCount() : int
    {
        return $this->driverResult->rowCount();
    }






    /**
     * Returns the row as array
     *
     * If parameter1 is set, returns the content of the column
     *
     * @param $obj
     * @return array|string
     * @throws \Exception
     */
    public function first(string $columnName = null)
    {
        $data = $this->driverResult->fetch();
        if ($data === null)
            throw new NoDataException("first(): No data matched request.");
        if ($columnName !== null) {
            if ( ! isset ($data[$columnName]))
                throw new \InvalidArgumentException("first(): Column '$columnName' not existing in result.");
            return $data[$columnName];
        }
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



    public function dump(bool $return=false)
    {
        $data = $this->all();
        // Print Header

        if (count($data) === 0) {
            echo "\ndump(): Empty result\n";
        }
        $header = array_keys($data[0]);

        echo "\ndump({$this->query}): " . count($data) . "rows.\n";
        foreach ($header as $col) {

        }

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