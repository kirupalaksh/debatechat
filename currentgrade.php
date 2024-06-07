<?php

 require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/gradelib.php');

if (isset($_GET['activity_id'])) {
	
	$activity_id = $_GET['activity_id'];
	$std_id = $_GET['student_id'];	
	$contextid = $DB->get_record('context', array('instanceid'=>$activity_id,'contextlevel'=>70)); 
	$context_id = $contextid->id;
	
	$course_module = $DB->get_record('course_modules', array('id'=>$activity_id,'module'=>29)); 
	$debatechatinstance = $course_module->instance;
	$section_id = $course_module->section;
	$courseid = $course_module->course;//die;
	
	$debatechatdetails = $DB->get_record('debatechat', array('id'=>$debatechatinstance)); 
	$topic = $debatechatdetails->title;
	
	$gradecat = $DB->get_record('grade_categories', array('fullname'=>'Debatechat','courseid' =>$courseid)); 
	$grade_catid = $gradecat->id;
	
	$areas = $DB->get_record('grading_areas', array('contextid'=>$context_id)); 
	$areas_id = $areas->id; 
	
	$definitions = $DB->get_record('grading_definitions', array('areaid'=>$areas_id)); 
	$definitionid = $definitions->id; 
	
	$criterions = $DB->get_records_sql("select id,description from {gradingform_rubric_criteria} where definitionid = $definitionid");
    $crits = count($criterions);
	
	
	 $getrubricsql = "select levels.id,method,grc.description AS criteria,criterionid,definition,score,definitionid from {grading_definitions} gf LEFT JOIN {gradingform_rubric_criteria} grc ON gf.id=grc.definitionid LEFT JOIN {gradingform_rubric_levels} levels
    ON levels.criterionid=grc.id where areaid='".$areas_id."' ORDER BY grc.sortorder";
	$rubric_details = $DB->get_records_sql($getrubricsql);
	
	foreach ($rubric_details as $item) {
    // Extract criterion and definition from each item
	$rubdetails = new stdClass();
    $rubdetails->criteria = $item->criteria;
    $rubdetails->criterionid = $item->criterionid;
    $rubdetails->definition = $item->definition;
    $rubdetails->definitionid = $item->definitionid;
    $rubdetails->level_id = $item->id;    
    $rubdetails->score = $item->score; 
	}
	$rubricjson = json_encode($rubric_details,true);  
	
	$url = 'https://api.vf.svhs.co/api/debate-bot?student_id='.$std_id.'&activity_id='.$activity_id;
	$ch = curl_init($url);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_HTTPGET, true); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}

curl_close($ch);

if (isset($error_msg)) {
  echo "cURL Error: " . $error_msg;

} else {
  $responsedata = json_decode($response, true);

 $data = $responsedata['data'];	
}  

//$currentgrade_array = array_pop($data);
$aival = $data['grade'];
$current_grade = $aival['estimated_grade'];
$final_score = $aival['final_score']; 
$predicted_grade = $aival['predicted_grade'];
    for ($i = 0; $i < $crits; $i++) {
		
        $criteria_key = $current_grade[$i]['criteria_key'];
		$criteria_id = $current_grade[$i]['id'];
		$critlevelpoints = $DB->get_record_sql("select id,max(score) AS maxscore from {gradingform_rubric_levels} where criterionid = $criteria_id"); $criteria_max_mark += $critlevelpoints->maxscore;	
		$criteria = $current_grade[$i]['criteria_levels'];    
        $description = $criteria['description'];
        $criteria_mark = $criteria['mark'];
        $criteria_grade = $criteria['grade'];
        $criteria_reason = $criteria['reason'];
		$criteriaarray[] = array('id' =>$criteria_id,'criteria_marks' =>  $criteria_mark);
	}	
$max_marks = $criteria_max_mark / $crits;
$finaljson = array('total_criteria' => $crits,
	'max_mark'=> $max_marks,	
		'criterias' => $criteriaarray			 	
		);

$criteria_reason=  $criteria_reason;
	
