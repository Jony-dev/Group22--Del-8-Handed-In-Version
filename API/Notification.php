<?php

require_once("db.php");

function getAllNotifications(){

    $db = db::getConnection();
    $query = "SELECT * FROM notification";
    $result = $db->query($query);
    $notifications = [];
    while( $record = $result->fetch_object()){

        $notification = new stdClass();
        $notification->id = $record->id;
        $notification->user_id = $record->user_id;
        $notification->message = $record->message;
        $notification->date = $record->date;
        $notification->deleted = $record->deleted;
        $notification->read = $record->read;
        $notifications[] = $notification;
    }

    return $notifications;
}

function getAllUserNotifications($userId)
{
    $db = db::getConnection();
    $query = "SELECT * FROM notification WHERE user_id=$userId ORDER BY notification.date DESC";
    $result = $db->query($query);
    $notifications = [];
    while( $record = $result->fetch_object()){

        if($record->deleted == false)
        {
            $notification = new stdClass();
            $notification->id = $record->id;
            $notification->message = $record->message;
            $notification->date = $record->date;
            $notification->deleted = $record->deleted;
            $notification->read = $record->read;
            $notifications[] = $notification;
        }
    }

    return $notifications;
}

function deleteANotification($id)
{
    $db = db::getConnection();
    $query = "DELETE FROM notification WHERE id=$id";
    $result = $db->query($query);
    return true;
}

function getAllUserCount($userId)
{
    $db = db::getConnection();
    $query = "SELECT count(id) AS 'unread' FROM notification WHERE user_id=$userId AND notification.read=false;";
    $result = $db->query($query);
    $record = $result->fetch_object();
    $notification = new stdClass();
    $notification->unread = $record->unread;
    return $notification;
}


function createANotification($user,$message)
{
    $db = db::getConnection();
    $createQuery = "INSERT INTO notification (user_id,message) VALUES ($user,'$message')";
    $createResult = $db->query($createQuery);

    if($createResult)
    {
        return true;
    }
    else
    {
        return false;
    }
}