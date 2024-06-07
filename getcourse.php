<?php
require_once('../../config.php');
global $CFG,$DB;

$courseid = $_GET['id'];
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$coursefullname = $course->fullname;	
if(!empty($coursefullname)){	
	echo json_encode($coursefullname);
}
?>