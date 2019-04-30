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

    /**
     * Execute raw query. May contain multiple Statements.
     *
     * @param string $stmt
     */
    public function multi_query(string $stmt) : void;

    public function escape(string $input) : string ;

    public function getLastInsertId() : string ;
}