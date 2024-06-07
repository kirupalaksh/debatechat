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

/* $activity_id = optional_param('id', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT); / */
$activity_id = $_REQUEST['id'];
$userid = $_REQUEST['userid']; 
	$contextid = $DB->get_record('context', array('instanceid'=>$activity_id,'contextlevel'=>70)); 
	$context_id = $contextid->id;	
	$course_module = $DB->get_record('course_modules', array('id'=>$activity_id,'module'=>29)); 
	$debatechatinstance = $course_module->instance;
$courseid = $course_module->course; 
	$context = context_course::instance($courseid);
	if(has_capability('mod/debatechat:grade', $context, $USER->id, true)){ 
	function digitGrade_to_lettergrade($gradeValue) {
    if ($gradeValue >= 98) {
        return "A+";
    } else if ($gradeValue >= 90) {
        return "A";
    } else if ($gradeValue >= 88) {
        return "A-";
    } else if ($gradeValue >= 85) {
        return "B+";
    } else if ($gradeValue >= 81) {
        return "B";
    } else if ($gradeValue >= 78) {
        return "B-";
    } else if ($gradeValue >= 75) {
        return "C+";
    } else if ($gradeValue >= 71) {
        return "C";
    } else if ($gradeValue >= 68) {
        return "C-";
    } else if ($gradeValue >= 67) {
        return "D+";
    } else if ($gradeValue >= 65) {
        return "D";
    } else if ($gradeValue >= 60) {
        return "D-";
    } else {
        return "F";
    }
}
	$course = $DB->get_record('course', array('id' => $courseid));
$debate_details = $DB->get_record('debatechat', array('id' => $debatechatinstance));
$debatechatid= $debate_details->id;
require_course_login($course);
	$gradecat = $DB->get_record('grade_categories', array('fullname'=>'Debatechat','courseid' =>$courseid)); 
	$grade_catid = $gradecat->id;
$areas = $DB->get_record('grading_areas', array('contextid'=>$context_id)); 
$userdetails = $DB->get_record('user', array('id'=>$userid)); 
	$areas_id = $areas->id;

$definitions = $DB->get_record('grading_definitions', array('areaid'=>$areas_id)); 
	$definitionid = $definitions->id; 
$strname = get_string('modulenameplural', 'mod_debatechat');
$PAGE->set_url('/mod/debatechat/gradeform.php', array('id' => $activity_id));
//$PAGE->navbar->add($strname);
$PAGE->set_title("$course->shortname: $strname");
$PAGE->set_heading($course->fullname);
//$PAGE->set_pagelayout('incourse');
echo $OUTPUT->header();
echo $OUTPUT->heading($strname);
$grades = $DB->get_record('debatechat_grades', array('debatechat'=>$debatechatid,'userid'=>$userid));
		if(!empty($grades) && ($grades->userid == $userid))  { 
		$gradesval = number_format($grades->grade, 2, '.', '');
		$gradefinal = digitGrade_to_lettergrade($gradesval) . ' ('.$gradesval.' %)'; // B (85.00 %)
		
		} 	else {  
		$gradefinal = "Not Graded";		
		}
	  $defname = $DB->get_record_sql("select d.id,d.method from {grading_areas} a join {grading_definitions} d on a.id = d.areaid where a.contextid = $context_id");
	$criterionss = $DB->get_records_sql("select id,description from {gradingform_rubric_criteria} where definitionid = $defname->id");
	  $crits = count($criterionss);
	$criterias = array();
  $critid = array();
  foreach ($criterionss as $criterion) {
      $critid[] = $criterion->id;
     
      $critpoint = array();
    
      $critlevelpoints = $DB->get_records_sql("select * from {gradingform_rubric_levels} where criterionid = $criterion->id");
  
      foreach ($critlevelpoints as $critlevelpoint) {        
        $critpoint[] = array(
        'description' => $critlevelpoint->definition,
        'mark' => $critlevelpoint->score
        );        
      }

      $criterias[] = array(
          'id' => $criterion->id,
          'criteria_key' =>    $criterion->description,
          'criteria_levels' =>  $critpoint
      );
  }
 $jsArray = $criterias;
		

// student data
$url = 'https://api.vf.svhs.co/api/debate-bot?student_id='.$userid.'&activity_id='.$activity_id;
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
 if(!empty($data)){
 
$aival = $data['grade'];
$current_grade = $aival['estimated_grade'];
$final_score = $aival['final_score']; 

    for ($i = 0; $i < $crits; $i++) {		
        $criteria_key = $current_grade[$i]['criteria_key'];
		$criteria_id = $current_grade[$i]['id'];
		$critlevelpoints = $DB->get_record_sql("select id,max(score) AS maxscore from {gradingform_rubric_levels} where criterionid =   $criteria_id"); 
		$criteria_max_mark += $critlevelpoints->maxscore;	
		$criteria = $current_grade[$i]['criteria_levels'];    
        $description = $criteria['description'];
        $criteria_mark = $criteria['mark'];
        $criteria_grade = $criteria['grade'];
        $criteria_reason = $criteria['reason'];
		$criteriaarray[] = array('id' =>$criteria_id,'criteria_marks' =>  $criteria_mark);
	}
	
 }	
 
}
$finaljson = array('total_criteria' => $crits,
	'criterias' => $criteriaarray,
'max_mark'=> $criteria_max_mark	
		);


				  
		$itemsgrade = $DB->get_record_sql(" select id from {grade_items} where iteminstance = $debatechatinstance and itemmodule = 'debatechat' and courseid = $courseid ");
		 $itemmid = $itemsgrade->id;
		 
		$gradess = $DB->get_record('grade_grades', array('userid' => $userid, 'itemid' => $itemmid));
         $gradess_id = $gradess->id;	 
		$grdstsd1 = $DB->get_record('grading_instances', array('raterid' => $userid,'itemid' => $itemmid, 'status' => 0));
		$gradeins = $grdstsd1->id;
		foreach($criterias as $val) {
		$rublevels = $DB->get_record('gradingform_rubric_fillings', array('criterionid' => $val['id'], 'instanceid' => $gradeins));
		 $rublevels_id = $rublevels->id;
		 $rublevels_instanceid = $rublevels->instanceid;
		 $rublevels_criterionid = $rublevels->criterionid;
		 $rublevels_levelid = $rublevels->levelid; 
		$filleddata[] = array(
			'id' => $rublevels->id,
			'instanceid' => $rublevels->instanceid,
			'criterionid' => $rublevels->criterionid,
			'levelid' => $rublevels->levelid
			); 
		}
		
	}		
