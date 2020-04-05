<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js'  => array(
		'/bitrix/js/report/js/activitywidget/activitywidget.js',
	),
	'css' => array(
		'/bitrix/js/report/js/activitywidget/css/activitywidget.css',
	),
	'rel' => array('popup'),
	'bundle_js' => 'activitywidget',
	'bundle_css' => 'activitywidget'
);