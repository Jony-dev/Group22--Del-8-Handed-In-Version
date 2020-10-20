<?php
function getAllDateSlots(){

    $db = db::getConnection();
    $query = "SELECT * FROM date_slot";
    $result = $db->query($query);
    $dateslots = [];
    while( $record = $result->fetch_object())
    {
        $dateslot = new stdClass();
        $dateslot->dateid= $record->date_id;
        $dateslot->slotid = $record->slot_id;
        $dateslot->deleted = $record->deleted;
        $dateslots[] = $dateslot;
    }

    return $dateslots;
}