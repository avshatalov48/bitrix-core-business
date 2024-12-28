<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'im.v2.provider.service',
		'main.core.events',
		'im.v2.lib.feature',
		'im.v2.component.animation',
		'ui.entity-selector',
		'im.v2.application.core',
		'main.core',
		'im.v2.const',
		'ui.forms',
		'im.v2.component.elements',
	],
	'skip_core' => false,
];
