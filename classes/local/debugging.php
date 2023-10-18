<?php
/**
 * Simple debugging class
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debatechat\local;

defined('MOODLE_INTERNAL') || die();

class debugging {
    public static function logit($message, $value) {

        $file = fopen('mylog.log', 'a');

        if ($file) {
            fwrite($file, print_r($message, true));
            fwrite($file, print_r($value, true));
            fwrite($file, "\n");
            fclose($file);
        }
    }
}