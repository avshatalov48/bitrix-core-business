<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!$USER->CanDoOperation('fileman_view_file_structure') && !$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/snippets.php");

if (isset($_GET['target']) && check_bitrix_sessid())
{
	switch ($_GET['target'])
	{
		case "load":
			CSnippets::LoadList(array(
				'template' => $APPLICATION->UnJSEscape($_GET["templateID"]),
				'bClearCache' => isset($_GET['clear_snippets_cache']) && $_GET['clear_snippets_cache'] == 'Y',
			));
			break;
		case "add":
		case "edit":
			CUtil::JSPostUnEscape();
			$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
			if (CFileman::IsPHP($code) && !$USER->CanDoOperation('edit_php'))
				return $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

			CSnippets::Edit(array(
				'bNew' => $_REQUEST['target'] == 'add',
				'name' => $_REQUEST['name'],
				'path' => $_REQUEST['target'] == 'add' && isset($_REQUEST['path']) ? $_REQUEST['path'] : false,
				'template' => $_REQUEST['templateID'],
				'site' => $_REQUEST['site'],
				'title' => isset($_REQUEST['title']) ? $_REQUEST['title'] : '',
				'description' => isset($_REQUEST['description']) ? $_REQUEST['description'] : '',
				'code' => $code,
				'thumb' => isset($_REQUEST['thumb']) ? $_REQUEST['thumb'] : false,
				'location' => isset($_REQUEST["location"]) ? $_REQUEST["location"] : false,
				'newGroup' => isset($_REQUEST["new_group"]) ? $_REQUEST["new_group"] : false
			));
			break;
		case "delete":
			CSnippets::Delete(array(
				'name' => $APPLICATION->UnJSEscape($_REQUEST['name']),
				'path' => isset($_REQUEST['path']) ? $APPLICATION->UnJSEscape($_REQUEST['path']) : false,
				'template' => $APPLICATION->UnJSEscape($_REQUEST['templateID']),
				'site' => $_REQUEST['site'],
				'thumb' => isset($_REQUEST['thumb']) ? $_REQUEST['thumb'] : ''
			));
			break;
		case "getgroups":
			CSnippets::GetGroups(array('template' => $APPLICATION->UnJSEscape($_REQUEST['templateID'])));
			break;
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>