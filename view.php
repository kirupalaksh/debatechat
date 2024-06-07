<?php
/**
 * Prints a particular instance of debatechat
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later */

use mod_debatechat\output\view;
require_once('../../config.php');
require_once(dirname(__FILE__).'/lib.php');

// We need the course module id (id) or
// the debatechat instance id (n).
$id = optional_param('id', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);
global $course;
if ($id) {
    $cm = get_coursemodule_from_id('debatechat', $id, 0, false,
            MUST_EXIST);
    $course = $DB->get_record('course',
            array('id' => $cm->course), '*', MUST_EXIST);
    $debatechat = $DB->get_record('debatechat',
            array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $debatechat = $DB->get_record('debatechat', array('id' => $n), '*',
            MUST_EXIST);
    $course = $DB->get_record('course',
            array('id' => $debatechat->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('debatechat', $debatechat->id,
            $course->id, false, MUST_EXIST);
}
// Print the page header.
$PAGE->set_url('/mod/debatechat/view.php', array('id' => $cm->id));
require_login($course, true, $cm);

$PAGE->set_title(format_string($debatechat->name));
$PAGE->set_heading($course->fullname.$debatechat->name);

// Check for intro page content.
if (!$debatechat->intro) {
    $debatechat->intro = '';
}
// Start output to browser.
echo $OUTPUT->header();


// Call classes/output/view and view.mustache to create output.
echo $OUTPUT->render(new view($debatechat, $cm->id));

// End output to browser.
echo $OUTPUT->footer();