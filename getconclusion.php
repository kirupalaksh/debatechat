<?php
require_once('../../config.php');
global $CFG,$DB;

$userid = $_GET['student_id'];
$actid = $_GET['activity_id'];
$context = $DB->get_record('course_modules', array('id'=>$actid)); 
$debateid = $context->instance;

$debate_user_map = $DB->get_record('debatechat_user_mapping', array('debatechat' => $debateid,'userid'=> $userid));
$usermapped = $debate_user_map->id;
$gradearray = array('gradestatus'=> 'Not Graded');
$grades = $DB->get_record('debatechat_grades', array('debatechat' => $debateid,'userid'=>$userid)); 
if(!empty($grades) && ($grades->userid == $userid))  {
$gradesval = $grades->grade; 
$gradefinal =  number_format($gradesval, 2) ;
$gradearray =  array('grade'=> $gradefinal,'gradestatus'=> 'Graded');
}	
	
/* if(!empty($usermapped)){	
	echo $debate_user_map->concluded;
}else { echo 'No Record'; } */


$student_status[] = array(
          'concluded' => $debate_user_map->concluded, 
		  'grade' => $gradearray		
      );

$studentArray = json_encode($student_status);
echo $studentArray;
?>