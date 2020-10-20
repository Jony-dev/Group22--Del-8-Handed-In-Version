<?php

require_once "./Notification.php";

function getAllTables(){

    $db = db::getConnection();
    $query = "SELECT tafel.id, tafel.type_id as 'ttypeId', tafel.floor_id as 'floorId', floor.floor_number as 'floorName', barcode, tafel.name as 'name', building_id as 'buildingId', building.name as 'buildingName'  
              FROM edumarxc_bmwdatabase.tafel 
                INNER JOIN floor on tafel.floor_id = floor.id
                INNER JOIN building on building.id = floor.building_id
                WHERE tafel.deleted = 0;";
    $result = $db->query($query);
    $tables = [];
    while( $record = $result->fetch_object())
    {
        $tables[] = $record;
    }

    return $tables;
}
// get all Deleted Tables
function getAllDeletedTables(){

    $db = db::getConnection();
    $query = "SELECT * FROM tafel";
    $result = $db->query($query);
    $tables = [];
    while( $record = $result->fetch_object())
    {
        if($record->deleted == false)
        {
        $table = new stdClass();
        $table->id = $record->id;
        $table->ttypeId = $record->type_id;
        $table->floorId = $record->floor_id;
        $table->barcode = $record->barcode;
        $table->name = $record->name;
        $table->createdBy = $record->createdBy;
        $table->updatedBy = $record->updatedBy;
        $table->createdDate = $record->createdDate;
        $table->updatedDate = $record->updatedDate;
        $table->deleted = $record->deleted;
        $tables[] = $table;
        }
    }
    return $tables;
}
// create
function createATable($typeId,$floorId,$name,$buildingId,$user)
{
    $todayDate = date('Y-m-d');
    $db = db::getConnection();
    $stmt = $db->prepare("SELECT * FROM tafel WHERE type_id = ? AND floor_id = ? AND name = ?;");
    $stmt->bind_param('iis',$typeId,$floorId,$name);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 1)
    {
        $stmt->close();
        $record = $result->fetch_object();

        if($record->deleted == 1)
        {
            $stmt = $db->prepare("UPDATE tafel SET deleted = 0 WHERE id = ? ");
            $stmt->bind_param("i",$record->id);
            if(!$stmt->execute())
                return "did not work";
        }
        else
            return "duplicate";
    }

    $stmt = $db->prepare("INSERT INTO tafel (type_id,floor_id,name,createdBy,updatedBy) VALUES (?,?,?,?,?);");
    $stmt->bind_param('iisii',$typeId,$floorId,$name,$user,$user);
    if(!$stmt->execute())
        return "did not work";
    $stmt->close();

    $newTableId = $db->insert_id;

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 2, 45)";
    $result3 = $db->query($query3);

    $success = true;
    if($typeId == 4)
    {

        $stmt = $db->prepare("SELECT * FROM date WHERE date >= ?");
        $stmt->bind_param('s',$todayDate);
        $stmt->execute();
        $dates = $stmt->get_result();
        $stmt->close();

        while($dates AND $record = $dates->fetch_object())
        {
            $stmt = $db->prepare("INSERT INTO table_date (date_id, table_id) VALUES (?,?);");
            $stmt->bind_param('ii',$record->id,$newTableId);
            if(!$stmt->execute())
                $success = false;

            $stmt->close();

            $stmt = $db->prepare("INSERT INTO audit_log (user_id, operation_id, database_id) VALUES (?, 2, 46)");
            $stmt->bind_param("i",$user);
            if(!$stmt->execute())
                $success = false;
        }

        if($success)
            return "worked";
        else
            return "did not work";
    }

    if($typeId == 3)
    {
        $success = true;
        $stmt = $db->prepare("SELECT * FROM slot;");
        $stmt->execute();
        $slots = $stmt->get_result();
        $stmt->close();

        $slotIds = [];
        while($slots AND $record = $slots->fetch_object())
            $slotIds[] = $record->id;

        $stmt = $db->prepare("SELECT * FROM date WHERE date >= ?");
        $stmt->bind_param('s',$todayDate);
        $stmt->execute();
        $dates = $stmt->get_result();
        $stmt->close();

        while ($dates AND $record = $dates->fetch_object())
        {
            foreach ($slotIds as $slotId)
            {
                $stmt = $db->prepare("INSERT INTO date_slot (date_id, slot_id) VALUE (?,?)");
                $stmt->bind_param('ii',$record->id,$slotId);
                $stmt->execute();
                $stmt->close();

                $stmt = $db->prepare("INSERT INTO table_date_slot (date_id, table_id, slot_id) VALUES (?,?,?)");
                $stmt->bind_param('iii',$record->id,$slotId,$newTableId);
                $stmt->execute();
                if(!$stmt->execute())
                    $success = false;
                $stmt->close();

                $stmt = $db->prepare("INSERT INTO audit_log (user_id, operation_id, database_id) VALUES (?, 2, 47);");
                $stmt->bind_param('i',$user);
                if(!$stmt->execute())
                    $success = false;
                $stmt->close();

            }

        }


        $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
        $adminResult = $db->query($adminQuery);

        while($record = $adminResult->fetch_object())
        {
            $notification1 = createANotification($record->user_id,"A table has been created.");
            $notification2 = createANotification($user,"You have created a table.");
        }

        if($success)
        {
            return "worked";
        }
        else
        {
            return "did not work";
        }
    }
}

//update
function updateATable($id,$type,$floorId,$name,$user)
{
    $db = db::getConnection();

    $stmt = $db->prepare("SELECT * FROM tafel WHERE type_id = ? AND floor_id = ? AND name = ?;");
    $stmt->bind_param("iis",$type,$floorId,$name);
    $stmt->execute();
    if($stmt->num_rows > 1)
        return "duplicate";
    $stmt->close();

    $stmt = $db->prepare("UPDATE tafel SET type_id=?, floor_id=?, tafel.name=? WHERE tafel.id=? ;");

    $stmt->bind_param("iisi",$type,$floorId,$name,$id);
    $result2 = $stmt->execute();


    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user, 3, 45)";
    $result3 = $db->query($query3);

    $adminQuery = "SELECT user_id FROM user_role WHERE role_id = 10";
    $adminResult = $db->query($adminQuery);

    while($record = $adminResult->fetch_object())
    {
        $notification1 = createANotification($record->user_id,"A table has been updated.");
        $notification2 = createANotification($user,"You have updated a table.");
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




function getSlotDetails(){

    $db = db::getConnection();
    $stmt = $db->prepare("SELECT * FROM edumarxc_bmwdatabase.slot ORDER by id ASC LIMIT 1;");
    $stmt->execute();
    $firstSlot = $stmt->get_result()->fetch_object();

    $stmt = $db->prepare("SELECT * FROM edumarxc_bmwdatabase.slot ORDER by id DESC LIMIT 1;");
    $stmt->execute();
    $lastSlot = $stmt->get_result()->fetch_object();

    $retObj = new stdClass();
    $retObj->startTime = $firstSlot->startTime;
    $retObj->endTime = $lastSlot->endTime;

    $stmt = $db->prepare("SELECT count(*) as numSlots FROM edumarxc_bmwdatabase.slot;");
    $stmt->execute();
    $numSlots = $stmt->get_result()->fetch_object()->numSlots;
    $retObj->numSlots = ($retObj->endTime - $retObj->startTime) / $numSlots;

    return $retObj;

}

    function deleteATable($id)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("UPDATE tafel SET deleted = 1 WHERE id = ?");
        $stmt->bind_param('i',$id);
        if($stmt->execute())
        {
            return true;
        }

        return false;
    }







