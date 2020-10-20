<?php

require_once "./Notification.php";

function getAllSpokenLanguages(){

    $db = db::getConnection();
    $query = "SELECT * FROM spoken_language";
    $result = $db->query($query);
    $spokenlanguages = [];
    while( $record = $result->fetch_object())
    {
        $spokenlanguage = new stdClass();
        $spokenlanguage->userId = $record->user_id;
        $spokenlanguage->languageId = $record->language_id;
        $spokenlanguage->deleted = $record->deleted;
        $spokenlanguages[] = $spokenlanguage;
    }

    return $spokenlanguages;
}

function createAUserLanguage($language_id, $user_id, $user)
{
    $db = db::getConnection();

    $query2 = "INSERT INTO spoken_language (language_id, user_id) VALUES ($language_id, $user_id)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 42)";
    $result3 = $db->query($query3);

    $notification2 = createANotification($user,"You have added a new language to your profile.");

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function deleteAUserLanguage($user_id, $language_id, $user)
{
    $db = db::getConnection();
    
    $userLanguageQuery = "DELETE FROM spoken_language WHERE language_id = $language_id AND user_id=$user_id";
    $userLanguageResult = $db->query($userLanguageQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 42)";
    $result4 = $db->query($query4);

    $notification2 = createANotification($user,"You have deleted a language from your profile.");

    if($userLanguageResult && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}