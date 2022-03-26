<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/saveblock.bundle.css',
	'js' => 'dist/saveblock.bundle.js',
	'rel' => [
		'landing.backend',
		'landing.env',
		'landing.imagecompressor',
		'landing.loc',
		'landing.main',
		'landing.screenshoter',
		'landing.ui.card.messagecard',
		'landing.ui.field.textfield',
		'landing.ui.panel.content',
		'main.core',
		'translit',
	],
	'skip_core' => false,
];