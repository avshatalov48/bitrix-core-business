<?php


namespace Bitrix\Sale\Exchange\Integration\Admin\Blocks;


class OrderDiscount extends \Bitrix\Sale\Helpers\Admin\Blocks\OrderDiscount
{
	static public function getEditPageUrlParams(array $items = null)
	{
		return ['target'=>'_blank'];
	}
}