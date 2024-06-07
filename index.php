<?php
/**
 * Prints a list of module instances
 *
 * @package   mod_debatechat
 /// This page lists all the instances of debatechat in a particular course
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT); // Course.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$params = array(
    'context' => context_course::instance($course->id)
);
$context = context_course::instance($course->id);
$strname = get_string('modulenameplural', 'mod_debatechat');
$PAGE->set_url('/mod/debatechat/index.php', array('id' => $id));
$PAGE->navbar->add($strname);
$PAGE->set_title("$course->shortname: $strname");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
echo $OUTPUT->heading($strname);

if (! $debates = get_all_instances_in_course('debatechat', $course)) {
    notice(get_string('nodebates', 'debatechat'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections  ) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
		if(has_capability('mod/debatechat:grade', $context, $USER->id, true)){ 
    $table->head  = array ($strsectionname, $strname,'Grade');
	 $table->align = array ('center', 'left');
	}else{
    $table->head  = array ($strsectionname, $strname);
	 $table->align = array ('center', 'left');
	}
   
} 

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($debates as $cm) {
    $row = array(); 
	
   // if ($debates ) {
		  if ($cm->section == $currentsection ) { 
			$row[] = ' '; 
		  }else if ($cm->section) {
                $row[] = get_section_name($course, $cm->section);
            }
            
            if ($currentsection !== '' && $cm->section !== $currentsection) {
                $table->data[] = 'hr';
            }
            $currentsection = $cm->section;
      //  }
    
	$debatechat = $DB->get_record('debatechat',array('id' => $cm->id), '*', MUST_EXIST);
    $class = $cm->visible ? null : array('class' => 'dimmed');
	$row[] =  "<a href='view.php?id=$cm->coursemodule'>".$cm->name."</a>";
	if (has_capability('mod/debatechat:grade', $context, $USER->id, true)) {
	$link = "<a href='debategrade.php?id=$cm->coursemodule&courseid=$id&debatechat=$cm->id'>".'Needs grading'."</a>";
	$row[] =  $link;
	}
    $table->data[] = $row;
	
} 


echo html_writer::table($table);

echo $OUTPUT->footer();