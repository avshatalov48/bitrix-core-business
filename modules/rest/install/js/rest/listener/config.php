<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'css' => [],
	'js' => [
		'dist/listener.bundle.js'
	],
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
	],
	'skip_core' => true,
);
