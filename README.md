# Phore Dba (DatabaseAbstraction)

## TL;DR;

Phore-dba is a very simple Object-Relational Mapper for PHP 7.

It will map Objects to Tables using exact the same Names.

It will work with mysqli, sql, and PDO in gerneral.

## Installation  

Install otto-db using composer:

```
composer require phore/dba
```


## Example

- Create as Entity Class `SomeEntity` and define `__META__` data
- Initialize a Sqlite Connection to `/path/to/sqlite.db3`
- Create a Table `SomeEntity`
- Insert a new Entity for `name: someName` and `company: SomeCompany`
- `query()` all Entities and map them back to Entity using `each(callable $fn)`
- `update()` each entity, then `delete()` it (hm - it's just a demo)

```php
class SomeEntity {
    const __META__ = [ "primaryKey" => "id" ];
    public $id;
    public $name;
    public $company;
}

$odb = PhoreDba::InitDSN("sqlite:/path/to/sqlite.db3");
$odb->query ("CREATE TABLE IF NOT EXISTS SomeEntity (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT
    company TEXT
)");

$odb->insert(new SomeEntity(["name"=>"someName", "company"=>"SomeCompany"]));

$odb->query("SELECT * FROM SomeEntity WHERE name=?", ["UnescapedValue"])->each(
    function (array $row) use ($odb) {
        print_r ($entity = SomeEntity::Cast($row));
        $entity->name = "MyNewName";
        $odb->update($entity);
        $odb->delete($entity);
    }
);
```



## Loading from Database

```php

$entity = $odb->load(SomeEntity::class, 103878);
```

or - with object casting (IDE Code-Completion):

```php
$entity = SomeEntity::Cast($odb->load(SomeEntity::class, 103878));
```



## Working with entities

### Changed fields

```php
$entity = SomeEntity::Cast($odb->load(SomeEntity::class, 103878));
$entity->name = "new Name";
assert ($enetity->isChanged("name") === true)
```

### Destructing entities



## Database Migrations

Database Migrations are maintained by OttoDb binary:

```

```

