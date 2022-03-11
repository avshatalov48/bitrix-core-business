<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxAtolFarmV5
 *
 * @package Bitrix\Sale\Cashbox
 */
class CashboxAtolFarmV5 extends CashboxAtolFarmV4
{
	const SERVICE_URL = 'https://online.atol.ru/possystem/v5';
	const SERVICE_TEST_URL = 'https://testonline.atol.ru/possystem/v5';

	private const MARK_CODE_TYPE_GS1_M = 'gs1m';

	/**
	 * @inheritDoc
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_V5_TITLE');
	}

	/**
	 * @inheritDoc
	 */
	protected function buildPosition(array $checkData, array $item): array
	{
		$result = [
			'name' => $this->buildPositionName($item),
			'price' => $this->buildPositionPrice($item),
			'sum' => $this->buildPositionSum($item),
			'quantity' => $this->buildPositionQuantity($item),
			'measure' => $this->buildPositionMeasure($item),
			'payment_method' => $this->buildPositionPaymentMethod($checkData),
			'payment_object' => $this->buildPositionPaymentObject($item),
			'vat' => [
				'type' => $this->buildPositionVatType($checkData, $item)
			],
		];

		if (isset($item['marking_code']))
		{
			$result['mark_processing_mode'] = '0';
			$result['mark_code'] = $this->buildPositionGs1mMarkCode($item);
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected static function hasMeasureSettings(): bool
	{
		return true;
	}

	/**
	 * @param array $item
	 * @return int|null
	 */
	private function buildPositionMeasure(array $item): ?int
	{
		$tag2108Value = $this->getValueFromSettings('MEASURE', $item['measure_code']);
		if (is_null($tag2108Value) || $tag2108Value === '')
		{
			$tag2108Value = $this->getValueFromSettings('MEASURE', 'DEFAULT');
		}

		return (is_null($tag2108Value) || $tag2108Value === '') ? null : (int)$tag2108Value;
	}

	/**
	 * @param array $item
	 * @return array
	 */
	private function buildPositionGs1mMarkCode(array $item): array
	{
		return [
			self::MARK_CODE_TYPE_GS1_M => base64_encode($item['marking_code']),
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getPaymentObjectMap()
	{
		return [
			Check::PAYMENT_OBJECT_COMMODITY => 1,
			Check::PAYMENT_OBJECT_SERVICE => 4,
			Check::PAYMENT_OBJECT_JOB => 3,
			Check::PAYMENT_OBJECT_EXCISE => 2,
			Check::PAYMENT_OBJECT_PAYMENT => 10,
			Check::PAYMENT_OBJECT_GAMBLING_BET => 5,
			Check::PAYMENT_OBJECT_GAMBLING_PRIZE => 6,
			Check::PAYMENT_OBJECT_LOTTERY => 7,
			Check::PAYMENT_OBJECT_LOTTERY_PRIZE => 8,
			Check::PAYMENT_OBJECT_INTELLECTUAL_ACTIVITY => 9,
			Check::PAYMENT_OBJECT_AGENT_COMMISSION => 11,
			Check::PAYMENT_OBJECT_COMPOSITE => 12,
			Check::PAYMENT_OBJECT_ANOTHER => 13,
			Check::PAYMENT_OBJECT_PROPERTY_RIGHT => 14,
			Check::PAYMENT_OBJECT_NON_OPERATING_GAIN => 15,
			Check::PAYMENT_OBJECT_SALES_TAX => 17,
			Check::PAYMENT_OBJECT_RESORT_FEE => 18,
			Check::PAYMENT_OBJECT_DEPOSIT => 19,
			Check::PAYMENT_OBJECT_EXPENSE => 20,
			Check::PAYMENT_OBJECT_PENSION_INSURANCE_IP => 21,
			Check::PAYMENT_OBJECT_PENSION_INSURANCE => 22,
			Check::PAYMENT_OBJECT_MEDICAL_INSURANCE_IP => 23,
			Check::PAYMENT_OBJECT_MEDICAL_INSURANCE => 24,
			Check::PAYMENT_OBJECT_SOCIAL_INSURANCE => 25,
			Check::PAYMENT_OBJECT_CASINO_PAYMENT => 26,
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING_EXCISE => 30,
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_EXCISE => 31,
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING => 32,
			Check::PAYMENT_OBJECT_COMMODITY_MARKING => 33,
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function getFfdVersion(): ?float
	{
		return 1.2;
	}

	/**
	 * @inheritDoc
	 */
	public static function isCorrectionOn(): bool
	{
		return false;
	}
}
