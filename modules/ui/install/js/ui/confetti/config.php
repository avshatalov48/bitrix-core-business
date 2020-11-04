<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => '/bitrix/js/ui/confetti/style.css',
	'js' => 'dist/confetti.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];