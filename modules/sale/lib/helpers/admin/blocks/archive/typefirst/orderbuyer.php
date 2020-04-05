<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive\TypeFirst;

use Bitrix\Sale\Helpers\Admin\Blocks,
	Bitrix\Sale\Helpers\Admin\Blocks\Archive\Template;

class OrderBuyer extends Template
{
	protected $name = "buyer";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		return Blocks\OrderBuyer::getView($this->order);
	}
}