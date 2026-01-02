<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'script.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.alerts',
		'bizproc.router',
		'ui.design-tokens',
	],
	'skip_core' => false,
];
