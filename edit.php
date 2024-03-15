<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     enrol_paystack
 * @copyright   2024 joshytheprogrammer <studymay.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require('../../config.php');
require_once('edit_form.php');

// Retrieve course and instance IDs from request parameters.
$courseId   = required_param('courseid', PARAM_INT);
$instanceId = optional_param('id', 0, PARAM_INT); // Instance ID.

// Retrieve course details and context.
$course = $DB->get_record('course', array('id' => $courseId), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Check user login and capabilities.
require_login($course);
require_capability('enrol/paystack:config', $context);

// Set page URL and layout.
$PAGE->set_url('/enrol/paystack/edit.php', array('courseid' => $course->id, 'id' => $instanceId));
$PAGE->set_pagelayout('admin');

// Define return URL.
$returnUrl = new moodle_url('/enrol/instances.php', array('id' => $course->id));

// Redirect if Paystack enrolment is not enabled for the site.
if (!enrol_is_enabled('paystack')) {
    redirect($returnUrl);
}

// Get Paystack enrolment plugin instance.
$plugin = enrol_get_plugin('paystack');

// Initialize instance for editing or add new instance.
if ($instanceId) {
    $instance = $DB->get_record('enrol',
        array('courseid' => $course->id, 'enrol' => 'paystack', 'id' => $instanceId), '*', MUST_EXIST);
    $instance->cost = format_float($instance->cost, 2, true);
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // No instance yet, create a new one.
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id' => $course->id)));
    $instance = new stdClass();
    $instance->id       = null;
    $instance->courseid = $course->id;
}

// Initialize Paystack enrolment edit form.
$enrolForm = new enrol_paystack_edit_form(null, array($instance, $plugin, $context));

// Process form submission.
if ($enrolForm->is_cancelled()) {
    redirect($returnUrl);
} else if ($formData = $enrolForm->get_data()) {
    if ($instance->id) {
        // Update existing instance.
        $reset = ($instance->status != $formData->status);

        $instance->status         = $formData->status;
        $instance->name           = $formData->name;
        $instance->cost           = unformat_float($formData->cost);
        $instance->currency       = $formData->currency;
        $instance->roleid         = $formData->roleid;
        $instance->customint3     = $formData->customint3;
        $instance->enrolperiod    = $formData->enrolperiod;
        $instance->enrolstartdate = $formData->enrolstartdate;
        $instance->enrolenddate   = $formData->enrolenddate;
        $instance->timemodified   = time();
        $DB->update_record('enrol', $instance);

        // Mark context as dirty if status changed.
        if ($reset) {
            $context->mark_dirty();
        }

    } else {
        // Add new instance.
        $fields = array('status' => $formData->status,
                        'name' => $formData->name,
                        'cost' => unformat_float($formData->cost),
                        'currency' => $formData->currency,
                        'roleid' => $formData->roleid,
                        'enrolperiod' => $formData->enrolperiod,
                        'customint3' => $formData->customint3,
                        'enrolstartdate' => $formData->enrolstartdate,
                        'enrolenddate' => $formData->enrolenddate
                    );
        $plugin->add_instance($course, $fields);
    }

    // Redirect to the return URL.
    redirect($returnUrl);
}

// Set page heading and title.
$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_paystack'));

// Display form.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_paystack'));
$enrolForm->display();
echo $OUTPUT->footer();
