<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxBusinessRu
 * @package Bitrix\Sale\Cashbox
 */
class CashboxBusinessRu extends CashboxAtolFarmV4
{
	public const SUPPORTED_KKM_ATOL = 'atol';
	public const SUPPORTED_KKM_SHTRIHM = 'shtrih-m';
	public const SUPPORTED_KKM_EVOTOR = 'evotor';

	public const SERVICE_URL = 'https://check.business.ru/api-bitrix24/v4';
	public const SERVICE_TEST_URL = 'https://check-dev.business.ru/api-bitrix24/v4';

	public const TOKEN_OPTION_NAME = 'business_ru_access_token';

	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_BUSINESS_RU_TITLE');
	}

	public function buildCheckQuery(Check $check)
	{
		$result = parent::buildCheckQuery($check);

		$result['service']['vendor_name'] = 'Bitrix24';
		$result['print_check'] = false;

		return $result;
	}

	/**
	 * @return array[]
	 */
	public static function getSupportedKkmModels()
	{
		return [
			self::SUPPORTED_KKM_ATOL => [
				'NAME' => Localization\Loc::getMessage('SALE_CASHBOX_BUSINESS_RU_KKM_ATOL')
			],
			self::SUPPORTED_KKM_SHTRIHM => [
				'NAME' => Localization\Loc::getMessage('SALE_CASHBOX_BUSINESS_RU_KKM_SHTRIHM')
			],
			self::SUPPORTED_KKM_EVOTOR => [
				'NAME' => Localization\Loc::getMessage('SALE_CASHBOX_BUSINESS_RU_KKM_EVOTOR')
			],
		];
	}

	/**
	 * @return array
	 */
	public static function getGeneralRequiredFields()
	{
		$fields = parent::getGeneralRequiredFields();

		$map = Internals\CashboxTable::getMap();
		$fields['KKM_ID'] = $map['KKM_ID']['title'];

		return $fields;
	}
}