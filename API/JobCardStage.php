<?php
function getAllJobCardStages(){

    $db = db::getConnection();
    $query = "SELECT * FROM job_card_stage";
    $result = $db->query($query);
    $jobcardstages = [];
    while( $record = $result->fetch_object())
    {
        $jobcardstage = new stdClass();
        $jobcardstage->stageid = $record->stage_id;
        $jobcardstage->cardid = $record->card_id;
        $jobcardstage->current= $record->current;
        $jobcardstage->startdate= $record->startDate;
        $jobcardstage->enddate= $record->endDate;
        $jobcardstage->deleted = $record->deleted;
        $jobcardstages[] = $jobcardstage;
    }

    return $jobcardstages;
}