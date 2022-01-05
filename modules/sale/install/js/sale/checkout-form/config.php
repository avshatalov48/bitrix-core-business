<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/checkout-form.bundle.css',
	'js' => 'dist/checkout-form.bundle.js',
	'rel' => [
		'rest.client',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];