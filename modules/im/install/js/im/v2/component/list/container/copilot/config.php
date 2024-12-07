<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-container.bundle.css',
	'js' => 'dist/copilot-container.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.public',
		'im.v2.component.list.items.copilot',
		'im.v2.const',
		'im.v2.lib.analytics',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'im.v2.component.elements',
	],
	'skip_core' => true,
];