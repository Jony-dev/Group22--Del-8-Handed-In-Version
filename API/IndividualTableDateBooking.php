<?php
function getAllIndividualTableDateBookings(){

    $db = db::getConnection();
    $query = "SELECT * FROM individual_booking";
    $result = $db->query($query);
    $individualbookings = [];
    while( $record = $result->fetch_object())
    {
        $individualbooking = new stdClass();
        $individualbooking->bookingID = $record->booking_id;
        $individualbooking->tableid= $record->table_id;
        $individualbooking->dateid= $record->date_id;
        $individualbooking->checkin = $record->checked_in;
        $individualbooking->checkout = $record->checked_out;
        $individualbooking->deleted = $record->deleted;
        $individualbookings[] = $individualbooking;
    }

    return $individualbookings;
}