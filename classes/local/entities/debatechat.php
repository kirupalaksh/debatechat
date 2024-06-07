<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * debatechat class.
 *
 * @package    mod_debatechat
 * @copyright  2019 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debatechat\local\entities;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/debatechat/lib.php');
//require_once($CFG->dirroot . '/rating/lib.php');

//use mod_debatechat\local\entities\discussion as discussion_entity;
use context;
use stdClass;

/**
 * debatechat class.
 *
 * @copyright  2019 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debatechat {
    /** @var context $context The debatechat module context */
    private $context;
    /** @var stdClass $coursemodule The debatechat course module record */
    private $coursemodule;
    /** @var stdClass $course The debatechat course record */
    private $course;
    /** @var int $effectivegroupmode The effective group mode */
    private $effectivegroupmode;
    /** @var int $id ID */
    private $id;
    /** @var int $courseid Id of the course this debatechat is in */
    private $courseid;
    /** @var string $name Name of the debatechat */
    private $name;
    /** @var string $intro Intro text */
    private $intro;
    /** @var int $introformat Format of the intro text */
    private $introformat;
    /** @var int $assessed The debatechat rating aggregate */
   /** @var int $scale The rating scale */
    private $scale;
    /** @var int $gradedebatechat The grade for the debatechat when grading holistically */
    private $gradedebatechat;
    /** @var int $timemodified Timestamp when the debatechat was last modified */
    private $timemodified;
    /** @var int $warnafter Warn after */
    

    /**
     * Constructor
     *
     * @param context $context The debatechat module context
     * @param stdClass $coursemodule The debatechat course module record
     * @param stdClass $course The debatechat course record
     * @param int $effectivegroupmode The effective group mode
     * @param int $id ID
     * @param int $courseid Id of the course this debatechat is in
     * @param string $type The debatechat type, e.g. single, qanda, etc
     * @param string $name Name of the debatechat
     * @param string $intro Intro text
     * @param int $introformat Format of the intro text
     * @param int $assessed The debatechat rating aggregate
     * @param int $assesstimestart Timestamp to begin assessment
     * @param int $assesstimefinish Timestamp to end assessment
     * @param int $scale The rating scale
     * @param int $gradedebatechat The holistic grade
     * @param bool $gradedebatechatnotify Default for whether to notify students when grade holistically
     * @param int $maxbytes Maximum attachment size
     * @param int $maxattachments Maximum number of attachments
     * @param int $forcesubscribe Does the debatechat force users to subscribe?
     * @param int $trackingtype Tracking type
     * @param int $rsstype RSS type
     * @param int $rssarticles RSS articles
     * @param int $timemodified Timestamp when the debatechat was last modified
     * @param int $warnafter Warn after
     * @param int $blockafter Block after
     * @param int $blockperiod Block period
     * @param int $completiondiscussions Completion discussions
     * @param int $completionreplies Completion replies
     * @param int $completionposts Completion posts
     * @param bool $displaywordcount Should display word counts in posts
     * @param int $lockdiscussionafter Timestamp after which discussions should be locked
     * @param int $duedate Timestamp that represents the due date for debatechat posts
     * @param int $cutoffdate Timestamp after which debatechat posts will no longer be accepted
     */
    public function __construct(
        context $context,
        stdClass $coursemodule,
        stdClass $course,
        int $effectivegroupmode,
        int $id,
        int $courseid,
        string $name,
        string $intro,
		string $title,
		string $prompt,
        int $scale,
        int $gradedebatechat,
        int $timemodified       
    ) {
        $this->context = $context;
        $this->coursemodule = $coursemodule;
        $this->course = $course;
        $this->effectivegroupmode = $effectivegroupmode;
        $this->id = $id;
        $this->courseid = $courseid;
        $this->name = $name;
        $this->intro = $intro;
        $this->introformat = $introformat;
        $this->title = $title;
        $this->prompt = $prompt;
        $this->scale = $scale;
        $this->gradedebatechat = $gradedebatechat;
        $this->timemodified = $timemodified;
    }

    /**
     * Get the debatechat module context.
     *
     * @return context
     */
    public function get_context() : context {
        return $this->context;
    }

    /**
     * Get the debatechat course module record
     *
     * @return stdClass
     */
    public function get_course_module_record() : stdClass {
        return $this->coursemodule;
    }

    /**
     * Get the effective group mode.
     *
     * @return int
     */
    public function get_effective_group_mode() : int {
        return $this->effectivegroupmode;
    }

    /**
     * Check if the debatechat is set to group mode.
     *
     * @return bool
     */
    public function is_in_group_mode() : bool {
        return $this->get_effective_group_mode() !== NOGROUPS;
    }

    /**
     * Get the course record.
     *
     * @return stdClass
     */
    public function get_course_record() : stdClass {
        return $this->course;
    }

    /**
     * Get the debatechat id.
     *
     * @return int
     */
    public function get_id() : int {
        return $this->id;
    }

    /**
     * Get the id of the course that the debatechat belongs to.
     *
     * @return int
     */
    public function get_course_id() : int {
        return $this->courseid;
    }

 
    /**
     * Get the debatechat name.
     *
     * @return string
     */
    public function get_name() : string {
        return $this->name;
    }

    /**
     * Get the debatechat intro text.
     *
     * @return string
     */
    public function get_intro() : string {
        return $this->intro;
    }

    /**
     * Get the debatechat intro text format.
     *
     * @return int
     */
    public function get_intro_format() : int {
        return $this->introformat;
    }

    /**
     * Get the debatechat title.
     *
     * @return string
     */
    public function get_title() : string {
        return $this->title;
    }
	/**
     * Get the debatechat prompt.
     *
     * @return string
     */
    public function get_prompt() : string {
        return $this->prompt;
    }

    /**
     * Get the rating scale.
     *
     * @return int
     */
    public function get_scale() : int {
        return $this->scale;
    }

    /**
     * Get the grade for the debatechat when grading holistically.
     *
     * @return int
     */
    public function get_grade_for_debatechat() : int {
        return $this->gradedebatechat;
    }

    /**
     * Whether grading is enabled for this item.
     *
     * @return bool
     */
    public function is_grading_enabled(): bool {
        return $this->get_grade_for_debatechat() !== 0;
    }

   

    /**
     * Get the timestamp for when the debatechat was last modified.
     *
     * @return int
     */
    public function get_time_modified() : int {
        return $this->timemodified;
    }
   
}
