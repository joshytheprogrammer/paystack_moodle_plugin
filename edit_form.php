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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once('lib.php');

class enrol_paystack_edit_form extends moodleform {
    /**
     * Defines the form's elements.
     */
    public function definition() {
        $mform = $this->_form; // Form object for easy access.

        // Retrieve instance, plugin, and context data passed to the form.
        list($instance, $plugin, $context) = $this->_customdata;

        // Add a header to the form.
        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_paystack'));

        // Add a field for setting a custom instance name.
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        // Enable/disable dropdown.
        $options = array(
            ENROL_INSTANCE_ENABLED  => get_string('yes'),
            ENROL_INSTANCE_DISABLED => get_string('no')
        );
        $mform->addElement('select', 'status', get_string('status', 'enrol_paystack'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));

        // Cost input field.
        $mform->addElement('text', 'cost', get_string('cost', 'enrol_paystack'), array('size' => 4));
        $mform->setType('cost', PARAM_RAW); // We will manually handle the format.
        $mform->setDefault('cost', format_float($plugin->get_config('cost'), 2, true));

        // Currency selection.
        $paystackcurrencies = $plugin->get_currencies();
        $mform->addElement('select', 'currency', get_string('currency', 'enrol_paystack'), $paystackcurrencies);
        $mform->setDefault('currency', $plugin->get_config('currency'));

        // Role assignment.
        $roles = $instance->id ? get_default_enrol_roles($context, $instance->roleid) : get_default_enrol_roles($context, $plugin->get_config('roleid'));
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_paystack'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));

        // Maximum number of enrolled users.
        $mform->addElement('text', 'customint3', get_string('maxenrolled', 'enrol_paystack'));
        $mform->setType('customint3', PARAM_INT);
        $mform->setDefault('customint3', $plugin->get_config('maxenrolled'));
        $mform->addHelpButton('customint3', 'maxenrolled', 'enrol_paystack');

        // Enrolment duration setting.
        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_paystack'), array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_paystack');

        // Enrolment start and end dates.
        $this->setupDateSelector($mform, 'enrolstartdate', 'enrolstartdate');
        $this->setupDateSelector($mform, 'enrolenddate', 'enrolenddate');

        // Hidden elements.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        // Warning for self-enrolment instances.
        if (enrol_accessing_via_instance($instance)) {
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        // Add action buttons conditionally.
        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        // Set form data.
        $this->set_data($instance);
    }

    /**
     * Adds date/time selectors for start and end dates.
     * 
     * @param MoodleQuickForm $mform The form instance
     * @param string $name The element name
     * @param string $stringKey The language string key
     */
    private function setupDateSelector($mform, $name, $stringKey) {
        $mform->addElement('date_time_selector', $name, get_string($stringKey, 'enrol_paystack'), array('optional' => true));
        $mform->setDefault($name, 0);
        $mform->addHelpButton($name, $stringKey, 'enrol_paystack');
    }

    /**
     * Validates form input, ensuring the enrolment end date is after the start date and cost is numeric.
     *
     * @param stdClass $data Form data
    * @param stdClass $files File data
    * @return array List of errors
        */
    public function validation($data, $files) {
        global $CFG;
        $errors = parent::validation($data, $files);

        // Retrieve instance, plugin, and context data passed to the form.
        list($instance, $plugin, $context) = $this->_customdata;

        // Validate enrolment end date.
        if (!empty($data['enrolenddate']) && $data['enrolenddate'] < $data['enrolstartdate']) {
            $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_paystack');
        }

        // Validate cost as numeric.
        $cost = str_replace(get_string('decsep', 'langconfig'), '.', $data['cost']);
        if (!is_numeric($cost)) {
            $errors['cost'] = get_string('costerror', 'enrol_paystack');
        }

        return $errors;
    }
}