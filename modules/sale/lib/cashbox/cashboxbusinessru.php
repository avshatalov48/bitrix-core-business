<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxBusinessRu
 *
 * @package Bitrix\Sale\Cashbox
 */
class CashboxBusinessRu extends CashboxAtolFarmV4
{
	use CashboxBusinessRuTrait;

	public const SERVICE_URL = 'https://check.business.ru/api-bitrix24/v4';
	public const SERVICE_TEST_URL = 'https://check-alpha.class365.ru/api-bitrix24/v4';

	/**
	 * @inheritDoc
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_BUSINESS_RU_TITLE');
	}
}
