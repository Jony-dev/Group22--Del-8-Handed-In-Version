<?php

require_once './Application.php';

function getAllAnswers(){

    $db = db::getConnection();
    $query = "SELECT * FROM answer";
    $result = $db->query($query);
    $answers = [];
    while( $record = $result->fetch_object())
    {
        $answer = new stdClass();
        $answer->applicationid = $record->application_id;
        $answer->questionid = $record->question_id;
        $answer->answer = $record->answer;
        $answer->deleted = $record->deleted;
        $answers[] = $answer;
    }

    return $answers;
}


//create
function createAnAnswer($answer,$question_id,$application_id,$user)
{   
    $db = db::getConnection();

    $result2;
    $result3;
    $isAnswer = is_null($answer);
    if($isAnswer == 1)
    {
        $isAnswer = 1;
    }
    else
    {
        $isAnswer = 0;
    }

    $query11 = "SELECT * FROM answer WHERE (application_id = $application_id AND question_id=$question_id)";
    $result11 = $db->query($query11);
    while($result11->fetch_object())
    {
        $query21 = "UPDATE answer SET MCQanswer = $answer WHERE $application_id AND question_id=$question_id";
        $result21 = $db->query($query21);

        if($result21)
        {
            return "worked";
        }
        else
        {
            return "did not work";
        }
       
    }

    $query = "SELECT * FROM question WHERE id=$question_id";
    $result = $db->query($query);
    $record = $result->fetch_object();
    
    if($record->skill_id)
    {
        if($record->critical==true)
        {
            if($isAnswer == 0)
            {
                echo("In answer function");
                $query2 = "INSERT INTO answer (application_id, question_id, MCQanswer) VALUES ($application_id,$question_id ,$answer)";
                $result2 = $db->query($query2);

                $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 1)";
                $result3 = $db->query($query3);

                if($answer==false)
                {
                    $rejectsuccessful= rejectApplicant($application_id,$user);
                    if($rejectsuccessful)
                    {
                        return "rejected successfully";
                    }
                    else
                    {
                        return "rejected unsuccessfully";
                    }
                }
            }
            else
            {
                return "Applicant did not answer all the questions";
            }
        }
        else
        {
            $query2 = "INSERT INTO answer (application_id, question_id, MCQanswer) VALUES ($application_id,$question_id,$answer)";
            $result2 = $db->query($query2);

            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 1)";
            $result3 = $db->query($query3);
        }
    }
    else if($record->language_id)
    {
        if($record->critical==true)
        {
            if($isAnswer==0)
            {
                $query2 = "INSERT INTO answer (application_id, question_id, MCQanswer) VALUES ($application_id,$question_id ,$answer)";
                $result2 = $db->query($query2);

                $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 1)";
                $result3 = $db->query($query3);

                if($answer==false)
                {
                    $rejectsuccessful = rejectAnApplicant($application_id,$user);
                    if($rejectsuccessful)
                    {
                        return "rejected successfully";
                    }
                    else
                    {
                        return "rejected unsuccessfully";
                    }
                }
            }
            else
            {
                return "Applicant did not answer all the questions";
            }
        }
        else
        {
            $query2 = "INSERT INTO answer (application_id, question_id, MCQanswer) VALUES ($application_id,$question_id ,$answer)";
            $result2 = $db->query($query2);

            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 1)";
            $result3 = $db->query($query3);
        }
    }
    else if($record->requirement_id)
    {
        if($record->critical==true)
        {
            if($isAnswer == 0)
            {
                $query2 = "INSERT INTO answer (application_id, question_id, MCQanswer) VALUES ($application_id,$question_id ,$answer)";
                $result2 = $db->query($query2);

                $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 1)";
                $result3 = $db->query($query3);
            
                if(!($answer==$record->expected_answer))
                {
                    $rejectsuccessful=rejectApplicant($application_id,$user);
                    if($rejectsuccessful)
                    {
                        return "rejected successfully";
                    }
                    else
                    {
                        return "rejected unsuccessfully";
                    }
                }
            }
            else
            {
                return "Applicant did not answer all the questions";
            }
        }
        else
        {
            $query2 = "INSERT INTO answer (application_id, question_id, MCQanswer) VALUES ($application_id,$question_id ,$answer)";
            $result2 = $db->query($query2);
            
            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 1)";
            $result3 = $db->query($query3);
        
         
        }
    }
    else
    {
        if($record->critical==true)
        {
            if($isAnswer == 0)
            {
                $query2 = "INSERT INTO answer (application_id, question_id, LongAnswer) VALUES ($application_id,$question_id ,'$answer')";
                $result2 = $db->query($query2);

                $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 1)";
                $result3 = $db->query($query3);
            }
            else
            {
                return "Applicant did not answer all the questions";
            }
        }
        else
        {
            $query2 = "INSERT INTO answer (application_id, question_id, LongAnswer) VALUES ($application_id,$question_id ,'$answer')";
            $result2 = $db->query($query2);

            $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 1)";
            $result3 = $db->query($query3);
        }
    }
                    

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}
    
