<?php

require_once "./Notification.php";
require_once "./JobCard.php";

function getAllJobRequests(){

    $db = db::getConnection();
    $query = "
    SELECT job_request.id, user_profile.id as 'userId' , user_profile.name as 'userName', user_profile.surname as 'userSurname', justification.id as 'justificationId', justification.justification, fulfilmentDate,
    job_name as 'jobName', job.id as 'jobId', brief, job_card.id as 'jobCardId'
    FROM job_request INNER JOIN user_profile on user_profile.id = job_request.createdBy INNER JOIN justification on justification.id = job_request.justification_id INNER JOIN
    job on job.id = job_request.job_id LEFT OUTER JOIN job_card on job_card.jrequest_id = job_request.id WHERE job_card.id IS NULL;
    ";
    $result = $db->query($query);
    $jobRequests = [];
    if($result)
    while( $record = $result->fetch_object())
    {
        $request = new stdClass();
        $request->id = $record->id;
        $request->brief = $record->brief;
        $request->fulfilmentDate = $record->fulfilmentDate;

        $justification = new stdClass();
        $justification->id = $record->justificationId;
        $justification->justification = $record->justification;

        $user = new stdClass();
        $user->id = $record->userId;
        $user->name = $record->userName;
        $user->surname = $record->userSurname;

        $job = new stdClass();
        $job->id = $record->jobId;
        $job->name = $record->jobName;

        $request->user = $user;
        $request->justification = $justification;
        $request->jobPosition = $job;
        $jobRequests[] = $request;

    }

    return $jobRequests;
}

