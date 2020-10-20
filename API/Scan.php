<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jonathan
 * Date: 6/16/2020
 * Time: 12:52 PM
 */
require_once "../Repository/scan-repo.php";

class Scan extends Request_Handler
{

    public function __construct()
    {

    }

    public function Variable($obj)
    {

        $request = $obj->request;
        if (!method_exists($this, $request)) {
            Request_Handler::error("Endpoint Error", "The Endpoint you tried to access does not exist");
            return;
        }
        if (count($_FILES) <= 0 && property_exists($obj, "payload"))
            $this->$request($obj->payload);
        else
            $this->$request();
    }

    public function handle_Request()
    {

        Request_Handler::format_Request();
        $req = Request_Handler::get_req();
        $this->Variable($req);

    }

    public function test()
    {
        echo "TESST HIT <br>";
    }

    public function scanStudent($obj){

        //CHECK DOES STUDENT EXIST
//        $student = studentExist($obj->id,$obj->name,$obj->surname);
        $student = studentExistById($obj->id);
        if(!(array)$student)
        {
            echo Request_Handler::error("Scan Error","The student does not exist in the database");
            return;
        }
        else
        {
            $res = enterScan($student->id,$student->class_id,$obj->temperature);
            if($res == 1)
                echo Request_Handler::success("Student Successfully Scanned");
            else
               echo Request_Handler::error("Scan Error","The student was not successfully scanned");
        }
        //ADD TO SCAN
    }
    public function checkScan( $obj){

        $result = checkScanned($obj);
        if($result)
            echo json_encode($result);
        else
            echo $this->error("Scan Record","The students temperature was not scanned yet please take their temperature");

    }

    public function wellnessUpload($obj){

        $result = uploadWellness($obj->student,$obj->wellness);
        if ($result > 0)
            echo $this->success("Successfully Added wellness to scans");
        else
            echo $this->error("Wellness Scan","Adding the wellness scan failed");
    }

    public function getStudentById($id){
        $child = getStudent($id->id);
        if($child)
            echo json_encode($child);
        else
            echo $this->error("Get Child","No student exists with the ID provided");
    }
}