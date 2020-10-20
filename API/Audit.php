<?php
function getAllAudits(){

    $db = db::getConnection();
    $auditQuery = "SELECT * FROM audit_log";
    $auditResult = $db->query($auditQuery);
    $audits = [];
    while($auditRecord = $auditResult->fetch_object())
    {
        $userQuery = "SELECT name, surname FROM user_profile WHERE id=$auditRecord->user_id";
        $userResult = $db->query($userQuery);
        $userRecord = $userResult->fetch_object();
        $userName = $userRecord->name;
        $userSurname = $userRecord->surname;

        $operationQuery = "SELECT operation, id FROM operation WHERE id=$auditRecord->operation_id";
        $operationResult = $db->query($operationQuery);
        $operationRecord = $operationResult->fetch_object();
        $operation = $operationRecord->operation;

        $databaseQuery = "SELECT name FROM database_table WHERE id=$auditRecord->database_id";
        $databaseResult = $db->query($databaseQuery);
        $databaseRecord = $databaseResult->fetch_object();
        $database = $databaseRecord->name;

        $audit = new stdClass();
        $audit->id = $auditRecord->id;
        $audit->userId = $auditRecord->user_id;
        $audit->userName = $userName;
        $audit->userSurname = $userSurname;
        $audit->operationPerformed = $operation." ".$database;
        $audit->operationId = $operationRecord->id;
        $audit->date = $auditRecord->date;
        $audits[] = $audit;
    }

    return $audits;
}

function getAllRecords(){
    $db = db::getConnection();
    $query = "SELECT TABLE_NAME AS 'Name', TABLE_ROWS AS 'Row'
    FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA='edumarxc_bmwdatabase';";

    $result = $db->query($query);

    $databases = [];
    while($record = $result->fetch_object())
    {
        $database = new stdClass();
        $database->databaseName = $record->Name;
        $database->rowCount = $record->Row;
        $databases[] = $database;
    }

    return $databases;
}
