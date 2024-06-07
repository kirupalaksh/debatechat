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
 * Forum data mapper.
 *
 * @package    mod_debatechat
 * @copyright  2019 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_debatechat\local\data_mappers\legacy;

defined('MOODLE_INTERNAL') || die();

use mod_debatechat\local\entities\debatechat as debatechat_entity;
use stdClass;

/**
 * Convert a debatechat entity into an stdClass.
 *
 * @copyright  2019 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class debatechat {
    /**
     * Convert a list of debatechat entities into stdClasses.
     *
     * @param debatechat_entity[] $debatechats The debatechats to convert.
     * @return stdClass[]
     */
    public function to_legacy_objects(array $debatechats) : array {
        return array_map(function(debatechat_entity $debatechat) {
            return (object) [
	            'id' => $debatechat->get_id(),
                'course' => $debatechat->get_course_id(),
                'name' => $debatechat->get_name(),
				'intro' => $debatechat->get_intro(),
                'introformat' => $debatechat->get_intro_format(),
                'title' => $debatechat->get_title(),
                'prompt' => $debatechat->get_prompt(),                
                'scale' => $debatechat->get_scale(),				
                'grade_debatechat' => $debatechat->get_grade_for_debatechat(),
				'timemodified' => $forum->get_time_modified()	
            ];
        }, $debatechats);
    }

    /**
     * Convert a debatechat entity into an stdClass.
     *
     * @param debatechat_entity $debatechat The debatechat to convert.
     * @return stdClass
     */
    public function to_legacy_object(debatechat_entity $debatechat) : stdClass {
        return $this->to_legacy_objects([$debatechat])[0];
    }
}
