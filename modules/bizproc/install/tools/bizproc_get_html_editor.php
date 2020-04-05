<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

/**
 * @global CUser $USER
 */

if(!CModule::IncludeModule("bizproc"))
	die();

if (!$USER->IsAuthorized())
	die();

$editorId = !empty($_REQUEST['editor_id']) ? $_REQUEST['editor_id'] : '';
$fieldName = !empty($_REQUEST['field_name']) ? $_REQUEST['field_name'] : '';

$editorId = preg_replace('#[^a-z0-9_\-]#i', '', $editorId);
$fieldName = preg_replace('#[^a-z0-9_\-\[\]]#i', '', $fieldName);

$GLOBALS['APPLICATION']->ShowAjaxHead();
echo \CBPViewHelper::getHtmlEditor($editorId, $fieldName);
CMain::FinalActions();
die;