<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/connector.intranet.bundle.js',
	'lang' => '/bitrix/modules/landing/lang/' . LANGUAGE_ID . '/js/connector/intranet.php',
	'rel' => [
		'main.polyfill.core',
		'landing.loc',
		'ui.dialogs.messagebox',
	],
	'skip_core' => true,
];