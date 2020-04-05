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
		$entity = new Entity\Segment($request->get('id'));
		$entity->remove();

		$content = $response->initContentJson();
		$content->getErrorCollection()->add($entity->getErrors());
	}
);
$checker = CommonAjax\Checker::getModifySegmentPermissionChecker();

Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();