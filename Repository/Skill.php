<?php

require_once "./Notification.php";

require_once("db.php");

function getAllSkills(){

    $db = db::getConnection();
    $query = "SELECT * FROM skill";
    $result = $db->query($query);
    $skills = [];
    while( $record = $result->fetch_object()){

        $skill = new stdClass();
        $skill->id = $record->id;
        $skill->skill = $record->skill;
        $skills[] = $skill;
    }

    return $skills;
}

function getAllDeletedSkills(){

    $db = db::getConnection();
    $query = "SELECT * FROM skill WHERE deleted = 0";
    $result = $db->query($query);
    $skills = [];
    while( $record = $result->fetch_object()){

        $skill = new stdClass();
        $skill->id = $record->id;
        $skill->skill = $record->skill;
        $skills[] = $skill;
    }

    return $skills;
}

function getAllSkillPendingCount()
{
    $db = db::getConnection();
    $query = "SELECT count(id) AS 'pending' FROM skill WHERE approved=false;";
    $result = $db->query($query);
    $record = $result->fetch_object();
    $pending = new stdClass();
    $pending->pending = $record->pending;
    return $pending;
}

//create
function createASkill($skill, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM skill";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->skill == $skill)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE skill SET deleted=0, WHERE skill=$skill";
                $result1 = $db->query($query1);
                return "worked";
            }
        }
    }


    $query2 = "INSERT INTO skill (qtype_id, skill, approved, createdBy, updatedBy, deleted) VALUES (1, '$skill', false, $user, $user,0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 40)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A skill has been created.");
        $notification2 = createANotification($user,"You have updated a skill.");
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
function updateASkill($id,$skill, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM skill";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->skill == $skill)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE skill SET skill='$skill', approved=false, updatedBy='$user', updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 40)";
    $result1 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A skill has been updated.");
        $notification2 = createANotification($user,"You have updated a skill.");
    }

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function approveASkill($id, $user)
{
    $db = db::getConnection();

    $query = "UPDATE skill SET approved = true, updatedBy=$user, updatedDate = NOW() WHERE id = $id";
    $result = $db->query($query);

    $query2 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 3, 40)";
    $result2 = $db->query($query2);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A skill has been approved.");
        $notification2 = createANotification($user,"You have approved a skill.");
    }


    return $result;
}

function getUsersSkills($userId)
{
    $db = db::getConnection();
    $query = "SELECT * FROM user_skill INNER JOIN skill on skill.id = user_skill.skill_id WHERE user_skill.user_id = $userId AND deleted=false;";
    $result = $db->query($query);


}

function deleteASkill($id, $user)
{
    $db = db::getConnection();
    
    $userSkillQuery = "DELETE FROM user_skill WHERE skill_id = $id";
    $userSkillResult = $db->query($userSkillQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 40)";
    $result4 = $db->query($query4);

    $questionQuery = "SELECT question.skill_id 
    FROM question";
    $questionResult = $db->query($questionQuery);

    while($record = $questionResult->fetch_object())
    {
        if($record->skill_id == $id)
        {
            $query1 = "UPDATE skill SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
                     
            
            $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 40)";
            $result4 = $db->query($query4);

            return $query1;
        }
    }

    $deleteQuery = "DELETE FROM skill WHERE id = $id";
    $deleteSkill = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 40)";
    $result4 = $db->query($query4);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A skill has been deleted.");
        $notification2 = createANotification($user,"You have deleted a skill.");
    }

    if($deleteSkill && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getAllPendingSkills(){

    $db = db::getConnection();
    $query = "SELECT * FROM skill WHERE approved = 0";
    $result = $db->query($query);
    $skills = [];
    while( $record = $result->fetch_object()){

        $skill = new stdClass();
        $skill->id = $record->id;
        $skill->skill = $record->skill;
        $skills[] = $skill;
    }

    return $skills;
}

function getAllApprovedSkills(){

    $db = db::getConnection();
    $query = "SELECT * FROM skill WHERE approved = 1";
    $result = $db->query($query);
    $skills = [];
    while( $record = $result->fetch_object()){

        $skill = new stdClass();
        $skill->id = $record->id;
        $skill->skill = $record->skill;
        $skills[] = $skill;
    }

    return $skills;
}