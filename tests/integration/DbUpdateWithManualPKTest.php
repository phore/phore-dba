<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 25.06.19
 * Time: 14:45
 */

namespace Test;


use Phore\Dba\Entity\Entity;
use Phore\Dba\Helper\EntityObjectAccessHelper;
use Phore\Dba\PhoreDba;
use PHPUnit\Framework\TestCase;

/**
 * Class EntityManual
 * @internal
 * @package Test
 */
class EntityManual {
    use Entity;


    const __META__ = [
        "primaryKey" => "id",
        "pkType" => "manual"
    ];

    public $id;
    public $name;

}


class DbUpdateWithManualPKTest extends TestCase
{

    protected static $db;

    public static function setUpBeforeClass(): void
    {

        system("rm /tmp/demo.db");
        PhoreDba::Destroy();
        self::$db = PhoreDba::InitDSN("sqlite:/tmp/demo.db");

        self::$db->multi_query('
        
            CREATE TABLE EntityManual (
              id TEXT PRIMARY KEY,
              name TEXT NOT NULL
                          );
        ');
    }


    public function testInsert()
    {
        $entity = new EntityManual(["id"=> "ABC", "name" => "XYZ"]);
        PhoreDba::Get()->insert($entity);

        $entity = EntityManual::Load("ABC");

        $this->assertEquals("XYZ", $entity->name);
        $this->assertEquals("ABC", $entity->id);

        $entity->name = "AAA";
        PhoreDba::Get()->update($entity);

        $entity = EntityManual::Load("ABC");
        $this->assertEquals("AAA", $entity->name);


    }

}