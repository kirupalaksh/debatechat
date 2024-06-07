<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/gradelib.php');

if (isset($_POST['debateid'])) {
	$activity_id = $_POST['debateid'];
	$contextid = $DB->get_record('context', array('instanceid'=>$activity_id,'contextlevel'=>70)); 
	$context_id = $contextid->id;
	 
	$areas = $DB->get_record('grading_areas', array('contextid'=>$context_id)); 
	$areas_id = $areas->id;
	$referencing_skill_array = array();	
	$criteraa_levels = array();	
	$getrubricsql = "select levels.id,method,grc.description AS criteria,criterionid,definition,score from {grading_definitions} gf LEFT JOIN {gradingform_rubric_criteria} grc ON gf.id=grc.definitionid LEFT JOIN {gradingform_rubric_levels} levels
    ON levels.criterionid=grc.id where areaid='".$areas_id."' ORDER BY grc.sortorder";
	$rubric_details = $DB->get_records_sql($getrubricsql);
	
	$getcriteria = "select *,grc.description AS criteria,grc.id AS criteriaid from {grading_definitions} gf LEFT JOIN {gradingform_rubric_criteria} grc ON gf.id=grc.definitionid where areaid='".$areas_id."' ORDER BY grc.sortorder";
	$criteria_details = $DB->get_records_sql($getcriteria);
	  $defname = $DB->get_record_sql("select d.id,d.method from {grading_areas} a join {grading_definitions} d on a.id = d.areaid where a.contextid = $context_id");
	  //echo $defname->id;
	$criterionss = $DB->get_records_sql("select id,description from {gradingform_rubric_criteria} where definitionid = $defname->id");
	  $criteriacount = count($criterionss);
	$criterias = array();
  $critid = array();
  $criteria_max_mark = 0;
  foreach ($criterionss as $criterion) {
      $critid[] = $criterion->id;
     
      $critpoint = array();
    
      $critlevelpoints = $DB->get_records_sql("select * from {gradingform_rubric_levels} where criterionid = $criterion->id");
  
      foreach ($critlevelpoints as $critlevelpoint) {
        $scoreval = (int)$critlevelpoint->score;
        $critpoint[] = array(
        'description' => $critlevelpoint->definition,
        'mark' => 'This should be integer '.$scoreval .' if the description happened in student arguments in my chat memory, otherwise it should be 0.',
		'grade' => "This  should be the grade in letter for this level by analysing student arguments in the chat memory, If the mark is 0 then this should be the grade F.",
		'reason' => "This should be the reason for the mark which you given for this level"
        );
      }

      $criterias[] = array(
          'id' => $criterion->id,
          'criteria_key' =>    $criterion->description,
          'criteria_levels' =>  $critpoint
      );
  }
 // $jsArray = json_encode($criterias);

   for ($i = 0; $i < $criteriacount; $i++) {
		
       	$criteria_id = $criterias[$i]['id'];
		$critlevelpoints = $DB->get_record_sql("select id,max(score) AS maxscore from {gradingform_rubric_levels} where criterionid = $criteria_id"); $criteria_max_mark += $critlevelpoints->maxscore;	
		//$critlevelpoints1 = $DB->get_record_sql("select id,min(score) AS minscore from {gradingform_rubric_levels} where criterionid = $criteria_id");
//		$criteria_min_mark += $critlevelpoints1->minscore;	
		}	
$max_marks = $criteria_max_mark;
//$min_marks = $criteria_min_mark;
$rubrics[] = array(
          'criterias' => $criterias, 'max_mark'=> $max_marks,
      );

	 $jsArray = json_encode($rubrics);
	//$rubricjson = json_encode($jsArray,true);  
	echo $jsArray ;	
}