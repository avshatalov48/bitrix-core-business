<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxBusinessRuV5
 *
 * @package Bitrix\Sale\Cashbox
 */
class CashboxBusinessRuV5 extends CashboxAtolFarmV5
{
	use CashboxBusinessRuTrait;

	public const SERVICE_URL = 'https://check.class365.ru/api-bitrix24/v5';
	public const SERVICE_TEST_URL = 'https://check-alpha.class365.ru/api-bitrix24/v5';

	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_BUSINESS_RU_TITLE_V5');
	}
}
