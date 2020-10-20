<?php
require_once("router.php");

$router = new Router();
$router->add("Answer","Answer");
$router->add("Application","Application");
$router->add("ApplicationList","ApplicationList");
$router->add("Audit","Audit");
$router->add("Building","Building");
$router->add("Country","Country");
$router->add("DatabaseTable","DatabaseTable");
$router->add("Date","Date");
$router->add("DateSlot","DateSlot");
$router->add("Department","Department");
$router->add("Division","Division");
$router->add("Floor","Floor");
$router->add("GroupTableDateSlotBooking","GroupTableDateSlotBooking");
$router->add("IndividualTableDateBooking","IndividualTableDateBooking");
$router->add("InterviewerInterview","InterviewerInterview");
$router->add("Interview","Interview");
$router->add("Job","Job");
$router->add("JobCard","JobCard");
$router->add("JobCardApprover","JobCardApprover");
$router->add("JobCardStage","JobCardStage");
$router->add("JobCardUser","JobCardUser");
$router->add("JobListing","JobListing");
$router->add("JobRequest","JobRequest");
$router->add("JobSurvey","JobSurvey");
$router->add("JobTest","JobTest");
$router->add("Justification","Justification");
$router->add("Language","Language");
$router->add("Location","Location");
$router->add("LoginCredential","LoginCredential");
$router->add("LongQuestion","LongQuestion");
$router->add("Nationality","Nationality");
$router->add("Notification","Notification");
$router->add("Operation","Operation");
$router->add("OperationAuthorisation","OperationAuthorisation");
$router->add("PasswordReset","PasswordReset");
$router->add("Question","Question");
$router->add("QuestionType","QuestionType");
$router->add("Requirement","Requirement");
$router->add("RequisitionApproval","RequisitionApproval");
$router->add("Role","Role");
$router->add("Schedule","Schedule");
$router->add("Skill","Skill");
$router->add("Slot","Slot");
$router->add("SpokenLanguage","SpokenLanguage");
$router->add("Stage","Stage");
$router->add("Status","Status");
$router->add("Tafel","Tafel");
$router->add("TableDate","TableDate");
$router->add("TableDateSlot","TableDateSlot");
$router->add("TableType","TableType");
$router->add("Team","Team");
$router->add("TeamMember","TeamMember");
$router->add("Test","Test");
$router->add("User","User");
$router->add("UserBooking","UserBooking");
$router->add("UserJobProfile","UserJobProfile");
$router->add("UserRole","UserRole");
$router->add("UserSkill","UserSkill");
$router->add("UserType","UserType");
$router->add("Verify","Verify");
$router->add("View","View");
$router->add("ViewAuthorisation","ViewAuthorisation");

$router->route();