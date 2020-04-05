<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'js' => Array(
		'/bitrix/js/im/ui/list/js/list.js',
		'/bitrix/js/im/ui/list/js/queue.js',
		'/bitrix/js/im/ui/list/js/markup.js',
		'/bitrix/js/im/ui/list/js/animation.js',
	),
	'css' => '/bitrix/js/im/ui/list/css/styles.css',
	'bundle_js' => 'im_ui_list',
);