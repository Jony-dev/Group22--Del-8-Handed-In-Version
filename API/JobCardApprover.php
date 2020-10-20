<?php
function getAllJobCardApprovers(){

    $db = db::getConnection();
    $query = "SELECT * FROM job_card_approver";
    $result = $db->query($query);
    $jobcardapprovers = [];
    while( $record = $result->fetch_object())
    {
        $jobcardapprover = new stdClass();
        $jobcardapprover->cardid = $record->card_id;
        $jobcardapprover->userid = $record->user_id;
        $jobcardapprover->comment= $record->comment;
        $jobcardapprover->approved = $record->approved;
        $jobcardapprover->approvaldate = $record->approvalDate;
        $jobcardapprover->createdby = $record->createdBy;
        $jobcardapprover->updatedby = $record->updatedBy;
        $jobcardapprover->createddate = $record->createdDate;
        $jobcardapprover->updateddate = $record->updatedDate;
        $jobcardapprover->deleted = $record->deleted;
        $jobcardapprovers[] = $jobcardapprover;
    }

    return $jobcardapprovers;
}
//
function createAJobCardApprover($card_id,$user_id,$user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM job_card_approver WHERE (card_id=$card_id AND user_id=$user_id)";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
            return "duplicate";
        }
        else
        {
            $query1 = "UPDATE job_card_approver SET deleted=0, WHERE (card_id = $card_id) AND (user_id = $user_id)";
            $result1 = $db->query($query1);

            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 17)";
            $result3 = $db->query($query3);

            if($result1 && $result3)
            {
                return "worked";
            }
            else
            {
                return "did not work";
            }
        }
    }

    $query2 = "INSERT INTO job_card_approver (card_id, user_id,createdBy, updatedBy) VALUES ($card_id,$user_id,$user,$user)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 17)";
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

// update
// function updateAJobCardApprover($id,$card_id,$user_id,$user)
// {
//     $db = db::getConnection();
    
  
//     $query2 = "UPDATE job_card_approver SET card_id=$card_id, user_id=$user_id ,updatedBy='$user', updatedDate = NOW() WHERE id = $id";
//     $result = $db->query($query2);

//     $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 17)";
//     $result3 = $db->query($query3);

//     if($result && $result3)
//     {
//         return "worked";
//     }
//     else
//     {
//         return "did not work";
//     }
// }

function rejectAJobCard($card_id, $comment, $user)
{
    $db = db::getConnection();

    $query = "UPDATE job_card_approver SET updatedBy=$user, approved = 0, updatedDate = NOW(), comment='$comment' WHERE (user_id = $user AND card_id=$card_id)";
    $result = $db->query($query);

    $query2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 3, 17)";
    $result2 = $db->query($query2);

    if($result && $result2)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}


    function approveAJobCard($card_id,$user)
    {
        $db = db::getConnection();

        $count = 0;
        $approver = 0;

        //echo("Card ID ".$card_id." User ID ".$user);

        $query1 = "UPDATE job_card_approver SET approved = true, updatedBy=$user, updatedDate = NOW(), approvalDate=NOW() WHERE ((user_id = $user) AND (card_id=$card_id))";
        $result1 = $db->query($query1);

        $query2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 3, 17)";
        $result2 = $db->query($query2);

        $query3 = "SELECT * FROM job_card_approver WHERE (card_id=$card_id)";
        $result3 = $db->query($query3);

        while($record3 = $result3->fetch_object())
        {
            $count = $count+1;
            if($record3->approved == 1)
            {
                $approver = $approver+1;
            }
        }

        if($count==$approver)
        {
            $query4 = "UPDATE job_card SET approved = 1, updatedDate = NOW() WHERE id=$card_id";
            $result4 = $db->query($query4);

            $query5 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 3, 16)";
            $result5 = $db->query($query5);
        }

        if($result1 && $result2)
        {
            return "worked";
        }
        else
        {
            return "did not work";
        }
    }

    function getMyApprovals($user)
    {
        $db = db::getConnection();
        $query = "
        SELECT card_id as 'cardId', job_card.card_name'cardName' FROM job_card_approver INNER JOIN job_card on job_card_approver.card_id = job_card.id WHERE user_id = $user AND job_card_approver.approved is NULL
        ";
        $result = $db->query($query);
        $approvals = [];

        if($result->num_rows === 0)
            return $approvals;

        while($record = $result->fetch_object())
        {
            $approvals[] = $record;
        }
        return $approvals;

    }

    function getApproverByCardId($cardId)
    {
        $db = db::getConnection();
        $query = "
        SELECT user_profile.id as 'id',user_profile.name, user_profile.surname, user_profile.picture as 'url', approved, comment as 'rejectMessage' FROM job_card_approver
        INNER JOIN user_profile on job_card_approver.user_id = user_profile.id
        WHERE card_id = $cardId;
        ";

        $result = $db->query($query);
        $approverList = [];

        while($record = $result->fetch_object())
        {
            $approverList[] = $record;
        }

        return $approverList;
    }