<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Error;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Sale\Result;
use Bitrix\Sale\TradingPlatformTable;

class TradePlatform extends ControllerBase
{
	public function getFieldsAction(): array
	{
		$view = $this->getViewManager()
			->getView($this);

		return ['TRADE_PLATFORM'=>$view->prepareFieldInfos(
			$view->getFields()
		)];
	}

	/**
	 * @param array $select
	 * @param array $filter
	 * @param array $order
	 * @param PageNavigation $pageNavigation
	 * @return Page
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function listAction(PageNavigation $pageNavigation, array $select = [], array $filter = [], array $order = []): Page
	{
		$select = empty($select) ? ['*'] : $select;
		$order = empty($order) ? ['ID'=>'ASC'] : $order;

		$tradingPlatforms = TradingPlatformTable::getList(
			[
				'select' => $select,
				'filter' => $filter,
				'order' => $order,
				'offset' => $pageNavigation->getOffset(),
				'limit' => $pageNavigation->getOffset(),
			]
		)->fetchAll();

		return new Page('TRADE_PLATFORMS', $tradingPlatforms, function() use ($filter)
		{
			return TradingPlatformTable::getCount($filter);
		});
	}

	protected function checkReadPermissionEntity(): Result
	{
		$r = new Result();

		$saleModulePermissions = self::getApplication()->GetGroupRight("sale");
		if ($saleModulePermissions  == "D")
		{
			$r->addError(new Error('Access Denied', 200040300010));
		}
		return $r;
	}
}