<?php
function getAllJustifications(){

    $db = db::getConnection();
    $query = "SELECT * FROM justification";
    $result = $db->query($query);
    $justifications = [];
    while( $record = $result->fetch_object())
    {
        $justification = new stdClass();
        $justification->id = $record->id;
        $justification->justification = $record->justification;
        $justifications[] = $justification;
    }

    return $justifications;
}

function createAJustification($justification, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM justification";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->justification == $justification)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO justification (justification) VALUES ('$justification')";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 24)";
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
    function updateAJustification($id, $justification, $user)
    {
        $db = db::getConnection();
    
        $query = "SELECT * FROM justification";
        $result = $db->query($query);
    
        while($record = $result->fetch_object())
        {
            if($record->justification == $justification)
            {
                return "duplicate";
            }
        }
    
    
        $query2 = "UPDATE justification SET justification='$justification' WHERE id = $id";
        $result = $db->query($query2);
    
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 24)";
        $result1 = $db->query($query3);
    
        if($result)
        {
            return "worked";
        }
        else{
            return "did not work";
        }
    }