<?php

require_once "./Notification.php";

function getAllUserBookings(){

    $db = db::getConnection();
    $query = "SELECT * FROM user_booking";
    $result = $db->query($query);
    $userbookings = [];
    while( $record = $result->fetch_object())
    {
        $userbooking = new stdClass();
        $userbooking->bookingid = $record->id;
        $userbooking->userid= $record->user_id;
        $userbooking->createdby = $record->createdBy;
        $userbooking->updatedby = $record->updatedBy;
        $userbooking->createddate = $record->createdDate;
        $userbooking->updateddate = $record->updatedDate;
        $userbooking->deleted = $record->deleted;
        $userbookings[] = $userbooking;
    }

    return $userbookings;
}

function makeAGroupBooking($table_id, $date_id, $slot_id, $user_id)
{
    $db = db::getConnection();

    $userBookingQuery = "INSERT INTO user_booking (user_id, createdBy, updatedBy) VALUES ($user_id,$user_id,$user_id)";
    $resultUserBooking = $db->query($userBookingQuery);

    $booking_id = $db->insert_id;

    $auditQuery1 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 2, 52)";
    $result = $db->query($auditQuery1);

    $groupBookingQuery = "INSERT INTO group_booking (booking_id, table_id, slot_id, date_id) VALUES ($booking_id,$table_id,$slot_id,$date_id)";
    $resultGroupBooking = $db->query($groupBookingQuery);

    $auditQuery2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 2, 11)";
    $result2 = $db->query($auditQuery2);

    $tableDateSlotQuery = "UPDATE table_date_slot SET booked = true WHERE ((table_id=$table_id) AND (date_id=$date_id) AND (slot_id=$slot_id))";
    $resultTableDateSlotQuery = $db->query($tableDateSlotQuery);

    $auditQuery3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 3, 47)";
    $result3 = $db->query($auditQuery3);

    $notification2 = createANotification($user_id,"You have booked a boardroom.");

    if($resultUserBooking && $result && $resultGroupBooking && $result2 && $resultTableDateSlotQuery && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }

}

function makeAIndividualBooking($table_id, $date_id, $user_id)
{
    $db = db::getConnection();

    $userBookingQuery = "INSERT INTO user_booking (user_id, createdBy, updatedBy) VALUES ($user_id,$user_id,$user_id)";
    $resultUserBooking = $db->query($userBookingQuery);

    $booking_id = $db->insert_id;

    $auditQuery1 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 2, 52)";
    $result = $db->query($auditQuery1);

    $individualBookingQuery = "INSERT INTO individual_booking (booking_id, table_id, date_id) VALUES ($booking_id,$table_id,$date_id)";
    $resultIndividualBooking = $db->query($individualBookingQuery);

    $auditQuery2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 2, 12)";
    $result2 = $db->query($auditQuery2);

    $tableDateQuery = "UPDATE table_date SET booked = true WHERE ((table_id=$table_id) AND (date_id=$date_id))";
    $resultTableDateQuery = $db->query($tableDateQuery);


    $auditQuery3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 3, 46)";
    $result3 = $db->query($auditQuery3);

    $notification2 = createANotification($user,"You have booked a desk.");

    if($resultUserBooking && $result && $resultIndividualBooking && $result2 && $resultTableDateQuery && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function makeAGroupBookingEmployee($user,$table_id, $date_id, $slot_id, $user_id)
{
    $db = db::getConnection();

    $userBookingQuery = "INSERT INTO user_booking (user_id, createdBy, updatedBy) VALUES ($user,$user_id,$user_id)";
    $resultUserBooking = $db->query($userBookingQuery);

    $booking_id = $db->insert_id;

    $auditQuery1 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 2, 52)";
    $result = $db->query($auditQuery1);

    $groupBookingQuery = "INSERT INTO group_booking (booking_id, table_id, slot_id, date_id) VALUES ($booking_id,$table_id,$slot_id,$date_id)";
    $resultGroupBooking = $db->query($groupBookingQuery);

    $auditQuery2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 2, 11)";
    $result2 = $db->query($auditQuery2);

    $tableDateSlotQuery = "UPDATE table_date_slot SET booked = true WHERE ((table_id=$table_id) AND (date_id=$date_id) AND (slot_id=$slot_id))";
    $resultTableDateSlotQuery = $db->query($tableDateSlotQuery);

    $auditQuery3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 3, 47)";
    $result3 = $db->query($auditQuery3);

    $notification2 = createANotification($user,"An admin has booked a boardroom on your behalf.");
    $notification2 = createANotification($user_id,"You have booked a boardroom for another employee.");

    if($resultUserBooking && $result && $resultGroupBooking && $result2 && $resultTableDateSlotQuery && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }

}