//if(isset($_POST['savechanges'])){ 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
		$userid = $_POST['userid'];
		$courseid = $_POST['courseid'];
		$activity_id = $_POST['id'];
		$debatechatid = $_POST['debatechatid'];
		$advancedgrading = $_POST['advancedgrading'];
		$teacherfeedback = $_POST['teacher_feedback'];
		//$grade = $_POST['grade'];
		
	 if(!empty($activity_id) && !empty($userid)){
           
            $levelIds = array_column($advancedgrading['criteria'], 'levelid');
			$rubricgrade = array_sum($levelIds);
			$max_marks = $finaljson['max_mark'];
			$final_grade = $rubricgrade / $max_marks * 100; 
			$finalgrade = number_format($final_grade, 2);
	 	

		$debate_id = $DB->get_record('debatechat_grades', array('userid' => $userid, 'debatechat' => $debatechatinstance));
        $debateexists = $debate_id->id;	
		if(empty($debateexists)){
			$debatechatgrade = new stdClass();
			$debatechatgrade->debatechat = $debatechatinstance;
			$debatechatgrade->userid = $userid;
			$debatechatgrade->itemnumber = 1;
			$debatechatgrade->grade = $finalgrade;
			$debatechatgrade->timemodified = time(); 
			$debatechatgrade->timecreated = time(); 
			$debatechatgrade->course = $courseid;
			$debatemapid = $DB->insert_record('debatechat_grades', $debatechatgrade);
		}else if(!empty($debateexists))	{						  
			$debatechatgrade = new stdClass();
			$debatechatgrade->id = $debateexists;
			$debatechatgrade->grade = $finalgrade;
			$debatechatgrade->timemodified = time(); 
			$debatechatgradeupdate = $DB->update_record('debatechat_grades', $debatechatgrade);
		}
		
		//$itemname = $debatechatdetails->name. ' debatechat';

		$itemsgrade = $DB->get_record_sql(" select id from {grade_items} where iteminstance = $debatechatinstance and itemmodule = 'debatechat' and courseid = $courseid ");
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
		$gradess = $DB->get_record('grade_grades', array('userid' => $userid, 'itemid' => $itemmid));
        $gradess_id = $gradess->id;	
		$raterid= $USER->id;
		if(!empty($gradess_id)) {
			$grades = new stdClass();			
            $grades->id	= $gradess_id;
            $grades->rawgrade	= $finalgrade;
            $grades->finalgrade	= $finalgrade;
            $grades->usermodified	= $raterid;
            $grades->timemodified = time();
            $grades->feedback	= $teacherfeedback;
            $grades_id =  $DB->update_record('grade_grades',$grades);
		}else if(empty($gradess_id)){
			$grades = new stdClass();			
            $grades->itemid	= $itemmid;
            $grades->rawgrade	= $finalgrade;
            $grades->finalgrade	= $finalgrade;
            $grades->userid	= $userid;
            $grades->usermodified	= $raterid;
            $grades->timecreated = time();
            $grades->timemodified = time();
            $grades->feedbackformat	= 1;
            $grades->feedback	= $teacherfeedback;
            $grades_id =  $DB->insert_record('grade_grades',$grades);
		}	
			if (!$DB->record_exists('grading_instances', array( 'itemid' => $itemmid,'status' => 0, 'raterid' => $userid))){ 

              $defRecord = new stdClass();
              $defRecord->definitionid	= $definitionid;
              $defRecord->raterid	= $userid;
              $defRecord->itemid	= $itemmid;
              $defRecord->rawgrade	= '';
              $defRecord->status	= 1;
              $defRecord->feedback	= '';
              $defRecord->feedbackformat = 0;
              $defRecord->timemodified = time();
              $instence =  $DB->insert_record('grading_instances',$defRecord);

              $defRecord2 = new stdClass();
              $defRecord2->definitionid	= $definitionid;
              $defRecord2->raterid	= $userid;
              $defRecord2->itemid	= $itemmid;
              $defRecord2->rawgrade	= '';
              $defRecord2->status	= 0;
              $defRecord2->feedback	= '';
              $defRecord2->feedbackformat = 0;
              $defRecord2->timemodified = time();
              $instenc2 =  $DB->insert_record('grading_instances',$defRecord2);
            } else {
              
              
              $grdstsd1 = $DB->get_record('grading_instances', array('raterid' => $userid,'itemid' => $itemmid, 'status' => 1));

              if(!empty($grdstsd1->id)){
              $defRecord2 = new stdClass();
              $defRecord2->id	= $grdstsd1->id;
              $defRecord2->definitionid	= $definitionid;
              $defRecord2->raterid	= $userid;
              $defRecord2->itemid	= $itemmid;
              $defRecord2->rawgrade	= '';
              $defRecord2->status	= 3;
              $defRecord2->feedback	= '';
              $defRecord2->feedbackformat = 0;
              $defRecord2->timemodified = time();
              $instence2 =  $DB->update_record('grading_instances',$defRecord2);
              }

              $grdstsd0 = $DB->get_record('grading_instances', array('raterid' => $userid,'itemid' => $itemmid, 'status' => 0));
              if(!empty($grdstsd0->id)){
              $defRecord = new stdClass();
              $defRecord->id	= $grdstsd0->id;
              $defRecord->definitionid	= $definitionid;
              $defRecord->raterid	= $userid;
              $defRecord->itemid	= $itemmid;
              $defRecord->rawgrade	= '';
              $defRecord->status	= 1;
              $defRecord->feedback	= '';
              $defRecord->feedbackformat = 0;
              $defRecord->timemodified = time();
              $instence =  $DB->update_record('grading_instances',$defRecord);
              }
              $defRecord3 = new stdClass();
              $defRecord3->definitionid	= $definitionid;
              $defRecord3->raterid	= $userid;
              $defRecord3->itemid	= $itemmid;
              $defRecord3->rawgrade	= '';
              $defRecord3->status	= 0;
              $defRecord3->feedback	= '';
              $defRecord3->feedbackformat = 0;
              $defRecord3->timemodified = time();
              $instenc3 =  $DB->insert_record('grading_instances',$defRecord3);			

            }

			//for ($i = 0; $i < $finaljson['total_criteria']; $i++) {
			foreach ($advancedgrading['criteria'] as $key => $val) {
                $criteria_id = $key;              
                $criteria_mark = $val['levelid'];
				$min_mark = 1;
				$gradeins = $DB->get_record('grading_instances', array('itemid' => $itemmid,'raterid' => $userid, 'status' => 0));
				$ins = $gradeins->id;
				$rublevels = $DB->get_record('gradingform_rubric_fillings', array('criterionid' => $criteria_id, 'instanceid' => $ins));
				$rublevels_id = $rublevels->id;
				if(empty($rublevels_id)){

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
			  
				} else {

                  $guidefillRecord2 = new stdClass();
                  $guidefillRecord2->id	= $rublevels_id;
                  $guidefillRecord2->instanceid	= $ins;
                  $guidefillRecord2->criterionid	= $criteria_id;
                  $guidefillRecord2->remark	= '';
                  $guidefillRecord2->remarkformat	= 0;
                  $guidefillRecord2->levelid	= $levels->id;
    
                  $ggradeinstence =  $DB->update_record('gradingform_rubric_fillings',$guidefillRecord2);
                  
                }

                $gradeins2 = $DB->get_record('grading_instances', array('itemid' => $itemmid, 'raterid' => $userid,'status' => 1));
				$ins2 = $gradeins2->id; 
				$rublevels1 = $DB->get_record('gradingform_rubric_fillings', array('criterionid' => $criteria_id, 'instanceid' => $ins2));
				$rublevels1_id = $rublevels1->id;
				if(empty($rublevels1_id)){
				  
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


				  $guidefillRecord1 = new stdClass();
				  $guidefillRecord1->instanceid	= $ins2;
				  $guidefillRecord1->criterionid	= $criteria_id;
				  $guidefillRecord1->remark	= '';
				  $guidefillRecord1->remarkformat	= 0;
				  $guidefillRecord1->levelid	= $levels1->id;

				  $ggradeinstence =  $DB->insert_record('gradingform_rubric_fillings',$guidefillRecord1);
			  
				}   else {

                  $guidefillRecord2 = new stdClass();
                  $guidefillRecord2->id	= $rublevels1_id;
                  $guidefillRecord2->instanceid	= $ins2;
                  $guidefillRecord2->criterionid	= $criteria_id;
                  $guidefillRecord2->remark	= '';
                  $guidefillRecord2->remarkformat	= 0;
                  $guidefillRecord2->levelid	= $levels1->id;
    
                  $ggradeinstence =  $DB->update_record('gradingform_rubric_fillings',$guidefillRecord2);
                  
                } 
				
                } 
            
              
            	

		    $coursecomplete = $DB->get_record('course_modules_completion', array('userid' => $userid, 'coursemoduleid' => $activity_id));
            $completed = $coursecomplete->id;
            if(empty($completed)){
              
			  // course mark as done
			  $coursecompletion = new stdClass();
              $coursecompletion->coursemoduleid	= $activity_id;
              $coursecompletion->userid	= $userid;
              $coursecompletion->completionstate	= 1;
              $coursecompletion->timemodified	= time();
              $markasdone =  $DB->insert_record('course_modules_completion',$coursecompletion);	
				// echo 'success';
			} else {
				$completion = new stdClass();
              $completion->id	= $completed;
              $completion->coursemoduleid	= $activity_id;
              $completion->userid	= $userid;
              $completion->completionstate	= 1;
              $completion->timemodified	= time();
              $markdone =  $DB->update_record('course_modules_completion',$completion);	
			  // echo 'success';	
			}	
		   
		  
		}
			
	 
		

	
	
	 
	} // activity 
	
