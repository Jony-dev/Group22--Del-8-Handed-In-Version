<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once "Notification.php";
    //REMOVE BOOKINGS
    $date = date("Y-m-d");
    echo "Todays Date: ".$date."\n";

    $db = db::getConnection();
    $stmt = $db->prepare("SELECT * from date WHERE date.date = ?");
    $stmt->bind_param("s",$date);
    $stmt->execute();
    $dateId = $stmt->get_result()->fetch_object()->id;
    echo "dateId".$dateId."\n";
    if($dateId)
    {
        $stmt->close();
        $stmt = $db->prepare("SELECT * FROM individual_booking INNER JOIN user_booking on user_booking.id = individual_booking.booking_id
                                    WHERE date_id = ?");
        $stmt->bind_param("i",$dateId);
        $stmt->execute();
        $result = $stmt->get_result();

        while($result AND $record = $result->fetch_object())
        {
            if($record->checked_in == 0)
            {
                $stmt = $db->prepare("UPDATE table_date SET booked = 0 WHERE table_id = ? AND date_id = ?;");
                $stmt->bind_param("ii",$record->table_id,$record->date_id);
                echo "UPDATING BOOKED".$stmt->execute()."\n";
                $stmt->close();

                $bookingId = $record->booking_id;

                $stmt = $db->prepare("DELETE FROM individual_booking WHERE table_id = ? AND date_id = ?;");
                $stmt->bind_param("ii",$record->table_id,$record->date_id);
                echo "DELETING INDIVIDUAL BOOKING". $stmt->execute()."\n";
                $stmt->close();

                $stmt = $db->prepare("DELETE FROM user_booking WHERE id = ?;");
                $stmt->bind_param("i",$bookingId);
                echo "DELETING USER BOOKING". $stmt->execute()."\n";
                $stmt->close();
                createANotification($record->user_id,"You did not check in on time and you table booking has been removed");
            }
        }


        $stmt = $db->prepare("SELECT * FROM job_card WHERE deleted = 0");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        while($result AND $record = $result->fetch_object())
        {
            if($record->publishingDate == $date AND $record->published == 0 AND $record->approved == 1)
            {
                $stmt = $db->prepare("UPDATE job_card SET published = 1 WHERE id =  ?");
                $stmt->bind_param("i",$record->id);
                $stmt->execute();

                changeStatus(4,$record->id);
            }

            if($record->closingDate == $date AND $record->published == 1)
            {
                $stmt = $db->prepare("UPDATE job_card SET published = 0 WHERE id =  ?");
                $stmt->bind_param("i",$record->id);
                $stmt->execute();

                changeStatus(5,$record->id);
            }
        }


        function changeStatus($status,$cardId)
        {
            $db = db::getConnection();
            $stmt = $db->prepare("UPDATE job_card_stage SET current = 0 WHERE card_id = ?");
            $stmt->bind_param("i",$cardId);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare("INSERT INTO job_card_stage (stage_id, card_id, current) VALUES (?,?,1);");
            $stmt->bind_param("ii",$status,$cardId);
            if(!$stmt->execute())
            {
                $stmt = $db->prepare("UPDATE job_card_stage SET current = 1 WHERE stage_id = ? AND card_id = ?");
                $stmt->bind_param("ii",$status,$cardId);
                $stmt->execute();
            }
            $stmt->close();
        }
    }


//UnPublish Job Cards



//PUBLISH JOB Cards