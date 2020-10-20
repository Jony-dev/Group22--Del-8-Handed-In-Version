<?php

require_once "./Notification.php";

require_once("db.php");

function getAllRoles(){

    $db = db::getConnection();
    $query = "SELECT * FROM role";
    $result = $db->query($query);
    $roles = [];
    while( $record = $result->fetch_object()){

        $role = new stdClass();
        $role->roleId = $record->id;
        $role->roleName = $record->name;
        $roles[] = $role;
    }

    return $roles;
}

function getAllDeletedRoles(){

    $db = db::getConnection();
    $query = "SELECT * FROM role WHERE deleted = 0";
    $result = $db->query($query);
    $roles = [];
    while( $record = $result->fetch_object()){

        $role = new stdClass();
        $role->roleId = $record->id;
        $role->roleName = $record->name;
        $roles[] = $role;
    }

    return $roles;
}


function createARole($name, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM role";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->name == $name)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE role SET deleted=0, WHERE name = $name";
                $result1 = $db->query($query1);
                return "worked";
            }
        }
    }


    $query2 = "INSERT INTO role (name, createdBy, updatedBy, deleted) VALUES ('$name', '$user', '$user',0)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 38)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A role has been created.");
        $notification2 = createANotification($user,"You have created a role.");
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

//update 
function updateARole($id,$name,$user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM role WHERE role.name = '$name'";
    $result = $db->query($query);

    if($result)
    while($record = $result->fetch_object())
    {
        if($record->name == $name )
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE role SET role.name='$name', updatedBy= $user, updatedDate = NOW() WHERE role.id = $id;";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 38);";
    $result1 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A role has been updated.");
        $notification2 = createANotification($user,"You have updated a role.");
    }

    if($db->affected_rows > 0)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}

function deleteARole($id, $user)
{
    $db = db::getConnection();

    $userRoleQuery = "SELECT user_role.role_id 
    FROM user_role";
    $userRoleResult = $db->query($userRoleQuery);

    while($record = $userRoleResult->fetch_object())
    {
        if($record->role_id == $id)
        {
            $query1 = "UPDATE role SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $viewAuthQuery = "SELECT view_auth.role_id 
    FROM view_auth";
    $viewAuthResult = $db->query($viewAuthQuery);

    while($record = $viewAuthResult->fetch_object())
    {
        if($record->role_id == $id)
        {
            $query1 = "UPDATE role SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $operationAuthQuery = "SELECT * FROM operation_auth";
    $operationAuthResult = $db->query($operationAuthQuery);

    while($record = $operationAuthResult->fetch_object())
    {
        if($record->role_effected == $id || $record->role_target == $id)
        {
            $query1 = "UPDATE role SET deleted=1 WHERE id = $id";
            $result1 = $db->query($query1);
            return $query1;           
        }
    }

    $deleteQuery = "DELETE FROM role WHERE id = $id";
    $deleteRole = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 38)";
    $result4 = $db->query($query4);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A role has been deleted.");
        $notification2 = createANotification($user,"You have deleted a role.");
    }

    if($deleteRole && $result4)
    {
        return true;
    }
    else
    {
        return false;
    }
}

function removeUserRole($user, $roleId)
{
    $db = db::getConnection();
    $stmt = $db->prepare("DELETE FROM user_role WHERE user_id = ? AND role_id = ?");
    $stmt->bind_param('ii',$user,$roleId);
    if($stmt->execute())
        return true;
    return false;

}

function addUserRole($user, $roleId,$causer)
{
    $db = db::getConnection();
    $stmt = $db->prepare("INSERT INTO user_role (user_id, role_id,updatedBy,createdBy) VALUES (?,?,?,?);");
    $stmt->bind_param('iiii',$user,$roleId,$causer,$causer);

    if($stmt->execute())
        return true;
    return false;

}
