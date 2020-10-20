<?php

require_once "./Location.php";
require_once "./Notification.php";

function getAllDeletedBuildings(){

    $db = db::getConnection();
    $query = "SELECT * FROM building";
    $result = $db->query($query);
    $buildings = [];
    while( $record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
            $building = new stdClass();
            $building->buildingId = $record->id;
            $building->locationId = $record->location_id;
            $building->name = $record->name;
            $buildings[] = $building;
        }
    }
    return $buildings;
}

function getAllBuildingsReport()
{
    $db = db::getConnection();
    $query = "SELECT country.country AS 'countryName', location.name AS 'locationName', building.name AS 'buildingName', floor.floor_number AS 'floorNumber'
    FROM country
    INNER JOIN location
    ON country.id = location.country_id
    LEFT JOIN building
    ON location.id = building.location_id
    LEFT JOIN floor
    ON building.id=floor.building_id;";

    $result = $db->query($query);
    $buildings = [];
    while( $record = $result->fetch_object())
    {
        $building = new stdClass();
        $building->countryName = $record->countryName;
        $building->locationName = $record->locationName;
        $building->buildingName = $record->buildingName;
        $building->floorNumber = $record->floorNumber;
        $buildings[] = $building;
    }
    return $buildings;
}

function getAllBuildings(){

    $db = db::getConnection();
    $query = "SELECT * FROM building";
    $result = $db->query($query);
    $buildings = [];
    while( $record = $result->fetch_object())
    {
        $building = new stdClass();
        $building->buildingId = $record->id;
        $building->locationId = $record->location_id;
        $building->name = $record->name;
        $buildings[] = $building;
    }
    return $buildings;
}

//create
function createABuilding($locationId,$name,$user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM building";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->location_id == $locationId && $record->name == $name)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE building SET deleted=0 WHERE id = $record->id";
                $result1 = $db->query($query1);
                return "worked";                    
            }   
        }
    }

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A new building has been created.");
        $notification2 = createANotification($user,"A new building has been created.");
    }

    $query2 = "INSERT INTO building (location_id, name,  createdBy, updatedBy, deleted) VALUES ('$locationId','$name', '$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 4)";
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

//update
function updateABuilding($buildingId, $locationId,$name, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM building";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->location_id == $locationId  && $record->name == $name)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
        }
    }


    $query2 = "UPDATE building SET location_id=$locationId, name='$name', updatedBy='$user', updatedDate = NOW() WHERE id = $buildingId";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 4)";
    $result1 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A building has been updated.");
        $notification2 = createANotification($user,"A building has been updated.");
    }

    if($result && $result1)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteABuilding($buildingId, $user)
{
    $db = db::getConnection();

    $floorQuery = "SELECT floor.building_id 
    FROM floor";
    $floorResult = $db->query($floorQuery);

    while($record = $floorResult->fetch_object())
    {
        if($record->building_id == $buildingId)
        {
            $query1 = "UPDATE building SET deleted=1 WHERE id = $buildingId";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $locationQuery = "SELECT location.id, location.deleted 
    FROM building 
    INNER JOIN location on location.id = building.location_id  
    WHERE building.id = $buildingId";

    $locationResult = $db->query($locationQuery);
    $locationId = 0;
    $locationDeleted = false;

    while($record = $locationResult->fetch_object())
    {
        $locationId = $record->id;
        $locationDeleted = $record->deleted;
    }


    $numberQuery = "SELECT count(building.id)
    FROM building 
    WHERE building.location_id = $locationId";

    $numberResult = $db->query($numberQuery);
    $row = $numberResult->fetch_row();
    $count = $row[0];

    $deleteQuery = "DELETE FROM building WHERE id = $buildingId";
    $deleteBuilding = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 4)";
    $result4 = $db->query($query4);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A building has been deleted.");
        $notification2 = createANotification($user,"A building has been deleted.");
    }

    if($count<2 && $locationDeleted == 1)
    {
        $deleteLocation = deleteALocation($locationId, $user);

    }

    if($deleteBuilding && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}
    // search by name
    function searchAllBuildings($name, $user){

        $db = db::getConnection();
        $query = "SELECT * FROM building WHERE name LIKE '%$name%'";
        $result = $db->query($query);
    
        $buildings = [];
    
        while( $record = $result->fetch_object()){
            if($record->deleted == false)
            {
                $building = new stdClass();
                $building->buildingId = $record->id;
                $building->locationId = $record->location_id;
                $building->name = $record->name;
                $buildings[] = $building;
            }      
            
        }
        
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 4)";
        $result3 = $db->query($query3);
    
        return $buildings;
    }
    // by id
    function getAllBuildingsByID($buildingId, $user){

        $db = db::getConnection();
        $query = "SELECT * FROM building WHERE id = $buildingId";
        $result = $db->query($query);
    
        $buildings = [];
    
        while( $record = $result->fetch_object()){
            if($record->deleted == false)
            {
                $department = new stdClass();
                $building->buildingId = $record->id;
                $building->locationId = $record->location_id;
                $building->name = $record->name;
                $buildings[] = $building;
               
            }
        }
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 4)";
        $result3 = $db->query($query3);
        
        return $buildings;
        
            
    }
        // fk
        function getAllBuildingsByLocation($locationId, $user){

            $db = db::getConnection();
            $query = "SELECT * FROM building WHERE location_id = $locationId";
            $result = $db->query($query);
        
            $buildings = [];
        
            while($record = $result->fetch_object()){
                if($record->deleted == false)
                {
                    $building = new stdClass();
                    $building->buildingId = $record->id;
                    $building->locationId = $record->location_id;
                    $building->name = $record->name;
                    $buildings[] = $building;
                }
                
            }
            
            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 4)";
            $result3 = $db->query($query3);
        
            return $buildings;
        }

    function getAvailableTables($buildingId, $dateId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT table_id as 'id', type_id as 'ttypeId', floor_id as 'floorId', tafel.name FROM edumarxc_bmwdatabase.table_date
                                    INNER JOIN tafel on table_date.table_id = tafel.id
                                    INNER JOIN floor on tafel.floor_id = floor.id
                                    INNER JOIN building on floor.building_id = building.id
                                    WHERE table_date.booked = 0 AND building.id = ? AND table_date.date_id = ?");
        $stmt->bind_param("ii",$buildingId, $dateId);

        if(!$stmt->execute())
            return false;

        $tables = [];

        $result = $stmt->get_result();

        while($result AND $record = $result->fetch_object())
        {
            $tables[] = $record;
        }
        return $tables;

    }


