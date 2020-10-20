<?php

/**

 * Created by IntelliJ IDEA.

 * User: Jonathan

 * Date: 6/15/2020

 * Time: 10:15 PM

 */

require_once "db.php";



function getAllUsers(){



    $db = db::getConnection();

    $query = "SELECT * FROM user_profile";

    $result = $db->query($query);

    $users = [];

    while( $record = $result->fetch_object())

    {

        $user = new stdClass();

        $user->userId = $record->id;

        $user->typeId= $record->user_type_id;

        $user->nationalityId = $record->nationality_id;

        $user->countryId= $record->country_id;

        $user->email = $record->email;

        $user->name = $record->name;

        $user->surname= $record->surname;

        $user->contact = $record->contact;

        $user->picture= $record->picture;

        $user->isVerified = $record->isVerified;

        $user->createdby = $record->created_by;

        $user->updatedby = $record->updated_by;

        $user->createddate = $record->created_date;

        $user->updateddate = $record->updated_date;

        $user->deleted = $record->deleted;

        $users[] = $user;

    }



    return $users;

}



function emailExists($user){



    //CHECK IF ACCOUNT WITH EMAIL EXISTS

    $db = db::getConnection();
    $details = strtolower($user->email);
    $query = "SELECT * FROM user_profile WHERE email = '$details';";

    $result = $db->query($query);



    if($result->num_rows > 0)

        return True;

    else

        return False;



}



function insertCredentials($user){



    $salt = md5(date("Y-m-d H:i:s"));

    $password = md5($user->password . $salt);



    $db = db::getConnection();

    $query = "INSERT INTO login_credentials (user_id, password_hash,password_salt,updated_by) VALUES( $user->id , '$password' ,'$salt' , $user->id)";

    $result = $db->query($query);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user->id, 2, 27)";
    $result3 = $db->query($query3);



    if($db->affected_rows > 0){

        // 9  is Applicant Role

        $query = "INSERT INTO user_role (user_id, role_id, createdBy, updatedBy) VALUES ($user->id, 9, $user->id, $user->id);";

        $db->query($query);

        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user->id, 2, 54)";
        $result3 = $db->query($query3);

        return True;

    }





    else{

        $query = "DELETE FROM user_profile WHERE id = $user->id";

        $db->query($query);

        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user->id, 4, 60)";
        $result3 = $db->query($query3);

        return false;

    }





}

function insertUserProfile(&$user){



    $db = db::getConnection();
    $email = strtolower($user->email);

    $query = "INSERT INTO user_profile (user_type_id, country_id, nationality_id, email, user_profile.name, surname, contact) VALUES 

    ( 1 , $user->countryId, $user->nationalityId , '$email' , '$user->name', '$user->surname', '$user->contact')";

    $result = $db->query($query);

    if($db->affected_rows > 0){

        $user->id = $db->insert_id;

        $query = "UPDATE user_profile SET created_by = $user->id, updated_by = $user->id WHERE id = $user->id";

        $db->query($query);

        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($user->id, 2, 60)";
        $result3 = $db->query($query3);

        return True;

    }

    else

        return False;

}



function updateUserAccount($newDetails, $userId){

        $db = db::getConnection();
        $query = "UPDATE user_profile SET country_id = $newDetails->countryId, nationality_id = $newDetails->nationalityId, email = '$newDetails->email', user_profile.name = '$newDetails->name'
                  , surname = '$newDetails->surname', contact = '$newDetails->contact' WHERE id = $userId  ";
                  
        $result = $db->query($query);

        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($userId, 4, 60)";
        $result3 = $db->query($query3);

        if($db->affected_rows < 0)
            return false;
        else
            return true;


}



function getUser($email,$password){



    $db = db::getConnection();

    $query = "SELECT * FROM user_profile INNER JOIN login_credentials on login_credentials.user_id = user_profile.id WHERE email = '$email' LIMIT 1";

    $result = $db->query($query);



    if($result && $result->num_rows>0){

        $userRec =  $result->fetch_object();

        $pass = md5($password . $userRec->password_salt);



        if($pass != $userRec->password_hash)

            return false;



        return $userRec;

    }

    else

        return false;

}



