<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-list.bundle.css',
	'js' => 'dist/copilot-list.bundle.js',
	'rel' => [
		'im.v2.lib.draft',
		'main.date',
		'im.v2.lib.utils',
		'im.v2.lib.parser',
		'im.v2.component.elements',
		'im.v2.lib.date-formatter',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'main.core',
		'im.public',
		'im.v2.lib.menu',
	],
	'skip_core' => false,
];