function makeAIndividualBookingEmployee($user,$table_id, $date_id, $user_id)
{
    $db = db::getConnection();

    $userBookingQuery = "INSERT INTO user_booking (user_id, createdBy, updatedBy) VALUES ($user,$user_id,$user_id)";
    $resultUserBooking = $db->query($userBookingQuery);

    $booking_id = $db->insert_id;

    $auditQuery1 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 2, 52)";
    $result = $db->query($auditQuery1);

    $individualBookingQuery = "INSERT INTO individual_booking (booking_id, table_id, date_id) VALUES ($booking_id,$table_id,$date_id)";
    $resultIndividualBooking = $db->query($individualBookingQuery);

    $auditQuery2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 2, 12)";
    $result2 = $db->query($auditQuery2);

    $tableDateQuery = "UPDATE table_date SET booked = true WHERE ((table_id=$table_id) AND (date_id=$date_id))";
    $resultTableDateQuery = $db->query($tableDateQuery);

    $auditQuery3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user_id, 3, 46)";
    $result3 = $db->query($auditQuery3);

    $notification2 = createANotification($user,"An admin has booked a desk on your behalf.");
    $notification2 = createANotification($user_id,"You have booked a desk for another employee.");

    if($resultUserBooking && $result && $resultIndividualBooking && $result2 && $resultTableDateQuery && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function cancelABooking($booking_id, $user_id)
{
    $db = db::getConnection();

    $bookingType;

    $bookingTypeQuery = "SELECT * FROM group_booking";
    $bookingTypeResult = $db->query($bookingTypeQuery);

    while($record = $bookingTypeResult->fetch_object())
    {
        if($record->booking_id == $booking_id)
        {
            $bookingType = "group";
        }
    }

    $bookingTypeQuery = "SELECT * FROM individual_booking";
    $bookingTypeResult = $db->query($bookingTypeQuery);

    while($record = $bookingTypeResult->fetch_object())
    {
        if($record->booking_id == $booking_id)
        {
            $bookingType = "individual";
        }
    }

    if($bookingType == "group")
    {
        $dateIDQuery = "SELECT date_id FROM group_booking WHERE booking_id = $booking_id";
        $dateResult = $db->query($dateIDQuery);
        $date_id = $dateResult->fetch_object();

        $slotIDQuery = "SELECT slot_id FROM group_booking WHERE booking_id = $booking_id";
        $slotResult = $db->query($slotIDQuery);
        $slot_id = $slotResult->fetch_object();

        $tableIDQuery = "SELECT table_id FROM group_booking WHERE booking_id = $booking_id";
        $tableResult = $db->query($tableIDQuery);
        $table_id = $tableResult->fetch_object();

        $updateTableQuery = "UPDATE table_date_slot SET booked = false WHERE ((table_id=$table_id->table_id) AND (date_id=$date_id->date_id) AND (slot_id=$slot_id->slot_id))";
        $updateTableResult = $db->query($updateTableQuery);

        $deleteGroupQuery = "DELETE FROM group_booking WHERE booking_id = $booking_id";
        $deleteGroupResult = $db->query($deleteGroupQuery);

        $deleteBookingQuery = "DELETE FROM user_booking WHERE id = $booking_id";
        $deleteBookingResult = $db->query($deleteBookingQuery);

        if($updateTableResult && $deleteGroupResult && $deleteBookingResult)
        {
            return "worked";
        }
        else
        {
            return "did not work";
        }
    }
    else
    {
        $dateIDQuery = "SELECT date_id FROM individual_booking WHERE booking_id = $booking_id";
        $dateResult = $db->query($dateIDQuery);
        $date_id = $dateResult->fetch_object();

        $tableIDQuery = "SELECT table_id FROM individual_booking WHERE booking_id = $booking_id";
        $tableResult = $db->query($tableIDQuery);
        $table_id = $tableResult->fetch_object();

        $updateTableQuery = "UPDATE table_date SET booked = false WHERE ((table_id=$table_id->table_id) AND (date_id=$date_id->date_id))";
        $updateTableResult = $db->query($updateTableQuery);

        $deleteIndividualQuery = "DELETE FROM individual_booking WHERE booking_id = $booking_id";
        $deleteIndividualResult = $db->query($deleteIndividualQuery);

        $deleteBookingQuery = "DELETE FROM user_booking WHERE id = $booking_id";
        $deleteBookingResult = $db->query($deleteBookingQuery);

        if($updateTableResult && $deleteIndividualQuery && $deleteBookingResult)
        {
            return "worked";
        }
        else
        {
            return "did not work";
        }
    }
}

function CheckAnAvailability($tableId, $user)
{
    
    $db = db::getConnection();

    $bookingType;
    $slotId;
    $dateId;

    $bookingTypeQuery = "SELECT * FROM table_date_slot WHERE table_id=$tableId";
    $bookingTypeResult = $db->query($bookingTypeQuery);

    while($record = $bookingTypeResult->fetch_object())
    {
        if($record->table_id == $tableId)
        {
            $bookingType = "group";
        }
    }

    $bookingTypeQuery = "SELECT * FROM table_date WHERE table_id = $tableId";
    $bookingTypeResult = $db->query($bookingTypeQuery);
    

    while($record = $bookingTypeResult->fetch_object())
    {
        if($record->table_id == $tableId)
        {
            $bookingType = "individual";
        }
    }

    if($bookingType == "group")
    {
        $slotIdQuery = "SELECT id, startTime, endTime FROM slot";
        $slotIdResult = $db->query($slotIdQuery);

        while($record = $slotIdResult->fetch_object())
        {
            if($record->startTime < date('H:i:s') && $record->endTime > date('H:i:s'))
            {
                $slotId = $record->id;
            }
        }

        if($slotId)
        {
            $dateIdQuery = "SELECT id, date FROM date";
            $dateIdResult = $db->query($dateIdQuery);

            while($record = $dateIdResult->fetch_object())
            {
                if($record->date == date('Y-m-d'))
                {
                    $dateId = $record->id;
                }
            }

            $availabilityQuery = "SELECT booked FROM table_date_slot WHERE table_id = $tableId AND slot_id=$slotId AND date_id=$dateId";
            $availabilityResult = $db->query($availabilityQuery);

            $isAvailableRecord = $availabilityResult->fetch_object();
            if($isAvailableRecord->booked == true)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    else
    {
        $dateIdQuery = "SELECT id, date FROM date";
        $dateIdResult = $db->query($dateIdQuery);

        while($record = $dateIdResult->fetch_object())
        {
            if($record->date == date('Y-m-d'))
            {
                $dateId = $record->id;
            }
        }

        $availabilityQuery = "SELECT booked FROM table_date WHERE table_id = $tableId AND date_id=$dateId";
        $availabilityResult = $db->query($availabilityQuery);

        $isAvailableRecord = $availabilityResult->fetch_object();
        if($isAvailableRecord->booked == true)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

function CheckInTable($tableId,$user)
{
    $db = db::getConnection();

    $booked = CheckAnAvailability($tableId, $user);

    if($booked)
    {
        
        $bookingType;
        $slotId;
        $dateId;

        $bookingTypeQuery = "SELECT * FROM table_date_slot WHERE table_id=$tableId";
        $bookingTypeResult = $db->query($bookingTypeQuery);

        while($record = $bookingTypeResult->fetch_object())
        {
            if($record->table_id == $tableId)
            {
                $bookingType = "group";
            }
        }

        $bookingTypeQuery = "SELECT * FROM table_date WHERE table_id = $tableId";
        $bookingTypeResult = $db->query($bookingTypeQuery);

        while($record = $bookingTypeResult->fetch_object())
        {
            if($record->table_id == $tableId)
            {
                $bookingType = "individual";
            }
        }

        if($bookingType == "group")
        {
            $slotIdQuery = "SELECT id, startTime, endTime FROM slot";
            $slotIdResult = $db->query($slotIdQuery);

            while($record = $slotIdResult->fetch_object())
            {
                if($record->startTime < date('H:i:s') && $record->endTime > date('H:i:s'))
                {
                    $slotId = $record->id;
                }
            }

            $dateIdQuery = "SELECT id, date FROM date";
            $dateIdResult = $db->query($dateIdQuery);

            while($record = $dateIdResult->fetch_object())
            {
                if($record->date == date('Y-m-d'))
                {
                    $dateId = $record->id;
                }
            }

            $bookingQuery = "SELECT booking_id FROM group_booking WHERE table_id = $tableId AND slot_id=$slotId AND date_id=$dateId";
            $bookingResult = $db->query($bookingQuery);

            $userBookingRecord = $bookingResult->fetch_object();
            $bookingId = $userBookingRecord->booking_id;

            $correctUser = "SELECT user_id FROM user_booking WHERE id = $bookingId";
            $correctUserResult = $db->query($correctUser);
            $userIdRecord = $correctUserResult->fetch_object();

            if($userIdRecord->user_id == $user)
            {
                $checkCheckedOutQuery = "SELECT check_in, check_out FROM group_booking WHERE booking_id = $bookingId";
                $checkCheckedOutResult = $db->query($checkCheckedOutQuery);
                $record = $checkCheckedOutResult->fetch_object();

                if($record->check_in == false && $record->check_out == false)
                {
                    $updateQuery = "UPDATE group_booking SET check_in = true WHERE booking_id = $bookingId";
                    $updateResult = $db->query($updateQuery);
                    return "successful";
                }
                else{
                    return "You have already checked into this table";
                }
            }
            else
            {
                return "This table is booked by someone else";
            }
        }
        else
        {
            $dateIdQuery = "SELECT id, date FROM date";
            $dateIdResult = $db->query($dateIdQuery);

            while($record = $dateIdResult->fetch_object())
            {
                if($record->date == date('Y-m-d'))
                {
                    $dateId = $record->id;
                }
            }

            $bookingQuery = "SELECT booking_id FROM individual_booking WHERE table_id = $tableId AND date_id=$dateId";
            $bookingResult = $db->query($bookingQuery);

            $userBookingRecord = $bookingResult->fetch_object();
            $bookingId = $userBookingRecord->booking_id;

            $correctUser = "SELECT user_id FROM user_booking WHERE id = $bookingId";
            $correctUserResult = $db->query($correctUser);
            $userIdRecord = $correctUserResult->fetch_object();

            if($userIdRecord->user_id == $user)
            {
                $checkCheckedOutQuery = "SELECT checked_in, checked_out FROM individual_booking WHERE booking_id = $bookingId";
                $checkCheckedOutResult = $db->query($checkCheckedOutQuery);
                $record = $checkCheckedOutResult->fetch_object();

                if($record->checked_in == false && $record->checked_out == false)
                {
                    $updateQuery = "UPDATE individual_booking SET checked_in = true WHERE booking_id = $bookingId";
                    $updateResult = $db->query($updateQuery);
                    return "successful";
                }
                else{
                    return "You have already checked into this table";
                }
            }
            else
            {
                return "This table is booked by someone else";
            }
        }
    }
    else
    {
        $bookingType;
        $slotId;
        $dateId;

        $bookingTypeQuery = "SELECT * FROM table_date_slot WHERE table_id=$tableId";
        $bookingTypeResult = $db->query($bookingTypeQuery);

        while($record = $bookingTypeResult->fetch_object())
        {
            if($record->table_id == $tableId)
            {
                $bookingType = "group";
            }
        }

        $bookingTypeQuery = "SELECT * FROM table_date WHERE table_id = $tableId";
        $bookingTypeResult = $db->query($bookingTypeQuery);

        while($record = $bookingTypeResult->fetch_object())
        {
            if($record->table_id == $tableId)
            {
                $bookingType = "individual";
            }
        }

        if($bookingType == "group")
        {
            $slotIdQuery = "SELECT id, startTime, endTime FROM slot";
            $slotIdResult = $db->query($slotIdQuery);

            while($record = $slotIdResult->fetch_object())
            {
                if($record->startTime < date('H:i:s') && $record->endTime > date('H:i:s'))
                {
                    $slotId = $record->id;
                }
            }

            $dateIdQuery = "SELECT id, date FROM date";
            $dateIdResult = $db->query($dateIdQuery);

            while($record = $dateIdResult->fetch_object())
            {
                if($record->date == date('Y-m-d'))
                {
                    $dateId = $record->id;
                }
            }

            $makeBooking = makeAGroupBooking($tableId, $dateId, $slotId, $user);

            $bookingQuery = "SELECT booking_id FROM group_booking WHERE table_id = $tableId AND slot_id=$slotId AND date_id=$dateId";
            $bookingResult = $db->query($bookingQuery);

            $userBookingRecord = $bookingResult->fetch_object();
            $bookingId = $userBookingRecord->booking_id;

            $updateQuery = "UPDATE group_booking SET check_in = true WHERE booking_id = $bookingId";
            $updateResult = $db->query($updateQuery);
            if($bookingResult && $updateResult)
            {
                return "You have successfully booked and checked into this table";
            }
            else
            {
                return "unsuccessful";
            }
        }
        else
        {
            $dateIdQuery = "SELECT id, date FROM date";
            $dateIdResult = $db->query($dateIdQuery);

            while($record = $dateIdResult->fetch_object())
            {
                if($record->date == date('Y-m-d'))
                {
                    $dateId = $record->id;
                }
            }

            $makeBooking = makeAIndividualBooking($tableId, $dateId, $user);

            $bookingQuery = "SELECT booking_id FROM individual_booking WHERE table_id = $tableId AND date_id=$dateId";
            $bookingResult = $db->query($bookingQuery);

            $userBookingRecord = $bookingResult->fetch_object();
            $bookingId = $userBookingRecord->booking_id;

            $updateQuery = "UPDATE individual_booking SET checked_in = true WHERE booking_id = $bookingId";
            $updateResult = $db->query($updateQuery);
            if($bookingResult && $updateResult)
            {
                return "You have successfully booked and checked into this table";
            }
            else
            {
                return "unsuccessful";
            }
        }
    }
}

function CheckOutTable($tableId,$user)
{
    $db = db::getConnection();

    $bookingType;
    $slotId;
    $dateId;

    $bookingTypeQuery = "SELECT * FROM table_date_slot WHERE table_id=$tableId";
    $bookingTypeResult = $db->query($bookingTypeQuery);

    while($record = $bookingTypeResult->fetch_object())
    {
        if($record->table_id == $tableId)
        {
            $bookingType = "group";
        }
    }

    $bookingTypeQuery = "SELECT * FROM table_date WHERE table_id = $tableId";
    $bookingTypeResult = $db->query($bookingTypeQuery);

    while($record = $bookingTypeResult->fetch_object())
    {
        if($record->table_id == $tableId)
        {
            $bookingType = "individual";
        }
    }


    if($bookingType == "group")
    {
        $slotIdQuery = "SELECT id, startTime, endTime FROM slot";
        $slotIdResult = $db->query($slotIdQuery);

        while($record = $slotIdResult->fetch_object())
        {
            if($record->startTime < date('H:i:s') && $record->endTime > date('H:i:s'))
            {
                $slotId = $record->id;
            }
        }

        $dateIdQuery = "SELECT id, date FROM date";
        $dateIdResult = $db->query($dateIdQuery);

        while($record = $dateIdResult->fetch_object())
        {
            if($record->date == date('Y-m-d'))
            {
                $dateId = $record->id;
            }
        }

        $bookingQuery = "SELECT booking_id FROM group_booking WHERE table_id = $tableId AND slot_id=$slotId AND date_id=$dateId";
        $bookingResult = $db->query($bookingQuery);

        $userBookingRecord = $bookingResult->fetch_object();
        $bookingId = $userBookingRecord->booking_id;

        $correctUser = "SELECT user_id FROM user_booking WHERE id = $bookingId";
        $correctUserResult = $db->query($correctUser);
        $userIdRecord = $correctUserResult->fetch_object();
        if($userIdRecord->user_id == $user)
        {
            $checkCheckedOutQuery = "SELECT check_in, check_out FROM group_booking WHERE booking_id = $bookingId";
            $checkCheckedOutResult = $db->query($checkCheckedOutQuery);
            $record = $checkCheckedOutResult->fetch_object();

            if($record->check_in == true && $record->check_out == false)
            {
                $updateQuery = "UPDATE group_booking SET check_out = true WHERE booking_id = $bookingId";
                $updateResult = $db->query($updateQuery);
                $cancel = cancelABooking($bookingId, $userId);
                if($updateResult && ($cancel == "worked"))
                {
                    return "successful";
                }
                else
                {
                    return "unsuccessful";
                }
            }
            else
            {
                return "You have not checked in to this table";
            }
        }
        else
        {
            return "This table is booked by someone else";
        }
    }
    else
    {
        $dateIdQuery = "SELECT id, date FROM date";
        $dateIdResult = $db->query($dateIdQuery);

        while($record = $dateIdResult->fetch_object())
        {
            if($record->date == date('Y-m-d'))
            {
                $dateId = $record->id;
            }
        }

        $bookingQuery = "SELECT booking_id FROM individual_booking WHERE table_id = $tableId AND date_id=$dateId";
        $bookingResult = $db->query($bookingQuery);

        $userBookingRecord = $bookingResult->fetch_object();
        $bookingId = $userBookingRecord->booking_id;

        $correctUser = "SELECT user_id FROM user_booking WHERE id = $bookingId";
        $correctUserResult = $db->query($correctUser);
        $userIdRecord = $correctUserResult->fetch_object();

        if($userIdRecord->user_id == $user)
        {
            $checkCheckedOutQuery = "SELECT checked_in, checked_out FROM individual_booking WHERE booking_id = $bookingId";
            $checkCheckedOutResult = $db->query($checkCheckedOutQuery);
            $record = $checkCheckedOutResult->fetch_object();

            if($record->checked_in == true && $record->checked_out == false)
            {
                $updateQuery = "UPDATE individual_booking SET checked_out = true WHERE booking_id = $bookingId";
                $updateResult = $db->query($updateQuery);
                $cancel = cancelABooking($bookingId, $user);
                if($updateResult && ($cancel == "worked"))
                {
                    return "successful";
                }
                else
                {
                    return "unsuccessful";
                }
            }
            else
            {
                return "You have not checked in to this table";
            }
        }
        else
        {
            return "This table is booked by someone else";
        }
    }
}

function getIndividualsBooking($month, $year, $userId)
{
    $startDate = $year.'-'.$month."-01";
    $lastDate = date('Y-m-t',strtotime($startDate));

    $db = db::getConnection();
    $stmt = $db->prepare("SELECT date.id as 'dateId', tafel.id as 'tableId', tafel.name, individual_booking.booking_id as 'bookingId', building.id as 'buildingId' FROM edumarxc_bmwdatabase.individual_booking
                                INNER JOIN user_booking on user_booking.id = individual_booking.booking_id
                                INNER JOIN tafel on individual_booking.table_id = tafel.id
                                INNER JOIN date on individual_booking.date_id = date.id
                                INNER JOIN floor on tafel.floor_id = floor.id
                                INNER JOIN building on floor.building_id = building.id
                                WHERE date.date BETWEEN ? AND ? AND user_booking.user_id = ?");
    $stmt->bind_param('ssi',$startDate,$lastDate,$userId);

    if(!$stmt->execute())
        return false;

    $result = $stmt->get_result();

    $userBookings = [];
    while ($result AND $record = $result->fetch_object())
    {
        $userBookings[] = $record;
    }

    return $userBookings;

}

    function getEmployeesAndTeams($user){

        $db = db::getConnection();
        $stmt = $db->prepare("SELECT team.id as 'teamId', team.name, user_job_profile.department_id as 'departmentId'  FROM user_profile 
                                        INNER JOIN user_job_profile on user_profile.id = user_job_profile.user_id
                                        LEFT JOIN team on user_job_profile.department_id = team.department_id
                                        WHERE user_profile.id = ?");

        $stmt->bind_param("i",$user);

        $teams = [];
        if(!$stmt->execute())
            return false;

        $result = $stmt->get_result();
        while($result AND $record = $result->fetch_object())
        {
            $teams[] = $record;
        }

        $stmt->prepare("SELECT department_id  FROM user_profile 
                                    INNER JOIN user_job_profile on user_profile.id = user_job_profile.user_id
                                    WHERE user_profile.id = ?");
        $stmt->bind_param('i',$user);
        $stmt->execute();
        $departmentId = $stmt->get_result()->fetch_object();

        $stmt = $db->prepare("SELECT user_profile.id, user_profile.name as 'userName', user_profile.surname as 'userSurname', user_profile.picture as 'imgUrl'  FROM user_profile 
                                        INNER JOIN user_job_profile on user_profile.id = user_job_profile.user_id
                                        WHERE user_job_profile.department_id = ? AND user_profile.id != ?");

        $stmt->bind_param("ii",$departmentId,$user);

        $employees = [];
        if(!$stmt->execute())
            return false;


        $result = $stmt->get_result();
        while($result AND $record = $result->fetch_object())
        {
            $employees[] = $record;
        }

        $object = new stdClass();
        $object->teams = $teams;
        $object->employees = $employees;

        return $object;
    }
    function getGroupBookings($month, $year, $userId)
    {
        $startDate = $year.'-'.$month."-01";
        $lastDate = date('Y-m-t',strtotime($startDate));

        $db = db::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as 'numBookings', date_id as 'dateId' FROM edumarxc_bmwdatabase.group_booking
                                    INNER JOIN tafel on tafel.id = group_booking.table_id
                                    INNER JOIN floor on tafel.floor_id = floor.id
                                    INNER JOIN date on date_id = date.id
                                    INNER JOIN user_booking on group_booking.booking_id = user_booking.id
                                    WHERE date.date BETWEEN ? AND ? AND user_id = ?
                                    GROUP BY date_id");
        $stmt->bind_param("ssi",$startDate,$lastDate,$userId);

        $bookings = [];
        if(!$stmt->execute())
            return false;

        $result = $stmt->get_result();
        while ($result AND $record = $result->fetch_object())
        {
            $bookings[]= $record;
        }

        return $bookings;
    }

    function getSlotsForBuilding($dateId,$buildingId){

        $db = db::getConnection();
        $stmt = $db->prepare("SELECT table_date_slot.table_id as 'tableId',tafel.name, table_date_slot.date_id as 'dateId', table_date_slot.slot_id as 'slotId',table_date_slot.booked , startTime, endTime, user_id as 'userId', user_profile.name as 'userName',user_profile.surname as 'userSurname', user_booking.id as 'bookingId'  FROM table_date_slot
                                    INNER JOIN tafel on tafel.id = table_date_slot.table_id
                                    INNER JOIN floor on tafel.floor_id = floor.id
                                    INNER JOIN building on floor.building_id = building.id
                                    INNER JOIN slot on slot.id = table_date_slot.slot_id
                                    LEFT JOIN group_booking on slot.id = group_booking.slot_id AND table_date_slot.table_id = group_booking.table_id AND group_booking.date_id = table_date_slot.date_id
                                    LEFT JOIN user_booking on group_booking.booking_id = user_booking.id
                                    LEFT JOIN user_profile on user_booking.user_id = user_profile.id
                                    WHERE table_date_slot.date_id = ? AND building.id = ? AND tafel.deleted = 0");
        $stmt->bind_param('ii',$dateId,$buildingId);

        if(!$stmt->execute())
            return false;

        $tables = [];
        $currentTable = new stdClass();
        $result = $stmt->get_result();

        while($result AND $record = $result->fetch_object())
        {
            if(!property_exists($currentTable,"tableId"))
            {
                $currentTable = new stdClass();
                $currentTable->tableId = $record->tableId;
                $currentTable->name = $record->name;
                $currentTable->slots = [];
            }
            else if($currentTable->tableId != $record->tableId)
            {
                $tables[] = $currentTable;
                $currentTable = new stdClass();
                $currentTable->tableId = $record->tableId;
                $currentTable->name = $record->name;
                $currentTable->slots = [];
            }

            $slot = new stdClass();
            $slot->dateId = $record->dateId;
            $slot->slotId = $record->slotId;
            $slot->startTime = $record->startTime;
            $slot->endTime = $record->endTime;
            $slot->userId = $record->userId;
            $slot->userSurname = $record->userSurname;
            $slot->userName = $record->userName;
            $slot->booked = $record->booked;
            $slot->bookingId = $record->bookingId;
            $currentTable->slots [] = $slot;
        }
        $tables [] = $currentTable;

        return $tables;
    }

    function getAllGroupBookingReport()
    {
        $db = db::getConnection();
        $query = "SELECT user_profile.name AS 'userName',user_profile.surname AS 'userSurname',tafel.id AS 'tableId',slot.startTime,slot.endTime, date.date
        FROM group_booking
        INNER JOIN user_booking
        ON user_booking.id = group_booking.booking_id
        INNER JOIN user_profile
        ON user_profile.id = user_booking.user_id
        INNER JOIN tafel
        ON group_booking.table_id=tafel.id
        INNER JOIN slot
        ON slot.id=group_booking.slot_id
        INNER JOIN date 
        ON date.id=group_booking.date_id";
        $result = $db->query($query);
        $bookings = [];
        while( $record = $result->fetch_object())
        {
            $booking = new stdClass();
            $booking->userName = $record->userName;
            $booking->userSurname= $record->userSurname;
            $booking->tableId = $record->tableId;
            $booking->startTime = $record->startTime;
            $booking->endTime = $record->endTime;
            $booking->date = $record->date;
            $bookings[] = $booking;
        }

        return $bookings;
    }

    function getAllIndividualBookingReport()
    {
        $db = db::getConnection();
        $query = "SELECT user_profile.name AS 'userName',user_profile.surname AS 'userSurname',tafel.id AS 'tableId', date.date
        FROM individual_booking
        INNER JOIN user_booking
        ON user_booking.id = individual_booking.booking_id
        INNER JOIN user_profile
        ON user_profile.id = user_booking.user_id
        INNER JOIN tafel
        ON individual_booking.table_id=tafel.id
        INNER JOIN date 
        ON date.id=individual_booking.date_id;";
        $result = $db->query($query);
        $bookings = [];
        while( $record = $result->fetch_object())
        {
            $booking = new stdClass();
            $booking->userName = $record->userName;
            $booking->userSurname= $record->userSurname;
            $booking->tableId = $record->tableId;
            $booking->date = $record->date;
            $bookings[] = $booking;
        }

        return $bookings;
    }