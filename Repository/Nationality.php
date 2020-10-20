<?php

function getAllNationalities(){

    $db = db::getConnection();
    $query = "SELECT * FROM nationality";
    $result = $db->query($query);
    $nationalities = [];
    while( $record = $result->fetch_object())
    {
        $nationality = new stdClass();
        $nationality->nationalityId= $record->id;
        $nationality->nationality = $record->nationality;
        $nationalities[] = $nationality;
    }

    return $nationalities;
}


function createANationality($nationality, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM nationality";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->nationality == $nationality)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO nationality (nationality) VALUES ('$nationality')";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 29)";
    $result3 = $db->query($query3);

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

// update 
function updateANationality($id,$nationality, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM nationality";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->nationality == $nationality)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE nationality SET nationality='$nationality' WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 29)";
    $result1 = $db->query($query3);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}