<?php

/**

 * Created by IntelliJ IDEA.

 * User: Jonathan

 * Date: 4/25/2020

 * Time: 5:01 PM

 */

require_once "Request_Handler.php";

require_once "../Repository/UserAccount.php";

require_once "../Repository/View.php";

require_once "../Repository/Verify.php";

class User extends Request_Handler {



    public function __construct(){



    }



    public function Variable($obj){

        $request = $obj->request;

        if(property_exists($obj,'payload'))

            $this->$request($obj->payload);

        else

            $this->$request();

    }



    public function test(){

//        $this->validateToken();

//        if($this->isTokenValid()){



            $ret = new stdClass();

            $ret->Test = "HIT TEST";

//            $ret->User = $this->getUserToken();

            echo json_encode($ret);

       // }





    }

    public function handle_Request(){



        Request_Handler::format_Request();

        $req = Request_Handler::get_req();

        $this->Variable($req);



    }



    public function getUsers()

    {

        $users = getAllUsers();

        echo json_encode($users);

    }



    public function createAccount($obj){

        //CHECK IF ACCOUNT WITH EMAIL EXISTS

        $userExists = emailExists($obj);



        if($userExists){

            echo $this->error("Create Account","The user already exists");

            die();

        }





        //IF SUCCESS CREATE USER PROFILE RECORD

        $createdProfile = insertUserProfile($obj);



        //IF SUCCESS CREATE CREDENTIALS RECORD

        if($createdProfile){

            $createdCredentials = insertCredentials($obj);

            if($createdCredentials){

                $token = generateVerify($obj->id,$obj->email,$obj->contact );

                sendToken($token,$obj->email,$obj->name);

                echo $this->success("Create Account","The account was successfully created for the user");

                die();

            }



            else

                echo $this->error("Create Account", "Error in adding the credentials provided");

        }

        else{

            echo $this->error("Create Account", "Error in creating user profile");

        }

    }



    public function forgotPassword($email){

        if(forgotPasswordReset($email->email))
            echo $this->success("Forgot Password","We have sent you a email that allows you to reset your password");
        else
            echo $this->error("Forgot Password","You do not have a valid account or something went wrong");


    }



    public function changePassword($pass){



        if($this->validateToken())

        {

            $result = changePassword($pass->password, $this->getUserToken()->userId);

            if($result)

                echo $this->success("Change Password","Your password was successfully changed");

            else

                echo $this->error("Change Password","An error occurred when changing your password");

        }

        else

            echo $this->error("Change Password","You do not have a valid token to change the password");



    }



    public function updateAccount($account){



        if($this->validateToken())

        {

            $updatedAccount = updateUserAccount($account, $this->getUserToken()->userId);



            if($updatedAccount)

                echo $this->success("Updating Account","You successfully updated you account details");

            else

                echo $this->error("Updating Account","We were unable to update your account try again later");



            die();

        }

        else

            echo $this->error("Editing Account","You do not have a valid token to edit the account");



    }

    public function changeProfileImage(){



        if($this->validateToken())

        {

            if(isset($_FILES['image']))

            {

               $result = changeProfileImage($this->getUserToken()->userId, $_FILES['image']);

               if($result)

                   echo json_encode($result);

               else

                   echo $this->error("Profile Change", "Failed to change the profile");



            }

            else

            {

                echo $this->error("Profile Change","Image was never sent to us");

                die();

            }



        }

        else

        {

            echo $this->error("Profile Change","Invalid Token");

            die();

        }





    }



    public function verifyUser($id){



    }



    public function logIn($user){





        //GET USER DETAILS

        if(!$user){

            echo $this->error("No Email","Invalid email was provided when logging in");

            die();

        }
        $email = strtolower($user->email);
        $retUser = getUser($email, $user->password);



        if($retUser){

            if(!$retUser->isVerified){

                echo $this->error("Verification","The account has not been verified");

                die();

            }

            $views = getUserViews($retUser->user_id);



            $loginCredentials = new stdClass();

            $loginCredentials->userId = $retUser->user_id;

            $loginCredentials->views = $views;

            $date = date("Y-m-d H:i");

            $loginCredentials->endSession = date("Y-m-d H:i:s", strtotime($date . "+360 minutes"));

            $res = JWT::encode($loginCredentials,"INF370");

            echo json_encode($res);

            die();

        }

        else{



            echo $this->error("Log In","No matching email or password");

        }

    }



    public function getWidgetDetails($user){



        $result = getOwnUserCard($user->id);

        if($result){

            echo json_encode($result);

            die();

        }

        else

            echo $this->error("Profile Card","Could not retrieve the users profile card information");

            die();



    }



    public function getUserProfile(){





        if($this->validateToken())

        {

            $user = getOwnDetails($this->getUserToken()->userId);



            if(!$user){

               echo $this->error("Profile Error","Error occurred when trying to get the profile information");

               die();

            }

            else{



                echo json_encode($user);

                die();



            }

        }

        else{

            echo $this->error("Invalid Token","The token used is invalid".$this->getUserToken()->userId."Something");

        }





    }

