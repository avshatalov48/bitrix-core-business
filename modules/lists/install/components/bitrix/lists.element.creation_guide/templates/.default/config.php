<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'script.css',
	'js' => 'script.js',
	'rel' => [
		'main.core',
		'main.date',
		'ui.buttons',
		'ui.dialogs.messagebox',
		'ui.tooltip',
		'ui.icons.b24',
	],
	'skip_core' => false,
];
