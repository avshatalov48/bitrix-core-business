<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/userfield-selector.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.entity-selector',
	],
	'skip_core' => false,
];
