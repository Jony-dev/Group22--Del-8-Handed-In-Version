<?php
function getAllQuestionTypes(){

    $db = db::getConnection();
    $query = "SELECT * FROM question_type";
    $result = $db->query($query);
    $questiontypes = [];
    while( $record = $result->fetch_object())
    {
        $questiontype = new stdClass();
        $questiontype->id = $record->id;
        $questiontype->type = $record->type;
        $questiontypes[] = $questiontype;
    }

    return $questiontypes;
}

function createAQuestionType($type, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM question_type";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->type == $type)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO question_type (type) VALUES ('$type')";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 35)";
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