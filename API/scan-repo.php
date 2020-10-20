<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jonathan
 * Date: 6/16/2020
 * Time: 12:58 PM
 */



function studentExist($sId,$sName,$sSurname)
{
    $con = db::getConnection();
    $query = "SELECT * FROM student WHERE id = $sId AND name = '$sName' AND surname= '$sSurname';";
    $result = $con->query($query);

    $obj = new stdClass();
    if (mysqli_num_rows($result)>0)
        return $obj = $result->fetch_object();
    else
        return $obj;
}

function studentExistById($sId)
{
    $con = db::getConnection();
    $query = "SELECT * FROM student WHERE id = $sId";
    $result = $con->query($query);

    $obj = new stdClass();
    if (mysqli_num_rows($result)>0)
        return $obj = $result->fetch_object();
    else
        return $obj;
}

function enterScan($sId,$classId,$temp)
{

    $con = db::getConnection();
    $query = "INSERT INTO scan (student_id,class_id,tempreture) VALUES ($sId, $classId,$temp);";
    $con->query($query);
    if($con->affected_rows > 0 )
        return 1;
    else
        return 0;


}

function checkScanned($studentId){

    $id = $studentId->id;
    $date = date("Y-m-d");
    $db = db::getConnection();
    $query = "SELECT * FROM scan INNER JOIN student on student.id = scan.student_id INNER JOIN class on student.class_id = class.id INNER JOIN grade on class.grade_id = grade.id
    WHERE student_id = $id AND scan_time BETWEEN '$date 00:00:00' AND '$date 23:59:59';";

    $result = $db->query($query);

    if($result->num_rows > 0)
    {
        $child = new stdClass();
        if($result){
            $result = $result->fetch_object();
            $child->id = $id;
            $child->name = $result->name;
            $child->surname = $result->surname;
            $child->grade = $result->grade;
            $child->class = $result->class;
            return $child;
        }
    }
    else
        return null;
}
function uploadWellness($student,$wellness){

    $con = db::getConnection();
    $query = "UPDATE scan SET highTemp = $wellness->temp, cough = $wellness->cough, throat = $wellness->throat, 
    breath = $wellness->breath, diarrhoea = $wellness->diarrhoea, lostSmell = $wellness->lostSmell, covid_case = $wellness->covidCase,
    body_ache = $wellness->bodyAche
    WHERE student_id = $student->id ORDER BY id DESC LIMIT 1;";

    $result = $con->query($query);

    if($con->affected_rows > 0)
        return 1;
    else
        return 0;

}

function getStudent($id){
    $con = db::getConnection();
    $query = "SELECT * FROM student INNER JOIN class on student.class_id = class.id INNER JOIN grade on class.grade_id = grade.id WHERE student.id = $id LIMIT 1;";
    $result = $con->query($query);
    $child = new stdClass();
    if($result){
        $result = $result->fetch_object();
        $child->id = $id;
        $child->name = $result->name;
        $child->surname = $result->surname;
        $child->grade = $result->grade;
        $child->class = $result->class;
        return $child;
    }

    return null;

}