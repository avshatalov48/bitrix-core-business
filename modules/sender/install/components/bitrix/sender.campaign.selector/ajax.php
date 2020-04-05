<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!Loader::includeModule('sender'))
{
	return;
}

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Sender\Internals\QueryController as Controller;
use Bitrix\Sender\Internals\CommonAjax;
use Bitrix\Sender\Entity;

use Bitrix\Sender\UI;

Loc::loadMessages(__FILE__);

$actions = array();
$actions[] = Controller\Action::create('getCampaigns')->setHandler(
	function (HttpRequest $request, Controller\Response $response)
	{
		$view = UI\TileView::create()
			->addSection(UI\TileView::SECTION_LAST)
			->addSection(UI\TileView::SECTION_FREQ)
			->addSection(UI\TileView::SECTION_ALL);

		$sites = Entity\Campaign::getSites();
		$list = Entity\Campaign::getList([
			'select' => ['ID', 'NAME', 'MAX_DATE_INSERT', 'COUNT_LETTERS', 'SUBSCRIBER_COUNT', 'SITE_ID'],
			'runtime' => [
				new ExpressionField('MAX_DATE_INSERT', 'MAX(%s)', 'CHAIN.DATE_INSERT'),
				new ExpressionField('COUNT_LETTERS', 'COUNT(%s)', 'CHAIN.ID'),
			]
		]);
		foreach ($list as $item)
		{
			$view->addTile(
				$item['ID'],
				$item['NAME'],
				[
					UI\TileView::SECTION_FREQ => $item['COUNT_LETTERS'],
					UI\TileView::SECTION_LAST => $item['MAX_DATE_INSERT'],
					'subscriberCount' => $item['SUBSCRIBER_COUNT'],
					'siteId' => $item['SITE_ID'],
					'siteName' => $sites[$item['SITE_ID']],
				]
			);
		}

		// get response
		$response->initContentJson()->set(array(
			'list' => $view->get(),
		));
	}
);
$checker = CommonAjax\Checker::getViewLetterPermissionChecker();
Controller\Listener::create()->addChecker($checker)->setActions($actions)->run();