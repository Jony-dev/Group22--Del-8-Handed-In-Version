<?php

/**
 * Created by IntelliJ IDEA.
 * User: Jonathan
 * Date: 6/14/2020
 * Time: 9:02 AM
 */

require_once "db.php";

class db
{
    static function getConnection()
    {
//        $serverName = 'localhost';
//        $password = 'L0gM31n12!';
//        $userName = 'edumarxc_root';
//        $database = 'edumarxc_waterkloof';
        $serverName = 'localhost';
        $password = 'bmw_INF370';
        $userName = 'edumarxc_bmw';
        $database = 'edumarxc_bmwdatabase';

        $connection = new mysqli($serverName, $userName, $password, $database) or die('Connection Failed');
        return $connection;
    }
}