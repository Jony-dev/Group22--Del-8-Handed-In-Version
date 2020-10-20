<?php

require_once "./Notification.php";

require_once("db.php");

function getAllUserTypes(){

    $db = db::getConnection();
    $query = "SELECT * FROM user_type";
    $result = $db->query($query);
    $usertypes = [];
    while( $record = $result->fetch_object()){

        $usertype = new stdClass();
        $usertype->usertypeid = $record->id;
        $usertype->type = $record->type;
        $usertypes[] = $usertype;
    }

    return $usertypes;
}

function createAUserType($type, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM user_type";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->type == $type)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO user_type (type) VALUES ('$type')";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 56)";
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
function updateAUserType($id,$type, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM user_type";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->type == $type)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE user_type SET type='$type' NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 56)";
    $result1 = $db->query($query3);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}