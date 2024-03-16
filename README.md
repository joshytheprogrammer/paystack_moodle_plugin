# Paystack #

Paystack is a Moodle plugin designed to integrate the Paystack payment gateway into your Moodle site, allowing users to make payments for course enrollments seamlessly.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can also be installed manually by following these steps:

1. Download the ZIP file containing the plugin code.
2. Extract the contents of the ZIP file.
3. Upload the extracted folder to the following directory on your Moodle server:

    `{your/moodle/dirroot}/enrol/paystack`

4. Log in to your Moodle site as an admin and go to _Site administration >
   Notifications_ to complete the installation.

Alternatively, you can run the following command from the Moodle server's command line to complete the installation:

    `$ php admin/cli/upgrade.php`

## License ##

2024 joshytheprogrammer <studymay.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
