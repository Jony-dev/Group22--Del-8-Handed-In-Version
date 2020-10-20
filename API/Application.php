<?php
function getAllApplications(){

    $db = db::getConnection();
    $query = "SELECT * FROM application";
    $result = $db->query($query);
    $applications = [];
    while( $record = $result->fetch_object())
    {
        $application = new stdClass();
        $application->applicationid = $record->id;
        $application->userid = $record->user_id;
        $application->cardid = $record->card_id;
        $application->statusid= $record->status_id;
        $application->stageid= $record->stage_id;
        $application->cv= $record->cv;
        $application->proofresult= $record->result_proof;
        $application->createdby = $record->createdBy;
        $application->updatedby = $record->updatedBy;
        $application->createddate = $record->createdDate;
        $application->updateddate = $record->updatedDate;
        $application->deleted = $record->deleted;
        $applications[] = $application;
    }

    return $applications;
}

//create
function createAnApplication($card_id,$user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM application WHERE card_id=$card_id";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->user_id == $user_id)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO application (user_id, card_id, status_id,stage_id, createdBy, updatedBy, deleted) VALUES ($user,$card_id ,7,12,$user,'$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 2)";
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

function rejectAnApplicant($id, $user)
{
    $db = db::getConnection();

    $query = "UPDATE application SET status_id=8, updatedDate=Now(), updatedBy=$user WHERE id=$id";
    $result = $db->query($query);
    
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 2)";
    $result1 = $db->query($query3);


    if($result && $result1)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function hireAnApplicant($id, $user)
{
    $db = db::getConnection();

    $query = "UPDATE application SET status_id=6, updatedDate=Now(), updatedBy=$user WHERE id=$id";
    $result = $db->query($query);
    
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 2)";
    $result1 = $db->query($query3);


    if($result && $result1)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function updateAnApplication($id,$user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM application WHERE id=$id";
    $result = $db->query($query);

    $record = $result->fetch_object();
    $cardId = $record->card_id;

    $query = "SELECT * FROM job_card_stage WHERE ((card_id=$cardId) AND (current = true))";
    $result = $db->query($query);

    $record = $result->fetch_object();
    $stageId = $record->stage_id;

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 2)";
    $result1 = $db->query($query3);

    if($stageId != 4)
    {
        return "Job Card is no longer being advertised";
    }
}

function changeApplicationStatus($appId,$statusId)
{
    $db = db::getConnection();
    $query = "UPDATE application SET status_id = $statusId WHERE application.id = $appId";
    $result = $db->query($query);

    if(!$result)
        return false;

    $stmt = $db->prepare("UPDATE job_card card
                                INNER JOIN application app on app.card_id = card.id 
                                SET card.confirmed = 0
                                WHERE app.id = ? ");
    $stmt->bind_param("i",$appId);
    $stmt->execute();

    return true;
}

function getApplication($appId){

    $db = db::getConnection();
    $stmt = $db->prepare("SELECT MCQanswer,application_id as 'applicationId',LongAnswer as 'lAnswer', skill,question,requirement, language, cv, result_proof as 'test' FROM answer
                                INNER JOIN application ON answer.application_id = application.id
                                INNER JOIN question on question.id = answer.question_id
                                LEFT JOIN requirement on requirement.id = question.requirement_id
                                LEFT JOIN long_question on long_question.id = question.longquestion_id
                                LEFT JOIN language on language.id = question.language_id
                                LEFT JOIN skill on skill.id = question.skill_id
                                WHERE answer.application_id = ?;");
    $stmt->bind_param("i",$appId);
    if(!$stmt->execute())
        return false;
    $result = $stmt->get_result();

    $applicationView = new stdClass();
    $skills = [];
    $reqs = [];
    $ans = [];
    $langs = [];
    while($result AND $record = $result->fetch_object())
    {
        $record = (object) array_filter((array) $record);
        $applicationView->id = $record->applicationId;
        $applicationView->cv = $record->cv;
        $applicationView->test = $record->test;

        if($record->skill)
        {
            $skillAnswer = new stdClass();
            $skillAnswer->answer = $record->MCQanswer;
            $skillAnswer->question = $record->skill;
            $skills[]= $skillAnswer;
        }
        else if($record->requirement)
        {
            $reqAnswer = new stdClass();
            $reqAnswer->answer = $record->MCQanswer;
            $reqAnswer->question = $record->requirement;
            $reqs[] = $reqAnswer;
        }
        if($record->language)
        {
            $languageAnswer = new stdClass();
            $languageAnswer->answer = $record->MCQanswer;
            $languageAnswer->question = $record->language;
            $langs[] = $languageAnswer;
        }
        if($record->question)
        {
            $lQuestionAnswer = new stdClass();
            $lQuestionAnswer->answer = $record->lAnswer;
            $lQuestionAnswer->question = $record->question;
            $ans[] = $lQuestionAnswer;
        }
    }
    $applicationView->skillAnswers = $skills;
    $applicationView->languageAnswers = $langs;
    $applicationView->requirementAnswers = $reqs;
    $applicationView->lQuestionAnswers = $ans;

    return $applicationView;


}


function batchDisqualify($cardId)
{
    $db = db::getConnection();
    $stmt = $db->prepare("UPDATE application SET status_id = 7 WHERE application.status_id != 6");


}