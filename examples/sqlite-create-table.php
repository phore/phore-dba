<?php
/**
 * Created by PhpStorm.
 * User: matthias
 * Date: 30.04.19
 * Time: 12:51
 */

namespace Test;

use Phore\Dba\Entity\Entity;
use Phore\Dba\PhoreDba;

require __DIR__ . "/../vendor/autoload.php";


/**
 * Class ParkingLot
 * @package Test
 * @internal
 */
class ParkingLot {
    use Entity;

    const __META__ = [
        'primaryKey' => 'parkingLotId'
    ];

    public $parkingLotId;
    public $name;
}

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


$db = PhoreDba::InitDSN("sqlite:/tmp/demo.slite");



if ($db->query("SELECT COUNT(name) as num FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->first("num") === 0) {
    // Create the schema if no tables found.
    $db->multi_query('

    CREATE TABLE ParkingLot (
      parkingLotId INTEGER PRIMARY KEY,
      name TEXT NOT NULL
    );
    CREATE TABLE Car (
      carId INTEGER PRIMARY KEY,
      name TEXT NOT NULL,
      parkingLot INTEGER,
      FOREIGN KEY (parkingLot) REFERENCES ParkingLot (parkingLotId) ON DELETE CASCADE  ON UPDATE NO ACTION 
    );
    ');
}


$pl = new ParkingLot(["parkingLotId"=>1, "name"=>"Lot 1"]);
$db->insert($pl);

$car = new Car(["carId"=>1, "name"=>"Car 1", "parkingLot"=>$pl]);
$db->insert($car);


print_r ($db->query("SELECT * FROM ParkingLot")->all());

