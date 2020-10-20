<?php

require_once("User.php");
require_once ("Answer.php");
require_once ("Application.php");
require_once ("ApplicationList.php");
require_once ("Audit.php");
require_once ("Building.php");
require_once ("Country.php");
require_once ("DatabaseTable.php");
require_once ("Date.php");
require_once ("DateSlot.php");
require_once ("Department.php");
require_once ("Division.php");
require_once ("Floor.php");
require_once ("GroupTableDateSlotBooking.php");
require_once ("IndividualTableDateBooking.php");
require_once ("InterviewerInterview.php");
require_once ("Interview.php");
require_once ("Job.php");
require_once ("JobCard.php");
require_once ("JobCardApprover.php");
require_once ("JobCardStage.php");
require_once ("JobCardUser.php");
require_once ("JobListing.php");
require_once ("JobRequest.php");
require_once ("JobSurvey.php");
require_once ("JobTest.php");
require_once ("Justification.php");
require_once ("Language.php");
require_once ("Location.php");
require_once ("LoginCredential.php");
require_once ("LongQuestion.php");
require_once ("Nationality.php");
require_once ("Notification.php");
require_once ("Operation.php");
require_once ("OperationAuthorisation.php");
require_once ("PasswordReset.php");
require_once ("Question.php");
require_once ("QuestionType.php");
require_once ("Requirement.php");
require_once ("RequisitionApproval.php");
require_once ("Role.php");
require_once ("Schedule.php");
require_once ("Skill.php");
require_once ("Slot.php");
 require_once ("SpokenLanguage.php");
 require_once ("Stage.php");
 require_once ("Status.php");
require_once ("Tafel.php");
 require_once ("TableDate.php");
 require_once ("TableDateSlot.php");
 require_once ("TableType.php");
require_once ("Team.php");
require_once ("TeamMember.php");
 require_once ("Test.php");
 require_once ("User.php");
 require_once ("UserBooking.php");
 require_once ("UserJobProfile.php");
require_once ("UserRole.php");
 require_once ("UserSkill.php");
 require_once ("UserType.php");
 require_once ("Verify.php");
 require_once ("View.php");
 require_once ("ViewAuthorisation.php");


class Router
{
    private $_routes = [];
    private $_routeHandler = [];

    public function add($url, $className){

        $this->_routes[] = $url;
        $this->_routeHandler[] = $className;
    }

    public function route(){

        if(isset($_GET["url"])) {
            $resource = $_GET["url"];
            //Unset the URL for future processing
            unset($_GET['url']);
            if (isset($resource)) {
                foreach ($this->_routes as $key => $value) {
                    if (preg_match("~\b$value\b~", $resource)) {
                        $controllerName = $this->_routeHandler[$key];
                        $controller = new $controllerName();
                        $controller->handle_Request();
                    }
                }
            }
        }
        else{
            echo "No Route Found";
            echo "<br>";
        }
    }
}