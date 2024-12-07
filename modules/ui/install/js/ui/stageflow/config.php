<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/stageflow.bundle.css',
	'js' => 'dist/stageflow.bundle.js',
	'rel' => [
		'ui.buttons',
		'main.core',
		'main.popup',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];
