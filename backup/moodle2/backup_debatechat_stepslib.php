<?php
/**
 * Define all the backup steps that will be used by the backup_debatechat_activity_task
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2019 Richard Jones richardnz@outlook.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete debatechat structure for backup, with file and id annotations
 *
* @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_debatechat_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define the root element describing the debatechat instance.
        $debatechat = new backup_nested_element('debatechat',
                array('id'), array('course', 'name', 'intro',
                'introformat', 'title','prompt', 'timecreated',
                'timemodified'));

        // If we had more elements, we would build the tree here.

        // Define data sources.
        $debatechat->set_source_table('debatechat', array('id' => backup::VAR_ACTIVITYID));

        // If we were referring to other tables, we would annotate the relation
        // with the element's annotate_ids() method.

        // Define file annotations (we do not use itemid in this example).
        $debatechat->annotate_files('mod_debatechat', 'intro', null);

        // Return the root element (debatechat), wrapped into standard activity structure.
        return $this->prepare_activity_structure($debatechat);
    }
}
