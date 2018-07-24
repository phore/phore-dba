<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 07.03.18
 * Time: 16:24
 */

namespace Phore\Dba\Driver;



interface DbDriver
{
    public function query(string $stmt) : DbDriverResult;

    public function escape(string $input) : string ;

    public function getLastInsertId() : string ;
}