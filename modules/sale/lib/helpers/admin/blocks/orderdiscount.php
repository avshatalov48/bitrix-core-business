<?php


namespace Bitrix\Sale\Helpers\Admin\Blocks;


use Bitrix\Sale\Helpers\Admin;
use Bitrix\Sale\Order;

class OrderDiscount
{
	static public function getOrderedDiscounts(Order $order, $needRecalculate = true)
	{
		return static::prepare(
			Admin\OrderEdit::getOrderedDiscounts($order, $needRecalculate));
	}

	static protected function prepare($list)
	{
		if(is_array($list) && count($list)>0)
		{
			if(isset($list['DISCOUNT_LIST']))
			{
				foreach ($list['DISCOUNT_LIST'] as $k => $item)
				{
					if(isset($item['EDIT_PAGE_URL']))
					{
						$params = static::getEditPageUrlParams();
						if(is_null($params) == false)
						{
							$list['DISCOUNT_LIST'][$k]['EDIT_PAGE_URL_PARAMS'] = $params;
						}
					}
				}
			}
		}
		return $list;
	}

	static public function getEditPageUrlParams(array $items = null)
	{
		return null;
	}
}