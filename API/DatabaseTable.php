<?php
function getAllDatabaseTables(){

    $db = db::getConnection();
    $query = "SELECT * FROM database_table";
    $result = $db->query($query);
    $databasetables = [];
    while( $record = $result->fetch_object())
    {
        $databasetable = new stdClass();
        $databasetable->id = $record->id;
        $databasetable->name = $record->name;
        $databasetables[] = $databasetable;
    }

    return $databasetables;
}
function createADbTable($name)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM database_table";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->name == $name)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO database_table (name) VALUES ('$name')";
    $result = $db->query($query2);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}