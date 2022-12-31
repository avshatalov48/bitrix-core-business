<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/access-denied-input.bundle.css',
	'js' => 'dist/access-denied-input.bundle.js',
	'rel' => [
		'ui.hint',
	],
	'skip_core' => false,
];
