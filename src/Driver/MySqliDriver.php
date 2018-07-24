<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 12.03.18
 * Time: 16:27
 */

namespace Phore\Dba\Driver;


class MySqliDriver implements DbDriver
{
    private $connection;

    public function __construct(\mysqli $connection)
    {
        $this->connection = $connection;
    }


    /**
     * @param string $stmt
     * @return DbDriverResult
     * @throws \Exception
     */
    public function query(string $stmt): DbDriverResult
    {
        $query = $this->connection->query($stmt);

        if($query === false){
            $e = $this->connection->error;

            throw new \Exception("Statement $stmt failed with message: $e!");
        }

        return new MySqliDriverResult($query, $this->connection->affected_rows);
    }

    public function escape(string $input): string
    {
        return "'" . $this->connection->real_escape_string($input) . "'";
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLastInsertId(): string
    {
        $id = $this->connection->insert_id;

        if($id === 0){
          throw new \Exception("No Insert made!");
        }

        return $id;
    }
}