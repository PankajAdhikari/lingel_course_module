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
 * Block for displayed course modules in that course along with course module id ...
 * The format would be <cmid>-<activity name>-<date of creation in d-M-Y format>-<completion-status>
 *
 * @package    block_lingel_course_modules
 * @copyright  2022 Lingel Learning India Pvt. Ltd.
 * @author     Lingel Learning India Pvt. Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_completion\privacy\provider;

class block_lingel_course_modules extends block_base {

    public function init() {
        $this->title = get_string('blocktitle', 'block_lingel_course_modules');
    }

    public function applicable_formats() {
        return array('course' => true);
    }

    public function get_content() {
        global $USER;
        $content = '';
        // If content is cached.
        if ($this->content !== null) {
            return $this->content;
        }

        $course = $this->page->course;
        $modinfo = get_fast_modinfo($course->id);
        $sections = $modinfo->get_section_info_all();
        $modinfosections = $modinfo->get_sections();

        foreach ($sections as $key => $section) {
            if (!$section->visible ) {
                continue;
            }
            // For each module of the section.
            if (!empty($modinfosections[$section->section])) {
                foreach ($modinfosections[$section->section] as $cmid) {
                    $cm = $modinfo->cms[$cmid];

                    if (!$cm->visible && !$cm->deletioninprogress) {
                        continue;
                    }

                    // Completion tracking.
                    $completiondata = provider::get_activity_completion_info($USER, $course, $cm);
                    // Labels don't have completion state.
                    $completionstate = '';
                    if (isset($completiondata->completionstate) && $completiondata->completionstate) {
                        $completionstate = " - <span class='alert-success'>" . get_string('completed') . "</span>";
                    }

                    $activitylink = $cm->get_formatted_name();
                    // Labels don't have url.
                    if ($cm->url) {
                        $moduleurl = $cm->url->out();
                        $activitylink = html_writer::link($moduleurl, $cm->get_formatted_name());
                    }
                    $creation = userdate(
                        $cm->added,
                        get_string('strftimedatefullshort', 'block_lingel_course_modules')
                    );
                    $content .= "<h5>{$cmid} - {$activitylink} - {$creation} {$completionstate}</h5>";
                }
            }
        }

        $this->content->text = $content;

        return $this->content;
    }
}