?>	
<div class="alert alert-success" role="alert">
		Changes to the grade is successfully Modified.
	</div>
	<?php 
	$redirection = $CFG->wwwroot . '/mod/debatechat/gradeform.php?id='.$activity_id.'&userid='.$userid;
	header('Location: '.$redirection);
	//$delay = 5; // Change this value as needed
	//header("Refresh: $delay");
	?>
	<meta http-equiv="refresh" content="5;url=<?php echo $redirection; ?>">
	<?php  } 
if(has_capability('mod/debatechat:grade', $context, $USER->id, true)){ 	
?>
<style>
.drawer-toggles .drawer-toggler { display:None; }
.secondary-navigation  { display:None; }
</style>

<div class="row">
<div data-region="assignment-info" class="col-md" >
	<h4>  <?php echo 'Student Name : ' .'<a href="'.$CFG->wwwroot.'/user/view.php?id='.$userid.'&course='.$courseid.'" title="'.$userdetails->firstname.'">' .$userdetails->firstname.' ' .$userdetails->lastname .'</a>'; ?> </h4>
	<a href="/course/view.php?id=<?php echo $courseid ?>" title="Course: <?php echo $course->fullname?>" >Course: <?php echo $course->fullname; ?></a>
	<br><a href="/mod/debatechat/view.php?id=<?php echo $activity_id;?>" title="Debatechat: <?php echo $debate_details->title;?>">Debatechat: <?php echo $debate_details->title; ?></a>
<br>
	<a href="/mod/debatechat/debategrade.php?id=<?php echo $activity_id ?>&courseid=<?php echo $courseid ?>&debatechat=<?php echo $debatechatid?>" aria-label="Edit settings" title="Edit settings"> View Participants </a>
	<br>
</div>

</div>
<form class="gradeform mform" id="gradeform-debate"   method="post"  data-boost-form-errors-enhanced="1" >
<div style="display: none;">
<input name="id" type="hidden" value="<?php echo $activity_id; ?>">
<input name="courseid" type="hidden" value="<?php echo $courseid ?>">
<input name="userid" type="hidden" value="<?php echo $userid ?>">
<input name="debatechatid" type="hidden" value="<?php echo $debatechatid ?>">
</div>


	
<div class="d-flex align-items-center mb-2">
    <div class="position-relative d-flex ftoggler align-items-center position-relative mr-1">
        <h3 class="d-flex align-self-stretch align-items-center mb-0" aria-hidden="true">
            Grade
        </h3>
    </div>
    
</div>
<div id="id_gradeheadercontainer" class="fcontainer  show">

<div id="fitem_id_currentgrade" class="form-group row  fitem   ">
    <div class="col-md-3 col-form-label d-flex pb-0 pr-md-0">
        
                <span class="d-inline-block ">
                    Current grade in gradebook
                </span>
        
        <div class="form-label-addon d-flex align-items-center align-self-start">
            
        </div>
    </div>
	<?php	
	//if (!empty($gradesval)) { $finalgrade = $gradesval; print_r($grades_count); } else { $finalgrade = 'Not graded'; } 
?><input name="gradecnt" type="hidden" value="<?php //echo count($grades_count) ?>">

    <div class="col-md-9 form-inline align-items-start felement" data-fieldtype="static">
        <div class="form-control-static">
        <span class="currentgrade"><a href="/grade/report/grader/index.php?id=<?php echo $courseid; ?>&gpr_userid=<?php echo $userid; ?>" id="action_link65b8ebdc720fd115" class=""><?php  echo $gradefinal;  ?></a></span>
        </div>
        <div class="form-control-feedback invalid-feedback" id="id_error_currentgrade">
            
        </div>
    </div>
</div>
<div id="rubric-advancedgrading" class="clearfix gradingform_rubric evaluate editable">
	<table class="criteria" id="advancedgrading-criteria">
	<tbody>
	<?php  for ($i = 0; $i < $finaljson['total_criteria']; $i++) {
                  $criteria = $finaljson['criterias'][$i];    
                  $criteria_id = $criteria['id'];
                  $criteria_mark = $criteria['criteria_marks'];
                  ?>
		<tr class="criterion first even" id="advancedgrading-criteria-360">
		<td class="description" id="advancedgrading-criteria-360-description-cell" tabindex="0" aria-label="Criterion Part 1: Sets up a system of equations to represent the situation"><?php echo $jsArray[$i]['criteria_key'];?></td>
		<td class="levels">
		<?php  $critlevelpoints = $DB->get_records_sql("select * from {gradingform_rubric_levels} where criterionid =  '".$criteria_id."' ");
		  $levelss = count($critlevelpoints); // //text-break level first even currentchecked checked ?>
		<table id="advancedgrading-criteria-360-levels-table" role="none">
		<tbody>
		<tr id="advancedgrading-criteria-<?php echo $criteria_id ?>-levels" aria-label="Levels group" role="radiogroup">
		 <?php foreach ($critlevelpoints as $critlevel) { ?> 
		<td id="advancedgrading-criteria-<?php echo $criteria_id ?>-levels-1285" class="<?php if((empty($filleddata[$i]['criterionid'])) && ($criteria_id == $critlevel->criterionid) && ($criteria_mark==round($critlevel->score,2))) { echo 'text-break level first currentchecked checked'; } else if(($filleddata[$i]['criterionid']== $critlevel->criterionid) && ($filleddata[$i]['levelid']== $critlevel->id)) {  echo 'text-break level first currentchecked checked'; } else { echo 'text-break level first'; } ?>" style="width: 25%;" tabindex="0" aria-label="Level Student writes two different equations that represent the situation. Identifies the variables in the equations. , 4 points." role="radio" aria-checked="<?php if(($criteria_id == $critlevel->criterionid) && ($criteria_mark ==round($critlevel->score,2))) {echo 'true'; }else { echo 'false'; } ?>">
		
			
			<div class="level-wrapper">
				<div class="radio" ><input name="levelid" type="hidden" value="<?php echo $critlevel->id; ?>">
						<input type="radio" id="advancedgrading-criteria-<?php echo $criteria_id ?>-levels-<?php echo $critlevel->id;?>-definition" name="advancedgrading[criteria][<?php echo $criteria_id ?>][levelid]" value="<?php echo $critlevel->score;?>" <?php if((empty($filleddata[$i]['criterionid'])) && ($criteria_id == $critlevel->criterionid) && ($criteria_mark==round($critlevel->score,2))) { echo 'checked'; } else if(($filleddata[$i]['criterionid']== $critlevel->criterionid) && ($filleddata[$i]['levelid']== $critlevel->id)) {  echo "checked"; }  ?> >
				</div>
					<div class="definition$" ><?php echo $critlevel->definition; ?></div>
				<div class="score d-inline"><span class="scorevalue"><?php echo round($critlevel->score,2); ?></span> points</div>
			</div>
		</td>	
		<?php  } ?>		
		</tr>		
		</tbody>
		</table>
		
		</td>		
		</tr>
		<?php } ?>
	</tbody>
	</table>
</div>
<div>
    <div class="col-form-label d-flex pb-0 pr-md-0">
        
                <label id="id_assignfeedbackcomments_editor_label" class="d-inline word-break " for="id_assignfeedbackcomments_editor">
                    <span style="color: #FF0000;"><b style="color: #FF0000">Teacher Feedback</b></span>
                </label>
      
  </div>
  <br/>
  <?php 
    $itemsgrade = $DB->get_record_sql(" select id from {grade_items} where iteminstance = $debatechatinstance and itemmodule = 'debatechat' and courseid = $courseid");
	$itemmid = $itemsgrade->id;
	 if (!empty($itemmid)) {
	$gradesgrades = $DB->get_record_sql(" select gg.id,gg.feedback from {grade_grades} gg join {grade_items} gi on gg.itemid = gi.id where iteminstance = $debatechatinstance and itemmodule = 'debatechat' and userid = $userid");
	$gradesfeedback = $gradesgrades->feedback;
	 }
  ?>
    <div class="form-inline align-items-start felement" style="min-height: 200px;height: 200px;;" >
       <textarea name="teacher_feedback" id="advancedgrading-criteria-360-remark" cols="90" rows="10" aria-label="Remark for criterion Technical Knowledge: "><?php if(!empty($gradesfeedback)) {echo $gradesfeedback; }else if(!empty($criteria_reason)) { echo $criteria_reason; }?></textarea>
    </div>
</div>
<div style="text-align:center;">
<button type="submit" class="btn btn-primary" name="savechanges">Save changes</button>
<button type="submit" class="btn btn-secondary" name="resetbutton">Reset</button>
</div>
		</div>
</form>

<?php } ?>
