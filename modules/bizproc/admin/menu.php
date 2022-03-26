<?
IncludeModuleLangFile(__FILE__);

$aMenu = array(
	"parent_menu" => "global_menu_services",
	"section" => "bizproc",
	"sort" => 550,
	"text" => GetMessage("BIZPROC_MENU_TEXT"),
	"title"=> GetMessage("BIZPROC_MENU_TITLE"),
	"icon" => "bizproc_menu_icon",
	"page_icon" => "bizproc_page_icon",
	"items_id" => "menu_bizproc",
	"items" => array(
		array(
			"text" => GetMessage("BIZPROC_MENU_TASKS_1"),
			"url" => "bizproc_task_list.php?lang=".LANGUAGE_ID,
			"more_url" => array("bizproc_task.php"),
			"title" => GetMessage("BIZPROC_MENU_TASKS_ALT")
		),
	)
);

return $aMenu;
?>
