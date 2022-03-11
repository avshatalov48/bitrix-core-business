<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Trait CashboxBusinessRuTrait
 *
 * @package Bitrix\Sale\Cashbox
 */
trait CashboxBusinessRuTrait
{
	/**
	 * @return bool
	 */
	public static function isCorrectionOn(): bool
	{
		return false;
	}

	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check)
	{
		$result = parent::buildCheckQuery($check);

		$result['service']['vendor_name'] = 'Bitrix24';
		$result['print_check'] = $this->getValueFromSettings('INTERACTION', 'CHECK_REAL_PRINT') === 'Y';

		return $result;
	}

	/**
	 * @return array[]
	 */
	public static function getSupportedKkmModels()
	{
		$result = [];

		foreach ([KkmRepository::ATOL, KkmRepository::EVOTOR, KkmRepository::SHTRIHM] as $kkmCode)
		{
			$result[$kkmCode] = KkmRepository::getByCode($kkmCode);
		}

		return $result;
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

	/**
	 * @param int $modelId
	 * @return array
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = parent::getSettings($modelId);

		$settings['INTERACTION']['ITEMS']['CHECK_REAL_PRINT'] = [
			'TYPE' => 'Y/N',
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BUSINESS_RU_SETTINGS_CHECK_REAL_PRINT_LABEL'),
			'VALUE' => 'N',
		];

		return $settings;
	}

	/**
	 * @return string
	 */
	protected function getOptionPrefix(): string
	{
		return 'business_ru_access_token';
	}
}
