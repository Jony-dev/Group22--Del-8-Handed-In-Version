<?php
function getAllQuestions(){

    $db = db::getConnection();
    $query = "SELECT * FROM question";
    $result = $db->query($query);
    $questions = [];
    while( $record = $result->fetch_object())
    {
        $question = new stdClass();
        $question->questionId = $record->id;
        $question->surveyId = $record->survey_id;
        $question->requirementId = $record->requirement_id;
        $question->longquestion = $record->longquestion_id;
        $question->skillId = $record->skill_id;
        $question->languageId = $record->language_id;
        $question->expectedAnswer = $record->expected_answer;
        $question->critical = $record->critical;
        $question->createdBy = $record->createdBy;
        $question->updatedBy = $record->updatedBy;
        $question->createdDate = $record->createdDate;
        $question->updatedDate = $record->ppdatedDate;
        $question->deleted = $record->deleted;
        $questions[] = $question;
    }

    return $questions;
}

function getAllDeletedQuestions(){

    $db = db::getConnection();
    $query = "SELECT * FROM question WHERE deleted = 0";
    $result = $db->query($query);
    $questions = [];
    while( $record = $result->fetch_object())
    {
        $question = new stdClass();
        $question->questionId = $record->id;
        $question->surveyId = $record->survey_id;
        $question->requirementId = $record->requirement_id;
        $question->longquestion = $record->longquestion_id;
        $question->skillId = $record->skill_id;
        $question->languageId = $record->language_id;
        $question->expectedAnswer = $record->expected_answer;
        $question->critical = $record->critical;
        $question->createdBy = $record->createdBy;
        $question->updatedBy = $record->updatedBy;
        $question->createdDate = $record->createdDate;
        $question->updatedDate = $record->ppdatedDate;
        $question->deleted = $record->deleted;
        $questions[] = $question;
    }

    return $questions;
}
//create
function createASkillQuestion($skill_id,$critical,$survey_id,$user)
{   
    $db = db::getConnection();
    $stmt = $db->prepare("SELECT * FROM question WHERE survey_id = ? AND skill_id = ?");
    $stmt->bind_param("ii",$survey_id,$skill_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if($result->num_rows > 0)
        return true;


    $stmt = $db->prepare("INSERT INTO question (survey_id, skill_id, expected_answer,critical, createdBy, updatedBy, deleted) VALUES (?,? ,1,?,?,?,0)");
    $stmt->bind_param("iiiii",$survey_id,$skill_id,$critical,$user,$user);


    if(!$stmt->execute())
        return false;
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 34)";
    $result3 = $db->query($query3);

    $db->close();
    return true;
}
function createARequirementQuestion($requirement_id,$critical,$expected_answer,$survey_id,$user)
{   
    $db = db::getConnection();

    $stmt = $db->prepare("SELECT * FROM question WHERE survey_id = ? AND requirement_id = ?");
    $stmt->bind_param("ii",$survey_id,$requirement_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if($result->num_rows > 0)
        return true;

    $stmt = $db->prepare("INSERT INTO question (survey_id, requirement_id, expected_answer,critical, createdBy, updatedBy) VALUES (?,? ,?,?,?,?)");
    $stmt->bind_param("iiiiii",$survey_id,$requirement_id,$expected_answer,$critical,$user,$user);

    if(!$stmt->execute())
        return "did not work";

                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 34)";
    $result3 = $db->query($query3);

    if( $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function createALongQuestionQuestion($longquestion_id,$critical,$survey_id,$user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM question WHERE survey_id=$survey_id";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->longquestion_id == $longquestion_id)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE question SET deleted=0, WHERE (language_id = $longquestion_id) AND (survey_id = $survey_id)";
                $result1 = $db->query($query1);
                return "worked";
            }
        }
    }


    $query2 = "INSERT INTO question (survey_id, longquestion_id, critical, createdBy, updatedBy, deleted) VALUES ($survey_id,$longquestion_id,$critical,$user,$user,0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 34)";
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

function createALanguageQuestion($language_id,$critical,$survey_id,$user)
{   
    $db = db::getConnection();

    $stmt = $db->prepare("SELECT * FROM question WHERE survey_id = ? AND language_id = ?");
    $stmt->bind_param("ii",$survey_id,$language_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if($result->num_rows > 0)
        return true;

    $stmt = $db->prepare("INSERT INTO question (survey_id, language_id, critical, expected_answer, createdBy, updatedBy) VALUES (?,?,?,1,?,?)");
    $stmt->bind_param("iiiii",$survey_id,$language_id,$critical,$user,$user);

    if(!$stmt->execute())
        return "did not work";

                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 34)";
    $result3 = $db->query($query3);

    if($result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

// update
function updateAQuestion($id,$critical,$user)
{
    $db = db::getConnection();
    
    $query= "UPDATE question SET critical=$critical Where id=$id";
    $result = $db->query($query);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 34)";
    $result3 = $db->query($query3);

    if($result && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function getCardQuestions($cardId)
{
    $db = db::getConnection();


    $returnObj = new stdClass();
    $returnObj->cardId = $cardId;

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
            question.id as 'id', question.skill_id as 'skillId', skill.skill, question.longquestion_id as 'lquestionId', long_question.question, question.language_id as 'languageId', language.language,
            question.requirement_id as 'requirementId', requirement.requirement,
            question.critical, question.expected_answer as 'expectedAnswer'
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
        $record = (object) array_filter((array) $record);
        if($record->skillId)
        {
            $skills[] = $record;
        }
        else if ($record->languageId)
        {

            $languages[] = $record;
        }
        else if($record->requirementId)
        {
            $requirements[] = $record;
        }
        else if($record->lquestionId)
        {
            $longQuestions[] = $record;
        }
    }
    $returnObj->skills = $skills;
    $returnObj->languages = $languages;
    $returnObj->requirements = $requirements;
    $returnObj->longQuestions = $longQuestions;

    return $returnObj;

}

