<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

$editChecker           = CommonAjax\Checker::getModifyAdPermissionChecker();
$pauseStartStopChecker = CommonAjax\Checker::getPauseStopStartAdsPermissionChecker();

$actions = array();
$actions[] = Controller\Action::create('send')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->send();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));
$actions[] = Controller\Action::create('pause')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->pause();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));
$actions[] = Controller\Action::create('resume')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->resume();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));

$actions[] = Controller\Action::create('stop')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->stop();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));

$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Ad($request->get('id'));
		$letter->remove();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($editChecker));

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
)->setCheckers(array($editChecker));

Controller\Listener::create()->setActions($actions)->run();