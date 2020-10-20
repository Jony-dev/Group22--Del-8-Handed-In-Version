<?php

require_once "Question.php";
require_once "JobCardApprover.php";
require_once "JobTest.php";
function getAllJobCards(){

    $db = db::getConnection();
    $query = "SELECT * FROM job_card";
    $result = $db->query($query);
    $jobcards = [];
    while( $record = $result->fetch_object())
    {
        $jobcard = new stdClass();
        $jobcard->cardid = $record->id;
        $jobcard->RequisitionApprovalID = $record->rapproval_id;
        $jobcard->Requestid = $record->jrequest_id;
        $jobcard->Locationid = $record->location_id;
        $jobcard->Scheduleid = $record->schedule_id;
        $jobcard->jobcardname= $record->card_name;
        $jobcard->approved= $record->approved;
        $jobcard->completiondate= $record->completionDate;
        $jobcard->introduction = $record->introduction;
        $jobcard->description= $record->description;
        $jobcard->enddate= $record->endDate;
        $jobcard->startdate = $record->startDate;
        $jobcard->travel= $record->travel;
        $jobcard->workinghours= $record->working_hours;
        $jobcard->createdby = $record->createdBy;
        $jobcard->updatedby = $record->updatedBy;
        $jobcard->createddate = $record->createdDate;
        $jobcard->updateddate = $record->updatedDate;
        $jobcard->deleted = $record->Deleted;
    
        $jobcards[] = $jobcard;
    }

    return $jobcards;
}


//create
function createAJobCard($id,$basicDetails,$tests, $languages,$skills,$requirements, $longQuestions, $approvers,$user)
{
    $db = db::getConnection();

    $query2 = "UPDATE job_card SET rapproval_id='$basicDetails->raApprovalId', location_id='$basicDetails->locationId' , schedule_id='$basicDetails->scheduleId' ,card_name='$basicDetails->jobCardName' 
              ,introduction='$basicDetails->introduction' ,description='$basicDetails->description' ,endDate='$basicDetails->endDate' ,startDate='$basicDetails->startDate' 
              ,travel='$basicDetails->travel' ,working_hours=$basicDetails->workingHours, publishingDate = '$basicDetails->publishDate', closingDate = '$basicDetails->closingDate'  , updatedBy=$user, updatedDate = NOW() WHERE id = $id";
    $result2 = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 16)";
    $result3 = $db->query($query3);

    $query5 = "INSERT INTO advert (card_id, pStartDate, pEndDate, createdBy, updatedBy) VALUES ($id, $basicDetails->startDate, $basicDetails->endDate, $user, $user)";
    $result5 = $db->query($query5);

    $query6 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 21)";
    $result6 = $db->query($query6);

    $query7 = "INSERT INTO job_survey (card_id, createdBy, updatedBy) VALUES ($id, $user, $user)";
    $result7 = $db->query($query7);
    $surveyId = $db->insert_id;

    $query8 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 22)";
    $result8 = $db->query($query8);

    $query10 = "UPDATE job_card_stage SET current=0, endDate=NOW() WHERE (card_id = $id)";
    $result10 = $db->query($query10);

    $query9 = "INSERT INTO job_card_stage (stage_id, card_id, current) VALUES (9,$id, 1)";
    $result9 = $db->query($query9);

    /////////////////////////////////////////////////ADDING QUESTIONS////////////////////////////////////////////////////
    foreach ($skills as $skill) {


        createASkillQuestion($skill->id, $skill->critical, $surveyId, $user);
    }

    foreach ($requirements as $req) {

        createARequirementQuestion($req->id, $req->critical, $req->expectedAnswer, $surveyId, $user);
    }

    foreach ($languages as $lang) {

        createALanguageQuestion($lang->id, $lang->critical, $surveyId, $user);
    }

    foreach ($longQuestions as $question) {

        createALongQuestionQuestion($question->id, $question->critical, $surveyId, $user);
    }
    /////////////////////////////////////////////////ADDING APPRROVERS////////////////////////////////////////////////////
    foreach ($approvers as $approver) {
        createAJobCardApprover($id, $approver->id, $user);
    }

    /////////////////////////////////////////////////ADDING TESTS////////////////////////////////////////////////////
    foreach ($tests as $test) {
        createAJobTest($test->id, $id, $test->critical, $user);
    }
    if ($result2 && $result3 && $result5 && $result6 && $result7 && $result8 && $result9 && $result10) {
        return "worked";
    } else {
        $answer = "FAILED ADDING JOB CARD BASIC DETAILS: " . $result2 . " AUDIT: " . $result3 . " ADVERT: " . $result5 . " ADVERT AUDIT: " . $result6 . " JOB SUR: " . $result7 . " SURVEY AUDIT: " . $result8 . " STAGE: " . $result10 . " JOB STAGE INSERT" . $result9;
        return $answer;
    }
}


