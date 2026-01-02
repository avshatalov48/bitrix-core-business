<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/ui.icon-set.core.bundle.js',
	'rel' => [
		'main.core',
		'ui.design-tokens.air',
	],
	'skip_core' => false,
];
