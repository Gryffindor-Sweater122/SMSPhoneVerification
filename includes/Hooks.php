<?php

namespace MediaWiki\Extension\MyExtension;

use OutputPage;
use Skin;

class Hooks {
    public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
        $out->addModules( 'ext.myextension' );
    }
}
