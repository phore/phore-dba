<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 07.03.18
 * Time: 17:59
 */

namespace Phore\Dba\Helper;


class MetaPropertyWrapper
{
    public $name;
    public $colName;
    public $value;

    public function __construct($name, $colName, $value)
    {
        $this->name = $name;
        $this->colName = $colName;
        $this->value = $value;
    }
}