// get answer by user
function getAllAnswersByUser($application_id, $user){

    $db = db::getConnection();
    $query = "SELECT * FROM answer WHERE application_id = $application_id";
    $result = $db->query($query);
    
    $answers = [];
    
    while( $record = $result->fetch_object()){
        if($record->deleted == false)
        {
            $answer = new stdClass();
            $answer->applicationid = $record->application_id;
            $answer->questionid = $record->question_id;
            $answer->answer = $record->answer;
            $answer->deleted = $record->deleted;
            $answers[] = $answer;
               
        }
    }  
    
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 5, 1)";
    $result3 = $db->query($query3);
    
    return $answers;
            
}

function createApplication($answers, $cv, $test, $user)
{
    $application = json_decode($answers->answers);

    $db = db::getConnection();

    $query = "INSERT INTO application (user_id, card_id, status_id, stage_id, createdBy, updatedBy) VALUES 
              ($user, $application->cardId, 8, 4, $user, $user);";
    $result = $db->query($query);

    $applicationId = $db->insert_id;
    $cvName = $user."-$applicationId.".pathinfo($cv["name"], PATHINFO_EXTENSION);
    $cvTarget = dirname(__DIR__)."/cvs/".$cvName;
    $cardId = $application->cardId;
    if(move_uploaded_file($cv['tmp_name'],$cvTarget))
    {
        $query = "UPDATE application SET application.cv = '$cvName' WHERE application.card_id = ".$cardId.";";
        $db->query($query);
    }

    $testName = $user."-$applicationId.".pathinfo($test["name"], PATHINFO_EXTENSION);
    $testTarget = dirname(__DIR__)."/tests/".$testName;
    if(move_uploaded_file($test['tmp_name'],$testTarget))
    {
        $query = "UPDATE application SET result_proof = '$testName' WHERE card_id = ".$cardId.";";
        $db->query($query);
    }

    foreach ($application->skills as $skill)
    {
        $query = "INSERT INTO answer (question_id, MCQanswer, application_id) VALUES ($skill->id, $skill->answer,$applicationId);";
        $db->query($query);
        if($db->affected_rows <= 0)
            echo "Skill not added";
    }

    foreach ($application->requirements as $req)
    {
        $query = "INSERT INTO answer (question_id, MCQanswer, application_id) VALUES ($req->id, $req->answer,$applicationId);";
        $db->query($query);
        if($db->affected_rows <= 0)
            echo "requirements not added";

    }

    foreach ($application->questions as $q)
    {
        $query = "INSERT INTO answer (question_id, LongAnswer, application_id) VALUES ($q->id, '$q->answer',$applicationId);";
        $db->query($query);
        if($db->affected_rows <= 0)
            echo "questions not added";
    }

    foreach ($application->languages as $lang)
    {
        $query = "INSERT INTO answer (question_id, MCQanswer, application_id) VALUES ($lang->id, $lang->answer,$applicationId);";
        $db->query($query);
        if($db->affected_rows <= 0)
            echo "languages not added";
    }

    $failed = compareQandA($applicationId);
    if($failed)
    {
        $query = "UPDATE application SET  status_id = 7 WHERE application.id = $applicationId;";
        $db->query($query);
    }

    return true;

}

    function compareQandA($appId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT MCQanswer,longAnswer, expected_answer, critical,skill_id as 'skillId', language_id as 'langId', 
                                    requirement_id as 'reqId', longquestion_id as 'longId' from answer INNER JOIN question on answer.question_id = question.id 
                                    WHERE application_id = ?");
        $stmt->bind_param("i",$appId);
        $stmt->execute();
        $result = $stmt->get_result();
        $failed = false;
        while($result AND $record = $result->fetch_object())
        {
            $record = (object) array_filter((array) $record);

            if($record->skillId)
            {
//                echo "Skill - critical: ".$record->critical." MCQ: ".$record->MCQanswer." EXPECTED: ".$record->expected_answer."\n";
                if($record->critical AND $record->MCQanswer != $record->expected_answer)
                    $failed = true;
            }
            else if($record->langId)
            {
//                echo "Lang - critical: ".$record->critical." MCQ: ".$record->MCQanswer." EXPECTED: ".$record->expected_answer."\n";
                if($record->critical AND $record->MCQanswer != $record->expected_answer)
                    $failed = true;
            }
            else if($record->reqId)
            {
//                echo "Req - critical: ".$record->critical." MCQ: ".$record->MCQanswer." EXPECTED: ".$record->expected_answer."\n";
                if($record->critical AND $record->MCQanswer != $record->expected_answer)
                    $failed = true;
            }
            else if($record->longId)
            {
//                echo "Long - critical: ".$record->critical." MCQ: ".$record->MCQanswer." EXPECTED: ".$record->expected_answer."\n";
                if($record->critical AND !$record->longAnswer)
                    $failed = true;
            }

        }
        return $failed;
    }
























