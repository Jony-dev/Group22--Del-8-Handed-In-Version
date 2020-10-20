<?php

require_once "./Notification.php";

require_once "./Role.php";

function getAllViewAuthorisations(){

    $db = db::getConnection();
    $query = "SELECT role.id as 'roleId', role.name as 'roleName', view.id as 'viewId', view.name as 'viewName'  FROM view_auth INNER JOIN role on role.id = view_auth.role_id INNER JOIN view on view.id = view_auth.view_id";
    $result = $db->query($query);
    $viewAuths = [];
    while( $record = $result->fetch_object())
    {
        $viewAuth = new stdClass();
        $view = new stdClass();
        $role = new stdClass();

        $view->id = $record->viewId;
        $view->view = $record->viewName;
        $role->roleId = $record->roleId;
        $role->roleName = $record->roleName;

        $viewAuth->view = $view;
        $viewAuth->role = $role;
        $viewAuths[] = $viewAuth;
    }

    return $viewAuths;
}

function createAViewAuthorisation($viewId, $roleId, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM view_auth";
    $result = $db->query($query);
    
    while($record = $result->fetch_object())
    {
        if($record->role_id == $roleId && $record->view_id == $viewId)
        {
            if($record->deleted == false)
            {
                return "duplicate";
            }
            else
            {
                $query1 = "UPDATE view_auth SET deleted=0 WHERE view_id = $viewId AND role_id = $roleId";
                $result1 = $db->query($query1);
                return "worked";                    
            }   
        }
    }

    $query2 = "INSERT INTO view_auth (view_id, role_id, createdBy) VALUES ($viewId, $roleId, $user)";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 59)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A view authorisation has been created.");
        $notification2 = createANotification($user,"You have created a view authorisation.");
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

function deleteAViewAuthorisation($viewId, $roleId, $user)
{
    $db = db::getConnection();

    $roleQuery = "SELECT role.id, role.deleted 
    FROM view_auth 
    INNER JOIN role on role.id = view_auth.role_id  
    WHERE view_auth.role_id = $roleId";

    $roleResult = $db->query($roleQuery);
    $roleId = 0;
    $roleDeleted = false;

    while($record = $roleResult->fetch_object())
    {
        $roleId = $record->id;
        $roleDeleted = $record->deleted;
    }


    $numberQuery = "SELECT count(view_auth.role_id)
    FROM view_auth
    WHERE view_auth.role_id = $roleId";

    $numberResult = $db->query($numberQuery);
    $row = $numberResult->fetch_row();
    $count = $row[0];

    $deleteQuery = "DELETE FROM view_auth WHERE role_id = $roleId AND view_id = $viewId";
    $deleteViewAuth = $db->query($deleteQuery);

    $query4 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 59)";
    $result4 = $db->query($query4);

    if($count<2 && $roleDeleted == 1)
    {
        $deleterole = deleteARole($roleId, $user);

    }

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A view authorisation has been deleted.");
        $notification2 = createANotification($user,"You have deleted a view authorisation.");
    }

    if($deleteViewAuth && $result4)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}