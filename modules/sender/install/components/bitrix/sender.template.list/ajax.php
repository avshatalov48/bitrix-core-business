<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Entity;

if (!Loader::includeModule('sender'))
{
	return;
}

$actions = array();
$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$entity = new Entity\Template($request->get('id'));
		$entity->remove();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
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

			$entity = new Entity\Template($id);
			$entity->remove();
			if ($entity->hasErrors())
			{
				$content->getErrorCollection()->add($entity->getErrors());
				break;
			}
		}
	}
);
$checker = CommonAjax\Checker::getModifyLetterPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();