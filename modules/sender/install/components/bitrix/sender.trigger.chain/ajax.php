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
$actions[] = Controller\Action::create('shiftTime')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$entity = new Entity\TriggerCampaign($request->get('id'));
		$entity->getChain()
			->shiftTime((int) $request->get('letterId'), (int) $request->get('timeShift'))
			->save();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
	}
);
$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$entity = new Entity\TriggerCampaign($request->get('id'));
		$entity->getChain()->removeLetter($request->get('letterId'))->save();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
	}
);
$actions[] = Controller\Action::create('moveUp')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$entity = new Entity\TriggerCampaign($request->get('id'));
		$entity->getChain()->moveUp($request->get('letterId'))->save();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
	}
);
$actions[] = Controller\Action::create('moveDown')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$entity = new Entity\TriggerCampaign($request->get('id'));
		$entity->getChain()->moveDown($request->get('letterId'))->save();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
	}
);
$checker = CommonAjax\Checker::getModifyLetterPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();