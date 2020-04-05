<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\HttpRequest;

use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Security;

if (!Loader::includeModule('sender'))
{
	return;
}

$readChecker = CommonAjax\Checker::getViewLetterPermissionChecker();
$writeChecker = CommonAjax\Checker::getModifyLetterPermissionChecker();

$actions = array();
$actions[] = Controller\Action::create('send')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$letter->send();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$readChecker, $writeChecker]);
$actions[] = Controller\Action::create('pause')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$letter->pause();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$readChecker, $writeChecker]);
$actions[] = Controller\Action::create('resume')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$letter->resume();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$readChecker, $writeChecker]);
$actions[] = Controller\Action::create('stop')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$letter->stop();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers([$readChecker, $writeChecker]);
$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Letter($request->get('id'));
		$letter->remove();

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