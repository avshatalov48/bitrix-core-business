<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-answer.bundle.css',
	'js' => 'dist/copilot-answer.bundle.js',
	'rel' => [
		'main.core',
		'im.v2.lib.utils',
		'im.v2.lib.parser',
		'im.v2.component.message.base',
		'im.v2.component.message.elements',
		'im.v2.lib.helpdesk',
		'im.v2.lib.notifier',
	],
	'skip_core' => false,
];