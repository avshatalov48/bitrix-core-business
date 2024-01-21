<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/tutor.bundle.css',
	'js' => 'dist/tutor.bundle.js',
	'rel' => [
		'main.core',
		'ui.tour',
		'main.loader',
		'ui.feedback.form',
		'ui.design-tokens',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];
