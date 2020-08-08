<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'/bitrix/js/ui/timeline/src/timeline.css'
	],
	'js' => '/bitrix/js/ui/timeline/dist/timeline.bundle.js',
	'rel' => [
		'main.loader',
		'ui.dialogs.messagebox',
		'main.popup',
		'main.core.events',
		'main.core',
		'main.date',
	],
	'skip_core' => false,
];