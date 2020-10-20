<?php

require_once "./Notification.php";

function getAllDivisions($user){

    $db = db::getConnection();
    $query = "SELECT * FROM division";
    $result = $db->query($query);
    $divisions = [];
    while( $record = $result->fetch_object())
    {
        
            $division = new stdClass();
            $division->divisionId = $record->id;
            $division->name = $record->name;
            $division->description = $record->description;
            $division->createdBy = $record->createdBy;
            $division->updatedBy = $record->updatedBy;
            $division->createdDate = $record->createdDate;
            $division->updatedDate = $record->updatedDate;
            $division->deleted = $record->deleted;
       
            $divisions[] = $division;

        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 9)";
        $result1 = $db->query($query3);
    }

    return $divisions;
}
//get deleted Division
function getAllDeletedDivisions($user){

    $db = db::getConnection();
    $query = "SELECT * FROM division";
    $result = $db->query($query);
    $divisions = [];
    while( $record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
            $division = new stdClass();
            $division->divisionId = $record->id;
            $division->name = $record->name;
            $division->description = $record->description;
            $division->createdBy = $record->createdBy;
            $division->updatedBy = $record->updatedBy;
            $division->createdDate = $record->createdDate;
            $division->updatedDate = $record->updatedDate;
            $division->deleted = $record->deleted;
       
            $divisions[] = $division;
        }
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 9)";
        $result1 = $db->query($query3);
    }

    return $divisions;
}
//
function getAllDivisionsByName($name, $user)
{
    $db = db::getConnection();

    $query1 = "SELECT * FROM division WHERE name LIKE '%$name%'";
    $result = $db->query($query1);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 9)";
    $result1 = $db->query($query3);

    $divisions = [];

    while($record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
            $division = new stdClass();
            $division->divisionId = $record->id;
            $division->name = $record->name;
            $division->description = $record->description;
            $division->createdBy = $record->createdBy;
            $division->updatedBy = $record->updatedBy;
            $division->createdDate = $record->createdDate;
            $division->updatedDate = $record->updatedDate;
            $division->deleted = $record->deleted;
   
            $divisions[] = $division;
        }
    }

    return $divisions;
}

function createADivision($name, $description, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM division";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->name == $name && $record->description == $description)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE division SET deleted=0, WHERE name = $name";
                $result1 = $db->query($query1);
                return "worked";
            }
        }

        if($record->name == $name || $record->description == $description)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
        }
    }


    $query2 = "INSERT INTO division (name, description, createdBy, updatedBy, deleted) VALUES ('$name', '$description', '$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 9)";
    $result3 = $db->query($query3);

    $notification2 = createANotification($user,"You have created a new division");

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function deleteADivision($divisionId, $user)
{
    $db = db::getConnection();

    $departmentQuery = "SELECT department.division_id 
    FROM department";
    $departmentResult = $db->query($departmentQuery);

    while($record = $departmentResult->fetch_object())
    {
        if($record->division_id == $divisionId)
        {
            $query1 = "UPDATE division SET deleted=1 WHERE id = $divisionId";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $deleteQuery = "DELETE FROM division WHERE id = $divisionId";
    $deleteDivision = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 9)";
    $result4 = $db->query($query4);

    $notification2 = createANotification($user,"You have deleted a division");

    if($deleteDivision && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function updateADivision($divisionId,$name, $description, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM division";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if(!($record->id == $divisionId))
        {
            if($record->name == $name || $record->description == $description)
            {
                return "duplicate";
            }
        }
        
    }


    $query2 = "UPDATE division SET name='$name', description='$description', updatedBy='$user', updatedDate = NOW() WHERE id = $divisionId";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 9)";
    $result1 = $db->query($query3);

    $notification2 = createANotification($user,"You have updated a division");

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}
 // search by name
//  function searchAllDivisions($name, $user){

//     $db = db::getConnection();
//     $query = "SELECT * FROM division WHERE name LIKE '%$name%'  OR description LIKE '%$name%'";
//     $result = $db->query($query);

//     $divisions = [];

//     while( $record = $result->fetch_object()){
//         if($record->deleted == false)
//         {
//             $building = new stdClass();
          
//         }      
        
//     }
    
//     $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 9)";
//     $result3 = $db->query($query3);

//     return $divisions;
// }
// by id
function getAllDivisionsByID($divisionId, $user){

    $db = db::getConnection();
    $query = "SELECT * FROM division WHERE id = $divisionId";
    $result = $db->query($query);

    $division = [];

    while( $record = $result->fetch_object()){
        if($record->deleted == false)
        {
            $division = new stdClass();
            $division->divisionId = $record->id;
            $division->name = $record->name;
            $division->description = $record->description;
            $division->createdBy = $record->createdBy;
            $division->updatedBy = $record->updatedBy;
            $division->createdDate = $record->createdDate;
            $division->updatedDate = $record->updatedDate;
            $division->deleted = $record->deleted;
   
            $divisions[] = $division;
           
        }
    }
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 9)";
    $result3 = $db->query($query3);
    
    return $division;
    
        
}

