<?php

require_once "./Notification.php";

function getAllTests(){

    $db = db::getConnection();
    $query = "SELECT * FROM test";
    $result = $db->query($query);
    $tests = [];
    while( $record = $result->fetch_object())
    {
        $test = new stdClass();
        $test->testId = $record->id;
        $test->testName= $record->test_name;
        $test->url= $record->URL;
        $test->description= $record->description;
        $test->createdBy = $record->createdBy;
        $test->updatedBy = $record->updatedBy;
        $test->createdDate = $record->createdDate;
        $test->updatedDate = $record->updatedDate;
        $test->deleted = $record->deleted;
        $tests[] = $test;
    }

    return $tests;
}
// get Deleted Tests
function getAllDeletedTests(){

    $db = db::getConnection();
    $query = "SELECT * FROM test";
    $result = $db->query($query);
    $tests = [];
    while( $record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
        $test = new stdClass();
        $test->testId = $record->id;
        $test->testName= $record->test_name;
        $test->url= $record->URL;
        $test->description= $record->description;
        $test->createdBy = $record->createdBy;
        $test->updatedBy = $record->updatedBy;
        $test->createdDate = $record->createdDate;
        $test->updatedDate = $record->updatedDate;
        $test->deleted = $record->deleted;
        $tests[] = $test;
        }
    }

    return $tests;
}
// create
function createATest($test_name,$URL ,$description, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM test";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->test_name == $test_name && $record->URL == $URL)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE test SET deleted=0, WHERE test_name = $test_name";
                $result1 = $db->query($query1);
                return "worked";
            }
        }

        if($record->test_name == $test_name || $record->URL == $URL)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
        }
    }


    $query2 = "INSERT INTO test (test_name, URL, description, createdBy, updatedBy, deleted) VALUES ('$test_name', '$URL', '$description', '$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 51)";
    $result3 = $db->query($query3);

    $notification2 = createANotification($user,"You have created a test.");

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
function updateATest($id,$test_name,$URL ,$description, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM test";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->id != $id)
        {
            if($record->test_name == $test_name || $record->URL == $URL)
            {
                return "duplicate";
            }
        }        
    }


    $query2 = "UPDATE test SET test_name='$test_name',url='$URL' ,description='$description', updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 51)";
    $result1 = $db->query($query3);

    $notification2 = createANotification($user,"You have updated a test.");

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteATest($id, $user)
{
    $db = db::getConnection();

    $jobQuery = "SELECT job_test.test_id 
    FROM job_test";
    $jobResult = $db->query($jobQuery);

    while($record = $jobResult->fetch_object())
    {
        if($record->test_id == $id)
        {
            $query1 = "UPDATE test SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $deleteQuery = "DELETE FROM test WHERE id = $id";
    $deleteTest = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 51)";
    $result4 = $db->query($query4);

    $notification2 = createANotification($user,"You have deleted a test.");

    if($deleteTest && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}