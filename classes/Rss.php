<?php
namespace App\Classes;

class Rss
{
    public $id;
    public $url;
    public $created_at;
    public $title;
    public $description;
    public $type;
    public $budget;
    public $engagement;
    public $engagement_weeks;
    public $contractor_tier;
    public $skills;

    public static function findAll($db)
    {
        $sql = 'SELECT * FROM rss ORDER BY created_at DESC';
        return $db->dbSelectObj($sql);
    }

    public static function findOne($db, $id)
    {
        $class = static::class;
        $sql = 'SELECT * FROM rss WHERE id = :id';
        return $db->dbSelect($class, $sql, [':id' => $id])[0];
    }

    public function insert($db)
    {
        $properties = get_object_vars($this);
        $columns = array_keys($properties);
        $places = [];
        $data = [];
        foreach ($columns as $property) {
            $places[] = ':' . $property;
            $data[':' . $property] = $this->$property;
        }
        $sql = 'INSERT INTO rss (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $places) . ')';
        return $db->dbExecute($sql, $data);
    }


}