// update
function updateAJobCard($id,$basicDetails,$tests, $languages,$skills,$requirements, $longQuestions, $approvers,$user)
{
    $db = db::getConnection();

    //DELETE APPROVER
    $stmt = $db->prepare("DELETE FROM job_card_approver WHERE card_id = ?;");
    $stmt->bind_param('i',$id);
    if(!$stmt->execute())
        return "REMOVING APPROVER ERROR";

    //DELETE LONG QUESTIONS
    $stmt = $db->prepare("SELECT id FROM job_survey WHERE card_id = ?;");
    $stmt->bind_param('i',$id);
    if(!$stmt->execute())
        return "COULD NOT GET SURVEY";

    $surveyId = $stmt->get_result()->fetch_object()->id;

    $stmt = $db->prepare("DELETE FROM edumarxc_bmwdatabase.question WHERE survey_id = ?;");
    $stmt->bind_param('i',$surveyId);
    if(!$stmt->execute())
        return "REMOVING QUESTIONS ERROR";

    //DELETE TESTS
    $stmt = $db->prepare("DELETE FROM edumarxc_bmwdatabase.job_test WHERE card_id = ?;");
    $stmt->bind_param('i',$id);
    if(!$stmt->execute())
        return "REMOVING QUESTIONS TESTS";


    $query2 = "UPDATE job_card SET rapproval_id='$basicDetails->raApprovalId', location_id='$basicDetails->locationId' , schedule_id='$basicDetails->scheduleId' ,card_name='$basicDetails->jobCardName' 
              ,introduction='$basicDetails->introduction' ,description='$basicDetails->description' ,endDate='$basicDetails->endDate' ,startDate='$basicDetails->startDate' 
              ,travel='$basicDetails->travel' ,working_hours=$basicDetails->workingHours, publishingDate = '$basicDetails->publishDate', closingDate = '$basicDetails->closingDate'  , updatedBy=$user, updatedDate = NOW() WHERE id = $id";
    $result2 = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 16)";
    $result3 = $db->query($query3);

    $query5 = "INSERT INTO advert (card_id, pStartDate, pEndDate, createdBy, updatedBy) VALUES ($id, $basicDetails->startDate, $basicDetails->endDate, $user, $user)";
    $result5 = $db->query($query5);

    $query6 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 21)";
    $result6 = $db->query($query6);

    $query8 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 22)";
    $result8 = $db->query($query8);

    $query10 = "UPDATE job_card_stage SET current=0, endDate=NOW() WHERE (card_id = $id)";
    $result10 = $db->query($query10);

    $query9 = "UPDATE job_card_stage SET current = 1 WHERE card_id = $id AND stage_id = 9";
    $result9 = $db->query($query9);

    /////////////////////////////////////////////////ADDING QUESTIONS////////////////////////////////////////////////////
    foreach ($skills as $skill) {


        createASkillQuestion($skill->id, $skill->critical, $surveyId, $user);
    }

    foreach ($requirements as $req) {

        createARequirementQuestion($req->id, $req->critical, $req->expectedAnswer, $surveyId, $user);
    }

    foreach ($languages as $lang) {

        createALanguageQuestion($lang->id, $lang->critical, $surveyId, $user);
    }

    foreach ($longQuestions as $question) {

        createALongQuestionQuestion($question->id, $question->critical, $surveyId, $user);
    }
    /////////////////////////////////////////////////ADDING APPRROVERS////////////////////////////////////////////////////
    foreach ($approvers as $approver) {
        createAJobCardApprover($id, $approver->id, $user);
    }

    /////////////////////////////////////////////////ADDING TESTS////////////////////////////////////////////////////
    foreach ($tests as $test) {
        createAJobTest($test->id, $id, $test->critical, $user);
    }
    if ($result2 && $result3 && $result5 && $result6 && $result8 && $result9 && $result10) {
        return "worked";
    } else {
        $answer = "FAILED ADDING JOB CARD BASIC DETAILS: " . $result2 . " AUDIT: " . $result3 . " ADVERT: " . $result5 . " ADVERT AUDIT: " . $result6 . " SURVEY AUDIT: " . $result8 . " STAGE: " . $result10 . " JOB STAGE INSERT" . $result9;
        return $answer;
    }
}


