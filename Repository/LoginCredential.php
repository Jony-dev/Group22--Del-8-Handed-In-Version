<?php

require_once("db.php");

function getAllCredentials(){

    $db = db::getConnection();
    $query = "SELECT * FROM login_credentials";
    $result = $db->query($query);
    $credentials = [];
    while( $record = $result->fetch_object()){

        $credential = new stdClass();
        $credential->loginid = $record->id;
        $credential->userid = $record->user_id;
        $credential->passswordhash = $record->password_hash;
        $credential->passwordsalt = $record->password_salt;
        $credential->updatedby = $record->updated_by;
        $credential->updateddate = $record->updated_date;
        $credential->deleted = $record->deleted;
        $credentials[] = $credential;
    }

    return $credentials;
}