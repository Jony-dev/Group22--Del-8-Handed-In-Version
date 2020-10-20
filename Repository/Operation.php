<?php
function getAllOperations(){

    $db = db::getConnection();
    $query = "SELECT * FROM operation";
    $result = $db->query($query);
    $operations = [];
    while( $record = $result->fetch_object())
    {
        $operation = new stdClass();
        $operation->operationid = $record->id;
        $operation->operationname= $record->operation;
        $operations[] = $operation;
    }

    return $operations;
}


function createAOperation($operation)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM operation";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->operation == $operation)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO operation (operation) VALUES ('$operation')";
    $result = $db->query($query2);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}