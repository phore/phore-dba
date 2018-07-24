<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 08.03.18
 * Time: 15:09
 */

namespace Phore\Dba\Driver;


class PdoDbDriverResult implements DbDriverResult
{
    private $result;

    public function __construct(\PDOStatement $result)
    {
        $this->result = $result;
    }

    /**
     * fetches the next row
     *
     * Returns false if no data available
     *
     * @return array|null
     */
    public function fetch()
    {

        $data = $this->result->fetch(\PDO::FETCH_ASSOC);

        if ($data === false)
            return null;
        return $data;
    }

    /**
     * returns the number of affected row when SELECT- UPDATE- or INSERT-Statement was used
     *
     * @return int
     */
    public function rowCount(): int
    {
        // RowCount is not working with PDO! see https://stackoverflow.com/questions/883365/row-count-with-pdo#883382
        return $this->result->rowCount();
    }

    public function __destruct()
    {
        $this->result->closeCursor();
    }

}