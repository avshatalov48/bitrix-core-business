<?php

namespace Sale\Handlers\DiscountPreset;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

class SimpleProductExtra extends SimpleProduct
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_SIMPLEPRODUCT_EXTRA_NAME');
	}

	protected function getLabelDiscountValue()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_SIMPLEPRODUCT_EXTRA_LABEL_DISCOUNT_VALUE');
	}

	protected function getTypeOfDiscount()
	{
		return static::ACTION_TYPE_EXTRA;
	}

	public function getSort()
	{
		return 300;
	}

	protected function addErrorEmptyActionValue(): void
	{
		$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_EXTRA_VALUE'));
	}
}
