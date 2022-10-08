<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'/bitrix/js/ui/stageflow/dist/stageflow.bundle.css'
	],
	'js' => '/bitrix/js/ui/stageflow/dist/stageflow.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];