<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 12.03.18
 * Time: 16:30
 */

namespace Phore\Dba\Driver;


class MySqliDriverResult implements DbDriverResult
{

    private $result;
    private $affectedRows;

    public function __construct($result, $affectedRows)
    {
        $this->result = $result;
        $this->affectedRows = $affectedRows;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function fetch(): array
    {
        if($this->result === true){
            throw new \Exception("execute a select-query fist");
        }
        return $this->result->fetch_assoc();
    }

    public function rowCount(): int
    {
        return $this->affectedRows;
    }
}