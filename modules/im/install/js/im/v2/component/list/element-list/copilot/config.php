<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-list.bundle.css',
	'js' => 'dist/copilot-list.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'im.v2.lib.slider',
		'im.v2.lib.draft',
		'im.v2.component.elements',
		'main.date',
		'im.v2.application.core',
		'im.v2.lib.utils',
		'im.v2.lib.parser',
		'im.v2.lib.date-formatter',
		'im.v2.const',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'main.core',
		'im.public',
		'im.v2.lib.menu',
	],
	'skip_core' => false,
];