<?php
function getAllUserRoles(){

    $db = db::getConnection();
    $query = "SELECT * FROM user_role";
    $result = $db->query($query);
    $userroles = [];
    while( $record = $result->fetch_object())
    {
        $userrole = new stdClass();
        $userrole->userId = $record->user_id;
        $userrole->roleId = $record->role_id;
        $userrole->createdby = $record->createdBy;
        $userrole->updatedby = $record->updatedBy;
        $userrole->createddate = $record->createdDate;
        $userrole->updateddate = $record->updatedDate;
        $userrole->deleted = $record->deleted;
        $userroles[] = $userrole;
    }

    return $userroles;
}

function getAllHiringTeam(){

    $db = db::getConnection();
    $recruiterQuery = "SELECT user_id FROM user_role WHERE role_id = 4";
    $recruiterResult = $db->query($recruiterQuery);

    $managerQuery = "SELECT user_id FROM user_role WHERE role_id = 2 OR role_id = 6";
    $managerResult = $db->query($managerQuery);

    $recruiters = [];
    $managers = [];

    while($recruiterRecord = $recruiterResult->fetch_object()){
        if($recruiterRecord->deleted == false)
        {
            $userRQuery = "SELECT * FROM user_profile WHERE id = $recruiterRecord->user_id";
            $userRResult = $db->query($userRQuery);
            
            $userRRecord = $userRResult->fetch_object();

            $recruiter = new stdClass();
            $recruiter->id = $userRRecord->id;
            $recruiter->name = $userRRecord->name;
            $recruiter->surname = $userRRecord->surname;

            $recruiters[] = $recruiter;
        }
    }

    while($managerRecord = $managerResult->fetch_object()){
        if($managerRecord->deleted == false)
        {
            $userMQuery = "SELECT * FROM user_profile WHERE id = $managerRecord->user_id";
            $userMResult = $db->query($userMQuery);
            
            $userMRecord = $userMResult->fetch_object();

            $manager = new stdClass();
            $manager->id = $userMRecord->id;
            $manager->name = $userMRecord->name;
            $manager->surname = $userMRecord->surname;

            $managers[] = $manager;
        }
    }

    $hiringTeam = new stdClass();
    $hiringTeam->recruiterList = $recruiters;
    $hiringTeam->managerList = $managers;
     
    return $hiringTeam;
}
