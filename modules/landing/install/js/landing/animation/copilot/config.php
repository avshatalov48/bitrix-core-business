<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot.bundle.css',
	'js' => 'dist/copilot.bundle.js',
	'rel' => [
		'pull.client',
		'landing.pageobject',
		'landing.ui.copilot.skeleton',
		'main.core.events',
		'ui.lottie',
		'main.core',
		'ui.confetti',
	],
	'skip_core' => false,
];
