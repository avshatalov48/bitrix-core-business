<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/hint.bundle.js',
	'rel' => [
		'ui.hint',
		'main.core',
		'main.popup',
	],
	'skip_core' => false,
];
