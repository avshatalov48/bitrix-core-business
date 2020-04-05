<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/bizproc/install/tools/bizproc_do_task_ajax.php');

$result = array('SUCCESS' => true);
$user = $GLOBALS["USER"];

if (!CModule::IncludeModule("bizproc") || !CModule::IncludeModule("iblock"))
	$result['SUCCESS'] = false;

if ($_SERVER["REQUEST_METHOD"] != "POST" || !$user->IsAuthorized() || !check_bitrix_sessid())
{
	$result['SUCCESS'] = false;
	$result['ERROR'] = 'Access denied.';
}

if ($result['SUCCESS'])
{
	$taskId = (int)$_REQUEST['TASK_ID'];
	$task = false;

	if ($taskId > 0)
	{
		$dbTask = CBPTaskService::GetList(
			array(),
			array("ID" => $taskId, "USER_ID" => $user->getId()),
			false,
			false,
			array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS", "USER_STATUS")
		);
		$task = $dbTask->fetch();
	}

	if (!$task)
	{
		$result['SUCCESS'] = false;
		$result['ERROR'] = Loc::getMessage('BIZPROC_DO_TASK_AJAX_ERROR_NOT_FOUND');
	}
	elseif ((int)$task['USER_STATUS'] !== CBPTaskUserStatus::Waiting)
	{
		$result['SUCCESS'] = false;
		$result['ERROR'] = Loc::getMessage('BIZPROC_DO_TASK_AJAX_ERROR_ALREADY_DONE');
	}
	else
	{
		$task["PARAMETERS"]["DOCUMENT_ID"] = CBPStateService::GetStateDocumentId($task['WORKFLOW_ID']);
		$task["MODULE_ID"] = $task["PARAMETERS"]["DOCUMENT_ID"][0];
		$task["ENTITY"] = $task["PARAMETERS"]["DOCUMENT_ID"][1];
		$task["DOCUMENT_ID"] = $task["PARAMETERS"]["DOCUMENT_ID"][2];

		$arErrorsTmp = array();

		if (SITE_CHARSET != "utf-8" && !empty($_SERVER['HTTP_BX_AJAX']))
		{
			CUtil::decodeURIComponent($_REQUEST);
			CUtil::decodeURIComponent($_FILES);
		}

		$formData = $_REQUEST + $_FILES;

		if (!CBPDocument::PostTaskForm($task, $user->getId(), $formData, $arErrorsTmp))
		{
			$arError = array();
			foreach ($arErrorsTmp as $e)
				$arError[] = array(
					"id" => "bad_task",
					"text" => $e["message"]);
			$e = new CAdminException($arError);
			$result['ERROR'] = HTMLToTxt($e->GetString());
		}
	}
}

$result['SUCCESS'] = (empty($result['ERROR']));
echo CUtil::PhpToJSObject($result);
\Bitrix\Main\Application::getInstance()->end();