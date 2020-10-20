<?php
function getAllDates(){

    $db = db::getConnection();
    $stmt = $db->prepare("SELECT id as 'dateId', date.date, bookable FROM edumarxc_bmwdatabase.date;");
    if(!$stmt->execute())
        return false;
    $result = $stmt->get_result();
    $dates = [];
    while($result AND $record = $result->fetch_object())
    {
        $dates[] = $record;
    }

    return $dates;
}

    function availableDates($month, $year)
    {
        $startDate = $year.'-'.$month."-01";
        $lastDate = date('Y-m-t',strtotime($startDate));

        $db = db::getConnection();
        $stmt = $db->prepare("SELECT id as 'dateId', date.date, bookable FROM edumarxc_bmwdatabase.date WHERE date.date BETWEEN ? AND ?");
        $stmt->bind_param('ss',$startDate,$lastDate);
        if(!$stmt->execute())
            return false;

        $availableDates = [];

        $result = $stmt->get_result();

        while($result AND $record = $result->fetch_object())
        {
            $availableDates[] = $record;
        }

        return $availableDates;
    }

    function changeDateBooking($dateId, $bookable)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE date SET bookable = ? WHERE id = ?;");
        $stmt->bind_param('ii',$bookable,$dateId);
        if($stmt->execute())
            return true;
        return false;

    }

    

