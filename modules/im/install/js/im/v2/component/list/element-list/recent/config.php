<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-list.bundle.css',
	'js' => 'dist/recent-list.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'im.v2.provider.service',
		'im.v2.lib.menu',
		'im.v2.lib.draft',
		'main.popup',
		'im.v2.lib.slider',
		'im.public',
		'main.date',
		'im.v2.lib.parser',
		'im.v2.lib.date-formatter',
		'im.v2.component.elements',
		'im.v2.lib.call',
		'main.core',
		'im.v2.lib.utils',
		'main.core.events',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => false,
];