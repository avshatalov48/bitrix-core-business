<?
// *******************************************************************************************************
// Install new right system: operation and tasks
// *******************************************************************************************************
// ############ FILEMAN MODULE OPERATION ###########
$arFOp = Array();
$arFOp[] = Array('fileman_view_all_settings', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_menu_types', 'fileman', '', 'module');
$arFOp[] = Array('fileman_add_element_to_menu', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_menu_elements', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_existent_files', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_existent_folders', 'fileman', '', 'module');
$arFOp[] = Array('fileman_admin_files', 'fileman', '', 'module');
$arFOp[] = Array('fileman_admin_folders', 'fileman', '', 'module');
$arFOp[] = Array('fileman_view_permissions', 'fileman', '', 'module');
$arFOp[] = Array('fileman_edit_all_settings', 'fileman', '', 'module');
$arFOp[] = Array('fileman_upload_files', 'fileman', '', 'module');
$arFOp[] = Array('fileman_view_file_structure', 'fileman', '', 'module');
$arFOp[] = Array('fileman_install_control', 'fileman', '', 'module');

// MEDIALIBRARY OPERATIONS
$arFOp[] = Array('medialib_view_collection', 'fileman', '', 'medialib');
$arFOp[] = Array('medialib_new_collection', 'fileman', '', 'medialib');
$arFOp[] = Array('medialib_edit_collection', 'fileman', '', 'medialib');
$arFOp[] = Array('medialib_del_collection', 'fileman', '', 'medialib');
$arFOp[] = Array('medialib_access', 'fileman', '', 'medialib');
$arFOp[] = Array('medialib_new_item', 'fileman', '', 'medialib');
$arFOp[] = Array('medialib_edit_item', 'fileman', '', 'medialib');
$arFOp[] = Array('medialib_del_item', 'fileman', '', 'medialib');

// STICKERS OPERATIONS
$arFOp[] = Array('sticker_view', 'fileman', '', 'stickers');
$arFOp[] = Array('sticker_edit', 'fileman', '', 'stickers');
$arFOp[] = Array('sticker_new', 'fileman', '', 'stickers');
$arFOp[] = Array('sticker_del', 'fileman', '', 'stickers');

// ############ FILEMAN MODULE TASKS ###########
$arTasksF = Array();
$arTasksF[] = Array('fileman_denied', 'D', 'fileman', 'Y', '', 'module');
$arTasksF[] = Array('fileman_allowed_folders', 'F', 'fileman', 'Y', '', 'module');
$arTasksF[] = Array('fileman_full_access', 'W', 'fileman', 'Y', '', 'module');

// MEDIALIBRARY TASKS
$arTasksF[] = Array('medialib_denied', 'D', 'fileman', 'Y', '', 'medialib');
$arTasksF[] = Array('medialib_view', 'F', 'fileman', 'Y', '', 'medialib');
$arTasksF[] = Array('medialib_only_new', 'R', 'fileman', 'Y', '', 'medialib');
$arTasksF[] = Array('medialib_edit_items', 'V', 'fileman', 'Y', '', 'medialib');
$arTasksF[] = Array('medialib_editor', 'W', 'fileman', 'Y', '', 'medialib');
$arTasksF[] = Array('medialib_full', 'X', 'fileman', 'Y', '', 'medialib');

// STICKERS TASKS
$arTasksF[] = Array('stickers_denied', 'D', 'fileman', 'Y', '', 'stickers');
$arTasksF[] = Array('stickers_read', 'R', 'fileman', 'Y', '', 'stickers');
$arTasksF[] = Array('stickers_edit', 'W', 'fileman', 'Y', '', 'stickers');

//Operations in Tasks
$arOInT = Array();
//FILEMAN: module
$arOInT['fileman_allowed_folders'] = Array(
	'fileman_view_file_structure',
	'fileman_add_element_to_menu',
	'fileman_edit_menu_elements',
	'fileman_edit_existent_files',
	'fileman_edit_existent_folders',
	'fileman_admin_files',
	'fileman_admin_folders',
	'fileman_view_permissions',
	'fileman_upload_files'
);

$arOInT['fileman_full_access'] = Array(
	'fileman_view_file_structure',
	'fileman_view_all_settings',
	'fileman_edit_menu_types',
	'fileman_add_element_to_menu',
	'fileman_edit_menu_elements',
	'fileman_edit_existent_files',
	'fileman_edit_existent_folders',
	'fileman_admin_files',
	'fileman_admin_folders',
	'fileman_view_permissions',
	'fileman_edit_all_settings',
	'fileman_upload_files',
	'fileman_install_control'
);


// MEDIALIBRARY OPERATIONS IN TASKS
$arOInT['medialib_view'] = Array('medialib_view_collection');

$arOInT['medialib_only_new'] = Array(
	'medialib_view_collection',
	'medialib_new_collection',
	'medialib_new_item',
);

$arOInT['medialib_edit_items'] = Array(
	'medialib_view_collection',
	'medialib_new_item',
	'medialib_edit_item',
	'medialib_del_item'
);

$arOInT['medialib_editor'] = Array(
	'medialib_view_collection',
	'medialib_new_collection',
	'medialib_edit_collection',
	'medialib_del_collection',
	'medialib_new_item',
	'medialib_edit_item',
	'medialib_del_item'
);

$arOInT['medialib_full'] = Array(
	'medialib_view_collection',
	'medialib_new_collection',
	'medialib_edit_collection',
	'medialib_del_collection',
	'medialib_access',
	'medialib_new_item',
	'medialib_edit_item',
	'medialib_del_item'
);

// STICKERS OPERATIONS IN TASKS
$arOInT['stickers_read'] = Array('sticker_view');
$arOInT['stickers_edit'] = Array('sticker_view', 'sticker_edit', 'sticker_new', 'sticker_del');


foreach($arFOp as $ar)
	$DB->Query("
		INSERT INTO b_operation
		(NAME,MODULE_ID,DESCRIPTION,BINDING)
		VALUES
		('".$ar[0]."','".$ar[1]."','".$ar[2]."','".$ar[3]."')
	", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

foreach($arTasksF as $ar)
	$DB->Query("
		INSERT INTO b_task
		(NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING)
		VALUES
		('".$ar[0]."','".$ar[1]."','".$ar[2]."','".$ar[3]."','".$ar[4]."','".$ar[5]."')
	", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

// ############ b_group_task ###########
$sql_str = "
	INSERT INTO b_group_task
	(GROUP_ID,TASK_ID)
	SELECT MG.GROUP_ID, T.ID
	FROM
		b_task T
		INNER JOIN b_module_group MG ON MG.G_ACCESS = T.LETTER
	WHERE
		T.SYS = 'Y'
		AND T.BINDING = 'module'
		AND MG.MODULE_ID = 'fileman'
		AND T.MODULE_ID = MG.MODULE_ID
";
$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

// ############ b_task_operation ###########
foreach($arOInT as $tname => $arOp)
{
	$sql_str = "
		INSERT INTO b_task_operation
		(TASK_ID,OPERATION_ID)
		SELECT T.ID, O.ID
		FROM
			b_task T
			,b_operation O
		WHERE
			T.SYS='Y'
			AND T.NAME='".$tname."'
			AND O.NAME in ('".implode("','", $arOp)."')
	";
	$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
}
?>