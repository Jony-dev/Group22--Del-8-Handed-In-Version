<?php

require_once "./Building.php";
require_once "./Notification.php";

function getAllFloors(){

    $db = db::getConnection();
    $query = "SELECT * FROM floor";
    $result = $db->query($query);
    $floors = [];
    while( $record = $result->fetch_object())
    {
        $buildingNamequery= "SELECT name FROM building WHERE id = $record->building_id";
        $nameresult = $db->query($buildingNamequery);
        $namerecord=$nameresult->fetch_object();

        $floor = new stdClass();
        $floor->floorId= $record->id;
        $floor->buildingId = $record->building_id;
        $floor->floorNumber = $record->floor_number;
        $floor->buildingName=$namerecord->name;
        $floor->createdby = $record->createdBy;
        $floor->updatedby = $record->updatedBy;
        $floor->createddate = $record->createdDate;
        $floor->updateddate = $record->updatedDate;
        $floor->deleted = $record->deleted;
        $floors[] = $floor;
    }

    return $floors;
}
// get Deleted floors
function getAllDeletedFloors(){

    $db = db::getConnection();
    $query = "SELECT * FROM floor";
    $result = $db->query($query);
    $floors = [];
    while( $record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
        $buildingNamequery= "SELECT name FROM building WHERE id = $record->building_id";
        $nameresult = $db->query($buildingNamequery);
        $namerecord=$nameresult->fetch_object();

        $floor = new stdClass();
        $floor->floorId= $record->id;
        $floor->buildingId = $record->building_id;
        $floor->floorNumber = $record->floor_number;
        $floor->buildingName=$namerecord->name;
        $floor->createdby = $record->createdBy;
        $floor->updatedby = $record->updatedBy;
        $floor->createddate = $record->createdDate;
        $floor->updateddate = $record->updatedDate;
        $floor->deleted = $record->deleted;
        $floors[] = $floor;
        }
    }

    return $floors;
}
//create
function createAFloor($building_id,$floor_number,$user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM floor";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->building_id == $building_id && $record->floor_number == $floor_number)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE floor SET deleted=0 WHERE id = $record->id";
                $result1 = $db->query($query1);
                return "worked";                    
            }   
        }
    }


    $query2 = "INSERT INTO floor (building_id, floor_number,  createdBy, updatedBy, deleted) VALUES ('$building_id','$floor_number', '$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 10)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A new floor has been created.");
        $notification2 = createANotification($user,"You have created a new floor.");
    }

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

//update
function updateAFloor($id, $building_id,$floor_number, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM floor";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->building_id == $building_id  && $record->floor_number == $floor_number)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
        }
    }


    $query2 = "UPDATE floor SET building_id='$building_id', floor_number='$floor_number', updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 10)";
    $result1 = $db->query($query3);

    
    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A floor has been updated.");
        $notification2 = createANotification($user,"You have updated a floor.");
    }

    if($result && $result1)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteAFloor($id, $user)
{
    $db = db::getConnection();

    $tableQuery = "SELECT tafel.floor_id 
    FROM tafel";
    $tableResult = $db->query($tableQuery);

    while($record = $tableResult->fetch_object())
    {
        if($record->floor_id == $id)
        {
            $query1 = "UPDATE tafel SET floor_id=null WHERE floor_id = $id";
            $result1 = $db->query($query1);
        }
    }

    $buildingQuery = "SELECT building.id, building.deleted 
    FROM floor 
    INNER JOIN building on building.id = floor.building_id  
    WHERE floor.id = $id";

    $buildingResult = $db->query($buildingQuery);
    $buildingId = 0;
    $buildingDeleted = false;

    while($record = $buildingResult->fetch_object())
    {
        $buildingId = $record->id;
        $buildingDeleted = $record->deleted;
    }


    $numberQuery = "SELECT count(floor.id)
    FROM floor
    WHERE floor.building_id = $buildingId";
    $numberResult = $db->query($numberQuery);

    $row = $numberResult->fetch_row();
    $count = $row[0];

    $deleteQuery = "DELETE FROM floor WHERE id = $id";
    $deleteFloor = $db->query($deleteQuery);


    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 10)";
    $result4 = $db->query($query4);

    if($count<2 && $buildingDeleted == 1)
    {
        $deleteBuilding = deleteABuilding($buildingId, $user);
    }

    
    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A floor has been deleted.");
        $notification2 = createANotification($user,"You have deleted a floor.");
    }

    if($deleteFloor && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}
// // search by floor number
// function searchAllFloors($floor_number, $user){

//     $db = db::getConnection();
//     $query = "SELECT * FROM floor WHERE name LIKE '%$name%'";
//     $result = $db->query($query);

//     $buildings = [];

//     while( $record = $result->fetch_object()){
//         if($record->deleted == false)
//         {
//             $building = new stdClass();
//             $building->buildingId = $record->id;
//             $building->locationId = $record->location_id;
//             $building->name = $record->name;
//             echo("In Function");
//             $buildings[] = $building;
//         }      
        
//     }
    
//     $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 4)";
//     $result3 = $db->query($query3);

//     return $buildings;
// }
// by id
function getAllFloorsByID($id, $user){

    $db = db::getConnection();
    $query = "SELECT * FROM floor WHERE id = $id";
    $result = $db->query($query);

    $floors = [];

    while( $record = $result->fetch_object()){
        if($record->deleted == false)
        {
            $floor = new stdClass();
            $floor->floorid= $record->id;
            $floor->buildingid = $record->building_id;
             $floor->floornumber = $record->floor_number;
            $floor->createdby = $record->createdBy;
            $floor->updatedby = $record->updatedBy;
            $floor->createddate = $record->createdDate;
             $floor->updateddate = $record->updatedDate;
            $floor->deleted = $record->Deleted;


        $floors[] = $floor;
            
        }
    }
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 10)";
    $result3 = $db->query($query3);
    
    return $floors;
    
        
}
    // fk
    function getAllFloorsByBuilding($building_id, $user){

        $db = db::getConnection();
        $query = "SELECT * FROM floor WHERE building_id = $building_id";
        $result = $db->query($query);
    
        $floors = [];
    
        while($record = $result->fetch_object()){
            if($record->deleted == false)
            {

                $buildingNamequery= "SELECT  name FROM building WHERE building_id = $record->buildingId";
                $nameresult = $db->query($buildingNamequery);
                $namerecord=$nameresult->fetch_object();

                $floor = new stdClass();
                $floor->floorId= $record->id;
                $floor->buildingId = $record->building_id;
                $floor->buildingName=$namerecord->name;
                 $floor->floorNumber = $record->floor_number;
                $floor->createdby = $record->createdBy;
                $floor->updatedby = $record->updatedBy;
                $floor->createddate = $record->createdDate;
                 $floor->updateddate = $record->updatedDate;
                $floor->deleted = $record->Deleted;
    
    
            $floors[] = $floor;
              
            }
            
        }
        
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 10)";
        $result3 = $db->query($query3);
    
        return $floor;
    }
