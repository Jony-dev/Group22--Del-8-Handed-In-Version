<?php

function getAllViews(){
    $db = db::getConnection();
    $query = "SELECT * FROM view";
    $result = $db->query($query);
    $views = [];
    while( $record = $result->fetch_object())
    {
        $view = new stdClass();
        $view->id = $record->id;
        $view->view = $record->name;
        $views[] = $view;
    }

    return $views;
}

function getUserViews($userId){
    $db = db::getConnection();

    $query = "SELECT view.id ,view.name FROM user_profile INNER JOIN user_role on user_profile.id = user_role.user_id INNER JOIN view_auth on view_auth.role_id = user_role.role_id 
              INNER JOIN view on view.id = view_auth.view_id WHERE user_profile.id = $userId";
    $result = $db->query($query);
    $views = [];
    if ($result)
        while($record = $result->fetch_object())
        {
            $view = new stdClass();
            $view->id = $record->id;
            $view->name = $record->name;
            $views[] = $view;
        }
        return $views;
}