function getOwnUserCard($userId){



    $db = db::getConnection();

    $query = "SELECT * FROM user_profile WHERE id = $userId LIMIT 1";

    $result = $db->query($query);



    if($result){

        $result = $result->fetch_object();

        $cardInfo = new stdClass();

        $cardInfo->id = $userId;

        $cardInfo->userName = $result->name;

        $cardInfo->userSurname = $result->surname;

        $cardInfo->imgUrl = $result->picture;

        return $cardInfo;

    }

    else

        return false;

}

function getOwnDetails($userId){



    $db = db::getConnection();

    $query = "SELECT * FROM user_profile INNER JOIN nationality on nationality.id = user_profile.nationality_id INNER JOIN country on country.id = user_profile.country_id WHERE user_profile.id = $userId";

    $userResult = $db->query($query);



    if(!$userResult)

        return false;



    $userResult = $userResult->fetch_object();

    $user = new stdClass();

    $user->id = $userId;

    $user->name = $userResult->name;

    $user->surname = $userResult->surname;

    $user->email = $userResult->email;

    $user->contact = $userResult->contact;

    $user->imgUrl = $userResult->picture;



    $nationality = new stdClass();

    $nationality->nationalityId = $userResult->nationality_id;

    $nationality->nationality = $userResult->nationality;



    $country = new stdClass();

    $country->id = $userResult->country_id;

    $country->country = $userResult->country;



    $user->country = $country;

    $user->nationality = $nationality;



    //Get the users skills

    $query = "SELECT skill.id, skill.skill FROM user_skill INNER JOIN skill on user_skill.skill_id = skill.id WHERE user_skill.user_id = $userId AND deleted = 0";

    $result = $db->query($query);



    $skills = [];

    if($result){

        while($record = $result->fetch_object()){

            $skill = new stdClass();

            $skill->id = $record->id;

            $skill->skill = $record->skill;

            $skills[] = $skill;

        }

    }

    // Get the users languages

    $query = "SELECT language.id, language.language FROM spoken_language INNER JOIN language on spoken_language.language_id = language.id WHERE spoken_language.user_id = $userId AND deleted = 0";

    $result = $db->query($query);

    $languages = [];

    if($result){

        while($record = $result->fetch_object()){

            $skill = new stdClass();

            $skill->id = $record->id;

            $skill->skill = $record->skill;

            $languages[] = $skill;

        }

    }

    $user->skills = $skills;

    $user->languages = $languages;



    return $user;

}

function changeProfileImage($userId, $img){

    $url = "http://bmwbackend.edumarx.co.za/profiles/";
    $imageName = $userId.pathinfo($img["name"], PATHINFO_EXTENSION);;
    $target = dirname(__DIR__)."/profiles/".$imageName;
    if(move_uploaded_file($img['tmp_name'],$target))
    {
        $db = db::getConnection();
        $query = "UPDATE user_profile SET picture = '".$url.$imageName."' WHERE user_profile.id = $userId";
        $result = $db->query($query);

        $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($userId, 3, 60)";
        $result3 = $db->query($query3);

        if($db->affected_rows > 0)
        {
            $obj = new stdClass();
            $obj->imgUrl = $url.$imageName;
            return $obj;
        }
        else
            return false;
    }
    else
        return false;

}

function changePassword($pass, $userId){

    $salt = md5(date("Y-m-d H:i:s"));
    $saltedPass = $pass.$salt;
    $password = md5($saltedPass);

    $db = db::getConnection();
    $query = "UPDATE login_credentials SET password_salt = '$salt', password_hash = '$password' WHERE user_id = $userId;";
    $result = $db->query($query);

    $query3 = "INSERT INTO audit_log (user_id, operation_id, database_id) VALUES ($userId, 3, 27)";
    $result3 = $db->query($query3);

    if($db->affected_rows > 0)
        return true;
    else
        return false;

}

function getAllEmployees()
{
    $db = db::getConnection();
    $query = "SELECT * FROM user_profile WHERE user_type_id = 2";
    $result = $db->query($query);
    $employees = [];

    while($record = $result->fetch_object())
    {
        $user = new stdClass();
        $user->id = $record->id;
        $user->userName = $record->name;
        $user->userSurname = $record->surname;
        if($record->picture)
        {
            $user->imgUrl = $record->picture;
        }
        else{
            $user->imgUrl = null;
        }
        $employees[] = $user;
    }

    return $employees;
}

