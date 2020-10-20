<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jonathan
 * Date: 4/27/2020
 * Time: 4:03 PM
 */
abstract class Request_Handler{

    private $req_obj;
    private $user_token;
    private $valid_token;

    public function __construct()
    {

    }

    abstract public function handle_Request();

    public function format_Request(){

        $this->setToken();
        $obj = new stdClass();

        if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

            die();
        }
        if (isset($_GET['request'])){
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            $get = $_GET;
            if(is_array($get)){
                $payload = new stdClass();
                while ($current = current($get)){
                    $key = key($get);
                    if($key != "request")
                        $payload->$key = $current;
                    next($get);
                }
                $obj->request = $_GET['request'];
                $obj->payload = $payload;
            }

        }
        elseif (isset($_POST['request'])){
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            $post = $_POST;

            if(is_array($post)){
              $payload = new stdClass();
              while ($current = current($post)){
                  $key = key($post);
                  if($key != "request")
                  $payload->$key = $current;
                  next($post);
              }
              $obj->request = $_POST['request'];
              $obj->payload = $payload;
            }
        }
        else{
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            $obj = file_get_contents("php://input");
            $obj = json_decode($obj);

            if($obj){
                if(!property_exists($obj,"request")){
                    $obj = new stdClass();
                    $obj->request = "test";
                }
                else
                {
                }
            }

        }

        $this->req_obj = $obj;

    }

    public function get_req(){

        return $this->req_obj;
    }

    public function error($errorTitle, $errorMsg){

        header("HTTP/1.0 400 Bad Request");
        $error = new stdClass();
        $error->Title = $errorTitle;
        $error->message = $errorMsg;
        return json_encode($error);
    }
    public function success($successTitle,$msg){

        $response = new stdClass();
        $response->Title = $successTitle;
        $response->message = $msg;
        return json_encode($response);
    }

    public function setToken(){
        $headers = getallheaders();

        if(isset($headers['Authorization'])){

            if(substr($headers['Authorization'],0, 7) != "Bearer ")
                $this->user_token = null;
            else
            {
                $this->user_token = substr($headers['Authorization'],7);
                try
                {
                    $decoded = JWT::decode($this->user_token,"INF370",array('HS256'));
                    $this->user_token = $decoded;
                    $this->valid_token = true;
                }
                catch (Exception $error){
                    $this->user_token = null;
                    $this->valid_token = false;
                }
            }

        }
        else{
            $this->user_token = null;
            $this->valid_token = false;
        }
    }

    public function getUserToken(){
        return $this->user_token;
    }

    public function isTokenValid(){
        return $this->valid_token;
    }

    function validateToken(){
        if($this->user_token == null){
            $this->valid_token = false;
        }
        else{
            $currenTime = new DateTime();
            $tokenTime = new DateTime($this->user_token->endSession);
            $diff = $tokenTime->diff($currenTime);

            if($diff->h <= 0 && $diff->i <= 0){
                $this->valid_token = false;
                return false;
            }

            else{
                $this->valid_token = true;
                return true;
            }
        }


    }


}


//$class = new stdClass();
//
//$class->userId = 22652;
//$class->roles = array(0 => ['id' => 0,'role' => "Manager"], 1 => ['id' => 1,'role' => "Employee"]);
//$class->views = array(0 => ['id' => 0,'view' => "Report"], 1 => ['id' => 1,'role' => "Booking"]);
//$class->endSession = strtotime("+30 minutes");
//
//$res = JWT::encode($class,"INF370");
//echo $res;
//
////$res = "xxx".$res;
//try
//{
//    $decoded = JWT::decode($res,"INF370",array('HS256'));
//}
//catch (Exception $error){
//    echo "Invalid Token";
//}