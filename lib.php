<?php
/**
 * Library of interface functions and constants for module debatechat
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the debatechat specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function debatechat_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:     return true;
        case FEATURE_SHOW_DESCRIPTION:  return true;
        case FEATURE_GRADE_HAS_GRADE:    return true;
		case FEATURE_GRADE_OUTCOMES:   return true;
        case FEATURE_BACKUP_MOODLE2:      return true;
		case FEATURE_ADVANCED_GRADING:        return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the debatechat into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $debatechat Submitted data from the form in mod_form.php
 * @param mod_debatechat_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted debatechat record
 */
function debatechat_add_instance(stdClass $debatechat, mod_debatechat_mod_form $mform = null) {
    global $DB;
//print_r($debatechat); die;
    $debatechat->timecreated = time();
    //$debatechat->grade_rescalegrades;
	if($debatechat->advancedgradingmethod_debatechat == 'rubric' ){
	 $debatechat->scale = 100;	
	 $debatechat->grade_debatechat = 100;		 
	}
	//var_dump($debatechat);exit;
   $debatechat->id = $DB->insert_record('debatechat', $debatechat);
	
     return $debatechat->id;
}

/**
     * Lookup this user id and return the unique id for this debatechat.
     *
     * @param int $debatechatid The debatechat id
     * @param int $userid The userid to lookup
     * @return int The unique id
     */
 function get_uniqueid_for_user_static($debatechatid, $userid) {
        global $DB;

        // Search for a record.
        $params = array('debatechat'=>$debatechatid, 'userid'=>$userid);
        if ($record = $DB->get_record('debatechat_user_mapping', $params, 'id')) {
            return $record->id;
        }

        // Be a little smart about this - there is no record for the current user.
        // We should ensure any unallocated ids for the current participant
        // list are distrubited randomly.
      //  self::allocate_unique_ids($debatechatid);

        // Retry the search for a record.
        if ($record = $DB->get_record('debatechat_user_mapping', $params, 'id')) {
            return $record->id;
        }

        // The requested user must not be a participant. Add a record anyway.
        $record = new stdClass();
        $record->debatechat = $debatechatid;
        $record->userid = $userid;

        return $DB->insert_record('debatechat_user_mapping', $record);
    }

/**
 * Updates an instance of the debatechat in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $debatechat An object from the form in mod_form.php
 * @param mod_debatechat_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function debatechat_update_instance(stdClass $debatechat, mod_debatechat_mod_form $mform = null) {
    global $DB;

    $debatechat->timemodified = time();
    $debatechat->id = $debatechat->instance;
	
   $result = $DB->update_record('debatechat', $debatechat);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every debatechat event in the site is checked, else
 * only debatechat events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function debatechat_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$debatechats = $DB->get_records('debatechat')) {
            return true;
        }
    } else {
        if (!$debatechats = $DB->get_records('debatechat', array('course' => $courseid))) {
            return true;
        }
    }

    foreach ($debatechats as $debatechat) {
        // Create a function such as the one below to deal with updating calendar events.
        // debatechat_update_events($debatechat);
    }

    return true;
}

/**
 * Removes an instance of the debatechat from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function debatechat_delete_instance($id) {
    global $DB;

    if (! $debatechat = $DB->get_record('debatechat', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.
    $DB->delete_records('debatechat', array('id' => $debatechat->id));

    return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course The course record
 * @param stdClass $user The user record
 * @param cm_info|stdClass $mod The course module info object or record
 * @param stdClass $debatechat The debatechat instance record
 * @return stdClass|null
 */
function debatechat_user_outline($course, $user, $mod, $debatechat) {

    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * It is supposed to echo directly without returning a value.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $debatechat the module instance record
 */
function debatechat_user_complete($course, $user, $mod, $debatechat) {
	return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in debatechat activities and print it out.
 *
 * @param stdClass $course The course record
 * @param bool $viewfullnames Should we display full names
 * @param int $timestart Print activity since this timestamp
 * @return boolean True if anything was printed, otherwise false
 */
function debatechat_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link debatechat_print_recent_mod_activity()}.
 *
 * Returns void, it adds items into $activities and increases $index.
 *
 * @param array $activities sequentially indexed array of objects with added 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 */
function debatechat_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@link debatechat_get_recent_mod_activity()}
 *
 * @param stdClass $activity activity record with added 'cmid' property
 * @param int $courseid the id of the course we produce the report for
 * @param bool $detail print detailed report
 * @param array $modnames as returned by {@link get_module_types_names()}
 * @param bool $viewfullnames display users' full names
 */
function debatechat_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 *
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * Note that this has been deprecated in favour of scheduled task API.
 *
 * @return boolean
 */
