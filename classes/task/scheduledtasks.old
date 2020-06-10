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
        global $DB, $CFG;
        require_once($CFG->dirroot.'/lib/blocklib.php');

        $quickcourseadd = array();
        $quickcourseadd['blockname'] = 'quickcourselist';
        $quickcourseadd['showinsubcontexts'] = 0;
        $quickcourseadd['requriedbytheme'] = 0;
        $quickcourseadd['pagetypepattern'] = 'my-index';
        $quickcourseadd['defaultregion'] = 'side-pre';
        $quickcourseadd['defaultweight'] = -5;
        $quickcourseadd['timecreated'] = time();
        $quickcourseadd['timemodified'] = time();


        /* Get all existing My Dashboard pages. *
         * ==================================== */
        $mysql = "SELECT id, userid FROM {my_pages} WHERE private = 1";
        $mypages = $DB->get_records_sql($mysql);
print_r($mypages);

        /* get list of all staff user ids *
         * ============================== */
        // Actually add to all users - block premissions prevent where not allowed.
        foreach ($mypages as $mypage) {
            $userid = $mypage->userid;
            if ($userid > 0) {
                $staffsql = "SELECT * FROM {user} WHERE id = '" . $userid . "'
                    AND deleted = 0";
                if ($DB->record_exists_sql($staffsql)) {
                    $quickcourseadd['subpagepattern'] = $mypage->id;

                    /* For each staff user get contextid *
                    * ================================= */
                    $contextsql = "SELECT * from {context} WHERE contextlevel = 30
                        AND instanceid = " . $userid;
echo $contextsql;
                    $contextid = $DB->get_record_sql($contextsql);
print_r($contextid);
                    if ($contextid->id == null) {continue;}
                    $quickcourseadd['parentcontextid'] = $contextid->id;

                    if (!$DB->record_exists('block_instances',
                        array('blockname'       => $quickcourseadd['blockname'],
                            'parentcontextid' => $quickcourseadd['parentcontextid']))) {
                        $instanceid = $DB->insert_record('block_instances', $quickcourseadd);
                        $quickcourseadd['id'] = $instanceid;
echo $quickcourseadd['id'];
                        // Ensure the block context is created.
                        context_block::instance($instanceid);

                        echo $mypage->userid . " Record added <br>";
                    }
                }
            }
        }

        echo 'Scheduled Task Template';
    }
}
