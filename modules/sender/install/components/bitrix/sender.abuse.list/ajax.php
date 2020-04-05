<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\Model;

if (!Loader::includeModule('sender'))
{
	return;
}

$actions = array();
$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$result = Model\AbuseTable::delete($request->get('id'));

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($result->getErrors());
	}
);
$actions[] = Controller\Action::create('removeList')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$list = $request->get('id');
		if (!is_array($list) || empty($list))
		{
			return;
		}

		$content = $response->initContentJson();
		foreach ($list as $id)
		{
			$id = (int) $id;
			if (!$id)
			{
				return;
			}

			$result = Model\AbuseTable::delete($id);
			if (!$result->getErrorCollection()->isEmpty())
			{
				$content->getErrorCollection()->add($result->getErrors());
				break;
			}
		}
	}
);
$checker = CommonAjax\Checker::getModifyAbusePermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();