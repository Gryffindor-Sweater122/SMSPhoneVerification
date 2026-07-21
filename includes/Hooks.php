<?php

namespace MediaWiki\Extension\SMSPhoneVerificarion;

use OutputPage;
use Skin;

class Hooks {
    public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
        $out->addModules( 'ext.smsphoneverification' );
    }
}
