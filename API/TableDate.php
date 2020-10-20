<?php
function getAllTableDates(){

    $db = db::getConnection();
    $query = "SELECT * FROM table_date";
    $result = $db->query($query);
    $tabledates = [];
    while( $record = $result->fetch_object())
    {
        $tabledate = new stdClass();
        $tabledate->dateId = $record->date_id;
        $tabledate->tableId = $record->table_id;
        $tabledate->booked = $record->booked;
        $tabledate->deleted = $record->deleted;
        $tabledates[] = $tabledate;
    }

    return $tabledates;
}

function createAllTableDateSlots($user_id)
{
    $db = db::getConnection();

    $bookingIdQuery = "SELECT booking_id FROM individual_booking";
    $bookingIdResult = $db->query($bookingIdQuery);

    $deleteIndividualQuery = "DELETE FROM individual_booking";
    $deleteIndividualResult = $db->query($deleteIndividualQuery);

    while($bookingIdRecord = $bookingIdResult->fetch_object())
    {
        $deleteUserBookingQuery = "DELETE FROM user_booking WHERE id = $bookingIdRecord->booking_id";
        $deleteUserBookingResult = $db->query($deleteUserBookingQuery);
    }

    $deleteTableDateQuery = "DELETE FROM table_date";
    $deleteTableDateResult = $db->query($deleteTableDateQuery);

    $tableIDQuery = "SELECT id FROM tafel WHERE type_id=4";
    $resultTableIDQuery = $db->query($tableIDQuery);

    $dateIDQuery = "SELECT id FROM date";
    $resultDateIDQuery = $db->query($dateIDQuery);

    while($tableRecord = $resultTableIDQuery->fetch_object())
    {
        while($dateRecord = $resultDateIDQuery->fetch_object())
        {
            $tableDateQuery = "INSERT INTO table_date (date_id, table_id) VALUES ($dateRecord->id, $tableRecord->id)";
            $resultTableDate = $db->query($tableDateQuery);
            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user_id', 2, 47)";
            $result3 = $db->query($query3);
        }
        $dateIDQuery = "SELECT id FROM date";
        $resultDateIDQuery = $db->query($dateIDQuery);
    }

    if($resultTableDate)
    {
        return "worked";
    }

    return "did not work";
}   
