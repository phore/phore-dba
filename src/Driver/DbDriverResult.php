<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 07.03.18
 * Time: 16:27
 */

namespace Phore\Dba\Driver;


interface DbDriverResult
{
    /**
     * @return array|null
     */
    public function fetch();

    public function rowCount(): int;

}