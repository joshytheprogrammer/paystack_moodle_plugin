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
 * Plugin strings are defined here.
 *
 * @package     enrol_paystack
 * @category    string
 * @copyright   2024 joshytheprogrammer <studymay.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

 // Texts for the Paystack enrolment plugin, simplified for user understanding.
 $string['addtogroup'] = 'Add to a group';
 $string['addtogroup_help'] = 'Choose a group to automatically add users into it after they pay and enroll in the course.';
 $string['assignrole'] = 'Assign a role';
 $string['assignrole_help'] = 'Select a role to be automatically assigned to users after they pay and enroll in the course.';
 $string['btntext'] = 'Pay Now'; // Button text for initiating payment
 $string['billingaddress'] = 'Ask for billing address';
 $string['billingaddress_desc'] = 'If enabled, users will have to provide their billing address during payment. Off by default, but turning it on is recommended for security.';
 $string['cost'] = 'Course fee';
 $string['costerror'] = 'The course fee entered is not a valid number';
 $string['costorkey'] = 'Choose a payment method.';
 $string['currency'] = 'Currency'; // Currency for course fee
 $string['customfields'] = 'User profile fields as custom fields';
 $string['customfields_desc'] = 'Select which user profile fields are used during enrolment for additional information.';
 $string['customwelcomemessage'] = 'Custom welcome message';
 $string['customwelcomemessage_help'] = 'A custom message that replaces the standard enrolment information on the course access page. Leave blank to use the default message.';
 $string['defaultrole'] = 'Default role assignment';
 $string['defaultrole_desc'] = 'Choose a default role to assign to users who enrol through Paystack.';
 $string['enrolenddate'] = 'End date';
 $string['enrolenddate_help'] = 'If set, users can only enrol up until this date.';
 $string['enrolenddaterror'] = 'Enrolment end date cannot be before the start date';
 $string['enrolmentnew'] = 'New Enrolment';
 $string['enrolmentnewuser'] = 'New User Enrolment';
 $string['enrolperiod'] = 'Enrolment duration';
 $string['enrolperiod_desc'] = 'The default duration that the enrolment is valid for. Zero means unlimited.';
 $string['enrolperiod_help'] = 'The duration of enrolment from the time of enrolment. Unlimited if disabled.';
 $string['enrolstartdate'] = 'Start date';
 $string['enrolstartdate_help'] = 'If set, users can start enrolling from this date onwards.';
 $string['expiredaction'] = 'Enrolment expiration action';
 $string['expiredaction_help'] = 'Choose what happens when a user\'s enrolment expires. Note: Some user data and settings are cleared from the course upon unenrolment.';
 $string['live_secretkey'] = 'Live Secret Key';
 $string['live_publickey'] = 'Live Public Key';
 $string['live_secretkey_desc'] = 'Your Paystack account\'s live secret key for processing real payments.';
 $string['live_publickey_desc'] = 'Your Paystack account\'s live public key for processing real payments.';
 $string['mailadmins'] = 'Notify admin'; // Notify the site admin of enrolments
 $string['mailstudents'] = 'Notify students'; // Notify students upon enrolment
 $string['mailteachers'] = 'Notify teachers'; // Notify teachers upon student enrolment
 $string['messageprovider:paystack_enrolment'] = 'Paystack enrolment notifications';
 $string['mode'] = 'Payment Mode';
 $string['mode_desc'] = 'Choose between live (real transactions) and test mode for payment processing.';
 $string['mode_live'] = 'Live Mode'; // Real transactions
 $string['mode_test'] = 'Test Mode'; // Dummy transactions for testing
 $string['nocost'] = 'This course is free!';
 $string['paymentthanks'] = 'Thank you for your payment!';
 $string['paystack:config'] = 'Configure Paystack enrolment settings'; // Permission to configure the plugin
 $string['paystack:manage'] = 'Manage enrollees'; // Permission to manage users enrolled via Paystack
 $string['paystack:unenrol'] = 'Unenrol users from the course'; // Permission to unenrol users
 $string['paystack:unenrolself'] = 'Unenrol oneself from the course'; // Permission for users to unenrol themselves
 $string['paystackaccepted'] = 'Paystack payments accepted here';
 $string['pluginname'] = 'Paystack';
 $string['pluginname_desc'] = 'The Paystack plugin allows for paid course enrolments. If a course is free, users won\'t be prompted to pay. Set a default site-wide fee and optionally override it for individual courses.';
 $string['sendpaymentbutton'] = 'Send payment via Paystack';
 $string['status'] = 'Enable Paystack enrolments'; // Enable or disable Paystack enrolment
 $string['status_desc'] = 'Allow users to enrol using Paystack by default.';
 $string['test_secretkey'] = 'Test Secret Key';
 $string['test_publickey'] = 'Test Public Key';
 $string['test_secretkey_desc'] = 'Your Paystack account\'s test secret key for testing payments.';
 $string['test_publickey_desc'] = 'Your Paystack account\'s test public key for testing payments.';
 $string['unenrolselfconfirm'] = 'Do you really want to leave the course "{$a}"?';
 $string['validatezipcode'] = 'Validate postal code';
 $string['validatezipcode_desc'] = 'When enabled, the postal code in the billing address must be verified during payment. It\'s strongly recommended to enable this to reduce fraud.';
 $string['maxenrolled'] = 'Maximum enrolments';
 $string['maxenrolled_help'] = 'Set the max number of users that can enrol through Paystack. Zero for no limit.';
 $string['maxenrolledreached'] = 'Maximum number of enrolments reached.';
 $string['canntenrol'] = 'Enrolment is not available';
 $string['paystackpayment:config'] = 'Configure Paystack'; // Permission to configure Paystack payments
 $string['paystackpayment:manage'] = 'Manage Paystack'; // Permission to manage Paystack payment settings
 $string['paystackpayment:unenrol'] = 'Unenrol from Paystack'; // Permission to unenrol users who paid via Paystack
 $string['paystackpayment:unenrolself'] = 'Unenrol oneself via Paystack'; // Permission for users to unenrol themselves from Paystack-paid courses
 $string['charge_description1'] = "Create customer for email receipt."; // Description for creating a customer on Paystack
 $string['charge_description2'] = 'Charge for enrolling in the course.'; // Description for the charge made for enrolment
 $string['paystack_sorry'] = "Sorry, you can't use the script this way."; // Error message for incorrect script usage
 $string['webhook'] = 'Paystack Webhook URL';
 $string['webhook_desc'] = 'Configure your Paystack account with this Webhook URL "{$a->webhook}" to handle payment notifications automatically. Set it up <a href="{$a->url}">here</a>.';
 