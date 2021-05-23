<?php

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\ListTable;
use Bitrix\Sender\UI;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

Loc::loadMessages(__FILE__);

$actions = array();
$actions[] = Controller\Action::create('getSets')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$view = UI\TileView::create()->addSection(UI\TileView::SECTION_ALL);
		$list = ListTable::getList([
			'select' => ['ID', 'NAME'],
			'order' => ['ID' => 'DESC']
		]);
		foreach ($list as $item)
		{
			$view->addTile($item['ID'], $item['NAME'], []);
		}

		// get response
		$response->initContentJson()->set(array(
			'list' => $view->get(),
		));
	}
);
$checker = CommonAjax\Checker::getViewSegmentPermissionChecker();
Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();