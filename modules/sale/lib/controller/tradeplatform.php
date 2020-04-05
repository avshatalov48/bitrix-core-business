<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\TradingPlatformTable;

class TradePlatform extends Controller
{
	public function getFieldsAction()
	{
		$entity = new \Bitrix\Sale\Rest\Entity\TradePlatform();
		return ['TRADE_PLATFORM'=>$entity->prepareFieldInfos(
			$entity->getFields()
		)];
	}

	public function listAction($select=[], $filter=[], $order=[], PageNavigation $pageNavigation)
	{
		$select = empty($select)? ['*']:$select;
		$order = empty($order)? ['ID'=>'ASC']:$order;

		$tradingPlatforms = TradingPlatformTable::getList(
			[
				'select'=>$select,
				'filter'=>$filter,
				'order'=>$order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getLimit()
			]
		)->fetchAll();

		return new Page('TRADE_PLATFORMS', $tradingPlatforms, function() use ($filter)
		{
			return TradingPlatformTable::getCount($filter);
		});
	}
}