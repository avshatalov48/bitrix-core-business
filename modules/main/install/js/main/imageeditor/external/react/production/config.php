<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/main/imageeditor/external/react/production/react.production.js',
		'/bitrix/js/main/imageeditor/external/react/production/react.dom.production.js'
	],

	'bundle_js' => 'main_imageeditor_react_production'
];