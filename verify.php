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

 // Disable Moodle-specific debug messages and any errors in output.
// Uncomment when debugging or better look into the error log!
// define('NO_DEBUG_DISPLAY', true);

require('../../config.php');
require_once('lib.php');

// Include necessary libraries based on Moodle version.
if ($CFG->version < 2018101900) {
    require_once($CFG->libdir . '/eventslib.php');
}
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->libdir . '/filelib.php');

require_login();

// Set exception handler for Paystack.
set_exception_handler(array('enrol_paystack\util', 'get_exception_handler'));

// Make sure the Paystack plugin is enabled.
if (!enrol_is_enabled('paystack')) {
    http_response_code(503);
    throw new moodle_exception('errdisabled', 'enrol_paystack');
}

// Keep out casual intruders.
if (empty($_POST) || !empty($_GET)) {
    http_response_code(400);
    throw new moodle_exception('invalidrequest', 'core_error');
}
if (empty(required_param('paystack-trxref', PARAM_RAW))) {
    print_error(get_string('paystack_sorry', 'enrol_paystack'));
}

// Process POST data securely.
$data = new stdClass();
foreach ($_POST as $key => $value) {
    if ($key !== clean_param($key, PARAM_ALPHANUMEXT)) {
        throw new moodle_exception('invalidrequest', 'core_error', '', null, $key);
    }
    if (is_array($value)) {
        throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Unexpected array param: ' . $key);
    }
    $data->$key = fix_utf8($value);
}

// Validate custom parameter.
if (empty($data->custom)) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Missing request param: custom');
}
$custom = explode('-', $data->custom);
unset($data->custom);
if (empty($custom) || count($custom) < 3) {
    throw new moodle_exception('invalidrequest', 'core_error', '', null, 'Invalid value of the request param: custom');
}

// Extract required data from custom parameter.
$data->userid           = (int) $custom[0];
$data->courseid         = (int) $custom[1];
$data->instanceid       = (int) $custom[2];
$data->payment_gross    = $data->amount;
$data->payment_currency = $data->currency_code;
$data->timeupdated      = time();

// Get user, course, and plugin instance records.
$user = $DB->get_record("user", array("id" => $data->userid), "*", MUST_EXIST);
$course = $DB->get_record("course", array("id" => $data->courseid), "*", MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Set the context.
$PAGE->set_context($context);

// Use the queried course's full name for the item_name field.
$data->item_name = $course->fullname;

// Get the Paystack plugin instance.
$plugin_instance = $DB->get_record("enrol", array("id" => $data->instanceid, "enrol" => "paystack", "status" => 0), "*", MUST_EXIST);
$plugin = enrol_get_plugin('paystack');
$paystack = new \enrol_paystack\paystack('moodle-enrol', $plugin->get_publickey(), $plugin->get_secretkey());

// Set Course and Paystack URL.
$courseUrl = "$CFG->wwwroot/course/view.php?id=$course->id";

// Verify the transaction.
$res = $paystack->verify_transaction($data->reference);

// Handle transaction verification response.
if (!$res['status']) {
    notice($res['message'], $courseUrl);
}

// Proceed with enrollment.
$data->tax = $res['data']['amount'] / 100;
$data->memo = $res['data']['gateway_response'];
$data->payment_status = $res['data']['status'];
// $data->reason_code = $code;

// Verify currency matches course settings.
if ($data->currency_code != $plugin_instance->currency) {
    $message = "Currency does not match course settings, received: " . $data->currency_code;
    \enrol_paystack\util::message_paystack_error_to_admin($message, $data);
    notice($message, $courseUrl);
}

// Check if the amount paid is correct.
if ((float) $plugin_instance->cost <= 0) {
    $cost = (float) $plugin->get_config('cost');
} else {
    $cost = (float) $plugin_instance->cost;
}

// Use the same rounding of floats as on the enrol form.
$cost = format_float($cost, 2, false);

// If cost is greater than payment_gross, then someone may be trying to cheat the system.
if ($data->payment_gross < $cost) {
    $message = "Amount paid is not enough ($data->payment_gross < $cost)";
    \enrol_paystack\util::message_paystack_error_to_admin($message, $data);
    notice($message, $courseUrl);
}

$fullname = format_string($course->fullname, true, array('context' => $context));

// Enroll user if not already enrolled.
if (is_enrolled($context, null, '', true)) {
    redirect($courseUrl, get_string('paymentthanks', '', $fullname));
}

if ($data->payment_status == 'success') {
    // Log transaction success.
    $paystack->log_transaction_success($data->reference);
    $DB->insert_record("enrol_paystack", $data);

    // Set enrolment period.
    if ($plugin_instance->enrolperiod) {
        $timestart = time();
        $timeend   = $timestart + $plugin_instance->enrolperiod;
    } else {
        $timestart = 0;
        $timeend   = 0;
    }

    // Enroll user.
    $plugin->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);

    // Send notification messages.
    $mailstudents = $plugin->get_config('mailstudents');
    $mailteachers = $plugin->get_config('mailteachers');
    $mailadmins   = $plugin->get_config('mailadmins');
    $shortname = format_string($course->shortname, true, array('context' => $context));

    // Notify students.
    if (!empty($mailstudents)) {
        $a = new stdClass();
        $a->course = format_string($course->fullname, true, array('context' => $context));
        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";
        $eventdata = new \core\message\message();
        $eventdata->modulename        = 'moodle';
        $eventdata->component         = 'enrol_paystack';
        $eventdata->name              = 'paystack_enrolment';
        $eventdata->userfrom          = empty($teacher) ? core_user::get_support_user() : $teacher;
        $eventdata->userto            = $user;
        $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
        $eventdata->fullmessage       = get_string('welcometocoursetext', '', $a);
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';
        message_send($eventdata);
    }

    // Notify teachers.
    if (!empty($mailteachers) && !empty($teacher)) {
        $a = new stdClass();
        $a->course = format_string($course->fullname, true, array('context' => $context));
        $a->user = fullname($user);
        $eventdata = new \core\message\message();
        $eventdata->modulename        = 'moodle';
        $eventdata->component         = 'enrol_paystack';
        $eventdata->name              = 'paystack_enrolment';
        $eventdata->userfrom          = $user;
        $eventdata->userto            = $teacher;
        $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
        $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';
        message_send($eventdata);
    }

    // Notify admins.
    if (!empty($mailadmins)) {
        $a = new stdClass();
        $a->course = format_string($course->fullname, true, array('context' => $context));
        $a->user = fullname($user);
        $admins = get_admins();
        foreach ($admins as $admin) {
            $eventdata = new \core\message\message();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_paystack';
            $eventdata->name              = 'paystack_enrolment';
            $eventdata->userfrom          = $user;
            $eventdata->userto            = $admin;
            $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);
        }
    }
} else {
    // Handle payment failure.
    $message = "Payment status not successful" . $data->memo;
    \enrol_paystack\util::message_paystack_error_to_admin($message, $data);
    notice($message, $courseUrl);
}

if (is_enrolled($context, null, '', true)) {
    redirect($courseUrl, get_string('paymentthanks', '', $fullname));
} else {   // Somehow they aren't enrolled yet!
    $PAGE->set_url($courseUrl);
    echo $OUTPUT->header();
    $a = new stdClass();
    $a->teacher = get_string('defaultcourseteacher');
    $a->fullname = $fullname;
    notice(get_string('paymentsorry', '', $a), $courseUrl);
}
