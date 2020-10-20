<?php

require_once "./Notification.php";

function getAllSlots(){

    $db = db::getConnection();
    $query = "SELECT * FROM slot";
    $result = $db->query($query);
    $slots = [];
    while( $record = $result->fetch_object())
    {
        $slot = new stdClass();
        $slot->id = $record->id;
        $slot->startTime = $record->startTime;
        $slot->endTime = $record->endTime;
        $slot->deleted = $record->deleted;
        $slot->createdBy = $record->createdBy;
        $slot->createdDate = $record->createdDate;
        $slots[] = $slot;
    }

    return $slots;
}
// create
function createAllSlots($startTime, $endTime, $noSlots, $user)
{
    $db = db::getConnection();

    $bookingIdQuery = "SELECT booking_id FROM group_booking";
    $bookingIdResult = $db->query($bookingIdQuery);

    

    $deleteGroupBookingQuery = "DELETE FROM group_booking";
    $deleteGroupBookingResult = $db->query($deleteGroupBookingQuery);


    while($bookingIdRecord = $bookingIdResult->fetch_object())
    {
        $userBookingIdQuery = "SELECT user_id FROM user_booking WHERE id = $bookingIdRecord->booking_id";
        $userIdResult = $db->query($userBookingIdQuery);
        $userIdRecord = $userIdResult->fetch_object();

        $notification2 = createANotification($userIdRecord->user_id,"Your booking for a boardroom has been removed. Please rebook it.");

        $deleteUserBookingQuery = "DELETE FROM user_booking WHERE id=$bookingIdRecord->booking_id";
        $deleteUserBookingResult = $db->query($deleteUserBookingQuery);
    }
    

    $deleteTableDateSlotQuery = "DELETE FROM table_date_slot";
    $deleteTableDateSlotResult = $db->query($deleteTableDateSlotQuery);

    $deleteDateSlotQuery = "DELETE FROM date_slot";
    $deleteDateSlotResult = $db->query($deleteDateSlotQuery);

    $deleteSlotQuery = "DELETE FROM slot";
    $deleteSlotResult = $db->query($deleteSlotQuery);

    $duration = 60/$noSlots;

    $finalTime;
    $result;
    $result3;

    $startArr = explode(':', $startTime);
    $decStartTime = ($startArr[0]*60)+($startArr[1])+($startArr[2]/60);

    $endArr = explode(':', $endTime);
    $decEndTime = ($endArr[0]*60)+($endArr[1])+($endArr[2]/60);


    while($decStartTime<$decEndTime)
    {
        //Work out end time
        $newTime = $decStartTime+$duration;

        //convert end time to time data type
        $hours = floor((int)$newTime / 60);
        $minutes = floor((int)$newTime % 60);
        $seconds = $newTime - (int)$newTime; 
        $seconds = round($seconds * 60); 
        $finalTime = str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":" . str_pad($seconds, 2, "0", STR_PAD_LEFT);


        $query = "INSERT INTO slot (startTime, endTime, createdBy) VALUES ('$startTime', '$finalTime',$user)";
        $result = $db->query($query);

        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 41)";
        $result3 = $db->query($query3);

        $startTime = $finalTime;

        $startArr = explode(':', $startTime);
        $decStartTime = ($startArr[0]*60)+($startArr[1])+($startArr[2]/60);
    }

    $dateIDQuery = "SELECT id FROM date";
    $resultDateIDQuery = $db->query($dateIDQuery);

    $slotIDQuery = "SELECT id FROM slot";
    $resultSlotIDQuery = $db->query($slotIDQuery);

    while($dateRecord = $resultDateIDQuery->fetch_object())
    {
        while($slotRecord = $resultSlotIDQuery->fetch_object())
        {
            $dateSlotQuery = "INSERT INTO date_slot (date_id, slot_id) VALUES ($dateRecord->id, $slotRecord->id)";
            $resultDateSlot = $db->query($dateSlotQuery);
            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 7)";
            $result3 = $db->query($query3);
        }
        $slotIDQuery = "SELECT id FROM slot";
        $resultSlotIDQuery = $db->query($slotIDQuery);
    }

    $tableIDQuery = "SELECT id FROM tafel where type_id = 3";
    $resultTableIDQuery = $db->query($tableIDQuery);

    $dateSlotIDQuery = "SELECT * FROM date_slot";
    $resultDateSlotIDQuery = $db->query($dateSlotIDQuery);

    while($tableRecord = $resultTableIDQuery->fetch_object())
    {
        while($dateSlotRecord = $resultDateSlotIDQuery->fetch_object())
        {
            $tableDateSlotQuery = "INSERT INTO table_date_slot (date_id, table_id, slot_id) VALUES ($dateSlotRecord->date_id, $tableRecord->id, $dateSlotRecord->slot_id)";
            $resultTableDateSlot = $db->query($tableDateSlotQuery);
            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 47)";
            $result3 = $db->query($query3);
        }
        $dateSlotIDQuery = "SELECT * FROM date_slot";
        $resultDateSlotIDQuery = $db->query($dateSlotIDQuery);
    }

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"New slots have been created");
        $notification2 = createANotification($user,"You have added new slots.");
    }

    if($resultDateSlot)
    {
        return "worked";
    }

    return "did not work";
}