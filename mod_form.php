<?php
/**
 * The main debatechat configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
use core_grades\component_gradeitems;
/**
 * Module instance settings form
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_debatechat_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE ,$COURSE, $DB;

        //$mform = $this->_form;
		$mform    =& $this->_form;

		$PAGE->requires->js('/mod/debatechat/module.js');
        // Adding the "general" fieldset, where all the common settings are showed.
		
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('debatechatname', 'debatechat'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'debatechatname', 'debatechat');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        // Add a specific mod_debatechat field - title.
        $mform->addElement('text', 'title',get_string('title', 'mod_debatechat'),array('size' => '64'));
        $mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', null, 'required', null, 'client');
		$mform->addRule('title', get_string('maximumchars', '', 100), 'maxlength', 100, 'client');
		$mform->addHelpButton('title', 'title', 'mod_debatechat');
		  // Add a specific mod_debatechat field - prompt.
   /*    $mform->addElement('textarea', 'prompt', get_string('prompt', 'mod_debatechat'),'wrap="virtual" rows="8" cols="20"');
       $mform->setType('prompt', PARAM_TEXT);
	   $mform->addRule('prompt', null, 'required', null, 'client');
	   $mform->addHelpButton('prompt', 'prompt', 'mod_debatechat');
		//$mform->setDefault('prompt', $promptgenerate);
*/
		$mform->addElement('header', 'gradeheading', get_string('gradeheading', 'debatechat'));
		
        // Add standard grading elements.
       
		 $this->add_debatechat_grade_settings($mform, 'debatechat');
		
		// Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
	
	
	
/**
     * Add the whole debatechat grade settings to the mform.
     *
     * @param   \mform $mform
     * @param   string $itemname
     */
    private function add_debatechat_grade_settings($mform, string $itemname) {
        global $COURSE;

        $component = "mod_{$this->_modname}";
        $defaultgradingvalue = 0;
        $itemnumber = component_gradeitems::get_itemnumber_from_itemname($component, $itemname);// echo 'itemnumber ' .$itemnumber; 
      //echo  $itemnumber = 1;
         $gradefieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'grade');
         $gradecatfieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'gradecat');
         $gradepassfieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'gradepass');
         $sendstudentnotificationsfieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber,
                'sendstudentnotifications');

        // The advancedgradingmethod is different in that it is suffixed with an area name... which is not the
        // itemnumber.
        $methodfieldname = "advancedgradingmethod_{$itemname}";
//exit;
        $headername = "{$gradefieldname}_header";
        $mform->addElement('header', $headername, get_string("grade_{$itemname}_header", $component));

        $isupdate = !empty($this->_cm);
        $gradeoptions = [
            'isupdate' => $isupdate,
            'currentgrade' => false,
            'hasgrades' => false,
            'canrescale' => false,
            'useratings' => false,
        ];

        if ($isupdate) {
            $gradeitem = grade_item::fetch([
                'itemtype' => 'mod',
                'itemmodule' => $this->_cm->modname,
                'iteminstance' => $this->_cm->instance,
                'itemnumber' => $itemnumber,
                'courseid' => $COURSE->id,
            ]);
            if ($gradeitem) {
                $gradeoptions['currentgrade'] = $gradeitem->grademax;
                $gradeoptions['currentgradetype'] = $gradeitem->gradetype;
                $gradeoptions['currentscaleid'] = $gradeitem->scaleid;
                $gradeoptions['hasgrades'] = $gradeitem->has_grades();
            }
        }
        $mform->addElement(
            'modgrade',
            $gradefieldname,
            get_string("{$gradefieldname}_title", $component),
            $gradeoptions
        );
        $mform->addHelpButton($gradefieldname, 'modgrade', 'grades');
        $mform->setDefault($gradefieldname, $defaultgradingvalue);

        if (!empty($this->current->_advancedgradingdata['methods']) && !empty($this->current->_advancedgradingdata['areas'])) {
            $areadata = $this->current->_advancedgradingdata['areas'][$itemname];
            $mform->addElement(
                'select',
                $methodfieldname,
                get_string('gradingmethod', 'core_grading'),
                $this->current->_advancedgradingdata['methods']
            );
            $mform->addHelpButton($methodfieldname, 'gradingmethod', 'core_grading');
            $mform->hideIf($methodfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');
        }

        // Grade category.
        $mform->addElement(
            'select',
            $gradecatfieldname,
            get_string('gradecategoryonmodform', 'grades'),
            grade_get_categories_menu($COURSE->id, $this->_outcomesused)
        );
        $mform->addHelpButton($gradecatfieldname, 'gradecategoryonmodform', 'grades');
        $mform->hideIf($gradecatfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');

        // Grade to pass.
        $mform->addElement('text', $gradepassfieldname, get_string('gradepass', 'grades'));
        $mform->addHelpButton($gradepassfieldname, 'gradepass', 'grades');
        $mform->setDefault($gradepassfieldname, '');
        $mform->setType($gradepassfieldname, PARAM_RAW);
        $mform->hideIf($gradepassfieldname, "{$gradefieldname}[modgrade_type]", 'eq', 'none');

    }

	 public function validation($data, $files) {
        $errors = parent::validation($data, $files);
		$this->validation_debatechat_grade($data, $files, $errors);

        return $errors;
    }	

	 /**
     * Handle definition after data for grade settings.
     *
     * @param array $data
     * @param array $files
     * @param array $errors
     */
    private function validation_debatechat_grade(array $data, array $files, array $errors) {
        global $COURSE;

        $mform =& $this->_form;

        $component = "mod_debatechat";
        $itemname = 'debatechat';
        $itemnumber = component_gradeitems::get_itemnumber_from_itemname($component, $itemname);
        $gradefieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'grade');
        $gradepassfieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'grade');

        $gradeitem = grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => $data['modulename'],
            'iteminstance' => $data['instance'],
            'itemnumber' => $itemnumber,
            'courseid' => $COURSE->id,
        ]);

        if ($mform->elementExists('cmidnumber') && $this->_cm) {
            if (!grade_verify_idnumber($data['cmidnumber'], $COURSE->id, $gradeitem, $this->_cm)) {
                $errors['cmidnumber'] = get_string('idnumbertaken');
            }
        }

        // Check that the grade pass is a valid number.
        $gradepassvalid = false;
        if (isset($data[$gradepassfieldname])) {
            if (unformat_float($data[$gradepassfieldname], true) === false) {
                $errors[$gradepassfieldname] = get_string('err_numeric', 'form');
            } else {
                $gradepassvalid = true;
            }
        }

        // Grade to pass: ensure that the grade to pass is valid for points and scales.
        // If we are working with a scale, convert into a positive number for validation.
        if ($gradepassvalid && isset($data[$gradepassfieldname]) && (!empty($data[$gradefieldname]))) {
            $grade = $data[$gradefieldname];
            if (unformat_float($data[$gradepassfieldname]) > $grade) {
                $errors[$gradepassfieldname] = get_string('gradepassgreaterthangrade', 'grades', $grade);
            }
        }
    }
	 
	 public function get_data() {
        $data = parent::get_data();
        if ($data) {
            $itemname = 'debatechat';
            $component = 'mod_debatechat';
            $gradepassfieldname = component_gradeitems::get_field_name_for_itemname($component, $itemname, 'gradepass');

            // Convert the grade pass value - we may be using a language which uses commas,
            // rather than decimal points, in numbers. These need to be converted so that
            // they can be added to the DB.
            if (isset($data->{$gradepassfieldname})) {
                $data->{$gradepassfieldname} = unformat_float($data->{$gradepassfieldname});
            }
        }

        return $data;
    }

}