function forgotPasswordReset($email){

    $userEmail = strtolower($email);

    $db = db::getConnection();
    $query = "SELECT * FROM user_profile WHERE user_profile.email = '$userEmail';";
    $result = $db->query($query);
    if($result && $result->num_rows<=0)
        return false;

    $user = $result->fetch_object();

    $token = md5($email. random_bytes(16));

    $query = "INSERT INTO forgot_token (user_id, token) VALUES ($user->id, '$token');";
    $db->query($query);


    $subject = "Forgotten Password";



    $msg = "

        Oops unfortunately we cannot fix short term memory but we can give you a reset link.

        

        http://bmwbackend.edumarx.co.za/changePassword.php?token=$token

        

        Regards

        The Futura Team

    ";

    $header = 'From:noreply@futurabmw.com' . "\r\n";

    mail($email,$subject,$msg,$header);

    return true;
}

function getAllApplicantsByJobCard($cardid)
{
    $db = db::getConnection();

    $query = "
        SELECT user_profile.id as 'id',  application.id as 'applicationId', application.card_id as 'cardId', user_profile.name as 'userName', user_profile.surname as 'userSurname', picture as 'imgUrl', status , status.id as 'statusId' ,
		CASE
		WHEN user_type_id = 1 then false
		WHEN user_type_id = 2 then true
		END As 'internal'
		FROM user_profile
        INNER JOIN application on user_profile.id = application.user_id
        INNER JOIN status on status.id = application.status_id 
        WHERE application.card_id= $cardid
    ";
    $result = $db->query($query);

    $applicants = [];

    while($record = $result->fetch_object())
    {
        $applicants[] = $record;
    }

    return $applicants;
}

function getAllExternalApplicants($cardid)
{
    $db = db::getConnection();

    $query = "SELECT application.id AS 'applicationId', user_id as 'id', name as 'userName', surname  as 'userSurname', picture as 'imgUrl' FROM application
            INNER JOIN user_profile on user_profile.id = application.user_id
            WHERE application.card_id = $cardid AND user_profile.user_type_id = 1";
    $result = $db->query($query);

    $applicants = [];

    while($record = $result->fetch_object())
    {
       $applicants[] = $record;
    }

    return $applicants;
}

function getAllInternalApplicants($cardid)
{
    $db = db::getConnection();

    $query = "SELECT application.id AS 'applicationId', user_id as 'id', name as 'userName', surname  as 'userSurname', picture as 'imgUrl' FROM application
            INNER JOIN user_profile on user_profile.id = application.user_id
            WHERE application.card_id = $cardid AND user_profile.user_type_id = 2";
    $result = $db->query($query);

    $applicants = [];

    while($record = $result->fetch_object())
    {
        $applicants[] = $record;
    }

    return $applicants;
}

