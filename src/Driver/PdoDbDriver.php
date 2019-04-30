<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 08.03.18
 * Time: 15:00
 */

namespace Phore\Dba\Driver;


use Phore\Dba\Ex\QueryException;

class PdoDbDriver implements DbDriver
{
    private $connection;

    /**
     * PdoDbDriver constructor.
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        // We want exceptions!
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->connection = $connection;
    }

    public function query(string $stmt): DbDriverResult
    {
        try {
            $query = $this->connection->query($stmt);
            //$query->execute();
        } catch (\PDOException $e) {
            throw new QueryException("Query failed: '$stmt' error: {$e->getMessage()}", (int)$e->getCode());
        }
        return new PdoDbDriverResult($query);
    }

    public function escape(string $input): string
    {
       return $this->connection->quote($input);
    }

    public function getLastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Execute raw query. May contain multiple Statements.
     *
     * @param string $stmt
     */
    public function multi_query(string $stmt): void
    {
        $this->connection->exec($stmt);
    }
}