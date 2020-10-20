<?php
function getAllLanguages(){

    $db = db::getConnection();
    $db->set_charset("utf8");
    $query = "SELECT * FROM language";
    $result = $db->query($query);
    $languages = [];
    while( $record = $result->fetch_object()){

        $language = new stdClass();
        $language->id = $record->id;
        $language->language = $record->language;
        $languages[] = $language;
    }
    return $languages;
}

function createALanguage($qtype_id,$language,$user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM language";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->language == $language)
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO language (qtype_id, language) VALUES ($qtype_id, '$language')";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 25)";
    $result3 = $db->query($query3);

    if($result2 && $result3)
    {
        return "worked";
    }
    else
    {
        return "did not work";
    }
}