function getAllInterviewsByInterviewer($user)
{
    $db = db::getConnection();

    $query = "SELECT interview.id user_profile.picture, interview.date, interview.time, interview.booking_id
    FROM interview
    INNER JOIN application ON interview.applicant_id = application.id
    INNER JOIN user_profile ON application.user_id = user_profile.id
    INNER JOIN interviewer_interview ON interview.id = interviewer_interview.interview_id
    WHERE interviewer_interview.user_id = $user";

    $result = $db->query($query);

    $bookingType;
    $tableName;
    $interviews = [];
    

    while($record = $result->fetch_object())
    {
        if($record->booking_id)
        {
            $bookingTypeQuery = "SELECT * FROM group_booking";
            $bookingTypeResult = $db->query($bookingTypeQuery);

            while($record = $bookingTypeResult->fetch_object())
            {
                if($record->booking_id == $booking_id)
                {
                    $bookingType = "group";
                }
            }

            $bookingTypeQuery = "SELECT * FROM individual_booking";
            $bookingTypeResult = $db->query($bookingTypeQuery);

            while($record2 = $bookingTypeResult->fetch_object())
            {
                if($record2->booking_id == $booking_id)
                {
                    $bookingType = "individual";
                }
            }

            if($bookingType == "group")
            {
                $tableIdQuery = "SELECT table_id FROM group_booking WHERE booking_id = $record->booking_id";
                $tableIdResult = $db->query($tableIdQuery);
                $tableRecord = $tableIdResult->fetch_object();
                $tableId = $tableRecord->table_id;

                $nameQuery = "SELECT name FROM tafel WHERE id=$tableId";
                $nameResult = $db->query($nameQuery);
                $nameRecord = $nameResult->fetch_object();
                $tableName = $nameRecord->name;
            } 
            if($bookingType == "group")
            {
                $tableIdQuery = "SELECT table_id FROM individual_booking WHERE booking_id = $record->booking_id";
                $tableIdResult = $db->query($tableIdQuery);
                $tableRecord = $tableIdResult->fetch_object();
                $tableId = $tableRecord->table_id;

                $nameQuery = "SELECT name FROM tafel WHERE id=$tableId";
                $nameResult = $db->query($nameQuery);
                $nameRecord = $nameResult->fetch_object();
                $tableName = $nameRecord->name;
            }

            $interview = new stdClass();            
            $interview->id = $record->id;
            $interview->time = $record->time;
            $interview->date = $record->date;
            $interview->bookingName = $tableName;
            
            if($record->picture)
            {
                $interview->imgUrl = $record->picture;
            }
            else{
                $interview->imgUrl = null;
            }

            $interviews[] = $interview;
        }
        else
        {
            $interview = new stdClass();            
            $interview->id = $record->id;
            $interview->time = $record->time;
            $interview->date = $record->date;
            $interview->bookingName = "none";
            
            if($record->picture)
            {
                $interview->applicantImgUrl = $record->picture;
            }
            else{
                $interview->applicantImgUrl = null;
            }

            $interviews[] = $interview;
        }
    }

    return $interviews;
}

function getAllUpcomingJobCardInterviews($cardId)
{
    $query = "SELECT interview.id user_profile.name, user_profile.surname, interview.date, interview.time
    FROM interview
    INNER JOIN application ON interview.applicant_id = application.id
    INNER JOIN user_profile ON application.user_id = user_profile.id
    WHERE interview.card_id = $cardId";

    $result = $db->query($query);
    $interviews = [];

    while($record = $result->fetch_object())
    {
        if($record->date < date('Y-m-d'))
        {
            $interview = new stdClass();            
            $interview->id = $record->id;
            $interview->time = $record->time;
            $interview->date = $record->date;
            $interview->applicantName = $record->name;
            $interview->applicantSurname = $record->surname;

            $interviews[] = $interview;
        }
    }

    return $interviews;
}

