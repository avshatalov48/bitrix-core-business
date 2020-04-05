<?php

namespace Sale\Handlers\DiscountPreset;


use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals;
use Bitrix\Sale\Helpers\Admin\Blocks;


Loc::loadMessages(__FILE__);

class PaySystemExtra extends PaySystem
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_PAYSYSTEMEXTRA_NAME');
	}

	protected function getLabelDiscountValue()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_PAYSYSTEMEXTRA_LABEL_DISCOUNT_VALUE');
	}

	protected function getTypeOfDiscount()
	{
		return static::ACTION_TYPE_EXTRA;
	}

	public function getSort()
	{
		return 300;
	}
}