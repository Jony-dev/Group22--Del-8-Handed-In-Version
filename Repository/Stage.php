<?php
function getAllStages(){

    $db = db::getConnection();
    $query = "SELECT * FROM stage";
    $result = $db->query($query);
    $stages = [];
    while( $record = $result->fetch_object())
    {
        $stage = new stdClass();
        $stage->id = $record->id;
        $stage->stage = $record->stage;
        $stage->createdBy = $record->createdBy;
        $stage->updatedBy = $record->updatedBy;
        $stage->createdDate = $record->createdDate;
        $stage->updatedDate = $record->updatedDate;
        $stage->deleted = $record->deleted;
        $stages[] = $stage;
    }

    return $stages;
}
// stage 
function createAStage($stage, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM stage";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->stage == $stage)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE stage SET deleted=0, WHERE stage = $stage";
                $result1 = $db->query($query1);
                return "worked";
            }
        }
    }


    $query2 = "INSERT INTO stage (stage, createdBy, updatedBy, deleted) VALUES ('$stage','$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 43)";
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
function updateAStage($id,$stage, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM stage";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->stage == $stage)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE stage SET stage='$stage',  updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 43)";
    $result1 = $db->query($query3);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteAStage($id, $user)
{
    $db = db::getConnection();

    $jobQuery = "SELECT job_card_stage.stage_id 
    FROM job_card_stage";
    $jobResult = $db->query($jobQuery);

    while($record = $jobResult->fetch_object())
    {
        if($record->stage_id == $id)
        {
            $query1 = "UPDATE stage SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $applicationQuery = "SELECT application.stage_id 
    FROM application";
    $applicationResult = $db->query($applicationQuery);

    while($record = $applicationResult->fetch_object())
    {
        if($record->stage_id == $id)
        {
            $query1 = "UPDATE stage SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $deleteQuery = "DELETE FROM stage WHERE id = $id";
    $deleteStage = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 43)";
    $result4 = $db->query($query4);

    if($deleteStage && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}