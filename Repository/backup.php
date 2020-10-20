<?php
/**
 * Created by IntelliJ IDEA.
 * User: Jonathan
 * Date: 10/12/2020
 * Time: 12:01 PM
 */

    function createBackup()
    {


        //Enter your database information here and the name of the backup file
        $mysqlDatabaseName ='edumarxc_bmwdatabase';
        $mysqlUserName ='edumarxc_bmw';
        $mysqlPassword ='bmw_INF370';
        $mysqlHostName ='dbxxx.hosting-data.io';
        $mysqlExportPath ='backup.sql';

        //Please do not change the following points
        //Export of the database and output of the status
        $command='mysqldump --opt -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' > ' .$mysqlExportPath;
        exec($command,$output=array(),$worked);
        switch($worked){
            case 0:
                return true;
                break;
            case 1:
                return false;
                break;
            case 2:
                return false;
                break;
        }



    }

    function restoreBackup()
    {
        //Enter your database information here and the name of the backup file
        $mysqlDatabaseName ='edumarxc_bmwdatabase';
        $mysqlUserName ='edumarxc_bmw';
        $mysqlPassword ='bmw_INF370';
        $mysqlHostName ='dbxxx.hosting-data.io';
        $mysqlImportFilename ='backup.sql';

        //Please do not change the following points
        //Import of the database and output of the status
        $command='mysql -u '.$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' < ' .$mysqlImportFilename;
        exec($command,$output=array(),$worked);
        switch($worked){
        case 0:
            return true;
        break;
        case 1:
            return false;
        break;
        }

    }