// jobcard user
function generateAJobCard($recruiter, $hiringManager, $jrequestId, $user)
{   
    $db = db::getConnection();

    $query = "INSERT INTO job_card (jrequest_id, createdBy, updatedBy) VALUES ($jrequestId, $user, $user)";
    $result = $db->query($query);
                                                                                        
    $query2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 16)";
    $result2 = $db->query($query2);

    $query4 = "SELECT id FROM job_card WHERE jrequest_id = $jrequestId";
    $result4 = $db->query($query4);

    $record2 = $result4->fetch_object();
    $cardID = $record2->id;

    $query5 = "INSERT INTO job_card_user (role_id, user_id, card_id) VALUES (5,$hiringManager,$cardID)";
    $result5 = $db->query($query5);
    // audit
    $query8 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 19)";
    $result8 = $db->query($query8);

    $query6 = "INSERT INTO job_card_user (role_id, user_id, card_id) VALUES (6,$user,$cardID)";
    $result6 = $db->query($query6);
    // audit
    $query9 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 19)";
    $result9 = $db->query($query9);

    $query7 = "INSERT INTO job_card_user (role_id, user_id, card_id) VALUES (4,$recruiter,$cardID)";
    $result7 = $db->query($query7);
    //audit
    $query10 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 19)";
    $result10 = $db->query($query10);

    $query11 = "INSERT INTO  job_card_stage (stage_id, card_id, current, startDate) VALUES (2,$cardID,1,NOW())";
    $result11 = $db->query($query11);
    //audit
    $query12 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 18)";
    $result12 = $db->query($query12);



    if($result && $result2 && $result5 && $result6 && $result7 && $result8 && $result9 && $result10 && $result11 && $result12)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}
 // by id
 function getAllJobCardsByID($id, $user){

    $db = db::getConnection();
    $query = "SELECT * FROM job_card WHERE id = $id";
    $result = $db->query($query);

    $jobcards = [];

    while( $record = $result->fetch_object()){
        if($record->deleted == false)
        {
            $jobcard = new stdClass();
            $jobcard->cardid = $record->id;
            $jobcard->RequisitionApprovalID = $record->rapproval_id;
            $jobcard->Requestid = $record->jrequest_id;
            $jobcard->Locationid = $record->location_id;
            $jobcard->Scheduleid = $record->schedule_id;
            $jobcard->jobcardname= $record->card_name;
            $jobcard->approved= $record->approved;
            $jobcard->completiondate= $record->completionDate;
            $jobcard->introduction = $record->introduction;
            $jobcard->description= $record->description;
            $jobcard->enddate= $record->endDate;
            $jobcard->startdate = $record->startDate;
            $jobcard->travel= $record->travel;
            $jobcard->workinghours= $record->working_hours;
            $jobcard->createdby = $record->createdBy;
            $jobcard->updatedby = $record->updatedBy;
            $jobcard->createddate = $record->createdDate;
            $jobcard->updateddate = $record->updatedDate;
            $jobcard->deleted = $record->Deleted;
        
            $jobcards[] = $jobcard;
           
        }
    }
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 16)";
    $result3 = $db->query($query3);
    
    return $jobcards;
    
        
}
    // fk
    function getAllJobCardsByRequest($jrequest_id, $user){

        $db = db::getConnection();
        $query = "SELECT * FROM job_card WHERE jrequest_id = $jrequest_id";
        $result = $db->query($query);
    
        $jobcardss = [];
    
        while($record = $result->fetch_object()){
            if($record->deleted == false)
            {
            $jobcard = new stdClass();
            $jobcard->cardid = $record->id;
            $jobcard->RequisitionApprovalID = $record->rapproval_id;
            $jobcard->Requestid = $record->jrequest_id;
            $jobcard->Locationid = $record->location_id;
            $jobcard->Scheduleid = $record->schedule_id;
            $jobcard->jobcardname= $record->card_name;
            $jobcard->approved= $record->approved;
            $jobcard->completiondate= $record->completionDate;
            $jobcard->introduction = $record->introduction;
            $jobcard->description= $record->description;
            $jobcard->enddate= $record->endDate;
            $jobcard->startdate = $record->startDate;
            $jobcard->travel= $record->travel;
            $jobcard->workinghours= $record->working_hours;
            $jobcard->createdby = $record->createdBy;
            $jobcard->updatedby = $record->updatedBy;
            $jobcard->createddate = $record->createdDate;
            $jobcard->updateddate = $record->updatedDate;
            $jobcard->deleted = $record->Deleted;
        
            $jobcards[] = $jobcard;
           
            }
            
        }
        
        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 16)";
        $result3 = $db->query($query3);
    
        return $jobcards;
    }
      // search by name
      function searchAllJobCards($ard_name, $user){

//        $db = db::getConnection();
//        $query = "SELECT * FROM job_card WHERE name LIKE '%$card_name%'";
//        $result = $db->query($query);
//
//        $jobcards = [];
//
//        while( $record = $result->fetch_object()){
//            if($record->deleted == false)
//            {
//                $jobcard = new stdClass();
//                $jobcard->cardid = $record->id;
//                $jobcard->RequisitionApprovalID = $record->rapproval_id;
//                $jobcard->Requestid = $record->jrequest_id;
//                $jobcard->Locationid = $record->location_id;
//                $jobcard->Scheduleid = $record->schedule_id;
//                $jobcard->jobcardname= $record->card_name;
//                $jobcard->approved= $record->approved;
//                $jobcard->completiondate= $record->completionDate;
//                $jobcard->introduction = $record->introduction;
//                $jobcard->description= $record->description;
//                $jobcard->enddate= $record->endDate;
//                $jobcard->startdate = $record->startDate;
//                $jobcard->travel= $record->travel;
//                $jobcard->workinghours= $record->working_hours;
//                $jobcard->createdby = $record->createdBy;
//                $jobcard->updatedby = $record->updatedBy;
//                $jobcard->createddate = $record->createdDate;
//                $jobcard->updateddate = $record->updatedDate;
//                $jobcard->deleted = $record->Deleted;
//
//                $jobcards[] = $jobcard;
//            }
//
//        }
//
//        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 4)";
//        $result3 = $db->query($query3);
//
//        return $jobcards;
    }

    function getAssignedCards($userId){

        $db = db::getConnection();
//        $query = "
//        SELECT job_card.card_name as 'cardName',job_card.id as 'cardId',  user_profile.name as 'requestingManager', job_card.endDate as 'fulfillmentDate',stage.stage as 'stage',
//        (SELECT count(*) as 'applicants' FROM application WHERE application.card_id = job_card.id) as 'applicants'
//        FROM job_card INNER JOIN job_card_user on job_card.id = job_card_user.card_id INNER JOIN role on role.id = job_card_user.role_id
//        INNER JOIN job_request on job_card.jrequest_id = job_request.id INNER JOIN user_profile on job_request.createdBy = user_profile.id
//        INNER JOIN job_card_stage on job_card_stage.card_id = job_card.id
//        INNER JOIN stage on job_card_stage.stage_id = stage.id
//        WHERE job_card_stage.current = 1 AND job_card_stage.stage_id != 9  AND job_card_user.role_id = 4 AND  job_card_user.user_id = $userId;
//        ";
        $query = "
        SELECT job_request.id as 'id' ,user_profile.id as 'userId', user_profile.name as 'requestingManager', user_profile.surname as 'userSurname', justification.id as 'justificationId'
        , justification.justification , fulfilmentDate, job_name as 'jobName',job.id as 'jobId', brief, job_card.id as 'cardId', job_card.card_name as 'cardName', job_card.endDate as 'fulfillmentDate'
        , stage.stage as 'stage', (SELECT count(*) as 'applicants' FROM application WHERE application.card_id = job_card.id) as 'applicants'
        FROM job_card_user
        RIGHT JOIN job_card on job_card_user.card_id = job_card.id
        INNER JOIN job_request on job_card.jrequest_id = job_request.id
        INNER JOIN justification on job_request.justification_id = justification.id
        INNER JOIN job on job_request.job_id = job.id
        INNER JOIN user_profile on job_request.CreatedBy = user_profile.id
        INNER JOIN job_card_stage on job_card_stage.card_id = job_card.id
        INNER JOIN stage on job_card_stage.stage_id = stage.id
        WHERE job_card_user.role_id = 4 AND job_card_user.user_id = $userId AND job_card.card_name IS NOT NULL AND job_card_stage.current = 1 AND job_card.deleted = 0
        ";







        $jobCards = [];
        $result = $db->query($query);



            while ($record = $result->fetch_object())
            {
                $jobCard = new stdClass();
                $jobCard = $record;
                $jobCards[] = $jobCard;
            }
            return $jobCards;



    }

    function getDisplayCard($cardId)
    {
        $db = db::getConnection();
        $query = "
            SELECT job_card.id as 'cardId', job_card.card_name as 'cardName', job_card.introduction as 'introduction', job_card.description as 'description',
            question.skill_id as 'skillId', skill.skill, question.longquestion_id as 'lquestion', long_question.question, question.language_id as 'languageId', language.language,
            question.requirement_id as 'requirementId', requirement.requirement,
            job_card.startDate, job_card.endDate, job_card.travel, job_card.working_hours as 'workingHours'
            FROM job_card INNER JOIN job_survey on job_survey.card_id = job_card.id
            INNER JOIN question on job_survey.id = question.survey_id 
            LEFT OUTER JOIN skill on question.skill_id = skill.id
            LEFT OUTER JOIN requirement on question.requirement_id = requirement.id
            LEFT OUTER JOIN language on question.language_id = language.id
            LEFT OUTER JOIN long_question on question.longquestion_id = long_question.id
            WHERE job_card.id = $cardId ;
         ";

        $result = $db->query($query);

        if(!$result)
            return false;

        $cardInfo = new stdClass();
        $cardInfo->cardId = $cardId;
        $skills = [];
        $languages = [];
        $requirements = [];
        while($record = $result->fetch_object())
        {
            $cardInfo->name = $record->cardName;
            $cardInfo->introduction = $record->introduction;
            $cardInfo->description = $record->description;
            $cardInfo->startDate = $record->startDate;
            $cardInfo->endDate = $record->endDate;
            $cardInfo->travel = $record->travel;
            $cardInfo->workingHours = $record->workingHours;

            if($record->skillId)
            {
                $skills[] = $record->skill;
            }
            else if ($record->languageId)
            {
                $languages[] = $record->language;
            }
            else if($record->requirementId)
            {
                $requirements[] = $record->requirement;
            }
        }
        $cardInfo->skills = $skills;
        $cardInfo->requirements = $requirements;
        $cardInfo->languages = $languages;



        $query = "
            SELECT test.id as 'id', test.test_name as 'name', test.URL as 'url', test.description as 'description' FROM job_card
            INNER JOIN job_test on job_card.id = job_test.card_id
            INNER JOIN test on job_test.test_id = test.id
            WHERE job_card.id = $cardId;
        ";
        $result = $db->query($query);
        $tests = [];
        if(!$result)
            return false;

        while ($record = $result->fetch_object())
            $tests[] = $record;

        $cardInfo->tests = $tests;

        return $cardInfo;
    }

    function getJobCardDetails($cardId){

        $db = db::getConnection();
        $query = "
        SELECT job_card.card_name as 'jobCardName', startDate, endDate, introduction, description, travel,publishingDate as 'publishDate', closingDate,schedule_id as 'scheduleId', location_id as 'locationId'
        , rapproval_id as 'raApprovalId', working_hours as 'workingHours'
        FROM job_card WHERE job_card.id = $cardId;
        ";

        $returnObj = new stdClass();
        $result = $db->query($query);
        $returnObj->basicDetails = $result->fetch_object();


        $tests = [];
        $query = "
        SELECT test_id as 'testId', description, URL as 'url', test_name as 'testName', critical FROM job_test INNER JOIN test on job_test.test_id = test.id  WHERE job_test.card_id = $cardId;
        ";
        if($result = $db->query($query)){
            while ($record = $result->fetch_object())
                $tests[] = $record;
        }
        $returnObj->tests = $tests;

        $query = "
            SELECT
            question.skill_id as 'skillId', skill.skill, question.longquestion_id as 'lquestionId', long_question.question, question.language_id as 'languageId', language.language,
            question.requirement_id as 'requirementId', requirement.requirement,
            question.critical, if(question.expected_answer=1,true,false) as 'expectedAnswer'
            FROM job_card INNER JOIN job_survey on job_survey.card_id = job_card.id
            INNER JOIN question on job_survey.id = question.survey_id 
            LEFT OUTER JOIN skill on question.skill_id = skill.id
            LEFT OUTER JOIN requirement on question.requirement_id = requirement.id
            LEFT OUTER JOIN language on question.language_id = language.id
            LEFT OUTER JOIN long_question on question.longquestion_id = long_question.id
            WHERE job_card.id = $cardId ;
         ";

        $result = $db->query($query);

        if(!$result)
            return false;
        $skills = [];
        $languages = [];
        $requirements = [];
        $longQuestions = [];
        while($record = $result->fetch_object())
        {
            $object = new stdClass();
            if($record->skillId)
            {
                $object->id = $record->skillId;
                $object->skill = $record->skill;
                $object->critical = $record->critical;
                $object->expectedAnswer = $record->expectedAnswer;
                $skills[] = $object;
            }
            else if ($record->languageId)
            {
                $object->id = $record->languageId;
                $object->language = $record->language;
                $object->critical = $record->critical;
                $object->expectedAnswer = $record->expectedAnswer;
                $languages[] = $object;
            }
            else if($record->requirementId)
            {
                $object->id = $record->requirementId;
                $object->requirement = $record->requirement;
                $object->critical = $record->critical;
                $object->expectedAnswer = $record->expectedAnswer;
                $requirements[] = $object;
            }
            else if($record->lquestionId)
            {
                $object->id = $record->lquestionId;
                $object->question = $record->question;
                $object->critical = $record->critical;
                $object->expectedAnswer = $record->expectedAnswer;
                $longQuestions[] = $object;
            }
        }
        $returnObj->skills = $skills;
        $returnObj->languages = $languages;
        $returnObj->requirements = $requirements;
        $returnObj->longQuestions = $longQuestions;

        $query ="
        SELECT user_profile.id as 'id', user_profile.picture as 'imgUrl', 
        user_profile.name as 'userName', user_profile.surname as 'userSurname' FROM job_card_approver 
        INNER JOIN user_profile on job_card_approver.user_id = user_profile.id WHERE card_id = $cardId;
        ";

        $approvers = [];
        $result = $db->query($query);
        if($result)
        {
            while($record = $result->fetch_object())
                $approvers[] = $record;
        }

        $returnObj->approvers = $approvers;
        return $returnObj;
    }

    function publishCard($user, $cardId){

        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE job_card SET published = 1, updatedBy = ? WHERE id = ?");
        $stmt->bind_param("ii",$user, $cardId);
        if(!$stmt->execute())
            return false;
        $stmt->close();

        $stmt = $db->prepare("UPDATE job_card_stage SET current = 0, endDate = NOW() WHERE card_id = $cardId AND current = 1;");
        $stmt->execute();

        $stmt = $db->prepare("INSERT INTO job_card_stage (current, card_id,startDate,stage_id ) VALUES (1,?,NOW(),4);");
        $stmt->bind_param("i",$cardId);

        if(!$stmt->execute())
        {
            $stmt = $db->prepare("UPDATE job_card_stage SET current = 0, endDate = NOW() WHERE card_id = $cardId AND current = 1");
            $stmt->execute();

            $stmt = $db->prepare("UPDATE job_card_stage SET current = 1, startDate = NOW() WHERE card_id = $cardId AND stage_id = 4");
            $stmt->execute();
        }

        $stmt = $db->prepare("INSERT INTO audit_log (user_id, operation_id,database_id) VALUES  (?,3,16)");
        $stmt->bind_param("i",$user);
        $stmt->execute();

        return true;
    }

    function unPublishCard($user, $cardId){

        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE job_card SET published = 0, updatedBy = ? WHERE id = ?");
        $stmt->bind_param("ii",$user, $cardId);
        if(!$stmt->execute())
            return false;

        $stmt->close();

        $stmt = $db->prepare("INSERT INTO audit_log (user_id, operation_id,database_id) VALUES  (?,3,16)");
        $stmt->bind_param($user);
        $stmt->execute();

        return true;
    }

    function published($cardId){

        $db = db::getConnection();
        $stmt = $db->prepare("SELECT published FROM job_card WHERE id = ?");
        $stmt->bind_param("i",$cardId);
        if(!$stmt->execute())
            return false;

        $result = $stmt->get_result();
        $record = $result->fetch_object();
        return $record;
    }

    function getCardStatus($cardId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT needsConfirmation, confirmed, role_id, job_card_user.user_id as 'userId' FROM job_card 
                                    INNER JOIN job_card_user ON job_card_user.card_id = job_card.id
                                    INNER JOIN role on role.id = job_card_user.role_id
                                    WHERE job_card.id = ?;");
        $stmt->bind_param("i",$cardId);

        if(!$stmt->execute())
            return false;

        $result = $stmt->get_result();
        $status = new stdClass();
        while($result AND $record = $result->fetch_object())
        {
            $status->needsConfirming = $record->needsConfirmation;
            $status->isConfirmed = $record->confirmed;
            if($record->role_id == 4)
                $status->recruiterId = $record->userId;

            if($record->role_id == 6)
                $status->hrManagerId = $record->userId;
        }

        return $status;
    }

    function recruiterConfirm($cardId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE job_card SET needsConfirmation = 1, confirmed = 0 WHERE id = ?");
        $stmt->bind_param("i",$cardId);
        if(!$stmt->execute())
            return false;

        return true;
    }

    function hrConfirm($cardID)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE job_card SET confirmed = 1, needsConfirmation = 0 WHERE id = ?");
        $stmt->bind_param("i",$cardID);
        if(!$stmt->execute())
            return false;

        return true;

        // EMAIL EVERYONE THAT DID NOT SUCCEED
    }

    function getHrManagersConfirmations($user)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT job_card.id, job_card.card_name as 'cardName' FROM job_card
                                    INNER JOIN job_card_user on job_card.id = job_card_user.card_id
                                    WHERE job_card_user.role_id = 6 AND job_card.needsConfirmation = true AND job_card_user.user_id = ?;");
        $stmt->bind_param("i",$user);
        if(!$stmt->execute())
            return false;

        $confirmations = [];
        $result = $stmt->get_result();
        while( $result AND $record = $result->fetch_object())
        {
            $confirmations[] = $record;
        }

        return $confirmations;
    }

    function getAllCardsReport()
    {
        $db = db::getConnection();
        $query = "SELECT job_card.card_name AS 'cardName', job_card.createdDate AS 'createdDate', job_card.completionDate AS 'completionDate', DATEDIFF(CURDATE(), job_card.createdDate) AS 'totalDays',stage.stage AS 'stage', COUNT(application.id) AS 'totalApplicants'
        FROM job_card
        LEFT OUTER JOIN job_card_stage
        ON job_card.id=job_card_stage.card_id
        LEFT OUTER JOIN stage
        ON job_card_stage.stage_id = stage.id
        LEFT OUTER JOIN application
        ON job_card.id=application.card_id
        WHERE job_card_stage.current = true
        GROUP BY job_card.id";

        $result = $db->query($query);
        $cards = [];
        $completed = "No";
        while( $record = $result->fetch_object())
        {
            if($record->completionDate)
            {                
                $completed = "Yes";
            }
            else
            {
                $completed = "No";
            }

            $card = new stdClass();
            $card->cardName = $record->cardName;
            $card->createdDate = $record->createdDate;
            $card->completionDate = $record->completionDate;
            $card->totalDays = $record->totalDays;
            $card->completed = $completed;
            $card->stage = $record->stage;
            $card->totalApplicants = $record->totalApplicants;
            $cards[] = $card;
        }
        return $cards;
    }

    
    function getJobCardsCount()
    {
        $db = db::getConnection();
        $query = "SELECT Count(job_card.id) AS 'cardCount' FROM job_card";
        $result = $db->query($query);
        $cardCount = $result->fetch_object();
        $count = $cardCount->cardCount;
        return $count;
    }

    function deleteAJobCard($id)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE job_card SET deleted = 1 WHERE id = ?");
        $stmt->bind_param("i",$id);

        if(!$stmt->execute())
            return false;
        $stmt->close();

        $db = db::getConnection();
        $stmt = $db->prepare("SELECT * FROM job_card WHERE id = ?");
        $stmt->bind_param("i",$id);

        if(!$stmt->execute())
            return false;

        $jobCardDetails = $stmt->get_result();
        $jobCardDetails = $jobCardDetails->fetch_object();
        $stmt->close();

        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE job_request SET deleted = 1 WHERE id = ?");
        $stmt->bind_param("i",$jobCardDetails->jrequest_id);

        if(!$stmt->execute())
            return false;
        $stmt->close();

        return true;
    }






















