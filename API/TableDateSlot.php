<?php
function getAllTableDateSlots(){

    $db = db::getConnection();
    $query = "SELECT * FROM table_date_slot";
    $result = $db->query($query);
    $tabledateslots = [];
    while( $record = $result->fetch_object())
    {
        $tabledateslot = new stdClass();
        $tabledateslot->tableId = $record->table_id;
        $tabledateslot->dateId = $record->date_id;
        $tabledateslot->slotId = $record->slot_id;
        $tabledateslot->booked = $record->booked;
        $tabledateslot->deleted = $record->deleted;
        $tabledateslots[] = $tabledateslot;
    }

    return $tabledateslots;
}