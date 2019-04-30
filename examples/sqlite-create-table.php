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

$db->query("SELECT COUNT(name) as num FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->dump();

if ($db->query("SELECT COUNT(name) as num FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->first("num") == 0) {

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
$db->lastResult->dump();


$pl = new ParkingLot(["parkingLotId"=>1, "name"=>"Lot 1 with long name"]);
$db->insert($pl);

$car = new Car(["carId"=>1, "name"=>"Car 1 with long name", "parkingLot"=>$pl]);
$db->insert($car);



$db->query("SELECT * FROM ParkingLot")->dump();

$db->query("SELECT * FROM Car")->dump();


// Query and Cast (Allowing multiple connections)
$parkingLot = ParkingLot::Cast($db->load(ParkingLot::class, 1));

// Quick query and update
$parkingLot = ParkingLot::Load(["parkingLotId"=>2]);

$parkingLot->name = "New name2";
$db->update($parkingLot);


// Resolve Foreign Keys
$car = Car::Load(1);
$parkingLot = ParkingLot::Load($car->parkingLot);


$db->query("SELECT c.name as cname, p.name as pname, * FROM ParkingLot as p LEFT JOIN Car as c ON p.parkingLotId = c.parkingLot")->dump();
