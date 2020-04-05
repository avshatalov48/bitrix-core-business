<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js'  => array(
		'/bitrix/js/report/js/dashboard/board.js',
		'/bitrix/js/report/js/dashboard/row.js',
		'/bitrix/js/report/js/dashboard/rowlayouts.js',
		'/bitrix/js/report/js/dashboard/cell.js',
		'/bitrix/js/report/js/dashboard/widget.js',
		'/bitrix/js/report/js/dashboard/content.js',
		'/bitrix/js/report/js/dashboard/utils.js'
	),
	'css' => array(
		'/bitrix/js/report/js/dashboard/css/dashboard.css',
	),
	'rel' => array('popup', 'color_picker', 'dnd'),
	'bundle_js' => 'dashboard',
	'bundle_css' => 'dashboard'
);