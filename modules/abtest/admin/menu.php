<?php

IncludeModuleLangFile(__FILE__);
$aMenu = array();

if ($APPLICATION->getGroupRight('abtest') >= 'R')
{
	$aMenu[] = array(
		'parent_menu' => 'global_menu_marketing',
		'section'     => 'abtest',
		'sort'        => 300,
		'text'        => GetMessage('ABTEST_MENU_TEXT'),
		'title'       => GetMessage('ABTEST_MENU_TITLE'),
		'url'         => 'abtest_admin.php?lang='.LANG,
		'icon'        => 'abtest_menu_icon',
		'page_icon'   => 'abtest_menu_icon',
		'items_id'    => 'menu_abtest',
		'more_url'    => array('abtest_admin.php', 'abtest_edit.php', 'abtest_report.php'),
		'items'       => array()
	);
}

return !empty($aMenu) ? $aMenu : false;
