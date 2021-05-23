<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => '/bitrix/js/bizproc/script/dist/script.bundle.js',
	'rel' => [
		'main.core',
		'ui.dialogs.messagebox',
		'ui.notification',
		'main.popup',
		'ui.buttons',
		'sidepanel',
		'bp_field_type',
	],
	'skip_core' => false,
];