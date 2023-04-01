<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'script.css',
	'js' => 'script.js',
	'rel' => [
		'ui.messagecard',
		'main.core.events',
		'main.popup',
		'main.core',
		'ui.notification',
	],
	'skip_core' => false,
];