//print_r($finaljson);die;
	
	
              if(!empty($activity_id)){
                $rubricgrade = 0;
               /*  for ($i = 0; $i < $finaljson['total_criteria']; $i++) {
                  $criteria = $finaljson['criterias'][$i];    
                  $criteria_id = $criteria['id'];
                  $criteria_mark = $criteria['criteria_marks'];
                  $maxmark = $finaljson['max_mark'];
                  $rubgrade = $criteria_mark / $maxmark * 100; 
                  $rubricgrade += number_format($rubgrade, 2);
                }
                $finalgrade = $rubricgrade / $finaljson['total_criteria'];
                if(!empty($finalgrade)){
                  $finalgrade = $finalgrade;
                } else {
                  $finalgrade = 0;
                } */
	//echo $finalgrade; 
	$finalgrade = $final_score;
				
		$debatechatgrade = new stdClass();
		$debatechatgrade->debatechat = $debatechatinstance;
		$debatechatgrade->userid = $std_id;
		$debatechatgrade->itemnumber = 1;
//		$debatechatgrade->grader = 2;
		$debatechatgrade->grade = $finalgrade;
		$debatechatgrade->timemodified = time(); 
		$debatechatgrade->timecreated = time(); 
		$debatechatgrade->course = $courseid;
		$debatechatgrade->id = $DB->insert_record('debatechat_grades', $debatechatgrade);
		$assign_mapid = $debatechatgrade->id;
		
		$itemname = $debatechatdetails->name. ' debatechat';

		 $itemsgrade = $DB->get_record_sql(" select id from {grade_items} where iteminstance = $debatechatinstance and itemmodule = 'debatechat' and courseid = $courseid");
		$itemmid = $itemsgrade->id;
if(empty($itemmid)) {	
$item = new stdClass();
$item->courseid = $courseid; 
$item->categoryid = $grade_catid; 
$item->itemname  = $itemname;
$item->itemtype = 'mod';
$item->itemmodule = 'debatechat';
$item->iteminstance = $debatechatinstance;
$item->itemnumber = 0;
$item->gradetype = 1;
$item->grademax = 100;
$item->grademin = 0;
$item->timecreated = time(); 
$item->timemodified = time(); 
 $itemid =  $DB->insert_record('grade_items',$item);
  $itemsgrade = $DB->get_record_sql(" select id from {grade_items} where iteminstance = $debatechatinstance and itemmodule = 'debatechat' and courseid = $courseid");
		$itemmid = $itemsgrade->id;

}
	
	 if (!empty($itemmid)) { 
	$raterid= $USER->id;	 
	 //echo $itemmid;
	 
			$grades = new stdClass();			
            $grades->itemid	= $itemmid;
            $grades->rawgrade	= $finalgrade;
            $grades->finalgrade	= $finalgrade;
              $grades->userid	= $std_id;
              $grades->usermodified	= $raterid;
              $grades->timecreated = time();
              $grades->timemodified = time();
              $grades->feedbackformat	= 1;
              $grades->feedback	= $criteria_reason;
              
              $grades_id =  $DB->insert_record('grade_grades',$grades);

//if( !$gradeins = $DB->get_record('grading_instances', array('itemid' => $debatechatinstance))) {
              $defRecord = new stdClass();
              $defRecord->definitionid	= $definitionid;
              $defRecord->raterid	= $std_id;
              $defRecord->itemid	= $itemmid;
              $defRecord->rawgrade	= '';
              $defRecord->status	= 0;
              $defRecord->feedback	= '';
              $defRecord->feedbackformat = 0;
              $defRecord->timemodified = time();
              $instenceid =  $DB->insert_record('grading_instances',$defRecord); 


              $defRecord2 = new stdClass();
              $defRecord2->definitionid	= $definitionid;
              $defRecord2->raterid	= $std_id;
              $defRecord2->itemid	= $itemmid;
              $defRecord2->rawgrade	= '';
              $defRecord2->status	= 1;
              $defRecord2->feedback	= '';
              $defRecord2->feedbackformat = 0;
              $defRecord2->timemodified = time();
              $instenceid2 =  $DB->insert_record('grading_instances',$defRecord2);
//}
	 

            

               // Loop through the criterias array
            for ($i = 0; $i < $finaljson['total_criteria']; $i++) {
              $criteria = $finaljson['criterias'][$i];

              $criteria_id = $criteria['id'];
              $criteria_mark = $criteria['criteria_marks'];
              $min_mark = 1;
             
            $gradeins = $DB->get_record('grading_instances', array('itemid' => $itemmid, 'status' => 0,'raterid' => $std_id));
            $ins = $gradeins->id;
			//echo 'rubric instacneid ' . $ins;
            if(!empty($ins)){

              if($min_mark != 0){
              if($criteria_mark == 0){
               
                $score = 1.00000;
              } else {
                $score = $criteria_mark;
              }
            } else {
              $score = $criteria_mark;
            }

              $levels = $DB->get_record('gradingform_rubric_levels', array('criterionid' => $criteria_id, 'score' => $score));

              $guidefillRecord = new stdClass();
              $guidefillRecord->instanceid	= $ins;
              $guidefillRecord->criterionid	= $criteria_id;
              $guidefillRecord->remark	= '';
              $guidefillRecord->remarkformat	= 0;
              $guidefillRecord->levelid	= $levels->id;

              $ggnceid =  $DB->insert_record('gradingform_rubric_fillings',$guidefillRecord);
          
            }

            $gradeins2 = $DB->get_record('grading_instances', array('itemid' => $itemmid, 'status' => 1,'raterid' => $std_id));
            $ins2 = $gradeins2->id; //echo 'rubric instacneid2 ' . $ins2;
            if(!empty($ins2)){
              
            if($min_mark != 0){
              if($criteria_mark == 0){
                $score = 1.00000;
              } else {
                $score = $criteria_mark;
              }
            } else {
              $score = $criteria_mark;
            }

              $levels1 = $DB->get_record('gradingform_rubric_levels', array('criterionid' => $criteria_id, 'score' => $score));


              $guidefillRecord = new stdClass();
              $guidefillRecord->instanceid	= $ins2;
              $guidefillRecord->criterionid	= $criteria_id;
              $guidefillRecord->remark	= 'AL Feedback';
              $guidefillRecord->remarkformat	= 0;
              $guidefillRecord->levelid	= $levels1->id;

              $ggradeinstence =  $DB->insert_record('gradingform_rubric_fillings',$guidefillRecord);
          
            }
          }
		   $coursecomplete = $DB->get_record('course_modules_completion', array('userid' => $std_id, 'coursemoduleid' => $activity_id));
           $completed = $coursecomplete->id;
            if(empty($completed)){
              
			  // course mark as done
			  $coursecompletion = new stdClass();
              $coursecompletion->coursemoduleid	= $activity_id;
              $coursecompletion->userid	= $std_id;
              $coursecompletion->completionstate	= 1;
             // $coursecompletion->viewed	= 0;
              $coursecompletion->timemodified	= time();

              $markasdone =  $DB->insert_record('course_modules_completion',$coursecompletion);
			 echo 'success';
			} 
		 
		  
	 }
	 
			  } // activity


}

?>