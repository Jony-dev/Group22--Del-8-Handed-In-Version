<?php

require_once "./Department.php";
require_once "./Notification.php";

function getAllTeams($user){

    $db = db::getConnection();
    $query = "SELECT * FROM team";
    $result = $db->query($query);
    $teams = [];
    while( $record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
        $team = new stdClass();
        $team->teamId = $record->id;
        $team->departmentId = $record->department_id;
        $team->name = $record->name;
        $team->description = $record->description;
        $team->createdBy = $record->createdBy;
        $team->updatedBy = $record->updatedBy;
        $team->createdDate = $record->createdDate;
        $team->updatedDate = $record->updatedDate;
        $team->deleted = $record->deleted;
        $teams[] = $team;
    }
}

    return $teams;
}

//create
function createATeam($department_id,$name, $description, $user)
    {   
        $db = db::getConnection();
    
        $query = "SELECT * FROM team";
        $result = $db->query($query);
    
        while($record = $result->fetch_object())
        {
            if($record->name == $name || ($record->department_id == $department_id && $record->description == $description))
            {
                if($record->deleted == false)
                {
                    return "duplicate";
                }
                else
                {
                    $query1 = "UPDATE team SET deleted=0 WHERE id = $record->id";
                    $result1 = $db->query($query1);
                    return "worked";                    
                }   
            }
        }
    
    
        $query2 = "INSERT INTO team (department_id, name, description, createdBy, updatedBy, deleted) VALUES ('$department_id','$name', '$description', '$user', '$user',0)";
        $result2 = $db->query($query2);
                                                                                            
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 49)";
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
    // search by id
    function getAllTeamsByID($id, $user){

        $db = db::getConnection();
        $query = "SELECT * FROM team WHERE id = $id";
        $result = $db->query($query);
    
        $departments = [];
    
        while( $record = $result->fetch_object()){
        if($record->deleted == false)
        {
        $team = new stdClass();
        $team->id = $record->id;
        $team->departmentId = $record->department_id;
        $team->name = $record->name;
        $team->description = $record->description;
        $team->createdBy = $record->createdBy;
        $team->updatedBy = $record->updatedBy;
        $team->createdDate = $record->createdDate;
        $team->updatedDate = $record->updatedDate;
        $team->deleted = $record->deleted;
        $teams[] = $team;
            }
            
        }
        
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 49)";
        $result3 = $db->query($query3);
    
        return $teams;
    }
    // search by name
    function searchAllTeams($name, $user){
    
        $db = db::getConnection();
        $query = "SELECT * FROM team WHERE name LIKE '%$name%' OR description LIKE '%$name%'";
        $result = $db->query($query);
    
        $departments = [];
    
        while( $record = $result->fetch_object()){
            if($record->deleted == false)
            {
                $team = new stdClass();
                $team->teamId = $record->id;
                $team->departmentId = $record->department_id;
                $team->name = $record->name;
                $team->description = $record->description;
                $team->createdBy = $record->createdBy;
                $team->updatedBy = $record->updatedBy;
                $team->createdDate = $record->createdDate;
                $team->updatedDate = $record->updatedDate;
                $team->deleted = $record->deleted;
                $teams[] = $team;
            }
            
        }
        
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 49)";
        $result3 = $db->query($query3);
    
        return $teams;
    }
    
// update
function updateATeam($id, $department_id,$name, $description, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM team";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->id != $id)
        {
            if($record->name == $name || ($record->department_id == $department_id && $record->description == $description))
            {
                if($record->deleted == false)
                {
                    return "duplicate";
                }
            }
        }        
    }


    $query2 = "UPDATE team SET department_id='$department_id', name='$name', description='$description', updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 49)";
    $result1 = $db->query($query3);


    if($result && $result1)
    {
        return "worked";
    }
    else{
        return "did not work";
    }

}

function getAllTeamsByDepartment($department_id, $user){

    $db = db::getConnection();
    $stmt = $db->prepare("SELECT name, department_id, description FROM team WHERE department_id = ? AND deleted = 0");
    $stmt->bind_param("i",$department_id);
    $stmt->execute();
    $rF = null;
    $rS = null;
    $rT = null;
    //$stmt->bind_result($rF,$rS,$rT);
    $resut = $stmt->get_result();
    $teams = [];

    while($record = $resut->fetch_object()){
            $team = new stdClass();
            $team->teamId = $record->name;
            $team->departmentId = $record->department_id;
            $team->name = $record->description;
            $teams[] = $team;
    }
    $stmt->close();
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 5, 49)";
    $result3 = $db->query($query3);

    return $teams;
}

function deleteATeam($id, $user)
{
    $db = db::getConnection();

    $teamMemberQuery = "DELETE FROM team_member WHERE team_id = $id";
    $teamMemberResult = $db->query($teamMemberQuery);


    $departmentQuery = "SELECT department.id, department.deleted 
    FROM team 
    INNER JOIN department on department.id = team.department_id  
    WHERE team.id = $id";

    $departmentResult = $db->query($departmentQuery);
    $departmentId = 0;
    $departmentDeleted = false;

    while($record = $departmentResult->fetch_object())
    {
        $departmentId = $record->id;
        $departmentDeleted = $record->deleted;
    }


    $numberQuery = "SELECT count(team.id)
    FROM team 
    WHERE team.department_id = $departmentId";

    $numberResult = $db->query($numberQuery);
    $row = $numberResult->fetch_row();
    $count = $row[0];

    $deleteQuery = "DELETE FROM team WHERE id = $id";
    $deleteTeam = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 49)";
    $result4 = $db->query($query4);

    if($count<2 && $departmentDeleted == 1)
    {
        $deleteDepartment = deleteADepartment($departmentId, $user);
    }


    if($deleteTeam && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getAllTeamCards($userId)
{
    $db = db::getConnection();
    $query = "SELECT team.id,team.department_id,team.name,team.description FROM team
    WHERE team.createdBy=42
    UNION 
    SELECT team.id,team.department_id,team.name,team.description FROM team
    INNER JOIN team_member 
    ON team.id=team_member.team_id
    WHERE team_member.user_id=42;";
    $result = $db->query($query);

    $teams = [];

    while($record = $result->fetch_object())
    {
        $team = new stdClass();
        if($record->createdBy = $userId)
        {
            $team->teamId = $record->id;
            $team->departmentId = $record->department_id;
            $team->name = $record->name;
            $team->description = $record->description;
            $team->position = "Owner";
            $teams[] = $team;
        }
        else
        {
            $team->teamId = $record->id;
            $team->departmentId = $record->department_id;
            $team->name = $record->name;
            $team->description = $record->description;
            $team->position = "Member";
            $teams[] = $team;
        }
    }

    return $teams;
}

function getTeamReport(){
    $db = db::getConnection();
    $query = "SELECT division.name AS 'divisionName', department.name AS 'departmentName', team.name AS 'teamName', user_profile.name AS 'userName',user_profile.surname AS 'userSurname'
    FROM division
    LEFT JOIN department
    ON division.id = department.division_id
    LEFT JOIN team
    ON department.id = team.department_id
    LEFT JOIN team_member
    ON team.id=team_member.team_id
    LEFT JOIN user_profile
    ON team_member.user_id=user_profile.id;";

    $result = $db->query($query);

    $teams = [];

    while($record = $result->fetch_object())
    {
        $team = new stdClass();
        $team->divisionName = $record->divisionName;
        $team->departmentName = $record->departmentName;
        $team->teamName = $record->teamName;
        $team->userName = $record->userName;
        $team->userSurname = $record->userSurname;
        $teams[] = $team;
        
    }

    return $teams;
}