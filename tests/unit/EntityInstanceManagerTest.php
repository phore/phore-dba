<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 30.04.19
 * Time: 15:22
 */

namespace Test;




use Phore\Dba\Helper\EntityInstanceManager;
use PHPUnit\Framework\TestCase;

class EntityInstanceManagerTest extends TestCase
{


    public function testLoadCorrectly()
    {
        $em = new EntityInstanceManager();
        $em->push("SomeClass", "1", ["name"=>"name"]);
        $this->assertEquals(["name"=>"name"], $em->get("SomeClass", "1"));
    }


    public function testDestroy()
    {
        $em = new EntityInstanceManager();
        $em->push("SomeClass", "1", ["name"=>"name"]);
        $this->assertEquals(["name"=>"name"], $em->get("SomeClass", "1"));

        $this->expectException(\InvalidArgumentException::class);
        $em->destroy("SomeClass", 1);
        $this->assertEquals(null, $em->get("SomeClass", "1"));
    }

    public function testDestroyMultiInstance()
    {
        $em = new EntityInstanceManager();
        $em->push("SomeClass", "1", ["name"=>"name"]);
        $em->push("SomeClass", "1", ["name"=>"name"]);
        $this->assertEquals(["name"=>"name"], $em->get("SomeClass", "1"));

        $this->expectException(\InvalidArgumentException::class);
        $em->destroy("SomeClass", 1);
        $this->assertEquals(["name"=>"name"], $em->get("SomeClass", "1"));

        $em->destroy("SomeClass", 1);
        $this->assertEquals(null, $em->get("SomeClass", "1"));
    }


}