function createAJobRequest($job_id, $justification_id, $brief, $fulfilmentDate, $user)
{
    $db = db::getConnection();

    $query2 = "INSERT INTO job_request(job_id, justification_id, brief, fulfilmentDate, createdBy, updatedBy, deleted) VALUES ($job_id, $justification_id,'$brief', '$fulfilmentDate', '$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 20)";
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

function updateAJobRequest($id, $job_id, $justification_id,$brief, $fulfilmentDate, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM job_card";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->jrequest_id == $id)
        {
            return "job card exists";
        }
    }

    $query2 = "UPDATE job_request SET job_id = $job_id, justification_id = $justification_id, brief = '$brief', fulfilmentDate = '$fulfilmentDate', updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 3, 20)";
    $result1 = $db->query($query3);

    if($result && $result1)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function getManagersJobRequests($user)
{
    $db = db::getConnection();
    $query = "
        SELECT * FROM (SELECT job_request.id as 'jobRequestId', justification_id as 'justificationId', job_request.createddate as 'requestedDate', job_request.rejected as 'rejected',job.job_name as 'job', job_card.id as 'jobCardId',
        job_request.brief as 'brief', job_request.job_id as 'jobId', job_request.fulfilmentDate as 'fulfillmentDate' FROM job_request 
        LEFT OUTER JOIN job_card on job_card.jrequest_id = job_request.id INNER JOIN job on job_request.job_id = job.id WHERE job_request.deleted = 0 AND job_request.Createdby = $user) AS t1

        LEFT OUTER JOIN
        
        (SELECT job_card_stage.card_id as 'cardId',stage.stage as 'stage' FROM job_card_stage INNER JOIN stage on job_card_stage.stage_id = stage.id WHERE job_card_stage.current = 1) AS t2
        
        ON t1.jobCardId = t2.cardId
    
    ";
    $result = $db->query($query);
    $requests = [];
    if($result)
    {
        while($record = $result->fetch_object())
        {
            $stage = "Approval";
            //IF WE HAVE A JOB CARD THEN IT WILL BE APPROVED
            $request = new stdClass();
            $request->status = $record->rejected ? "Rejected" : "Undecided";

            if($record->jobCardId)
            {
                $stage = $record->stage;
                $request->status = "Approved";
            }


            $request->requestId = $record->jobRequestId;
            $request->stage = $stage;

            $request->requestedDate = $record->requestedDate;
            $request->jobCardId = $record->jobCardId;
            $request->job = $record->job;
            $request->justificationId = $record->justificationId;
            $request->brief = $record->brief;
            $request->fulfillmentDate = $record->fulfillmentDate;
            $request->jobId = $record->jobId;
            $requests[] = $request;
        }

    }
    return $requests;
}
function getJobReqById($id){

    $db = db::getConnection();
    $query = "
    SELECT job_request.id, user_profile.id as 'userId' , user_profile.name as 'userName', user_profile.surname as 'userSurname', justification.id as 'justificationId', justification.justification, fulfilmentDate,
    job_name as 'jobName', job.id as 'jobId', brief, job_card.id as 'jobCardId'
    FROM job_request INNER JOIN user_profile on user_profile.id = job_request.createdBy INNER JOIN justification on justification.id = job_request.justification_id INNER JOIN
    job on job.id = job_request.job_id LEFT OUTER JOIN job_card on job_card.jrequest_id = job_request.id WHERE job_request.id = $id;
    ";
    $result = $db->query($query);
    $jobRequests = [];
    if($result)
        while( $record = $result->fetch_object())
        {
            $request = new stdClass();
            $request->id = $record->id;
            $request->brief = $record->brief;
            $request->fulfilmentDate = $record->fulfilmentDate;

            $justification = new stdClass();
            $justification->id = $record->justificationId;
            $justification->justification = $record->justification;

            $user = new stdClass();
            $user->id = $record->userId;
            $user->name = $record->userName;
            $user->surname = $record->userSurname;

            $job = new stdClass();
            $job->id = $record->jobId;
            $job->name = $record->jobName;

            $request->user = $user;
            $request->justification = $justification;
            $request->jobPosition = $job;
            $jobRequests[] = $request;

        }

    return $jobRequests;

}

function getAssignedJobs($userId){


    $db = db::getConnection();
    $query = "
    SELECT job_request.id as 'id' ,user_profile.id as 'userId', user_profile.name as 'userName', user_profile.surname as 'userSurname', justification.id as 'justificationId'
    , justification.justification , fulfilmentDate, job_name as 'jobName',job.id as 'jobId', brief, job_card.id as 'jobCardId'
    FROM job_card_user
    RIGHT JOIN job_card on job_card_user.card_id = job_card.id
    INNER JOIN job_request on job_card.jrequest_id = job_request.id
    INNER JOIN justification on job_request.justification_id = justification.id
    INNER JOIN job on job_request.job_id = job.id
    INNER JOIN user_profile on job_request.CreatedBy = user_profile.id
    WHERE job_card_user.role_id = 4 AND job_card_user.user_id = $userId AND job_card.card_name is NULL
    ";
    $result = $db->query($query);
    $jobRequests = [];
    if($result)
        while( $record = $result->fetch_object())
        {
            $request = new stdClass();
            $request->id = $record->id;
            $request->brief = $record->brief;
            $request->fulfilmentDate = $record->fulfilmentDate;
            $request->jobCardId = $record->jobCardId;

            $justification = new stdClass();
            $justification->id = $record->justificationId;
            $justification->justification = $record->justification;

            $user = new stdClass();
            $user->id = $record->userId;
            $user->name = $record->userName;
            $user->surname = $record->userSurname;

            $job = new stdClass();
            $job->id = $record->jobId;
            $job->name = $record->jobName;

            $request->user = $user;
            $request->justification = $justification;
            $request->jobPosition = $job;
            $jobRequests[] = $request;

        }

    return $jobRequests;







}

function rejectAJobRequest($jrequestId,$message,$user)
{
    $db = db::getConnection();

    $HManagerIdQuery = "SELECT createdBy FROM job_request WHERE id=$jrequestId";
    $HManagerIdResult = $db->query($HManagerIdQuery);

    $record = $HManagerIdResult->fetch_object();
    $HiringId = $record->createdBy;

    $createNotification = createANotification($HiringId,$message);
    
    $deleteJobRequestQuery = "UPDATE job_request SET deleted = 1 WHERE id = $jrequestId";
    $deleteJobRequestResult = $db->query($deleteJobRequestQuery);

    $removeJobCard = " UPDATE job_card SET deleted = 1 WHERE jrequest_id = $jrequestId";
    $removeJobCardResult = $db->query($removeJobCard);

    if($createNotification && $deleteJobRequestResult && $removeJobCardResult)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}


function getRequestByCard($cardId){

    $db = db::getConnection();
    $query = "
        SELECT DISTINCT(job_request.id), user_profile.id as 'userId' , user_profile.name as 'userName', user_profile.surname as 'userSurname', justification.id as 'justificationId', justification.justification, fulfilmentDate,
        job_name as 'jobName', job.id as 'jobId', brief, job_card.id as 'jobCardId'
        FROM job_request INNER JOIN user_profile on user_profile.id = job_request.createdBy INNER JOIN justification on justification.id = job_request.justification_id INNER JOIN
        job on job.id = job_request.job_id INNER JOIN job_card on job_card.jrequest_id = job_request.id RIGHT JOIN job_card_user on job_card_user.card_id = job_card.id WHERE
         job_card.location_id AND job_card.id = $cardId;
    ";
    $result = $db->query($query);

    if($result)
    {
        $record = $result->fetch_object();
        $request = new stdClass();
        $request->id = $record->id;
        $request->brief = $record->brief;
        $request->fulfilmentDate = $record->fulfilmentDate;
        $request->jobCardId = $record->jobCardId;

        $justification = new stdClass();
        $justification->id = $record->justificationId;
        $justification->justification = $record->justification;

        $user = new stdClass();
        $user->id = $record->userId;
        $user->name = $record->userName;
        $user->surname = $record->userSurname;

        $job = new stdClass();
        $job->id = $record->jobId;
        $job->name = $record->jobName;

        $request->user = $user;
        $request->justification = $justification;
        $request->jobPosition = $job;
        return $request;
    }
    else
        return false;

}

    function removeAJobRequest($id)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT * FROM job_card WHERE  jrequest_id = ?");
        $stmt->bind_param('i',$id);
        if(!$stmt->execute())
            return false;

        $jobCard = $stmt->get_result();
        $jobCard = $jobCard->fetch_object();

        return deleteAJobCard($jobCard->id);

    }






















