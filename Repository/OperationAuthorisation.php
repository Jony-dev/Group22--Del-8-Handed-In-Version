<?php

require_once "./Notification.php";

function getAllOperationAuthorisations(){

    $db = db::getConnection();
    $stmt = $db->prepare("SELECT role_effected as 'effectedId', role_target as 'targetId', operation_id as 'operationId', effected.name as 'effector', 
                                target.name as 'target', database_table.id as 'databaseId', database_table.name as 'database', operation.operation
                                FROM edumarxc_bmwdatabase.operation_auth
                                INNER JOIN role effected on operation_auth.role_effected = effected.id
                                INNER JOIN role target on operation_auth.role_target = target.id
                                INNER JOIN database_table on operation_auth.database_id = database_table.id
                                INNER JOIN operation on operation_auth.operation_id = operation.id;");
    $stmt->execute();
    $result = $stmt->get_result();
    $operationauthorisations = [];

    while($result AND $record = $result->fetch_object())
        $operationauthorisations[] = $record;

    return $operationauthorisations;
}

function checkAnOperationAuthorisation($userId, $operationId, $databaseTableId)
{
    $db = db::getConnection();

    $query = "SELECT * FROM operation_auth WHERE operation_id = $operationId AND dbTable_id = $databaseTableId";
    $result = $db->query($query);

    $query2 = "SELECT * FROM user_role WHERE user_id = $userId";
    $result2 = $db->query($query2);

    while($record = $result->fetch_object())
    {
        while($record2 = $result2->fecth_object())
        {
            if($record->role_target == $record2->role_id)
            {
                return true;
            }
        }
        $query = "SELECT * FROM operation_auth WHERE operation_id = $operationId AND dbTable_id = $databaseTableId";
        $result = $db->query($query);
    }

    return false;
}

function deleteAOperationAuthorisation($roleaffected, $roletarget,$operationid,$dbtableid,$user)
{
    $db = db::getConnection();

    $deleteQuery = "DELETE FROM operation_auth WHERE role_effected = $roleaffected AND role_target = $roletarget AND operation_id = $operationid AND database_id = $dbtableid";
    $deleteResult = $db->query($deleteQuery);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 4, 32)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"An operation authorisation has been deleted.");
        $notification2 = createANotification($user,"You have deleted an operation authorisation.");
    }

    if($deleteResult && $result3)
    {
        return "worked";
    }
    else{
        return "DELETING ".$deleteResult." AUDIT ".$result3;
    }
}

function createAOperationAuthorisation($roleaffected, $roletarget,$operationid,$dbtableid,$user)
{
    $db = db::getConnection();

    $duplicateQuery = "SELECT * FROM operation_auth";
    $duplicateResult = $db->query($duplicateQuery);

    while($duplicateRecord = $duplicateResult->fetch_object())
    {
        if(($duplicateRecord->role_effected == $roleaffected) && ($duplicateRecord->role_target==$roletarget) && ($duplicateRecord->operation_id == $operationid) && ($duplicateRecord->database_id == $dbtableid))
        {
            return "duplicate";
        }
    }

    $insertQuery = "INSERT INTO operation_auth (role_effected, role_target, operation_id, database_id) VALUES ($roleaffected,$roletarget,$operationid,$dbtableid)";
    $insertResult = $db->query($insertQuery);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 32)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"An operation authorisation has been created.");
        $notification2 = createANotification($user,"You have created an operation authorisation.");
    }

    if($insertResult && $result3)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}