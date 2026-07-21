<?php
/**
 * Special page for SMS phone verification with customizable prefix.
 *
 * @file
 * @ingroup Extensions
 * @license GPL-3.0
 */

namespace MediaWiki\Extension\SMSPhoneVerification;

use SpecialPage;
use Html;
use HtmlForm;
use Status;
use MediaWiki\MediaWikiServices;

class SpecialSMSPhoneVerify extends SpecialPage {

    public function __construct() {
        parent::__construct( 'SMSPhoneVerify' );
    }

    public function execute( $subPage ) {
        $this->setHeaders();
        $this->checkPermissions();

        $out = $this->getOutput();
        $request = $this->getRequest();
        $user = $this->getUser();

        if ( $request->wasPosted() && $request->getCheck( 'wpSubmit' ) ) {
            $phone = trim( $request->getText( 'wpPhone' ) );
            $code  = trim( $request->getText( 'wpCode' ) );

            if ( $phone === '' ) {
                $out->addHTML( Html::errorBox( 'Please enter your phone number.' ) );
            } elseif ( $code === '' ) {
                $status = $this->sendVerificationCode( $phone, $user );
                if ( $status->isOK() ) {
                    $out->addHTML( Html::successBox( 'Verification code sent to ' . htmlspecialchars( $phone ) ) );
                } else {
                    $out->addHTML( Html::errorBox( $status->getMessage() ) );
                }
            } else {
                $status = $this->verifyCode( $phone, $code, $user );
                if ( $status->isOK() ) {
                    $out->addHTML( Html::successBox( 'Phone number verified successfully!' ) );
                } else {
                    $out->addHTML( Html::errorBox( $status->getMessage() ) );
                }
            }
        }

        $this->showForm();
    }

    private function showForm() {
        $formDescriptor = [
            'phone' => [
                'label' => 'Phone Number',
                'type' => 'text',
                'name' => 'wpPhone',
                'size' => 20,
                'required' => true
            ],
            'code' => [
                'label' => 'Verification Code (leave blank to request one)',
                'type' => 'text',
                'name' => 'wpCode',
                'size' => 15
            ]
        ];

        $htmlForm = HtmlForm::factory( 'ooui', $formDescriptor, $this->getContext() );
        $htmlForm->setSubmitText( 'Submit' );
        $htmlForm->setMethod( 'post' );
        $htmlForm->prepareForm()->displayForm( false );
    }

    /**
     * Send a verification code via SMS with prefix.
     */
    private function sendVerificationCode( $phone, $user ) {
        global $wgSMSPhoneVerificationPrefix;

        // Default prefix if not set
        $prefix = isset( $wgSMSPhoneVerificationPrefix ) && $wgSMSPhoneVerificationPrefix !== ''
            ? $wgSMSPhoneVerificationPrefix
            : 'SMS-';

        // Generate prefixed code
        $numericCode = random_int( 100000, 999999 );
        $fullCode = $prefix . $numericCode;

        // Store code in cache
        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
        $cache->set( $cache->makeKey( 'smsverify', $user->getId() ), $fullCode, 300 );

        // TODO: Replace with actual SMS API integration
        wfDebugLog( 'smsverify', "Sent code $fullCode to $phone" );

        return Status::newGood();
    }

    /**
     * Verify the submitted code.
     */
    private function verifyCode( $phone, $code, $user ) {
        $cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
        $storedCode = $cache->get( $cache->makeKey( 'smsverify', $user->getId() ) );

        if ( !$storedCode ) {
            return Status::newFatal( 'No verification code found or it expired.' );
        }

        if ( $storedCode !== $code ) {
            return Status::newFatal( 'Invalid verification code.' );
        }

        $user->setOption( 'phone_verified', $phone );
        $user->saveSettings();

        $cache->delete( $cache->makeKey( 'smsverify', $user->getId() ) );

        return Status::newGood();
    }
}
