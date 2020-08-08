<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => '/bitrix/js/ui/userfieldfactory/src/userfieldfactory.css',
	'js' => '/bitrix/js/ui/userfieldfactory/dist/userfieldfactory.bundle.js',
	'rel' => [
		'main.popup',
		'main.core',
		'sidepanel',
		'ui.userfield',
	],
	'skip_core' => false,
];