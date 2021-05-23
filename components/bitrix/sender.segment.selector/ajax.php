<?php

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Message;
use Bitrix\Sender\Segment;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

Loc::loadMessages(__FILE__);

$actions = array();
$actions[] = Controller\Action::create('getSegments')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$message = null;
		$messageCode = $request->get('messageCode');
		if ($messageCode)
		{
			$message = Message\Adapter::create($messageCode);
		}

		// get response
		$response->initContentJson()->set(array(
			'list' => Segment\TileView::create($request->get('include') == 'Y')
				->setMessage($message)
				->getSections(),
		));
	}
);
$checker = CommonAjax\Checker::getSelectSegmentPermissionChecker();
Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();