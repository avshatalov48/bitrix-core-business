<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => '/bitrix/js/ui/ears/style.css',
	'js' => 'dist/ears.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];