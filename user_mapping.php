
<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
// Create a user mapping for chat 
require_once(__DIR__ . '/lib.php'); // Include the lib.php file
$actid = $_POST['activity_id'];
$userid = $_POST['student_id'];
$context = $DB->get_record('course_modules', array('id'=>$actid)); 
$debatechatid = $context->instance;

 $params = array('debatechat'=>$debatechatid, 'userid'=>$userid);
        if ($record = $DB->get_record('debatechat_user_mapping', $params, 'id')) {
          echo $record->id;
        } //print_r($record);
        // Be a little smart about this - there is no record for the current user.
        // We should ensure any unallocated ids for the current participant
        // list are distrubited randomly.
      //  self::allocate_unique_ids($debatechatid);

        // Retry the search for a record.
        if ($record = $DB->get_record('debatechat_user_mapping', $params, 'id')) {
         $user_mapid =  $record->id;
        }
		if(empty($user_mapid)){
        // The requested user must not be a participant. Add a record anyway.
			$record = new stdClass();
			$record->debatechat = $debatechatid;
			$record->userid = $userid;
		    $DB->insert_record('debatechat_user_mapping', $record); 
			
		}
/* function calluser($actid,$userid) {
    $result = get_uniqueid_for_user_static($actid,$userid); 
    // Process the result as needed
    return $result;
} */ 


?>