function getAllPassedJobCardInterviews($cardId)
{
    $query = "SELECT interview.id user_profile.name, user_profile.surname, interview.date, interview.time
    FROM interview
    INNER JOIN application ON interview.applicant_id = application.id
    INNER JOIN user_profile ON application.user_id = user_profile.id
    WHERE interview.card_id = $cardId";

    $result = $db->query($query);
    $interviews = [];
    $overallRating=0;
    $interviewerCount = 0;
    $averageRating=0;

    while($record = $result->fetch_object())
    {
        if($record->date > date('Y-m-d'))
        {
            $interviewersQuery = "SELECT rating FROM interviewer_interview WHERE interview_id = $record->id";
            $interviewResult = $db->query($interviewersQuery);

            while($record = $interviewRes->fetch_object())
            {
                $overallRating = $overallRating + $record->rating;
                $interviewerCount = $interviewerCount + 1;
            }

            $interviewerCount = $interviewerCount * 10;

            $averageRating = $overallRating/$interviewerCount*10;

            $interview = new stdClass();            
            $interview->id = $record->id;
            $interview->score = $averageRating;
            $interview->date = $record->date;
            $interview->applicantName = $record->name;
            $interview->applicantSurname = $record->surname;

            $interviews[] = $interview;
        }
    }

    return $interviews;
}

    function getUserAccount($interviewId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT user_profile.id as 'userId', user_profile.name, user_profile.surname, user_profile.picture,user_profile.contact, user_profile.email,nationality.nationality, country.country, user_job_profile.job_id as 'jobId', user_job_profile.location_id as 'locationId', 
                                    user_job_profile.schedule_id as 'scheduleId', user_job_profile.department_id as 'departmentId', user_job_profile.salary, user_job_profile.contract, user_type.id as 'userType', user_job_profile.startDate as 'startDate', user_job_profile.endDate as 'endDate', department_id as 'departmentId'
                                    FROM interview 
                                    INNER JOIN application on application.id = interview.application_id
                                    INNER JOIN user_profile on user_profile.id = application.user_id
                                    INNER JOIN user_type on user_type.id = user_profile.user_type_id
                                    INNER JOIN nationality on nationality.id = user_profile.nationality_id
                                    INNER JOIN country on country.id = user_profile.country_id
                                    LEFT JOIN user_job_profile on user_profile.id = user_job_profile.user_id
                                    WHERE interview.id = ?;");
        $stmt->bind_param('i',$interviewId);
        $userDetails = new stdClass();

        $stmt->execute();
        $result = $stmt->get_result();
        $userDetails = $result->fetch_object();
        $roles = [];

        $stmt->prepare("SELECT role.id , role.name FROM interview 
                                INNER JOIN application on application.id = interview.application_id
                                INNER JOIN user_role on application.user_id = user_role.user_id
                                INNER JOIN role on user_role.role_id = role.id
                                WHERE interview.id = ? ;");

        $stmt->bind_param('i',$interviewId);
        $stmt->execute();

        $result = $stmt->get_result();

        while($result AND $record = $result->fetch_object())
        {
            $roles[] = $record;
        }

        $userDetails->roles = $roles;

        return $userDetails;

    }
    function getUserRoles($userId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT role_id as 'roleId', name as 'roleName' FROM user_role 
                                    INNER JOIN role on user_role.role_id = role.id
                                    WHERE user_role.user_id = ?");
        $stmt->bind_param('i',$userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $roles = [];
        while($result AND $record = $result->fetch_object())
        {
            $roles[] = $record;
        }

        return $roles;
    }

    function updateEmployeeAccount($employeeId, $userId, $jobId,$locationId, $scheduleId, $departmentId, $salary, $startDate, $endDate , $email)
    {
        //var_dump($employeeId, $userId, $jobId,$locationId, $scheduleId, $departmentId, $salary, $startDate, $endDate);
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT * FROM user_job_profile 
                                    WHERE user_id = ? ");
        $stmt->bind_param('i',$employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows < 0)
        {
            $stmt->prepare("INSERT INTO user_job_profile (user_id , job_id, location_id, schedule_id, department_id, salary, startDate, endDate, createdBy, updatedBy)
                                  VALUES (?,?,?,?,?,?,?,?,?,?);");
            $stmt->bind_param("iiiiiissii",$employeeId,$jobId,$locationId,$scheduleId,$departmentId,$salary,$startDate, $endDate,$userId , $userId);
            $stmt->execute();
           if($db->affected_rows <= 0)
               return "NO JOB PROFILE EXISTED, INSERTING FAILED";

            return true;
        }
        else
        {

            $stmt->prepare("UPDATE user_job_profile SET job_id = ? , location_id = ?, schedule_id = ?, department_id = ? , salary = ? , startDate = ? , endDate = ? , createdBy = ?, updatedBy = ? WHERE user_id = ?");
            $stmt->bind_param("iiiiissiii",$jobId,$locationId,$scheduleId,$departmentId, $salary ,$startDate, $endDate, $userId, $userId,$employeeId );
            $stmt->execute();

            if($db->affected_rows <= 0)
                return "UPDATING FAILED";

            $stmt->prepare("UPDATE user_profile SET email = ? WHERE id = ?");
            $stmt->bind_param("si",$email ,$employeeId);
            $stmt->execute();

            if($db->affected_rows <= 0)
                return "UPDATING EMAIL FAILED";

            return true;
        }

    }

    function createEmployee($employeeId, $userId, $jobId,$locationId, $scheduleId, $departmentId, $salary, $startDate, $endDate , $email)
    {
        //CHECK IF USER HAS JOB PROFILE

        $db = db::getConnection();
        $stmt = $db->prepare("SELECT * FROM user_job_profile 
                                    WHERE user_id = ? ");
        $stmt->bind_param('i',$employeeId);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows <= 0)
        {

            $stmt->prepare("INSERT INTO user_job_profile (user_id , job_id, location_id, schedule_id, department_id, salary, startDate, endDate, createdBy, updatedBy)
                                  VALUES (?,?,?,?,?,?,?,?,?,?);");
            $stmt->bind_param("iiiiiissii",$employeeId,$jobId,$locationId,$scheduleId,$departmentId,$salary,$startDate, $endDate,$userId , $userId);
            $stmt->execute();
            if($db->affected_rows <= 0)
                return false;

            return true;
        }
        else
        {

            $stmt->prepare("UPDATE user_job_profile SET job_id = ? , location_id = ?, schedule_id = ?, department_id = ? , salary = ? , startDate = ? , endDate = ? , createdBy = ?, updatedBy = ? WHERE user_id = ?");
            $stmt->bind_param("iiiiissiii",$jobId,$locationId,$scheduleId,$departmentId, $salary ,$startDate, $endDate, $userId, $userId,$employeeId );
            $stmt->execute();

            if($db->affected_rows <= 0)
                return false;

            $stmt->prepare("UPDATE user_profile SET email = ?, user_type_id = 2 WHERE id = ?");
            $stmt->bind_param("si",$email ,$employeeId);
            $stmt->execute();

            if($db->affected_rows <= 0)
                return false;

            return true;
        }

    }


    function uploadContract()
    {


    }

    function getAllUsersForSearch()
    {
        $db = db::getConnection();

        $query = "SELECT a.id, a.name AS 'userName', a.surname,b.id as 'typeId', b.type,c.id as 'departmentId', c.name AS 'departmentName'
        FROM user_profile a
        INNER JOIN user_type b
        ON a.user_type_id=b.id
        LEFT OUTER JOIN user_job_profile d
        ON d.user_id=a.id
        LEFT OUTER JOIN department c
        ON c.id=d.department_id;";

        $result = $db->query($query);

        $users = [];
        while( $record = $result->fetch_object())
        {
            $user = new stdClass();
            $user->id = $record->id;
            $user->typeId = $record->typeId;
            $user->departmentId = $record->departmentId;
            $user->name = $record->userName;
            $user->surname= $record->surname;
            $user->type = $record->type;
            $user->departmentName= $record->departmentName;
            $users[] = $user;
    }
    return $users;   
    }

    function getHomeCards($userId){

        $date = date("Y-m-d");
        $retObj = new stdClass();
        $db = db::getConnection();
        $stmt = $db->prepare("
        SELECT tafel.name FROM edumarxc_bmwdatabase.individual_booking
        INNER JOIN user_booking on individual_booking.booking_id = user_booking.id
        INNER JOIN tafel on individual_booking.table_id = tafel.id
        INNER JOIN date on individual_booking.date_id = date.id
        WHERE user_id = ? AND date.date = ?");
        $stmt->bind_param('is',$userId,$date);
        $stmt->execute();
        $bookingObj = new stdClass();
        $bookingObj->tableName = $stmt->get_result()->fetch_object()->name;

        $retObj->table = $bookingObj;

        $stmt = $db->prepare("
        SELECT count(*) as 'numCards'
        FROM job_card_user
        RIGHT JOIN job_card on job_card_user.card_id = job_card.id
        INNER JOIN job_request on job_card.jrequest_id = job_request.id
        INNER JOIN justification on job_request.justification_id = justification.id
        INNER JOIN job on job_request.job_id = job.id
        INNER JOIN user_profile on job_request.CreatedBy = user_profile.id
        WHERE job_card_user.role_id = 4 AND job_card_user.user_id = ? AND job_card.card_name is NULL");
        $stmt->bind_param("i",$userId);
        $stmt->execute();

        $assignedCards = new stdClass();
        $assignedCards->total = $stmt->get_result()->fetch_object()->numCards;
        $retObj->assignedCards = $assignedCards;

        $stmt = $db->prepare("
                            SELECT count(*) as 'totalRequests'
                            FROM job_request INNER JOIN user_profile on user_profile.id = job_request.createdBy INNER JOIN justification on justification.id = job_request.justification_id INNER JOIN
                            job on job.id = job_request.job_id LEFT OUTER JOIN job_card on job_card.jrequest_id = job_request.id WHERE job_card.id IS NULL;
                            ");
        $stmt->bind_param("i",$userId);
        $stmt->execute();

        $jobRequests = new stdClass();
        $jobRequests->total = $stmt->get_result()->fetch_object()->totalRequests;
        $retObj->jobRequests = $jobRequests;

        $stmt = $db->prepare("
                            SELECT count(*) as 'numApprovals' FROM job_card_approver INNER JOIN job_card on job_card_approver.card_id = job_card.id WHERE user_id = ? AND job_card_approver.approved is NULL
                            ");
        $stmt->bind_param("i",$userId);
        $stmt->execute();

        $jobApprovals = new stdClass();
        $jobApprovals->total = $stmt->get_result()->fetch_object()->numApprovals;
        $retObj->jobApprovals = $jobApprovals;

        $stmt = $db->prepare("
                                  SELECT count(*) as 'interviews' FROM interviewer_interview 
                                    INNER JOIN interview on interview.id = interviewer_interview.interview_id 
                                    INNER JOIN application on interview.application_id = application.id
                                    INNER JOIN user_profile on application.user_id = user_profile.id
                                    WHERE interview.date <= now() 
                                    AND interviewer_interview.user_id = ? 
                                    AND (interviewer_interview.comment is null OR interviewer_interview.rating is null)  
                            ");
        $stmt->bind_param("i",$userId);
        $stmt->execute();

        $interviews = new stdClass();
        $interviews->total = $stmt->get_result()->fetch_object()->interviews;
        $retObj->interviews = $interviews;


        return $retObj;
    }

    function getUserSkillsAndLangs($userId)
    {

        $db = db::getConnection();
        $db->set_charset("utf8");
        $retObj = new stdClass();
        $stmt = $db->prepare("
                                SELECT skill_id as 'id', skill FROM edumarxc_bmwdatabase.user_skill
                                INNER JOIN skill on user_skill.skill_id = skill.id
                                WHERE user_id = ?;");
        $stmt->bind_param('i',$userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $skills = [];
        while($result && $record = $result->fetch_object())
            $skills[] = $record;


        $stmt = $db->prepare("
                               SELECT language_id as 'id', language FROM edumarxc_bmwdatabase.spoken_language
                                INNER JOIN language on spoken_language.language_id = language.id
                                WHERE user_id = ?;");
        $stmt->bind_param('i',$userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $languages = [];
        while($result && $record = $result->fetch_object())
            $languages[] = $record;

        $retObj->skills = $skills;
        $retObj->languages = $languages;

        return $retObj;

    }
    function removeSkill($userId, $skillId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("DELETE FROM user_skill
                                    WHERE user_id = ? AND skill_id = ?;");
        $stmt->bind_param("ii",$userId,$skillId);
        if(!$stmt->execute())
            return false;

        return true;
    }

    function removeLanguage($userId, $languageId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("DELETE FROM spoken_language
                                    WHERE user_id = ? AND language_id = ?;");
        $stmt->bind_param("ii",$userId,$languageId);
        if(!$stmt->execute())
            return false;

        return true;
    }

    function addLanguage($userId, $languageId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("INSERT INTO spoken_language (user_id,language_id) VALUES(?,?);");
        $stmt->bind_param("ii",$userId,$languageId);
        if(!$stmt->execute())
            return false;

        return true;
    }

    function addSkill($userId, $skillId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("INSERT INTO user_skill (user_id, skill_id) VALUES (?, ?);");
        $stmt->bind_param("ii",$userId,$skillId);
        if(!$stmt->execute())
            return false;

        return true;
    }

    function unassignedSkills($userId)
    {
        $db = db::getConnection();
        $stmt = $db->prepare("SELECT id, skill from skill
                                    WHERE id NOT IN (SELECT skill_id FROM user_skill WHERE user_id = ?);");
        $stmt->bind_param("i",$userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $skills = [];

        while($result AND $record = $result->fetch_object())
            $skills[] = $record;

        return $skills;
    }

    function unassignedLanguages($userId)
    {
        $db = db::getConnection();
        $db->set_charset("utf8");
        $stmt = $db->prepare("SELECT id, language from language
                                    WHERE id NOT IN (SELECT language_id FROM spoken_language WHERE user_id = ?);");
        $stmt->bind_param("i",$userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $languages = [];

        while($result AND $record = $result->fetch_object())
            $languages[] = $record;

        return $languages;
    }
