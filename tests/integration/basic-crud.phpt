<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 29.06.18
 * Time: 14:36
 */

namespace Test;
use Phore\Dba\Entity\Entity;
use Phore\Dba\PhoreDba;
use Tester\Assert;

require __DIR__ . "/../../vendor/autoload.php";

@unlink ("/tmp/testdb.db3");
$odb = PhoreDba::InitDSN("sqlite:/tmp/testdb.db3");

$odb->query("
CREATE TABLE __TestEntity (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  name2 TEXT
)
");

class __TestEntity {
    use Entity;

    const __META__ = [
        'primaryKey' => 'id'
    ];

    public $id;
    public $name;
    public $name2;
}



// Insert entity and ensure changed properties are empty
$entity1 = new __TestEntity(["name"=>"AAA1"]);
$odb->insert($entity1);
Assert::equal("INSERT INTO __TestEntity (id, name, name2) VALUES (NULL, 'AAA1', NULL);", $odb->lastStatement);
Assert::equal([], $entity1->getChangedProperties());
Assert::equal(1, $odb->entityInstanceManager->getObservedEntityCount());

// Change a property and ensure the property is marked changed
$entity1->name = "AAA2";
Assert::equal(["name"], $entity1->getChangedProperties());

// Update and ensure changed properties is empty again
// Ensure only the changed property was updated
$odb->update($entity1);
Assert::equal("UPDATE __TestEntity SET name='AAA2' WHERE id='1';", $odb->lastStatement);
Assert::equal([], $entity1->getChangedProperties());

// Update again and ensure no query was sent at all
$odb->update($entity1);
Assert::equal(null, $odb->lastStatement);

// Test loading by primary key
$entity3 = __TestEntity::Cast($odb->load(__TestEntity::class, 1));
Assert::equal("SELECT * FROM __TestEntity WHERE id='1';", $odb->lastStatement);




// Load an Entity from Database and ensure changed properties are empty
$entity2 = __TestEntity::Cast($odb->load(__TestEntity::class, ["name"=>"AAA2"]));
Assert::equal([], $entity2->getChangedProperties());


$entity2->name2 = "abc";
Assert::equal(["name2"], $entity2->getChangedProperties());

// Delete the Property
$odb->delete($entity2);
Assert::equal("DELETE FROM __TestEntity WHERE id='1'", $odb->lastStatement);

unset($entity1);
unset($entity2);
unset($entity3);

// After unsetting, entitymanager should have no more entities observed
Assert::equal(0, $odb->entityInstanceManager->getObservedEntityCount());

