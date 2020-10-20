<?php

function getAllVerifies(){

    $db = db::getConnection();

    $query = "SELECT * FROM verify";
    $result = $db->query($query);
    $verifies = [];

    while( $record = $result->fetch_object())
    {
        $verify = new stdClass();
        $verify->verifyId = $record->id;
        $verify->userId= $record->user_id;
        $verify->Token = $record->token;
        $verify->Deleted = $record->deleted;
        $verifies[] = $verify;
    }
    return $verifies;
}

function generateVerify($userId,$email,$cell){
    $token = md5($userId . $email . $cell);
    $db = db::getConnection();
    $query = "INSERT INTO verify (user_id, token) VALUES ($userId, '$token');";
    $result = $db->query($query);

    if(!$result)
        return false;
    else{
        return $token;
    }
}

function validateToken($token){
    $db = db::getConnection();

    $query = "SELECT * FROM verify WHERE token = '$token' WHERE deleted = 0";
    $result = $db->query($query);

    if($result){
        return true;
    }
    else{
        return false;
    }
}

function useToken($token){
    $db = db::getConnection();

    $query = "UPDATE verify SET deleted = 1 WHERE token = '$token'";
    $result = $db->query($query);

    if($result)
        return true;
    else
        return false;
}



function sendToken($token,$email,$name){
    $subject = "Account Verification";
    $msg = "
        Thank you $name for signing up with BMW to verify your account please click the link below!

        

        http://bmwbackend.edumarx.co.za/verify.php?token=$token

        

        Regards

        The Futura Team

    ";
    $header = 'From:noreply@futurabmw.com' . "\r\n";
    mail($email,$subject,$msg,$header);
}