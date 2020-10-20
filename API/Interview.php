<?php
function getAllInterviews(){

    $db = db::getConnection();
    $query = "SELECT * FROM interview";
    $result = $db->query($query);
    $interviews = [];
    while( $record = $result->fetch_object())
    {
        $interview = new stdClass();
        $interview->interviewid = $record->id;
        $interview->cardid = $record->card_id;
        $interview->date = $record->date;
        $interview->applicationid = $record->application_id;
        $interview->overallcomment= $record->overal_comment;
        $interview->time= $record->time;
        $interview->where= $record->Where;
        $interview->conducted= $record->conducted;
        $interview->createdby = $record->createdBy;
        $interview->updatedby = $record->updatedBy;
        $interview->createddate = $record->createdDate;
        $interview->updateddate = $record->updatedDate;
        $interview->deleted = $record->deleted;
        $interviews[] = $interview;
    }

    return $interviews;
}

    function generateInterview($userId, $cardId,$date,$time,$place, $applicationId, $interviewers)
    {
        $db = db::getConnection();
        $query = "INSERT INTO interview (card_id, date, time, createdBy, updatedBy, application_id, place) VALUES ($cardId,'$date','$time', $userId,$userId,$applicationId, '$place' )";
        $result = $db->query($query);

        if(!$result)
            return false;

        $interviewId = $db->insert_id;

        foreach ($interviewers as $interviewer)
        {
            $query = "INSERT INTO interviewer_interview (user_id, interview_id, createdBy, updatedBy) VALUES ($interviewer,$interviewId,$userId, $userId)";
            $result = $db->query($query);
            if(!$result)
                return false;
        }
        return true;

    }

    function deleteAnInterview($interviewId, $userId)
    {
        $db = db::getConnection();

        $interviewerQuery = "SELECT interviewer_interview.user_id 
        FROM interviewer_interview
        WHERE interviewer_interview.interview_id=$interviewId";
        $interviewerResult = $db->query($interviewerQuery);

        while($record = $interviewerResult->fetch_object())
        {
                $query1 = "DELETE FROM interviewer_interview WHERE interview_id=$interviewId AND user_id=$record->user_id";
                $notification2 = createANotification($record->user_id,"You have been removed from an interview and no longer need to conduct it.");
                $result1 = $db->query($query1);
        }

        $intervieweeQuery = "SELECT application.user_id 
        FROM application
        INNER JOIN interview
        ON interview.application_id = application.id
        WHERE interview.id=$interviewId";
        $intervieweeResult = $db->query($intervieweeQuery);
        $intervieweeRecord = $intervieweeResult->fetch_object();
        $notification = createANotification($intervieweeRecord->user_id,"You have been removed from an interview and no longer need to attend it.");
                



    $deleteQuery = "DELETE FROM interview WHERE id = $interviewId";
    $interviewResult = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($userId, 4, 13)";
    $result4 = $db->query($query4);


    if($interviewResult && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
    }


    function getInterViewByCard($cardId)
    {
        $db = db::getConnection();
        $query = "
            SELECT interview.id as 'id', user_profile.name as 'applicantName',user_profile.surname as 'applicantSurname', interview.date, interview.time FROM interview 
            INNER JOIN application on interview.application_id = application.id
            INNER JOIN user_profile on user_profile.id = application.user_id 
            WHERE interview.card_id = $cardId AND date > CURDATE()
        ;";
        $result = $db->query($query);

        $interviews = [];
        if(!$result)
            return $interviews;
        while($record = $result->fetch_object())
        {
            $interviews[] = $record;
        }
        return $interviews;
    }

    function getConductingInterviews($user)
    {
        $db = db::getConnection();
        $query = "
                SELECT interview.id as 'interviewId', user_profile.id as 'id', user_profile.name as 'userName',user_profile.surname as 'userSurname', interview.date, interview.time FROM interviewer_interview
                INNER JOIN interview on interviewer_interview.interview_id = interview.id
                INNER JOIN application on interview.application_id = application.id
                INNER JOIN user_profile on user_profile.id = application.user_id 
                WHERE interviewer_interview.user_id = $user 
            ;";
        $result = $db->query($query);

        $interviews = [];
        if(!$result)
            return $interviews;
        while($record = $result->fetch_object())
        {
            $interviews[] = $record;
        }
        return $interviews;
    }

    function myInterviews($user)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT interviewer_interview.user_id, interview_id as 'interviewId', interview.date as 'date', interview.time as 'time', 
                                    interview.booking_id as 'booking', user_profile.name as 'userName',user_profile.surname as 'userSurname',user_profile.picture as 'userImg' FROM interviewer_interview 
                                    INNER JOIN interview on interview.id = interviewer_interview.interview_id 
                                    INNER JOIN application on interview.application_id = application.id
                                    INNER JOIN user_profile on application.user_id = user_profile.id
                                    WHERE interview.date <= now() 
                                    AND interviewer_interview.user_id = ? 
                                    AND (interviewer_interview.comment is null OR interviewer_interview.rating is null)");
        $stmt->bind_param("i",$user);
        if(!$stmt->execute())
            return false;

        $result = $stmt->get_result();
        $interviews = [];
        while($result AND $record = $result->fetch_object())
        {
            $interviews[] = $record;
        }

        return $interviews;

    }

    function passedInterviews($cardId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT interview.id as 'id', user_profile.name as 'applicantName',user_profile.surname as 'applicantSurname', interview.date, interview.time, 
                                    (SELECT AVG(rating) FROM interviewer_interview
                                    INNER JOIN interview on interviewer_interview.interview_id = interview.id
                                    WHERE card_id = ? ) as 'score'
                                    FROM interview 
                                    INNER JOIN application on interview.application_id = application.id
                                    INNER JOIN user_profile on user_profile.id = application.user_id 
                                    WHERE interview.card_id = ? AND interview.date <= CURDATE()");
        $stmt->bind_param("ii",$cardId,$cardId);

        if(!$stmt->execute())
            return false;

        $result = $stmt->get_result();
        $passedInterviews = [];
        while($result AND $record = $result->fetch_object())
        {
            $passedInterviews [] = $record;
        }

        return $passedInterviews;
    }

    function getApplicationInterviews($interviewId)
    {

        $db = db::getConnection();
        $stmt = $db->prepare("SELECT user_profile.name as 'userName', user_profile.surname as 'userSurname', user_profile.picture as 'imgUrl', interviewer_interview.comment as 'comment', interviewer_interview.rating as 'rating'  FROM interview 
                                    INNER JOIN interviewer_interview on interviewer_interview.interview_id = interview.id
                                    INNER JOIN user_profile on interviewer_interview.user_id = user_profile.id
                                    WHERE interview.id = ?;");
        $stmt->bind_param("i",$interviewId);

        if(!$stmt->execute())
            return false;

        $result = $stmt->get_result();
        $interviews = [];
        while($result AND $record = $result->fetch_object())
        {
            $interviews[] = $record;
        }

        $stmt->prepare("SELECT overal_comment as 'overallComment' , (SELECT AVG(rating) FROM interview 
                                INNER JOIN interviewer_interview on interview.id = interviewer_interview.interview_id
                                WHERE interview.id = ?) as 'totalScore'
                                FROM interview
                                WHERE interview.id = ?");
        $stmt->bind_param("ii",$interviewId,$interviewId);

        if(!$stmt->execute())
            return false;

        $result = $stmt->get_result();

        if(!$result)
            return false;

        $interviewDetails = new stdClass();
        $result = $result->fetch_object();
        $interviewDetails->overallComment = $result->overallComment;
        $interviewDetails->totalScore = $result->totalScore;
        $interviewDetails->interviews = $interviews;

        return $interviewDetails;
    }

    function makeRating($user, $interviewId,$rating,$comment)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE interviewer_interview SET comment = ?, rating = ?, updatedBy = ?, updatedDate = Now() WHERE user_id = ? AND interview_id = ?");
        $stmt->bind_param("siiii",$comment,$rating, $user,$user,$interviewId );
        if(!$stmt->execute())
            return false;
        else
            return true;

    }
    function overallComment($comment, $interviewId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE interview SET overal_comment = ? WHERE id = ?");
        $stmt->bind_param("si",$comment , $interviewId);

        if(!$stmt->execute())
            return false;

        return true;
    }

    function getInterviewById($id)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("
                            SELECT * FROM
                            (
                           SELECT interview.id as 'interviewId', user_profile.id as 'userId' ,user_profile.name as 'interviewerName', user_profile.surname as 'interviewerSurname',user_profile.picture as 'interviewerImg', interview.date, interview.time, interview.place FROM interview
                            INNER JOIN interviewer_interview on interviewer_interview.interview_id = interview.id
                            INNER JOIN user_profile on interviewer_interview.user_id = user_profile.id
                            WHERE interview.id = ?
                            ) as A
                            JOIN 
                            (
                            SELECT interview.id,user_profile.name as 'userName',user_profile.surname as 'userSurname', user_profile.picture as 'userImg', application.id as 'applicationId' FROM interview
                            INNER JOIN application on interview.application_id = application.id
                            INNER JOIN user_profile on application.user_id = user_profile.id
                            WHERE interview.id = ?
                            ) as B
                            
                            ON A.interviewId = B.id
                            ");
        $stmt->bind_param('ii',$id,$id);
        $stmt->execute();

        $object = new stdClass();
        $applicant = new stdClass();
        $interviewers = [];
        $result = $stmt->get_result();
        while($result AND $record = $result->fetch_object())
        {
            $interviewer = new stdClass();

            $interviewer->id = $record->userId;
            $interviewer->userName = $record->interviewerName;
            $interviewer->userSurname = $record->interviewerSurname;
            $interviewer->imgUrl = $record->interviewerImg;


            $applicant->userName = $record->userName;
            $applicant->userSurname = $record->userSurname;
            $applicant->imgUrl = $record->userImg;

            $object->place = $record->place;
            $object->date = $record->date;
            $object->time = $record->time;
            $object->applicationId = $record->applicationId;
            $object->applicant = $applicant;
            $object->interviewId = $record->interviewId;
            $interviewers[] = $interviewer;
        }
        $object->interviewers = $interviewers;

        return $object;
    }
    function editInterviewDetails($userId, $date,$time,$place,$interviewId, $interviewers)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE interview SET date = ?, time = ?, updatedBy = ?, place = ? WHERE interview.id = ? ");
        $stmt->bind_param('ssisi',$date,$time,$userId,$place,$interviewId);


        if(!$stmt->execute())
            return false;

        $stmt->prepare("DELETE FROM interviewer_interview WHERE interview_id = ?");
        $stmt->bind_param('i',$interviewId);
        $stmt->execute();

        foreach($interviewers as $interview)
        {
            $stmt->prepare("INSERT INTO interviewer_interview (user_id, interview_id, createdBy, updatedBy) VALUES (?, ? ,? ,?)");
            $stmt->bind_param('iiii',$interview, $interviewId,$userId,$userId);
            if(!$stmt->execute())
                return false;
        }
        return true;

    }



















