<?php
function getAllJobCardUsers(){

    $db = db::getConnection();
    $query = "SELECT * FROM job_card_user";
    $result = $db->query($query);
    $jobcardusers = [];
    while( $record = $result->fetch_object())
    {
        $jobcarduser = new stdClass();
        $jobcarduser->userId = $record->user_id;
        $jobcarduser->cardId = $record->card_id;
        $jobcarduser->roleId = $record->role_id;
        $jobcarduser->deleted = $record->deleted;
        $jobcardusers[] = $jobcarduser;
    }

    return $jobcardusers;
}
