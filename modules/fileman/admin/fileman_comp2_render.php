<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!isset($_POST['name']) && !isset($_POST['source'])) // Some bogus call
	die();

if (!($USER->CanDoOperation('fileman_admin_files') || $USER->CanDoOperation('fileman_edit_existent_files')))
	die('BX_EDITOR_ERROR: ACCESS_DENIED');

if(!check_bitrix_sessid())
{
	$APPLICATION->RestartBuffer();
	die('<!--BX_EDITOR_ERROR_SESSION_EXPIRED'.bitrix_sessid().'-->');
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

CEditorUtils::RenderComponents(array(
	'name' => isset($_POST['name']) ? $_POST['name'] : false,
	'template' => isset($_POST['template']) ? $_POST['template'] : '',
	'params' => isset($_POST['params']) ? CEditorUtils::UnJSEscapeArray($_POST['params']) : false,
	'source' => isset($_POST['source']) ? $_POST['source'] : false,
	'siteTemplateId' => isset($_POST['stid']) ? $_POST['stid'] : false
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
?>