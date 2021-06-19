<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'script.css',
	'js' => 'script.js',
	'rel' => [
		'main.core',
		'main.menu',
		'ui.entity-selector'
	],
	'skip_core' => false,
];