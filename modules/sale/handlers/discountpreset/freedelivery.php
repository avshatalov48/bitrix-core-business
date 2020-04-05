<?php

namespace Sale\Handlers\DiscountPreset;


use Bitrix\Iblock\SectionTable;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Discount\Preset\ArrayHelper;
use Bitrix\Sale\Discount\Preset\BasePreset;
use Bitrix\Sale\Discount\Preset\HtmlHelper;
use Bitrix\Sale\Discount\Preset\Manager;
use Bitrix\Sale\Discount\Preset\State;
use Bitrix\Sale\Helpers\Admin\OrderEdit;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Helpers\Admin\Blocks;
use Bitrix\Sale\Order;


Loc::loadMessages(__FILE__);

class FreeDelivery extends Delivery
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_FREEDELIVERY_NAME');
	}

	public function getDescription()
	{
		return '';
	}

	/**
	 * @return int
	 */
	public function getCategory()
	{
		return Manager::CATEGORY_DELIVERY;
	}

	protected function renderDiscountValue(State $state, $currency)
	{
		return '';
	}

	public function processSaveInputAmount(State $state)
	{
		$state['discount_type'] = 'Perc';
		$state['discount_value'] = '100';

		return parent::processSaveInputAmount($state);
	}
}