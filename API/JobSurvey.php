<?php
function getAllJobSurveys(){

    $db = db::getConnection();
    $query = "SELECT * FROM job_survey";
    $result = $db->query($query);
    $jobsurveys = [];
    while( $record = $result->fetch_object())
    {
        $jobsurvey = new stdClass();
        $jobsurvey->surveyid = $record->id;
        $jobsurvey->cardid = $record->card_id;
        //$jobsurvey->description = $record->Description;
        $jobsurvey->createdby = $record->createdBy;
        $jobsurvey->updatedby = $record->updatedBy;
        $jobsurvey->createddate = $record->createdDate;
        $jobsurvey->updateddate = $record->updatedDate;
        //$jobsurvey->deleted = $record->Deleted;
        $jobsurveys[] = $jobsurvey;
    }

    return $jobsurveys;
}