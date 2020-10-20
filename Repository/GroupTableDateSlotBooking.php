<?php
function getAllGroupTableDateSlotBookings(){

    $db = db::getConnection();
    $query = "SELECT * FROM group_booking";
    $result = $db->query($query);
    $groupbookings = [];
    while( $record = $result->fetch_object())
    {
        $groupbooking = new stdClass();
        $groupbooking->tableid= $record->table_id;
        $groupbooking->slotid = $record->slot_id;
        $groupbooking->dateid= $record->date_id;
        $groupbooking->bookingid = $record->booking_id;
        $groupbooking->checkin = $record->check_in;
        $groupbooking->checkout = $record->check_out;
        $groupbooking->deleted = $record->deleted;
        $groupbookings[] = $groupbooking;
    }

    return $groupbookings;
}