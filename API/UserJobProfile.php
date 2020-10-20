<?php

require_once "./Notification.php";

require_once("db.php");

function getAllUserJobProfiles(){

    $db = db::getConnection();
    $query = "SELECT * FROM user_job_profile";
    $result = $db->query($query);
    $userjobprofiles = [];
    while( $record = $result->fetch_object()){

        $userjobprofile = new stdClass();
        $userjobprofile->userjobid = $record->id;
        $userjobprofile->userid = $record->user_id;
        $userjobprofile->jobid= $record->job_id;
        $userjobprofile->Locationid = $record->location_id;
        $userjobprofile->Scheduleid = $record->schedule_id;
        $userjobprofile->DepartmentId = $record->department_id;
        $userjobprofile->Salary = $record->salary;
        $userjobprofile->contract = $record->contract;
        $userjobprofile->enddate = $record->endDate;
        $userjobprofile->startdate = $record->startDate;
        $userjobprofile->createdby = $record->createdBy;
        $userjobprofile->updatedby = $record->updatedBy;
        $userjobprofile->createddate = $record->createdDate;
        $userjobprofile->updateddate = $record->updatedDate;
        $userjobprofile->deleted = $record->deleted;
        $userjobprofiles[] = $userjobprofile;
    }

    return $userjobprofiles;
}

function createAUserJobProfile($user_id,$job_id,$location_id,$schedule_id,$department_id,$salary,$startDate,$endDate,$user)
{   
    $db = db::getConnection();

    $query2 = "INSERT INTO user_job_profile (user_id, job_id,location_id, schedule_id,department_id, salary, startDate, endDate, createdBy, updatedBy) VALUES ($user_id,$job_id,$location_id,$schedule_id,$department_id,$salary,$startDate,$endDate,$user,$user)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 53)";
    $result3 = $db->query($query3);

    $notification2 = createANotification($user,"You have created a user job profile.");

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
function updateAUserJobProfile($id,$user_id,$job_id,$location_id,$schedule_id,$department_id,$salary,$startDate,$endDate,$user)
{
    $db = db::getConnection();
    
  
    $query2 = "UPDATE user_job_profile SET user_id=$user_id, job_id=$job_id,location_id= $location_id, schedule_id = $schedule_id,department_id = $department_id, salary=$salary, startDate = $startDate, endDate = $endDate, updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 53)";
    $result3 = $db->query($query3);

    $notification2 = createANotification($user,"You have updated a user job profile");

    if($result && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}