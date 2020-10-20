<?php

require_once("db.php");

function getAllJobs(){

    $db = db::getConnection();
    $query = "SELECT * FROM job";
    $result = $db->query($query);
    $jobs = [];
    while( $record = $result->fetch_object()){

        $job = new stdClass();
        $job->id = $record->id;
        $job->name = $record->job_name;

        $jobs[] = $job;
    }

    return $jobs;
}

function getAllDeletedJobs(){

    $db = db::getConnection();
    $query = "SELECT * FROM job WHERE deleted = 0";
    $result = $db->query($query);
    $jobs = [];
    while( $record = $result->fetch_object()){

        $job = new stdClass();
        $job->id = $record->id;
        $job->name = $record->job_name;

        $jobs[] = $job;
    }

    return $jobs;
}

function createAJob($job_name, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM job";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->job_name == $job_name)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE job SET deleted=0, WHERE job_name = $job_name";
                $result1 = $db->query($query1);
                return "worked";
            }
        }
    }


    $query2 = "INSERT INTO job (job_name, createdBy, updatedBy, deleted) VALUES ('$job_name', '$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 15)";
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
function updateAJob($id,$job_name, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM job";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->job_name == $job_name)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE job SET job_name='$job_name',  updatedBy=$user, updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 15)";
    $result1 = $db->query($query3);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteAJob($id, $user)
{
    $db = db::getConnection();

    $requestQuery = "SELECT job_request.job_id 
    FROM job_request";
    $requestResult = $db->query($requestQuery);

    while($record = $requestResult->fetch_object())
    {
        if($record->job_id == $id)
        {
            $query1 = "UPDATE job SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $jobProfileQuery = "SELECT user_job_profile.job_id 
    FROM user_job_profile";
    $jobProfileResult = $db->query($jobProfileQuery);

    while($record = $jobProfileResult->fetch_object())
    {
        if($record->job_id == $id)
        {
            $query1 = "UPDATE job SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $deleteQuery = "DELETE FROM job WHERE id = $id";
    $deleteJob = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 15)";
    $result4 = $db->query($query4);

    if($deleteJob && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}

  // search by name
  function searchAllJobs($job_name, $user){

    $db = db::getConnection();
    $query = "SELECT * FROM job WHERE job_name LIKE '%$job_name%'";
    $result = $db->query($query);

    $jobs = [];

    while( $record = $result->fetch_object()){
        if($record->deleted == false)
        {
            $job = new stdClass();
            $job->id = $record->id;
            $job->name = $record->job_name;

            $jobs[] = $job;
        }      
        
    }
    
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 5, 15)";
    $result3 = $db->query($query3);

    return $jobs;
}
// by id
function getAllJobsById($id, $user){

    $db = db::getConnection();
    $query = "SELECT * FROM job WHERE id = $jobId";
    $result = $db->query($query);

    $jobs = [];

    while($record = $result->fetch_object()){
        if($record->deleted == false)
        {
            $job = new stdClass();
            $job->id = $record->id;
            $job->name = $record->job_name;

            $jobs[] = $job;
        }
    }
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 5, 15)";
    $result3 = $db->query($query3);
    
    return $jobs;
    
        
}
