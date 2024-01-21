<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-answer.bundle.css',
	'js' => 'dist/copilot-answer.bundle.js',
	'rel' => [
		'ui.notification',
		'im.v2.lib.parser',
		'im.v2.provider.service',
		'main.core',
		'im.v2.component.message.elements',
		'im.v2.component.message.base',
	],
	'skip_core' => false,
];