<?php

require_once("db.php");

function getAllPasswordResets(){

    $db = db::getConnection();
    $query = "SELECT * FROM password_reset";
    $result = $db->query($query);
    $passwordresets = [];
    while( $record = $result->fetch_object()){

        $passwordreset = new stdClass();
        $passwordreset->resetid = $record->id;
        $passwordreset->loginid = $record->login_id;
        $passwordreset->token = $record->token;
        $passwordreset->deleted = $record->deleted;
        $passwordreset->created = $record->created;
        $passwordresets[] = $passwordreset;
    }

    return $passwordresets;
}