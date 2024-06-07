<?php
/**
 * Redirect to particular user chat history review
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$courseid = required_param('courseid', PARAM_INT); // Course.
$activityid = optional_param('id', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT); // Graded user ID (optional).
$debatechat = optional_param('debatechat', 0, PARAM_INT); // DEBATE ID 
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$debatechatdet = $DB->get_record('debatechat', array('id' => $debatechat));
require_course_login($course);

$params = array(
    'context' => context_course::instance($courseid)
);

$strname = get_string('modulenameplural', 'mod_debatechat');
$PAGE->set_url('/mod/debatechat/review.php', array('id' => $debatechat));
$PAGE->navbar->add($strname);
$PAGE->set_title("$course->shortname: $strname");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

echo $OUTPUT->header();
$userdetails = $DB->get_record('user',array('id'=>$userid));
//echo $OUTPUT->heading($strname); //https://api.vf.svhs.co/api/activity-bot?student_id=1017&activity_id=42001
$content =  file_get_contents("https://api.vf.svhs.co/api/debate-bot?student_id=$userid&activity_id=$activityid");
$debatechatdetails  = json_decode($content,true);
$results = new stdClass();
$debatedata= $debatechatdetails->data;
$grades = $DB->get_record('debatechat_grades', array('debatechat'=>$debatechat,'userid'=>$userid));
	$gradesval = $grades->grade;  
//$.each(result.chat_content, function(index, items) {
	//		let aival = items.ai;
?>

<div class="card grey lighten-3 chat-room">
  <div class="card-body">
   <div class="col-12">	
   <?php echo '<h4>'.'Debate Bot Topic : ' .$debatechatdet->title . '</h4>';	; ?>
					<?php foreach($debatedata as $val){ ?>
						<div class="chat-message-left pb-4">						
							<div class="flex-shrink-1 bg-light rounded">
							<div class="font-weight-bold mb-1">DEBATE BOT</div>
							 <?php echo $ai = $val->ai; 
								$decoded_ai = json_decode($ai);
								echo $decoded_ai->argument; 
								//echo $decoded_json->concluded; // Output: False?>
							</div>
						</div>	
						<div class="chat-message-left pb-4">	
							<div class="flex-shrink-1 bg-light rounded">
							<div class="font-weight-bold mb-1"><?php echo $userdetails->firstname; ?></div>
							 <?php $student = $val->student; echo $student; ?>
							</div>
							
						</div>
						<?php } ?>			
					
</div>
	</div>
	</div>
<style>
.chat-message-left,
.chat-message-right {
    display: flex;
    flex-shrink: 0
}
.chat-messages {
    display: flex;
    flex-direction: column;
   /* max-height: 800px;
    overflow-y: scroll*/
}
.chat-message-left {
    margin-right: auto
}

.chat-message-right {
    flex-direction: row-reverse;
    margin-left: auto
}
.py-3 {
    padding-top: 1rem!important;
    padding-bottom: 1rem!important;
}
.px-4 {
    padding-right: 1.5rem!important;
    padding-left: 1.5rem!important;
}
.flex-grow-0 {
    flex-grow: 0!important;
}
.border-top {
    border-top: 1px solid #dee2e6!important;
}
.user-msg {
    float: right !important;
    text-align: left;
    color: #fff;
    animation: 300ms 1 forwards slideUpAndRight cubic-bezier(0.4, 0, 0.2, 1), 300ms 1 forwards fadeIn cubic-bezier(0.4, 0, 0.2, 1);
    background: #009eff;
    border: none 0;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 1rem;
    outline: none;
    padding: 0.5rem 1rem;
    position: relative;
    word-wrap: break-word;
    max-width: 100%;
    box-shadow: 1px 1px 2px 0 #d9d9d9;
}

</style>