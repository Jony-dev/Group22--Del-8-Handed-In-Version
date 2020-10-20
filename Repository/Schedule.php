<?php
function getAllSchedules(){

    $db = db::getConnection();
    $query = "SELECT * FROM schedule";
    $result = $db->query($query);
    $schedules = [];
    while( $record = $result->fetch_object())
    {
        $schedule = new stdClass();
        $schedule->id = $record->id;
        $schedule->schedule = $record->schedule;
        $schedules[] = $schedule;
    }

    return $schedules;
}

function createASchedule($schedule, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM schedule";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->schedule == $schedule)
        {
            return "duplicate";
            
        }
    }


    $query2 = "INSERT INTO schedule (schedule) VALUES ('$schedule')";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 39)";
    $result3 = $db->query($query3);

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

//update schedule
function updateASchedule($id,$schedule, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM schedule";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->schedule == $schedule )
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE schedule SET schedule='$schedule' WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 39)";
    $result1 = $db->query($query3);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}