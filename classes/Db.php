<?php
namespace App\Classes;

class Db
{
    protected $dbh;

    public function __construct($config)
    {
        $dsn = 'mysql:dbname=' . $config->dbname . ';host=' . $config->host;
        $this->dbh = new \PDO($dsn, $config->user, $config->password);
    }


    function dbSelect($sql, $params = [])
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        $res = ($sth->fetchAll(\PDO::FETCH_OBJ));
        return $res;
    }

//    function dbSelect($class, $sql, $params = [])
//    {
//        $sth = $this->dbh->prepare($sql);
//        $sth->execute($params);
//        $res = ($sth->fetchAll(\PDO::FETCH_CLASS, $class));
//        return $res;
//    }



    function dbGetId()
    {
        return $this->dbh->lastInsertId();
    }


    function dbExecute($sql, $params = [])
    {
        $sth = $this->dbh->prepare($sql);
        return $sth->execute($params);
    }

}


