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
namespace enrol_paystack;

defined('MOODLE_INTERNAL') || die();

class paystack
{
    public string $plugin_name;
    public string $public_key;
    public string $secret_key;
    private string $base_url = "https://api.paystack.co/";

    /**
     * Constructor for initializing Paystack payment integration.
     *
     * @param string $plugin Plugin identifier.
     * @param string $pk Public key for Paystack authentication.
     * @param string $sk Secret key for Paystack authentication.
     */
    public function __construct(string $plugin, string $pk, string $sk) {
        $this->plugin_name = $plugin;
        $this->public_key = $pk;
        $this->secret_key = $sk;
    }

    /**
     * Initiates a payment transaction with Paystack.
     *
     * @param array $data Payload for transaction initialization.
     * @return array Decoded JSON response from Paystack.
     */
    public function initialize_transaction(array $data): array {
        $paystackUrl = $this->base_url . "transaction/initialize";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $paystackUrl,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->secret_key,
                "Content-Type: application/json",
                "Cache-Control: no-cache",
            ],
        ]);

        $response = curl_exec($curl);
        $res = json_decode($response, true);
        curl_close($curl);

        if (curl_errno($curl)) {
            throw new \moodle_exception('errpaystackconnect', 'enrol_paystack', '', ['url' => $paystackUrl, 'response' => $res], json_encode($data));
        }

        return $res;
    }

    /**
     * Confirms the status of a payment transaction using its reference.
     *
     * @param string $reference Unique reference of the transaction.
     * @return array Decoded JSON response indicating transaction status.
     */
    public function verify_transaction(string $reference): array {
        $paystackUrl = $this->base_url . "transaction/verify/" . $reference;
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $paystackUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $this->secret_key,
                "Content-Type: application/json",
                "Cache-Control: no-cache",
            ],
        ]);

        $response = curl_exec($curl);
        $res = json_decode($response, true);

        if (curl_errno($curl)) {
            $errorMessage = curl_error($curl);
            throw new \moodle_exception('errpaystackconnect', 'enrol_paystack', '', ['url' => $paystackUrl, 'response' => $res, 'error' => $errorMessage]);
        }
    
        curl_close($curl);

        return $res;
    }

    /**
     * Logs the success of a transaction to a remote server for tracking purposes.
     *
     * @param string $reference Transaction reference identifier.
     */
    public function log_transaction_success(string $reference): void {
        $url = "https://plugin-tracker.paystackintegrations.com/log/charge_success";
        $params = [
            'public_key' => $this->public_key,
            'plugin_name' => $this->plugin_name,
            'transaction_reference' => $reference,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Cache-Control: no-cache",
            ],
        ]);

        curl_exec($curl);
        curl_close($curl);
    }

    /**
     * Validates the integrity of data received through Paystack webhooks.
     *
     * @param string $input Payload received from the webhook.
     * @return bool True if the webhook signature matches, false otherwise.
     */
    public function validate_webhook(string $input): bool {
        return $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $this->secret_key);
    }
}
