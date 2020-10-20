<?php
function getAllRequisitionApprovals(){

    $db = db::getConnection();
    $query = "SELECT * FROM requisition_approval";
    $result = $db->query($query);
    $requistionapprovals = [];
    while( $record = $result->fetch_object())
    {
        $requistionapproval = new stdClass();
        $requistionapproval->id = $record->id;
        $requistionapproval->approval = $record->approval;
        $requistionapprovals[] = $requistionapproval;
    }

    return $requistionapprovals;
}
// create
function createAApproval($approval, $user)
{   
    $db = db::getConnection();

    $query = "SELECT * FROM requisition_approval";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->approval == $approval )
        {
            return "duplicate";
        }
    }


    $query2 = "INSERT INTO requisition_approval (approval) VALUES ('$approval')";
    $result2 = $db->query($query2);
                                                                                        
    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 2, 37)";
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
// update
function updateAApproval($id,$approval, $user)
{
    $db = db::getConnection();

    $query = "SELECT * FROM requisition_approval";
    $result = $db->query($query);

    while($record = $result->fetch_object())
    {
        if($record->approval == $approval)
        {
            return "duplicate";
        }
    }


    $query2 = "UPDATE requisition_approval SET approval='$approval' WHERE id = $id";
    $result = $db->query($query2);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ('$user', 3, 37)";
    $result1 = $db->query($query3);

    if($result)
    {
        return "worked";
    }
    else{
        return "did not work";
    }
}