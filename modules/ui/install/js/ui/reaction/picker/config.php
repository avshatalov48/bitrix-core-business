<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/reaction-picker.bundle.css',
	'js' => 'dist/reaction-picker.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core.z-index-manager',
		'ui.icon-set.api.core',
		'ui.icon-set.outline',
		'main.core',
		'ui.reaction.item',
	],
	'skip_core' => false,
];