function debatechat_cron () {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * For example, this could be array('moodle/site:accessallgroups') if the
 * module uses that capability.
 *
 * @return array
 */
function debatechat_get_extra_capabilities() {
    return array();
}

/* Gradebook API */
/**
 * Is a given scale used by the instance of debatechat?
 *
 * This function returns if a scale is being used by one debatechat
 * if it has support for grading and scales.
 *
 * @param int $debatechatid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given debatechat instance
 */
function debatechat_scale_used($debatechatid, $scaleid) {
    global $DB;
    if ($scaleid and $DB->record_exists('debatechat', array('id' => $debatechatid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}
/**
 * Checks if scale is being used by any instance of debatechat.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any debatechat instance
 */
/* function debatechat_scale_used_anywhere($scaleid) {
    global $DB;
    if ($scaleid and $DB->record_exists('debatechat', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
} */
function debatechat_scale_used_anywhere(int $scaleid): bool {
    global $DB;

    if (empty($scaleid)) {
        return false;
    }

    return $DB->record_exists_select('debatechat', "scale = ?", [$scaleid * -1]);
}
/**
 * Return grade for given user or all users.
 *
 * @param stdClass $assign record of assign with an additional cmidnumber
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function debatechat_get_user_grades($debatechat, $userid=0) {
    global $CFG,$DB;

    require_once($CFG->dirroot . '/mod/debatechat/locallib.php');

    $cm = get_coursemodule_from_instance('assign', $debatechat->id, 0, false, MUST_EXIST);
    $context = context_module::instance($cm->id);
    $debatechat = new debatechat($context, null, null);
    $debatechat->set_instance($assign);
    return $debatechat->get_user_grades_for_gradebook($userid);
}
 
/**
 * Creates or updates grade item for the given debatechat instance
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $debatechat instance object with extra cmidnumber and modname property
 * @param bool $reset reset grades in the gradebook
 * @return void
 */
 
 
function debatechat_grade_item_update(stdClass $debatechat, $debatechatgrades) { //print_r($debatechat); die;
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
   /*  $item = array();
    $item['itemname'] = clean_param($debatechat->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    if ($debatechat->grade > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax']  = $debatechat->grade;
        $item['grademin']  = 0;
    } else if ($debatechat->grade < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid']   = -$debatechat->grade;
    } else {
        $item['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($reset) {
        $item['reset'] = true;
    }
    grade_update('mod/debatechat', $debatechat->course, 'mod', 'debatechat',
            $debatechat->id, 0, null, $item);
	 */		
     // Whole debatechat grade.
    $item = [
        'itemname' => get_string('gradeitemnameforwholedebatechat', 'debatechat', $debatechat),
        'idnumber' => $debatechat->id
    ];

    if (!$debatechat->grade_debatechat) {
        $item['gradetype'] = GRADE_TYPE_NONE;
    } else if ($debatechat->grade_debatechat > 0) {
        $item['gradetype'] = GRADE_TYPE_VALUE;
        $item['grademax'] = $debatechat->grade_debatechat;
        $item['grademin'] = 0;
    } else if ($debatechat->grade_debatechat < 0) {
        $item['gradetype'] = GRADE_TYPE_SCALE;
        $item['scaleid'] = $debatechat->grade_debatechat * -1;
    }

    if ($debatechatgrades === 'reset') {
        $item['reset'] = true;
        $debatechatgrades = null;
    }
    // Itemnumber 1 is the whole debatechat grade.
    grade_update('mod/debatechat', $debatechat->course, 'mod', 'debatechat', $debatechat->id, 1, $debatechatgrades, $item); 
}
/**
 * Delete grade item for given debatechat instance
 *
 * @param stdClass $debatechat instance object
 * @return grade_item
 */
function debatechat_grade_item_delete($debatechat) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
    return grade_update('mod/debatechat', $debatechat->course, 'mod', 'debatechat',
            $debatechat->id, 0, null, array('deleted' => 1));
}
/**
 * Update debatechat grades in the gradebook
 *
 * Needed by {@link grade_update_mod_grades()}.
 *
 * @param stdClass $debatechat instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 */
function debatechat_update_grades(stdClass $debatechat, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');
    // Populate array of grade objects indexed by userid.
   // $grades = array();
   // grade_update('mod/debatechat', $debatechat->course, 'mod', 'debatechat', $debatechat->id, 0, $grades);
	

    if ($debatechat->grade == 0) {
        debatechat_grade_item_update($debatechat);

    } else if ($grades = debatechat_get_user_grades($debatechat, $userid)) {
        foreach ($grades as $k => $v) {
            if ($v->rawgrade == -1) {
                $grades[$k]->rawgrade = null;
            }
        }
        debatechat_grade_item_update($debatechat, $grades);

    } else {
        debatechat_grade_item_update($debatechat);
    }
	
}

/* File API */

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function debatechat_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for debatechat file areas
 *
 * @package mod_debatechat
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function debatechat_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    return null;
}

/**
 * Serves the files from the debatechat file areas
 *
 * @package mod_debatechat
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the debatechat's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function debatechat_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    require_login($course, true, $cm);

    send_file_not_found();
}

/* Navigation API */

/**
 * Extends the global navigation tree by adding debatechat nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the debatechat module instance
 * @param stdClass $course current course record
 * @param stdClass $module current debatechat instance record
 * @param cm_info $cm course module information
 */
function debatechat_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    // TODO Delete this function and its docblock, or implement it.
}

/**
 * Extends the settings navigation with the debatechat settings
 *
 * This function is called when the context for the page is a debatechat module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $debatechatnode debatechat administration node
 */
function debatechat_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $debatechatnode=null) {
    // TODO Delete this function and its docblock, or implement it.
}
 /**
     * The id used to uniquily identify the cache for this instance of the assign object.
     *
     * @return string
     */
     function get_useridlist_key_id() {
        return $this->useridlistid;
    }

    /**
     * Generates the key that should be used for an entry in the useridlist cache.
     *
     * @param string $id Generate a key for this instance (optional)
     * @return string The key for the id, or new entry if no $id is passed.
     */
    function get_useridlist_key($id = null) {
        if ($id === null) {
            $id = $this->get_useridlist_key_id();
        }
        return $this->get_course_module()->id . '_' . $id;
    }
