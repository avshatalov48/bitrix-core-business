<?php
namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive\View1;

use Bitrix\Sale,
	Bitrix\Sale\Helpers\Admin\Blocks\Archive;

class Schema extends Archive\Schema
{
	/**
	 * Return list of blocks's names
	 * 
	 * @return array		List of block names for archived order with version "1"
	 */
	protected function collectBlocks()
	{		
		return array(
			__NAMESPACE__."\\OrderStatus",
			__NAMESPACE__."\\OrderBuyer",
			__NAMESPACE__."\\OrderShipment",
			__NAMESPACE__."\\OrderFinanceInfo",
			__NAMESPACE__."\\OrderPayment",
			__NAMESPACE__."\\OrderAdditional",
			__NAMESPACE__."\\OrderBasket",
		);
	}
}