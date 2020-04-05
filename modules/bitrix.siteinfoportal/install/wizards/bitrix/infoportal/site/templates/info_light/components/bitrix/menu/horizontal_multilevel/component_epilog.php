<?
__IncludeLang(dirname(__FILE__).'/lang/'.LANGUAGE_ID.'/component_epilog.php');

$APPLICATION->SetEditArea("horizontal-multilevel-menu", array(
	array(
		'URL' => "/bitrix/admin/iblock_section_admin.php?IBLOCK_ID=#NEWS_IBLOCK_ID#&type=news&lang=".LANGUAGE_ID."&SECTION_ID=0",
		'TITLE' => GetMessage("MENU_NEWS_SECTION_EDIT"),
		'ICON' => 'bx-context-toolbar-edit-icon'
	)
));
?>