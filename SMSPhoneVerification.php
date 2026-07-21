<?php
/**
 * SMSPhoneVerification MediaWiki Extension
 * 
 * Adds a user preference for phone number and sends a verification code via SMS.
 * Now supports a customizable 3-letter prefix before the code.
 * Do NOT show codes in plain text; use hash databases.
 */

use MediaWiki\MediaWikiServices;

class SMSPhoneVerification {

    /**
     * Hook: Add a phone number field to user preferences
     */
    public static function onGetPreferences( $user, &$preferences ) {
        $preferences['phone_number'] = [
            'type' => 'text',
            'label-message' => 'smsphoneverification-phone-label',
            'section' => 'personal/info',
            'default' => '',
        ];
        return true;
    }

    /**
     * Send SMS verification code
     * Replace with your SMS provider API logic
     */
    public static function sendVerificationCode( $phoneNumber ) {
        global $wgSMSVerificationPrefix;

        // Validate phone number format (E.164 recommended)
        if (empty($phoneNumber) || !preg_match('/^\+?[1-9]\d{7,14}$/', $phoneNumber)) {
            throw new InvalidArgumentException("Invalid phone number format.");
        }

        // Ensure prefix is set and valid (default: "SMS")
        $prefix = isset($wgSMSVerificationPrefix) && preg_match('/^[A-Z]{3}$/i', $wgSMSVerificationPrefix)
            ? strtoupper($wgSMSVerificationPrefix)
            : 'SMS';

        // Generate a secure random 6-digit code
        $numericCode = random_int(100000, 999999);

        // Combine prefix and numeric code
        $fullCode = $prefix . $numericCode;

        // Store hashed numeric code in session (prefix not stored for security)
        $session = MediaWikiServices::getInstance()->getSessionManager()->getSessionForRequest();
        $session->set('sms_verification_code', password_hash((string)$numericCode, PASSWORD_DEFAULT));

        // Example: Replace with actual SMS API call
        // self::sendViaTwilio($phoneNumber, "Your verification code is: $fullCode");

        wfDebugLog('SMSPhoneVerification', "Verification code $fullCode sent to $phoneNumber");

        return true;
    }

    /**
     * Verify the code entered by the user
     */
    public static function verifyCode( $inputCode ) {
        global $wgSMSVerificationPrefix;

        // Ensure prefix is set and valid (default: "SMS")
        $prefix = isset($wgSMSVerificationPrefix) && preg_match('/^[A-Z]{3}$/i', $wgSMSVerificationPrefix)
            ? strtoupper($wgSMSVerificationPrefix)
            : 'SMS-';

        // Remove prefix before verification
        if (stripos($inputCode, $prefix) === 0) {
            $inputCode = substr($inputCode, strlen($prefix));
        }

        $session = MediaWikiServices::getInstance()->getSessionManager()->getSessionForRequest();
        $storedHash = $session->get('sms_verification_code');

        if (!$storedHash) {
            return false;
        }

        return password_verify((string)$inputCode, $storedHash);
    }

    /**
     * Example SMS sending function (Twilio)
     */
    private static function sendViaTwilio( $to, $message ) {
        // Placeholder — integrate Twilio SDK here
        // Example:
        // $client = new Twilio\Rest\Client($sid, $token);
        // $client->messages->create($to, ['from' => $fromNumber, 'body' => $message]);
    }
}
