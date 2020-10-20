<?php
/**
 * Created by IntelliJ IDEA.
 * User: jonospc
 * Date: 2020/06/14
 * Time: 21:56
 */
require_once "../Repository/pdf-repo.php";
class PDF extends Request_Handler
{

    public function __construct()
    {


    }
    public function test(){
        echo "TESST HIT <br>";
    }

    public function Variable($obj)
    {
        unset($_FILES);
        $request = $obj->request;
        if (!isset($_FILES))
        {
            $this->$request($obj->payload);

        }

        else
            $this->$request();
    }

    public function handle_Request()
    {

        Request_Handler::format_Request();
        $req = Request_Handler::get_req();
        $this->Variable($req);

    }

    public function generateFloorPDF($obj)
    {

        $floorId = $obj->floorId;
        generateQRCodes($floorId);
        generatePDF($floorId);
    }
}// // // // // // 