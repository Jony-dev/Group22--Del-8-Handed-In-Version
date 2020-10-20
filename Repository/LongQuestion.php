<?php

require_once "./Notification.php";

function getAllLongQuestions(){

    $db = db::getConnection();
    $query = "SELECT * FROM long_question";
    $result = $db->query($query);
    $longQuestions = [];
    while( $record = $result->fetch_object())
    {
        $question = new stdClass();
        $question->id = $record->id;
        $question->question = $record->question;
        $longQuestions[] = $question;
    }

    return $longQuestions;
}

function getAllDeletedLongQuestions(){

    $db = db::getConnection();
    $query = "SELECT * FROM long_question WHERE deleted=0";
    $result = $db->query($query);
    $longQuestions = [];
    while( $record = $result->fetch_object())
    {
        $question = new stdClass();
        $question->id = $record->id;
        $question->question = $record->question;
        $longQuestions[] = $question;
    }

    return $longQuestions;
}

//create
function createALongQuestion($question, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM long_question";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->question == $question)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE long_question SET deleted=0, WHERE question= $question";
                $result1 = $db->query($query1);
                return "worked";
            }
        }
    }

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A long question has been created, please approve it.");
        $notification2 = createANotification($user,"You have deleted a floor.");
    }


    $query2 = "INSERT INTO long_question (qtype_id, question, createdBy, updatedBy, deleted) VALUES (4, '$question', $user,$user,0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 28)";
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
function updateALongQuestion($id,$question, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM long_question";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->question == $question)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE long_question SET question='$question', updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 28)";
    $result1 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A long question has been updated.");
        $notification2 = createANotification($user,"You have updated a long question.");
    }

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteALongQuestion($id, $user)
{
    $db = db::getConnection();

    $questionQuery = "SELECT question.longquestion_id 
    FROM question";
    $questionResult = $db->query($questionQuery);

    while($record = $questionResult->fetch_object())
    {
        if($record->longquestion_id == $id)
        {
            $query1 = "UPDATE long_question SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            if($result1)
            {
                return $query1;
            }
            else{
                return false;
            }
        }
    }

    $deleteQuery = "DELETE FROM long_question WHERE id = $id";
    $deleteLongQuestion = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 28)";
    $result4 = $db->query($query4);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A long question has been deleted.");
        $notification2 = createANotification($user,"You have deleted a long question.");
    }

    if($deleteLongQuestion && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function approveALongQuestion($id, $user)
{
    $db = db::getConnection();

    $query = "UPDATE long_question SET approved = true, updatedBy=$user, updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query);

    $query2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 3, 28)";
    $result2 = $db->query($query2);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A long question has been approved.");
        $notification2 = createANotification($user,"You have approved a long question.");
    }


    return $result;
}

function getAllPendingLongQuestions(){

    $db = db::getConnection();
    $query = "SELECT * FROM long_question WHERE approved = 0";
    $result = $db->query($query);
    $longQuestions = [];
    while( $record = $result->fetch_object())
    {
        $question = new stdClass();
        $question->id = $record->id;
        $question->question = $record->question;
        $longQuestions[] = $question;
    }

    return $longQuestions;
}

function getAllApprovedLongQuestions(){

    $db = db::getConnection();
    $query = "SELECT * FROM long_question WHERE approved = 1";
    $result = $db->query($query);
    $longQuestions = [];
    while( $record = $result->fetch_object())
    {
        $question = new stdClass();
        $question->id = $record->id;
        $question->question = $record->question;
        $longQuestions[] = $question;
    }

    return $longQuestions;
}

function getAllQuestionPendingCount()
{
    $db = db::getConnection();
    $query = "SELECT count(id) AS 'pending' FROM long_question WHERE approved=false;";
    $result = $db->query($query);
    $record = $result->fetch_object();
    $pending = new stdClass();
    $pending->pending = $record->pending;
    return $pending;
}