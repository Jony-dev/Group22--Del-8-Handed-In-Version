<?php
function getAllStatuses(){

    $db = db::getConnection();
    $query = "SELECT * FROM status";
    $result = $db->query($query);
    $statuses = [];
    while( $record = $result->fetch_object())
    {
        $status = new stdClass();
        $status->id = $record->id;
        $status->status = $record->status;
        $statuses[] = $status;
    }

    return $statuses;
}

function createAStatus($status)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM status";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->status == $status)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO status (status) VALUES ('$status')";
    $result = $db->query($query2);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteAStatus($id)
{
    $db = db::getConnection();

    $query2 = "DELETE FROM status WHERE id = $id";
    $result = $db->query($query2);  

    return $result;

}

function updateAStatus($id, $status)
{
    $db = db::getConnection();

    $query = "SELECT * FROM status";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->status == $status)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE status SET status='$status' WHERE id = $id";
    $result = $db->query($query2);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}


function searchAStatus($status)
{
    $db = db::getConnection();

    $query1 = "SELECT * FROM status WHERE status LIKE '%$status%'";
    $result = $db->query($query1);

    $statuses = [];

    while( $record = $result->fetch_object())
    {
        $status = new stdClass();
        $status->id = $record->id;
        $status->status = $record->status;
        $statuses[] = $status;
    }

    return $statuses;
}