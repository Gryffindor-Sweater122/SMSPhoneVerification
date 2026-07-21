<?php
/**
 * SMSPhoneVerification MediaWiki Extension
 * 
 * This extension adds a user preference for phone number
 * and sends a verification code via SMS.
 * 
 * SECURITY: Do not store raw verification codes in plain text.
 * Use hashing and secure storage.
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
        if (empty($phoneNumber) || !preg_match('/^\+?[1-9]\d{7,14}$/', $phoneNumber)) {
            throw new InvalidArgumentException("Invalid phone number format.");
        }

        // Generate a secure random 6-digit code
        $code = random_int(100000, 999999);

        // Store hashed code in session (or DB)
        $session = MediaWikiServices::getInstance()->getSessionManager()->getSessionForRequest();
        $session->set('sms_verification_code', password_hash((string)$code, PASSWORD_DEFAULT));

        // Example: Replace with actual SMS API call
        // self::sendViaTwilio($phoneNumber, "Your verification code is: $code");

        wfDebugLog('SMSPhoneVerification', "Verification code $code sent to $phoneNumber");

        return true;
    }

    /**
     * Verify the code entered by the user
     */
    public static function verifyCode( $inputCode ) {
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
        // This is a placeholder — integrate Twilio SDK here
        // Example:
        // $client = new Twilio\Rest\Client($sid, $token);
        // $client->messages->create($to, ['from' => $fromNumber, 'body' => $message]);
    }
}
