<?php

require_once "./Division.php";
require_once "./Notification.php";

require_once("db.php");

function getAllDepartments(){

    $db = db::getConnection();
    $query = "SELECT * FROM department";
    $result = $db->query($query);
    $departments = [];
    while($record = $result->fetch_object())
    {
        
            $department = new stdClass();
            $department->departmentId = $record->id;
            $department->divisionId = $record->division_id;
            $department->name = $record->name;
            $department->description = $record->description;
       
            $departments[] = $department;
        
        
    }


    return $departments;
}
// get Deleted Departments
function getAllDeletedDepartments(){

    $db = db::getConnection();
    $query = "SELECT * FROM department";
    $result = $db->query($query);
    $departments = [];
    while($record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
            $department = new stdClass();
            $department->departmentId = $record->id;
            $department->divisionId = $record->division_id;
            $department->name = $record->name;
            $department->description = $record->description;
            $departments[] = $department;
        }
        
    }

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 8)";
    $result3 = $db->query($query3);

    return $departments;
}

//
function getAllDepartmentsByDivision($divisionId, $user){

    $db = db::getConnection();
    $stmt = $db->prepare("SELECT id, division_id, name, description FROM department WHERE division_id = ? AND deleted = 0");
    $stmt->bind_param('i',$divisionId);
    $stmt->bind_result($id, $div, $name,$descrip);
    $stmt->execute();

    $departments = [];

    while($stmt->fetch()){

            $department = new stdClass();
            $department->departmentId = $id;
            $department->divisionId = $div;
            $department->name = $name;
            $department->description = $descrip;
       
            $departments[] = $department;
        
    }
    
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 8)";
    $result3 = $db->query($query3);

    return $departments;
}

function getAllDepartmentsByID($departmentId, $user){

    $db = db::getConnection();
    $query = "SELECT * FROM department WHERE id = $departmentId";
    $result = $db->query($query);

    $departments = [];

    while( $record = $result->fetch_object()){
        if($record->deleted == false)
        {
            $department = new stdClass();
            $department->departmentId = $record->id;
            $department->divisionId = $record->division_id;
            $department->name = $record->name;
            $department->description = $record->description;
            $department->createdBy = $record->createdBy;
            $department->updatedBy = $record->updatedBy;
            $department->createdDate = $record->createdDate;
            $department->updatedDate = $record->updatedDate;
            $department->deleted = $record->deleted;
       
            $departments[] = $department;
        }
        
    }
    
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 8)";
    $result3 = $db->query($query3);

    return $departments;
}

function getAllDepartmentsMembers($userId)
{
    $db = db::getConnection();
    $query = "SELECT department_id FROM user_job_profile WHERE user_id = $userId";
    $result = $db->query($query);

    $record1 = $result->fetch_object();
    $departmentId = $record1->department_id;

    $query2 = "SELECT user_profile.name,user_profile.surname,user_profile.picture 
    FROM user_profile
    INNER JOIN user_job_profile 
    ON user_profile.id = user_job_profile.user_id
    WHERE department_id = $departmentId";
    $result2 = $db->query($query2);

    while($record = $result2->fetch_object())
        {
            $user = new stdClass();
            $user->name = $record->name;
            $user->surname = $record->surname;
            if($record->picture)
            {
                $user->pic = $record->picture;
            }
            else{
                $user->pic = null;
            }
            $members[] = $user;
        }

    return $members;

}

function searchAllDepartments($name, $user){

    $db = db::getConnection();
    $query = "SELECT * FROM department WHERE name LIKE '%$name%' OR description LIKE '%$name%'";
    $result = $db->query($query);

    $departments = [];

    while( $record = $result->fetch_object()){
        if($record->deleted == false)
        {
            $department = new stdClass();
            $department->deparmentId = $record->id;
            $department->divisionId = $record->division_id;
            $department->name = $record->name;
            $department->description = $record->description;
       
            $departments[] = $department;
        }
        
    }
    
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 8)";
    $result3 = $db->query($query3);

    return $departments;
}

function updateADepartment($departmentId,$divisionId,$name, $description, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM department";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->id != $departmentId)
        {
            if($record->name == $name || ($record->division_id == $divisionId && $record->description == $description))
            {
                if($record->deleted == false)
                {
                    return "duplicate";
                }
            }
        }
        
    }


    $query2 = "UPDATE department SET division_id=$divisionId, name='$name', description='$description', updatedBy='$user', updatedDate = NOW() WHERE id = $departmentId";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 8)";
    $result1 = $db->query($query3);

    $notification2 = createANotification($user,"You have successfully updated a department");
    

    if($result && $result1)
    {
        return "worked";
    }
    else{
        return "did not work";
    }

}
//dlete

function deleteADepartment($departmentId, $user)
{
    $db = db::getConnection();

    $teamQuery = "SELECT team.department_id 
    FROM team";
    $teamResult = $db->query($teamQuery);

    while($record = $teamResult->fetch_object())
    {
        if($record->department_id == $departmentId)
        {
            $query1 = "UPDATE department SET deleted=1 WHERE id = $departmentId";
            $result1 = $db->query($query1);

            $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 8)";
            $result4 = $db->query($query4);

            

            if($result1 && $result4)
            {
                return "worked";
            }
            else{
                return "did not work";
            }
                       
        }
    }

    $divisionQuery = "SELECT division.id, division.deleted 
    FROM department 
    INNER JOIN division on division.id = department.division_id  
    WHERE department.id = $departmentId";

    $divisionResult = $db->query($divisionQuery);
    $divisionId = 0;
    $divisionDeleted = false;

    while($record = $divisionResult->fetch_object())
    {
        $divisionId = $record->id;
        $divisionDeleted = $record->deleted;
    }


    $numberQuery = "SELECT count(department.id)
    FROM department 
    WHERE department.division_id = $divisionId";

    $numberResult = $db->query($numberQuery);
    $row = $numberResult->fetch_row();
    $count = $row[0];

    $deleteQuery = "DELETE FROM department WHERE id = $departmentId";
    $deleteDepartment = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 8)";
    $result4 = $db->query($query4);

    $notification2 = createANotification($user,"A department has been deleted.");

    if($count<2 && $divisionDeleted == 1)
    {
        $deleteDivision = deleteADivision($divisionId, $user);

    }

    if($deleteDepartment && $result4)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}


    
    function createADepartment($division_id,$name, $description, $user)
    {   
        $db = db::getConnection();
    
        $query = "SELECT * FROM department";
        $result = $db->query($query);
    
        while($record = $result->fetch_object())
        {
            if($record->name == $name || ($record->division_id == $division_id && $record->description == $description))
            {
                if($record->deleted == false)
                {
                    return "duplicate";
                }
                else
                {
                    $query1 = "UPDATE department SET deleted=0 WHERE id = $record->id";
                    $result1 = $db->query($query1);
                    return "worked";                    
                }   
            }
        }
    
    
        $query2 = "INSERT INTO department (division_id, name, description, createdBy, updatedBy, deleted) VALUES ('$division_id','$name', '$description', '$user', '$user',0)";
        $result2 = $db->query($query2);
                                                                                            
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 8)";
        $result3 = $db->query($query3);

        $notification2 = createANotification($user,"A new department has been created.");

        if($result2 && $result3)
        {
            return "worked";
        }

        else
        {
            return "did not work";
        }
    }

                                                                                                                                                                                                                                                                                                                    