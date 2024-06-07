<?php
/**
 * Redirect the user to the appropriate submission related page.
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/gradelib.php');

if (isset($_GET['uid'])) {
	$debatechatid = $_GET['debateid'];
	$userid = $_GET['uid'];
	$context = $DB->get_record('course_modules', array('instance'=>$debatechatid,'module'=>29)); 
	$activity_id = $context->id;
	$courseid = $context->course;
	$debatechatdetails = $DB->get_record('debatechat', array('id'=>$debatechatid)); 
	$topic = $debatechatdetails->title;
	
	$contextid = $DB->get_record('context', array('instanceid'=>$activity_id,'contextlevel'=>70)); 
	$context_id = $contextid->id;
	/* 
	$areas = $DB->get_record('grading_areas', array('contextid'=>$context_id)); 
	$areas_id = $areas->id;

	 $getrubricsql = "select levels.id,method,grc.description AS criteria,criterionid,definition,score from {grading_definitions} gf LEFT JOIN {gradingform_rubric_criteria} grc ON gf.id=grc.definitionid LEFT JOIN {gradingform_rubric_levels} levels
    ON levels.criterionid=grc.id where areaid='".$areas_id."' ORDER BY grc.sortorder";
	$rubric_details = $DB->get_records_sql($getrubricsql);
	foreach($rubric_details as $key=>$rub) { 
			$rubriclevel1[$key] = $rub->criteria;
			$rubriclevel2[$rub->criteria] =   array($rub->definition => "This should be the score out of ".$rub->score. " and the reason for the score.");	
			$levels[$key][$rub->criteria] =  $rub->definition ;	
	}
	
	foreach($levels as $key1=>$rub1) { 
			$rubriclevel3[$key1] = $rub1;			
	}
	
	$skills = array_unique($rubriclevel1);
	$dynamic_skills = implode(',', $skills);
	     
   $character = '';
   $json_array = array( 'topic' =>  $topic,
    'student_id' => $userid,
    'activity_id' => $activity_id,
	'criteria'=> $dynamic_skills,
	'rubric' => $rubriclevel2,
	'character'=> $character,
	'activity_type'=> "debate"
	);
  $rubricjson = json_encode($json_array,true);  
  //echo $rubricjson;
 
$url = "https://api.vf.svhs.co/api/grading-bot";  
$datapost = json_encode($json_array); 


$ch = curl_init($url);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $rubricjson);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}

if (isset($error_msg)) {
    print_r($error_msg);exit;
}

$responsejson = json_decode($response, true);
  //print_r($responsejson); 
  if(isset($responsejson) && $responsejson['success'] == 1) {
	 
		$gradedata = $responsejson['data'];
		$rubrickeys = array_keys($rubriclevel2);
		$mergeddata = array_merge($gradedata,$rubrickeys);
		// calculate grades 
		$responseoutput = array(); 
		$responseoutput1 = array(); 
		
		foreach($mergeddata as $value) {
				$responseoutput[] = $value;
		}
		
		// Iterate through the array and display keys and values
foreach ($responsejson['data'] as $category => $scores) {
    $mainskill = $category;
	//echo $category;
    foreach ($scores as $description => $score) {
		//$finalgrade += $score;
		$scoreval = $score;
		
//$commaPosition = strpos($score, ',');

//if ($commaPosition !== false) {
    // Extract the score (before the comma)
   // $score1 = substr($score, 0, $commaPosition);

    // Extract the reason (after the comma, excluding leading space)
   // $reason1 = trim(substr($score, $commaPosition + 1));
	//$finalgrade = $finalgrade + number_format($score1,5);
    // Display the extracted score and reason
   // echo "Score: $score1 out of 6.00000";
   // echo "Reason: $reason1\n";
	$jsondataa = array( 'score' =>  $score1,
		'reason' => $reason1,
		'subskill' => $description,
		'skill' => $mainskill
		);
//}
		
		
       // $reason = $description;	
	
	} //echo $finalgrade;
    } */
	
		$grader = 1017;
		$finalgrade = 10;
		//$jsonval = json_encode($jsondataa,true); 
		$debatechatgrade = new stdClass();
		$debatechatgrade->debatechat = $debatechatid;
		$debatechatgrade->userid = $userid;
		$debatechatgrade->itemnumber = 1;
		$debatechatgrade->grader = $grader;
		$debatechatgrade->grade = $finalgrade;
		$debatechatgrade->timemodified = time(); 
		$debatechatgrade->timecreated = time(); 
		$debatechatgrade->id = $DB->insert_record('debatechat_grades', $debatechatgrade);
	
	$item = [
        'itemname' => 'gradeitemnameforwholedebatechat','idnumber' => $debatechatdetails->id
    ];

    if (!$debatechatdetails->grade_debatechat) {
        $item['gradetype'] = GRADE_TYPE_NONE;
    } else if ($debatechatdetails->grade_debatechat > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax'] = $debatechatdetails->grade_debatechat;
        $item['grademin'] = 0;
    } else if ($debatechatdetails->grade_debatechat < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid'] = $debatechatdetails->grade_debatechat * -1;
	}

    print_r($item);
    // Itemnumber 1 is the whole debatechat grade.
    $itemid = grade_update('mod/debatechat', $courseid, 'mod', 'debatechat', $debatechatdetails->id, 1, $debatechatgrade, $item);
	print_r($itemid);
			$grades = new stdClass();
			
            $grades->itemid	= $itemid;
            $grades->rawgrade	= $finalgrade;
            $grades->finalgrade	= $finalgrade;
              $grades->userid	= $userid;
              $grades->usermodified	= $USER->id;
              $grades->timemodified = time();
              $grades->feedbackformat	= 1;
              $grades->feedback	= "Reason from AI";
              
              $grades_id =  $DB->insert_record('grade_grades',$grades);
	

  
}  else {
    echo 'No user ID received.';
}
?>