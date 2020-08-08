<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Security;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

$writeChecker = CommonAjax\Checker::getModifyRcPermissionChecker();
$pauseStartStopChecker = CommonAjax\Checker::getPauseStopStartRcPermissionChecker();

$actions = array();
$actions[] = Controller\Action::create('send')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Rc($request->get('id'));
		$letter->send();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));
$actions[] = Controller\Action::create('pause')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Rc($request->get('id'));
		$letter->pause();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));
$actions[] = Controller\Action::create('resume')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Rc($request->get('id'));
		$letter->resume();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));
$actions[] = Controller\Action::create('wait')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Rc($request->get('id'));
		$letter->wait();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));
$actions[] = Controller\Action::create('halt')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Rc($request->get('id'));
		$letter->halt();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));
$actions[] = Controller\Action::create('stop')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Rc($request->get('id'));
		$letter->stop();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($pauseStartStopChecker));
$actions[] = Controller\Action::create('remove')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Rc($request->get('id'));
		$letter->remove();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($writeChecker));
$actions[] = Controller\Action::create('copy')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$letter = new Entity\Rc($request->get('id'));
		$copiedId = $letter->copy();

		$content = $response->initContentJson();
		$content->add('id', $letter->getId());
		$content->add('copiedId', $copiedId);
		$content->getErrorCollection()->add($letter->getErrors());
	}
)->setCheckers(array($writeChecker));
$actions[] = Controller\Action::create('acceptAgreement')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		if (!Security\Agreement::acceptByCurrentUser())
		{
			$response->initContentJson()->addError('');
		}
	}
);

$readChecker = CommonAjax\Checker::getViewRcPermissionChecker();
Controller\Listener::create()->addChecker($readChecker)->setActions($actions)->run();