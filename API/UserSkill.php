<?php

require_once "./Notification.php";

function getAllUserSkills(){

    $db = db::getConnection();
    $query = "SELECT * FROM user_skill";
    $result = $db->query($query);
    $userskills = [];
    while( $record = $result->fetch_object())
    {
        $userskill = new stdClass();
        $userskill->userid= $record->user_id;
        $userskill->skillid= $record->skill_id;
        $userskill->deleted= $record->deleted;
        $userskills[] = $userskill;
    }

    return $userskills;
}

function createAUserSkill($skill_id, $user_id, $user)
{
    $db = db::getConnection();

    $query2 = "INSERT INTO user_skill (skill_id, user_id) VALUES ($skill_id, $user_id)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 55)";
    $result3 = $db->query($query3);

    $notification2 = createANotification($user,"You have added a skill from profile");

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function deleteAUserSkill($user_id, $skill_id, $user)
{
    $db = db::getConnection();
    
    $userSkillQuery = "DELETE FROM user_skill WHERE skill_id = $skill_id AND user_id=$user_id";
    $userSkillResult = $db->query($userSkillQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 55)";
    $result4 = $db->query($query4);

    $notification2 = createANotification($user,"You have deleted a skill from your profile");

    if($userSkillResult && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}