    public function getEmployees()
    {
        $employees = getAllEmployees();
        echo json_encode($employees);
    }


    public function getPassedJobCardInterviews($payload)
    {
        $passedInterviews = getAllPassedJobCardInterviews($payload->cardid);
        echo json_encode($passedInterviews);
    }

    public function getUpcomingJobCardInterviews($payload)
    {
        $upcomingInterviews = getAllUpcomingJobCardInterviews($payload->cardid);
        echo json_encode($upcomingInterviews);
    }

    public function getInternalApplicants($payload)
    {
        $internalApplicants = getAllInternalApplicants($payload->cardId);
        echo json_encode($internalApplicants);
    }

    public function getApplicantsForCard($payload)
    {
        $internalApplicants = getAllApplicantsByJobCard($payload->cardId);
        echo json_encode($internalApplicants);
    }

    public function getExternalApplicants($payload)
    {
        $externalApplicants = getAllExternalApplicants($payload->cardId);
        echo json_encode($externalApplicants);
    }

    public function getInterviewsByInterviewer()
    {
        $valid = $this->validateToken();
        if($valid)
        {
            $user = $this->getUserToken();
            $result = getAllInterviewsByInterviewer($user->userId);
            echo json_encode($result);
        }
    }

    public function getUserByInterview($payload)
    {

        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }

        if(!$result = getUserAccount($payload->interviewId) AND !is_array($result))
        {
            echo $this->error("Create Account", "Could not retrieve user details properly please try again later");
            die();
        }
        else
        {
            echo json_encode($result);
            die();
        }
    }

    public function getUserRoles($payload)
    {
        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }
        $result = getUserRoles($payload->userId);

        echo json_encode($result);
    }

    public function updateEmployeeAccountDetails($payload)
    {

        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }

        $result = updateEmployeeAccount($payload->userId, $this->getUserToken()->userId, $payload->jobId,$payload->locationId, $payload->scheduleId,$payload->departmentId, $payload->salary, $payload->startDate, $payload->endDate, $payload->email);
        if($result == true)
        {
            echo $this->success("Updating Employee Details", "Successfully updated the employees details");
            die();
        }
        else
        {
            echo $this->error("Updating Employee Details", "Could not updated the employees details".$result);
        }

    }
    public function createAEmployee($payload)
    {
        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }

        $result = createEmployee($payload->userId, $this->getUserToken()->userId, $payload->jobId,$payload->locationId, $payload->scheduleId,$payload->departmentId, $payload->salary, $payload->startDate, $payload->endDate, $payload->email);
        if($result)
        {
            echo $this->success("Create Employee", "Successfully created the employee");
            die();
        }
        else
        {
            echo $this->error("Create Employee", "Could not create the employee");
        }

    }

    public function getUsersForSearch()
    {
        $users = getAllUsersForSearch();
        echo json_encode($users);
    }

    public function getHomeData(){

        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }

        $result = getHomeCards($this->getUserToken()->userId);
        echo json_encode($result);

    }

    public function getSkillsAndLangs()
    {
        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }

        echo json_encode(getUserSkillsAndLangs($this->getUserToken()->userId));
    }
    public function getUnassignedSkills()
    {

        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }
        echo json_encode(unassignedSkills($this->getUserToken()->userId));
    }

    public function getUnassignedLangs()
    {
        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }

        $result = json_encode(unassignedLanguages($this->getUserToken()->userId));

        echo $result;
    }

    public function removeUserSkill($payload)
    {
        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }
        $result = removeSkill($this->getUserToken()->userId,$payload->id);

        if(!$result)
        {
            echo $this->error("Removing Skill","Failed to remove the skill please try again later");
            die();
        }
        echo $this->success("Removing Skill","Successfully removed the skill");
        die();

    }
    public function removeUserLanguage($payload)
    {
        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }
        $result = removeLanguage($this->getUserToken()->userId,$payload->id);

        if(!$result)
        {
            echo $this->error("Removing Language","Failed to remove the language please try again later");
            die();
        }
        echo $this->success("Removing Language","Successfully removed the language");
        die();

    }

    public function addUserSkill($payload)
    {
        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }

        $result = addSkill($this->getUserToken()->userId,$payload->id);

        if(!$result)
        {
            echo $this->error("Adding Skill","Failed to add skill please try again later");
            die();
        }
        echo $this->success("Adding Skill","Successfully added the skill");
        die();
    }
    public function addUserLanguage($payload)
    {
        if(!$this->validateToken()){
            echo $this->error("Invalid Token", "You do not have a valid token");
            die();
        }

        $result = addLanguage($this->getUserToken()->userId,$payload->id);

        if(!$result)
        {
            echo $this->error("Adding Language","Failed to add language please try again later");
            die();
        }
        echo $this->success("Adding Language","Successfully added the language");
        die();
    }

}





