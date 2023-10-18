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
        global $CFG;

        $mform = $this->_form;

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
		$mform->addHelpButton('title', 'title', 'mod_debatechat');
		
		  // Add a specific mod_debatechat field - prompt.
        $mform->addElement('text', 'prompt', get_string('prompt', 'mod_debatechat'),array('size' => '64'));
        $mform->setType('prompt', PARAM_TEXT);

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}
