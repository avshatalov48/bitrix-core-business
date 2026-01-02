<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/wizard.bundle.css',
	'js' => 'dist/wizard.bundle.js',
	'rel' => [
		'main.core',
		'ui.hint',
	],
	'skip_core' => false,
];