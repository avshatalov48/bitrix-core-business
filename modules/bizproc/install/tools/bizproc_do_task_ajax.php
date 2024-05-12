<?php

const STOP_STATISTICS = true;

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/bizproc/install/tools/bizproc_do_task_ajax.php');

$result = ['SUCCESS' => true];
$user = $GLOBALS['USER'];

if (!Loader::includeModule('bizproc') || !Loader::includeModule('iblock'))
{
	$result['SUCCESS'] = false;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !$user->IsAuthorized() || !check_bitrix_sessid())
{
	$result['SUCCESS'] = false;
	$result['ERROR'] = 'Access denied.';
}

if ($result['SUCCESS'])
{
	$currentUserId = (int)($user->getId());
	$taskService = new \Bitrix\Bizproc\Api\Service\TaskService(
		new \Bitrix\Bizproc\Api\Service\TaskAccessService($currentUserId)
	);

	$taskId = (int)$_REQUEST['TASK_ID'];

	$formData = $_REQUEST + $_FILES;

	$request = new Bitrix\Bizproc\Api\Request\TaskService\DoTaskRequest(
		taskId: $taskId,
		userId: $currentUserId,
		taskRequest: $formData,
	);

	$doTaskResult = $taskService->doTask($request);
	if (!$doTaskResult->isSuccess())
	{
		$result['SUCCESS'] = false;
		$result['ERROR'] = $doTaskResult->getErrorMessages()[0];
	}
}

$result['SUCCESS'] = (empty($result['ERROR']));
echo CUtil::PhpToJSObject($result);
Application::getInstance()->end();
