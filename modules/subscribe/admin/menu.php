<?php
IncludeModuleLangFile(__FILE__);
/** @var CMain $APPLICATION */
if (CMain::GetUserRight('subscribe') != 'D')
{
	$aMenu = [
		'parent_menu' => 'global_menu_services',
		'section' => 'subscribe',
		'sort' => 200,
		'text' => GetMessage('mnu_sect'),
		'title' => GetMessage('mnu_sect_title'),
		'icon' => 'subscribe_menu_icon',
		'page_icon' => 'subscribe_page_icon',
		'items_id' => 'menu_subscribe',
		'items' => [
			[
				'text' => GetMessage('mnu_posting'),
				'url' => 'posting_admin.php?lang=' . LANGUAGE_ID,
				'more_url' => ['posting_edit.php'],
				'title' => GetMessage('mnu_posting_alt')
			],
			[
				'text' => GetMessage('mnu_subscr'),
				'url' => 'subscr_admin.php?lang=' . LANGUAGE_ID,
				'more_url' => ['subscr_edit.php'],
				'title' => GetMessage('mnu_subscr_alt')
			],
			[
				'text' => GetMessage('mnu_subscr_import'),
				'url' => 'subscr_import.php?lang=' . LANGUAGE_ID,
				'more_url' => ['subscr_import.php'],
				'title' => GetMessage('mnu_subscr_import_alt')
			],
			[
				'text' => GetMessage('mnu_rub'),
				'url' => 'rubric_admin.php?lang=' . LANGUAGE_ID,
				'more_url' => ['rubric_edit.php', 'template_test.php'],
				'title' => GetMessage('mnu_rub_alt')
			],
		]
	];

	return $aMenu;
}
return false;
