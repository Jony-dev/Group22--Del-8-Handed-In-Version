<?php
function getAllJobTests(){

    $db = db::getConnection();
    $query = "SELECT * FROM job_test";
    $result = $db->query($query);
    $jobtests = [];
    while( $record = $result->fetch_object())
    {
        $jobtest = new stdClass();
        $jobtest->testid = $record->test_id;
        $jobtest->cardid = $record->card_id;
        $jobtest->critical = $record->critical;
        $jobtest->deleted = $record->Deleted;
        $jobtests[] = $jobtest;
    }

    return $jobtests;
}

function createAJobTest($test_id,$card_id,$critical,$user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM job_test WHERE (card_id=$card_id AND test_id=$test_id)";
    $result = $db->query($query);

    while($result AND $record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
            return "duplicate";
        }
        else
        {
            $query1 = "UPDATE job_test SET deleted=0, WHERE (card_id = $card_id) AND (test_id = $test_id)";
            $result1 = $db->query($query1);

            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 23)";
            $result3 = $db->query($query3);

            if($result1 && $result3)
            {
                return "worked";
            }
            else
            {
                return "did not work";
            }
        }
    }


    $query2 = "INSERT INTO job_test (card_id, test_id, critical) VALUES ($card_id,$test_id,$critical)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 23)";
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
function updateAJobTest($test_id,$card_id,$critical,$user)
{
    $db = db::getConnection();
    
    $query= "UPDATE job_test SET critical=$critical WHERE (test_id=$test_id AND card_id=$card_id)";
    $result = $db->query($query);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 23)";
    $result3 = $db->query($query3);

    if($result && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}