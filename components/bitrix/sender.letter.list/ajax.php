<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Security;
use Bitrix\Sender\UI\PageNavigation;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

$readChecker = CommonAjax\Checker::getViewLetterPermissionChecker();
$writeChecker = CommonAjax\Checker::getModifyLetterPermissionChecker();
$pauseStartStopChecker = CommonAjax\Checker::getPauseStopStartLetterPermissionChecker();

$actions = array();
$actions[] = Controller\Action::create('send')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$userId = Security\User::current()->getId();
		if ($userId)
		{
			$letter->set('UPDATED_BY', $userId);
		}
		$letter->send();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$pauseStartStopChecker]);
$actions[] = Controller\Action::create('pause')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$userId = Security\User::current()->getId();
		if ($userId)
		{
			$letter->set('UPDATED_BY', $userId);
		}
		$letter->pause();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$pauseStartStopChecker]);
$actions[] = Controller\Action::create('resume')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$userId = Security\User::current()->getId();
		if ($userId)
		{
			$letter->set('UPDATED_BY', $userId);
		}
		$letter->resume();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$pauseStartStopChecker]);
$actions[] = Controller\Action::create('stop')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$userId = Security\User::current()->getId();
		if ($userId)
		{
			$letter->set('UPDATED_BY', $userId);
		}
		$letter->stop();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$pauseStartStopChecker]);
$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$letter->remove();

		(new PageNavigation("page-sender-letters"))->resetSessionVar();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$readChecker, $writeChecker]);
$actions[] = Controller\Action::create('copy')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$copiedId = $letter->copy();

		$content = $response->initContentJson();
		$content->add('id', $letter->getId());
		$content->add('copiedId', $copiedId);
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$readChecker, $writeChecker]);
$actions[] = Controller\Action::create('acceptAgreement')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		if (!Security\Agreement::acceptByCurrentUser())
		{
			$response->initContentJson()->addError('');
		}
	}
);

Controller\Listener::create()->setActions($actions)->run();