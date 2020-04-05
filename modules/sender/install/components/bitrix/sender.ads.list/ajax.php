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
$actions[] = Controller\Action::create('send')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->send();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
);
$actions[] = Controller\Action::create('pause')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->pause();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
);
$actions[] = Controller\Action::create('resume')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->resume();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
);
$actions[] = Controller\Action::create('stop')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->stop();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
);
$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->remove();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
);
$actions[] = Controller\Action::create('copy')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$copiedId = $letter->copy();

		$content = $response->initContentJson();
		$content->add('id', $letter->getId());
		$content->add('copiedId', $copiedId);
		$content->getErrorCollection()->add($letter->getErrors());
	}
);
$checker = CommonAjax\Checker::getModifyAdPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();