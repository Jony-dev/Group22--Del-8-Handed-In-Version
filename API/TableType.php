<?php
function getAllTableTypes(){

    $db = db::getConnection();
    $query = "SELECT * FROM table_type";
    $result = $db->query($query);
    $tabletypes = [];
    while( $record = $result->fetch_object())
    {
        $tabletype = new stdClass();
        $tabletype->id = $record->id;
        $tabletype->type = $record->type;
        $tabletype->deleted = $record->deleted;
        $tabletypes[] = $tabletype;
    }

    return $tabletypes;
}
//create
function createATableType($type)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM table_type";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->type == $type)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO table_type (type) VALUES ('$type')";
    $result2 = $db->query($query2);

    if($result2)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}