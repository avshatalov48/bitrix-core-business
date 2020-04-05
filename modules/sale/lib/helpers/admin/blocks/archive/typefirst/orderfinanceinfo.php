<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive\TypeFirst;

use Bitrix\Sale\Helpers\Admin\Blocks,
	Bitrix\Sale\Helpers\Admin\Blocks\Archive\Template;

class OrderFinanceInfo extends Template
{
	protected $name = "financeinfo";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		return Blocks\OrderFinanceInfo::getView($this->order, false);
	}
}