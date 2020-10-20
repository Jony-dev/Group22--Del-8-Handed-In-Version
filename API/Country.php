<?php

require_once("db.php");
require_once "./Notification.php";



function getAllCountries(){
    $db = db::getConnection();

    $db->set_charset("utf8");

    $query = "SELECT * FROM country";
    $result = $db->query($query);

    $countries = [];

    while( $record = $result->fetch_object()){
        $country = new stdClass();
        $country->id = $record->id;
        $country->country = $record->country;
        $countries[] = $country;
    }

    return $countries;
}


function createACountry($country, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM country";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->country == $country)
        {
                return "duplicate";
        }
    }

    $query2 = "INSERT INTO country (country) VALUES ('$country')";
    $result2 = $db->query($query2);                                                                                        

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 5)";
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
function updateACountry($id, $country, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM country";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->country == $country)
        {
            return "duplicate";
        }
    }

    $query2 = "UPDATE country SET country='$country' WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 5)";
    $result1 = $db->query($query3);

    if($result)
    {
        return "worked";
    }

    else{
        return "did not work";
    }
}