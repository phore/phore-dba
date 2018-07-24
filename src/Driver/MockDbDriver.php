<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 07.03.18
 * Time: 16:25
 */

namespace Phore\Dba\Driver;


class MockDbDriver implements DbDriver
{
    private $lastInsertIdIndex = 1;

    public $lastQuery;

    public function query(string $stmt): DbDriverResult
    {
        $this->lastQuery = $stmt;
        return new MockDbDriverResult();
    }

    public function escape(string $input): string
    {
        return "'" . addslashes($input) . "'";
    }

    public function getLastInsertId(): string
    {
        return $this->lastInsertIdIndex++;
    }
}