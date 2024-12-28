<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/enable-feature.bundle.css',
	'js' => 'dist/enable-feature.bundle.js',
	'rel' => [
		'im.v2.component.elements',
		'im.v2.component.message.supervisor.base',
		'main.core',
		'im.v2.lib.analytics',
		'im.v2.lib.helpdesk',
		'stafftrack.user-statistics-link',
	],
	'skip_core' => false,
];