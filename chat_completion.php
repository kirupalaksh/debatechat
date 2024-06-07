
<?php 
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(__DIR__ . '/lib.php'); // Include the lib.php file
$actid = $_POST['activity_id'];
$userid = $_POST['student_id'];
$courseid = $_POST['courseid'];
$context = $DB->get_record('course_modules', array('id'=>$actid)); 
$debatechatid = $context->instance;

 $params = array('debatechat'=>$debatechatid, 'userid'=>$userid, 'concluded' =>0);

        // Retry the search for a record.
        if ($record = $DB->get_record('debatechat_user_mapping', $params, 'id')) {
         $user_mapid =  $record->id;
        } //die;
		if(!empty($user_mapid)){
        // The requested user must not be a concluded. Add a record anyway.			
			$updatee = new stdClass();
            $updatee->id	= $user_mapid;
            $updatee->userid	= $userid;
            $updatee->debatechat	= $debatechatid;
            $updatee->concluded	= 1;
            
            $con =  $DB->update_record('debatechat_user_mapping',$updatee);
			echo json_decode($con);
		
		}


?>