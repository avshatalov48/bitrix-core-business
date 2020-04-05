<?php

namespace Bitrix\Sale\Basket;

class RefreshFactory
{
	const TYPE_FULL = 'full';
	const TYPE_SINGLE = 'single';

	public static function create($type = '', $params = null)
	{
		switch ($type)
		{
			case self::TYPE_SINGLE:
				$strategy = new SingleRefreshStrategy($params);
				break;

			case self::TYPE_FULL:
			default:
				$strategy = new FullRefreshStrategy($params);
		}

		return $strategy;
	}

	public static function createSingle($basketItemCode)
	{
		return static::create(self::TYPE_SINGLE, array(
			'BASKET_ITEM_CODE' => $basketItemCode
		));
	}
}