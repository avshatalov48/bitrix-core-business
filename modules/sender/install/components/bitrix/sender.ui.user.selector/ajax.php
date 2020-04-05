<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;

if (!Loader::includeModule('sender'))
{
	return;
}

$actions = [];
$actions[] = Controller\Action::create('getDestinationData')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$result = [
			'LAST' => [],
			'DEST_SORT' => [],
			'USERS' => [],
			'ROLES' => [],
		];

		$content = $response->initContentJson();
		$content->add('DATA', $result);

		if (!\Bitrix\Main\Loader::includeModule('socialnetwork'))
			return;

		$arStructure = \CSocNetLogDestination::GetStucture(array());
		$result['DEPARTMENT'] = $arStructure['department'];
		$result['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
		$result['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

		$result['DEST_SORT'] = \CSocNetLogDestination::GetDestinationSort(array(
			"DEST_CONTEXT" => "CRM_AUTOMATION",
		));

		\CSocNetLogDestination::fillLastDestination(
			$result['DEST_SORT'],
			$result['LAST']
		);

		$destUser = array();
		foreach ($result["LAST"]["USERS"] as $value)
		{
			$destUser[] = str_replace("U", "", $value);
		}

		$result["USERS"] = \CSocNetLogDestination::getUsers(array("id" => $destUser));
		$result["ROLES"] = array();

		$content->add('DATA', $result);
	}
);
$checker = CommonAjax\Checker::getReadPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();