<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/hint.bundle.js',
	],
	'rel' => [
		'main.popup',
		'main.core',
		'ui.hint',
	],
	'skip_core' => false,
];