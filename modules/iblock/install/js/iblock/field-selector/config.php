<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/field-selector.bundle.js',
	'rel' => [
		'ui.entity-selector',
		'main.core',
	],
	'skip_core' => false,
];
