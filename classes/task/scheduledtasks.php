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
 * A scheduled task for scripted database integrations.
 *
 * @package    local_scheduledtask - template
 * @copyright  2016 ROelmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_scheduledtasks\task;
use stdClass;
use context_block;

defined('MOODLE_INTERNAL') || die;

/**
 * A scheduled task for scripted database integrations.
 *
 * @copyright  2016 ROelmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scheduledtasks extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('scheduledtasks', 'local_scheduledtasks');
    }

    /**
     * Run sync.
     */
    public function execute() {
        /* Overwrite students given module editor role with Other Student role.
         * ==================================================================== */
        global $DB;
        $othstu = $DB->get_record('role', array('shortname' => 'otherstudent'), 'id');
        $moded = $DB->get_record('role', array('shortname' => 'editor'), 'id');

        $editor = $DB->get_records('role_assignments', array('roleid' => $moded->id));
        foreach ($editor as $e) {
            $s = $DB->get_record('user', array('id' => $e->userid), '*');
            if (strpos($s->email, 'connect.glos.ac.uk') !== false) {
                $e->roleid = $othstu->id;
                $DB->update_record('role_assignments', $e);
            }
        }
        echo 'Overwrite Student-ModEditor';
        /* ===================================================================== */

        /* Hide External Examiner Only section
         * =================================== */
        $extexamstr = strtolower(get_string('externalexaminer', 'local_scheduledtasks'));
        $sections = $DB->get_records_sql("SELECT * FROM {course_sections}
                                        WHERE LOWER(`name`) LIKE '%" . $extexamstr . "%'
                                        AND `visible` = 1;");
        foreach ($sections as $s) {
            $s->visible = 0;
            $DB->update_record('course_sections', $s);
        }
        echo 'Hide all External Examiner Sections';
        /* ===================================================================== */

        /* Remove 18/19 Link codes from 19/20 modules
         * ========================================== */

        $linksql = "SELECT cm.* FROM {course_modules} cm
                        JOIN {course} c on c.id = cm.course
                    WHERE cm.idnumber LIKE '%2018/19%'
                    AND c.idnumber LIKE '%2019/20%';";
        $links = $DB->get_records_sql($linksql);
        foreach ($links as $l) {
            $l->idnumber = '';
            $DB->update_record('course_modules', $l);
        }
        echo 'Remove 18/19 link codes from 19/20 modules';

    }
}
