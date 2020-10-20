<?php
function getAllApplicationLists(){

    $db = db::getConnection();
    $query = "SELECT * FROM ApplicationList";
    $result = $db->query($query);
    $applicationlists = [];
    while( $record = $result->fetch_object())
    {
        $applicationlist = new stdClass();
        $applicationlist->listid = $record->ListID;
        $applicationlist->cardid = $record->CardID;
        $applicationlist->deleted = $record->Deleted;
        $applicationlist->applicationlist = $record->applicationlist;
        $applicationlists[] = $applicationlist;
    }

    return $applicationlists;
}