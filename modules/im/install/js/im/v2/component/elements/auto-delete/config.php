<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'ui.forms',
		'main.popup',
		'im.v2.const',
		'im.v2.lib.auto-delete',
		'main.core',
		'im.v2.component.elements.popup',
		'im.v2.lib.helpdesk',
	],
	'skip_core' => false,
];
