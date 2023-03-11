<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/condition.bundle.css',
	'js' => 'dist/condition.bundle.js',
	'rel' => [
		'main.core',
		'bp_field_type',
	],
	'skip_core' => false,
];