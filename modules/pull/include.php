<?php

define("PULL_REVISION_WEB", 19);
define("PULL_REVISION_MOBILE", 3);

global $APPLICATION;

require_once __DIR__.'/autoload.php';

CJSCore::RegisterExt('pull', array(
	'skip_core' => true,
	'rel' => array('pull.client')
));
