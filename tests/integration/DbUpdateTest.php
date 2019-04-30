<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 30.04.19
 * Time: 16:49
 */

namespace Test;


use Phore\Dba\Entity\Entity;
use Phore\Dba\PhoreDba;
use PHPUnit\Framework\TestCase;


/**
 * Class Car
 * @package Test
 * @internal
 */
class Car {
    use Entity;

    const __META__ = [
        'primaryKey' => 'carId'
    ];


    public $carId;
    public $name;
    public $parkingLot;
}


/**
 * Class DbUpdateTest
 * @package Test
 * @internal
 */
class DbUpdateTest extends TestCase
{

    /**
     * @var PhoreDba
     */
    private static $db = null;

    public static function setUpBeforeClass(): void
    {

        system("rm /tmp/demo.db");
        self::$db = PhoreDba::InitDSN("sqlite:/tmp/demo.db");

        self::$db->multi_query('
        
            CREATE TABLE Car (
              carId INTEGER PRIMARY KEY,
              name TEXT NOT NULL,
              parkingLot INTEGER
            );
        ');
    }

    public function testCreateEntity()
    {
        $db = self::$db;

        $car = new Car(["name"=>"my car's name", "parkingLot" => 3]);
        $db->insert($car);

        $this->assertEquals(1, $db->query("SELECT COUNT(*) as c FROM Car")->first("c"));
    }

    public function testLoadEntity()
    {
        $db = self::$db;

        $car = $db->load(Car::class, 1);
        $this->assertEquals(1, $car->carId);

        $car = Car::Load(1);
        $this->assertEquals(1, $car->carId);

        $car = Car::Load(["name" => "my car's name"]);
        $this->assertEquals(1, $car->carId);
    }

    public function testUpdateEntity()
    {
        $db = self::$db;

        $car = $db->load(Car::class, 1);
        $this->assertEquals("my car's name", $car->name);

        $car->name = "car2";
        $this->assertEquals(["name"], $car->getChangedProperties());

        $db->update($car);
        $this->assertEquals("car2", $db->query("SELECT name FROM Car WHERE carId = 1")->first("name"));
    }


    public function testDeleteEntity()
    {
        $db = self::$db;

        $car = $db->load(Car::class, 1);

        $db->delete($car);
        $this->assertEquals(0, $db->query("SELECT COUNT(*) as c FROM Car WHERE carId = 1")->first("c"));

    }

}