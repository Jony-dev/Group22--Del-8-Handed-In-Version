<?php
function getAlladverts(){

    $db = db::getConnection();
    $query = "SELECT * FROM advert";
    $result = $db->query($query);
    $adverts = [];
    while( $record = $result->fetch_object())
    {
        $advert = new stdClass();
        $advert->advertid = $record->id;
        $advert->cardid = $record->card_id;
        $advert->startdate = $record->startDate;
        $advert->enddate = $record->endDate;
        //$advert->expired = $record->Expired;
        $advert->createdby = $record->createdBy;
        $advert->updatedby = $record->updatedBy;
        $advert->createddate = $record->createdDate;
        $advert->updateddate = $record->updatedDate;
        $advert->deleted = $record->deleted;
        $adverts[] = $advert;
    }

    return $adverts;
}

function getMyAdverts($user){

    $db = db::getConnection();
    $query = "
        SELECT id as 'cardId', card_name as 'cardName', description FROM job_card WHERE job_card.published = 1 AND job_card.id NOT IN (SELECT card_id as 'cardId' FROM application WHERE user_id = $user)
    ";
    $result = $db->query($query);
    $adverts = [];
    if($result)
        while( $record = $result->fetch_object())
        {
            $advert = new stdClass();
            $advert->cardId = $record->cardId;
            $advert->cardName = $record->cardName;
            $advert->description = $record->description;
            $adverts[] = $advert;
        }
    return $adverts;
}
function getApplications($user)
{

    $db = db::getConnection();
    $query = "
        SELECT application.id as 'applicationId', job_card.card_name as 'cardName', status.status FROM application
        INNER JOIN status on status_id = status.id
        INNER JOIN job_card on application.card_id = job_card.id
        WHERE user_id = $user
    ";
    $result = $db->query($query);
    $adverts = [];
    if($result)
        while( $record = $result->fetch_object())
        {
            $adverts[] = $record;
        }
    return $adverts;

}