<?php

require_once "./Notification.php";

function getAllDeletedLocations(){

    $db = db::getConnection();
    $query = "SELECT * FROM location WHERE deleted = 0";
    $result = $db->query($query);
    $locations = [];
    while( $record = $result->fetch_object())
    {
        $location = new stdClass();
        $location->locationId = $record->id;
        $location->countryId = $record->country_id;
        $location->name = $record->name;
        $locations[] = $location;
    }

    return $locations;
}

function getAllLocations(){

    $db = db::getConnection();
    $query = "SELECT * FROM location";
    $result = $db->query($query);
    $locations = [];
    while( $record = $result->fetch_object())
    {
        $location = new stdClass();
        $location->locationId = $record->id;
        $location->countryId = $record->country_id;
        $location->name = $record->name;
        $locations[] = $location;
    }

    return $locations;
}
// create
 
function createALocation($country_id,$name,$user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM location";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->country_id == $country_id && $record->name == $name)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE location SET deleted=0 WHERE id = $record->id";
                $result1 = $db->query($query1);
                return "worked";                    
            }   
        }
    }


    $query2 = "INSERT INTO location (country_id, name,  createdBy, updatedBy, deleted) VALUES ('$country_id','$name', '$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 26)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A new location has been created.");
        $notification2 = createANotification($user,"A new location has been created.");
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
function updateALocation($id, $country_id,$name, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM location";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->country_id == $country_id  && $record->name == $name)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
        }
    }


    $query2 = "UPDATE location SET country_id='$country_id', name='$name', updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 26)";
    $result1 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A location has been updated.");
        $notification2 = createANotification($user,"A location has been updated.");
    }

    if($result && $result1)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteALocation($id, $user)
{
    $db = db::getConnection();

    $buildingQuery = "SELECT building.location_id 
    FROM building";
    $buildingResult = $db->query($buildingQuery);

    while($record = $buildingResult->fetch_object())
    {
        if($record->location_id == $id)
        {
            $query1 = "UPDATE location SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $deleteQuery = "DELETE FROM location WHERE id = $id";
    $deleteLocation = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 26)";
    $result4 = $db->query($query4);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A location has been deleted.");
        $notification2 = createANotification($user,"A location has been deleted.");
    }

    if($deleteLocation && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}