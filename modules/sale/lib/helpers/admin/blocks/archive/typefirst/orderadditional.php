<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks\Archive\TypeFirst;

use Bitrix\Sale\Helpers\Admin\Blocks,
	Bitrix\Sale\Helpers\Admin\Blocks\Archive\Template;

class OrderAdditional extends Template
{
	protected $name = "additional";
	
	/**
	 * @return string $result
	 */
	public function buildBlock()
	{
		return Blocks\OrderAdditional::getView($this->order, "archive");
	}
}