<?php
/**
 * Prints a particular instance of debatechat
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debatechat\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Simplemod: Create a new view page renderable object
 *
 * @param string title - intro page title.
 * @param int height - course module id.
 */

class view implements renderable, templatable {

    protected $debatechat;
    protected $id;

    public function __construct($debatechat, $id) {

        $this->debatechat = $debatechat;
        $this->id = $id;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
		global $COURSE,$USER;
        $data = new stdClass();

        $data->intro = strip_tags($this->debatechat->intro);
        $data->title = $this->debatechat->title;
        $data->prompt = $this->debatechat->prompt;
        $data->courseid = $this->debatechat->course;
        $data->fullname = $COURSE->fullname;
        $data->userid = $USER->id;
        $data->username = fullname($USER);
        $data->debatechatid = $this->id;
        // Moodle handles processing of std intro field.
        $data->body = format_module_intro('debatechat',
                $this->debatechat, $this->id);
       $data->message = get_string('welcome', 'mod_debatechat');

        return $data;
    }
}
