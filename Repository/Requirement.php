<?php

require_once "./Notification.php";

function getAllRequirements(){

    $db = db::getConnection();
    $query = "SELECT * FROM requirement";
    $result = $db->query($query);
    $requirements = [];
    while( $record = $result->fetch_object())
    {
        $requirement = new stdClass();
        $requirement->id = $record->id;
        $requirement->requirement = $record->requirement;
        $requirement->approved = $record->approved;
        $requirements[] = $requirement;
    }

    return $requirements;
}

function getAllDeletedRequirements(){

    $db = db::getConnection();
    $query = "SELECT * FROM requirement WHERE deleted = 0";
    $result = $db->query($query);
    $requirements = [];
    while( $record = $result->fetch_object())
    {
        $requirement = new stdClass();
        $requirement->id = $record->id;
        $requirement->requirement = $record->requirement;
        $requirement->approved = $record->approved;
        $requirements[] = $requirement;
    }

    return $requirements;
}

//create
function createARequirement($requirement, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM requirement";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->requirement == $requirement)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE requirement SET deleted=0, WHERE name = $requirement";
                $result1 = $db->query($query1);
                return "worked";
            }
        }
    }


    $query2 = "INSERT INTO requirement (qtype_id, requirement, approved, createdBy, updatedBy, deleted) VALUES (3, '$requirement', false, $user, $user,0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 36)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A requirement has been created, please approve it.");
        $notification2 = createANotification($user,"You have created a requirement.");
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
// update
function updateARequirement($id,$requirement, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM requirement";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->requirement == $requirement)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE requirement SET requirement='$requirement', approved=false, updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 36)";
    $result1 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A requirement has been updated.");
        $notification2 = createANotification($user,"You have updated a requirement.");
    }

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function approveARequirement($id, $user)
{
    $db = db::getConnection();

    $query = "UPDATE requirement SET approved = true, updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query);

    $query2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 36)";
    $result2 = $db->query($query2);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A requirement has been approved.");
        $notification2 = createANotification($user,"You have approved a requirement.");
    }

    return $result;
}

function deleteARequirement($id, $user)
{
    $db = db::getConnection();

    $questionQuery = "SELECT question.requirement_id 
    FROM question";
    $questionResult = $db->query($questionQuery);

    while($record = $questionResult->fetch_object())
    {
        if($record->requirement_id == $id)
        {
            $query1 = "UPDATE requirement SET deleted=1 WHERE id = $id";
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

    $deleteQuery = "DELETE FROM requirement WHERE id = $id";
    $deleteRequirement = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 36)";
    $result4 = $db->query($query4);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A requirement has been deleted.");
        $notification2 = createANotification($user,"You have deleted a requirement.");
    }

    if($deleteRequirement && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getAllPendingRequirements(){

    $db = db::getConnection();
    $query = "SELECT * FROM requirement WHERE approved = 0";
    $result = $db->query($query);
    $requirements = [];
    while( $record = $result->fetch_object())
    {
        $requirement = new stdClass();
        $requirement->id = $record->id;
        $requirement->requirement = $record->requirement;
        $requirement->approved = $record->approved;
        $requirements[] = $requirement;
    }

    return $requirements;
}

function getAllApprovedRequirements(){

    $db = db::getConnection();
    $query = "SELECT * FROM requirement WHERE approved = 1";
    $result = $db->query($query);
    $requirements = [];
    while( $record = $result->fetch_object())
    {
        $requirement = new stdClass();
        $requirement->id = $record->id;
        $requirement->requirement = $record->requirement;
        $requirement->approved = $record->approved;
        $requirements[] = $requirement;
    }

    return $requirements;
}

function getAllRequirementPendingCount()
{
    $db = db::getConnection();
    $query = "SELECT count(id) AS 'pending' FROM requirement WHERE approved=false;";
    $result = $db->query($query);
    $record = $result->fetch_object();
    $pending = new stdClass();
    $pending->pending = $record->pending;
    return $pending;
}