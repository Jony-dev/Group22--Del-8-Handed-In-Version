<?php

require_once "./Notification.php";

function getAllMembers(){

    $db = db::getConnection();
    $query = "SELECT * FROM team_member";
    $result = $db->query($query);
    $members = [];
    while( $record = $result->fetch_object())
    {
        $member = new stdClass();
        $member->userId = $record->user_id;
        $member->teamID = $record->team_id;
        $member->createdBy = $record->createdBy;
        $member->createdDate = $record->createdDate;
        $member->deleted = $record->deleted;
        $members[] = $member;
    }

    return $members;
}

function getAllTeamsMembers($teamId){

    $db = db::getConnection();
    $query = "SELECT user_profile.name, user_profile.surname, user_profile.picture 
    FROM user_profile
    INNER JOIN team_member 
    ON user_profile.id = team_member.user_id
    INNER JOIN team
    ON team_member.team_id = team.id
    WHERE team.id=$teamId;";
    $result = $db->query($query);
    $member = [];

        while($record = $result->fetch_object())
        {
            $user = new stdClass();
            $user->name = $record->name;
            $user->surname = $record->surname;
            if($record->picture)
            {
                $user->pic = $record->picture;
            }
            else{
                $user->pic = null;
            }
            $members[] = $user;
        }

    return $members;
}

function createATeamMember($team_id, $user_id, $user)
{
    $db = db::getConnection();

    $query2 = "INSERT INTO team_member (team_id, user_id, createdBy) VALUES ($team_id, $user_id, $user)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 50)";
    $result3 = $db->query($query3);

    
    $notification2 = createANotification($user_id,"You have been added to a team.");

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}

function deleteATeamMember($team_id, $user_id, $user)
{
    $db = db::getConnection();
    
    $teamMemberQuery = "DELETE FROM team_member WHERE team_id = $team_id AND user_id=$user_id";
    $teamMemberResult = $db->query($teamMemberQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 50)";
    $result4 = $db->query($query4);

    $notification2 = createANotification($user,"You have been removed from a team.");

    if($teamMemberResult && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}