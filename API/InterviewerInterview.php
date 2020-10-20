<?php
function getAllInterviewers(){

    $db = db::getConnection();
    $query = "SELECT * FROM interviewer_interview";
    $result = $db->query($query);
    $interviewers = [];
    while( $record = $result->fetch_object())
    {
        $interviewer = new stdClass();
        $interviewer->userid = $record->user_id;
        $interviewer->interviewid= $record->interview_id;
        $interviewer->comment= $record->comment;
        $interviewer->rating = $record->rating;
        $interviewer->createdby = $record->createdBy;
        $interviewer->updatedby = $record->updatedBy;
        $interviewer->createddate = $record->createdDate;
        $interviewer->updateddate = $record->updatedDate;
        $interviewer->deleted = $record->deleted;
        $interviewers[] = $interviewer;
    }

    return $interviewers;
}