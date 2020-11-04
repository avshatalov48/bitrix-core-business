<?php

require_once __DIR__.'/autoload.php';

\CJSCore::RegisterExt('translate.process', array(
	'js' => array(
		'/bitrix/js/translate/process/dialog.js',
		'/bitrix/js/translate/process/process.js',
	),
	'css' => '/bitrix/js/translate/process/css/dialog.css',
	'rel' => array('main.popup', 'ui.progressbar', 'ui.buttons')
));
