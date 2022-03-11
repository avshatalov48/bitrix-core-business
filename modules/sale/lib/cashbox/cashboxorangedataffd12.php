<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxOrangeDataFfd12
 *
 * @package Bitrix\Sale\Cashbox
 */
class CashboxOrangeDataFfd12 extends CashboxOrangeData
{
	private const FFD_12_VERSION = 4;

	/**
	 * @see http://www.consultant.ru/document/cons_doc_LAW_362322/78cda7f497d697a7a544ce05660a93fe557cf915/
	 */
	private const PLANNED_STATUS_SALE = 1;
	private const PLANNED_STATUS_SALE_RETURN = 3;

	/**
	 * @inheritDoc
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_ORANGE_DATA_TITLE_FFD_12');
	}

	/**
	 * @inheritDoc
	 */
	protected function buildPosition(array $checkData, array $item, bool $isSellReturn): array
	{
		$result = [
			'text' => $this->buildPositionText($item),
			'quantity' => $this->buildPositionQuantity($item),
			'price' => $this->buildPositionPrice($item),
			'tax' => $this->buildPositionTax($checkData, $item),
			'paymentMethodType' => $this->buildPositionPaymentMethodType($checkData),
			'paymentSubjectType' => $this->buildPositionPaymentSubjectType($item),
			'plannedStatus' => $isSellReturn ? self::PLANNED_STATUS_SALE_RETURN : self::PLANNED_STATUS_SALE,
		];

		if (isset($item['marking_code']))
		{
			$result['itemCode'] = $this->buildPositionMarkingCode($item);
		}

		$result['quantityMeasurementUnit'] = $this->buildPositionQuantityMeasurementUnit($item);

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function buildCheckQuery(Check $check)
	{
		$result = parent::buildCheckQuery($check);
		$result['content']['ffdVersion'] = self::FFD_12_VERSION;

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function buildCorrectionCheckQuery(CorrectionCheck $check)
	{
		$data = $this->getCheckData($check);
		$correctionInfo = $data['correction_info'];

		$result = $this->buildCheckQueryByCheckData($data, ($check->getType() === 'sellreturn'));
		$result['content']['ffdVersion'] = self::FFD_12_VERSION;
		$result['content']['correctionType'] = $this->getCorrectionTypeMap($correctionInfo['type']);
		$result['content']['causeDocumentDate'] = $this->getCorrectionCauseDocumentDate($correctionInfo);
		$result['content']['causeDocumentNumber'] = $this->getCorrectionCauseDocumentNumber($correctionInfo);
		$result['content']['totalSum'] = $this->getCorrectionTotalSum($correctionInfo);

		$vats = $this->getVatsByCheckData($data);
		if (is_array($vats))
		{
			foreach ($vats as $vat)
			{
				$result['content'][$vat['code']] = $vat['value'];
			}
		}

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getVatKeyPrefix(): string
	{
		return 'vat';
	}

	/**
	 * @inheritDoc
	 */
	protected function getCorrectionUrlPath(): string
	{
		return '/correction12/';
	}

	/**
	 * @return array
	 */
	protected function getPaymentObjectMap()
	{
		return [
			Check::PAYMENT_OBJECT_COMMODITY => 1,
			Check::PAYMENT_OBJECT_EXCISE => 2,
			Check::PAYMENT_OBJECT_JOB => 3,
			Check::PAYMENT_OBJECT_SERVICE => 4,
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
	 * @param array $item
	 * @return mixed
	 */
	private function buildPositionMarkingCode(array $item)
	{
		return $item['marking_code'];
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
	private function buildPositionQuantityMeasurementUnit(array $item): ?int
	{
		$tag2108Value = $this->getValueFromSettings('MEASURE', $item['measure_code']);
		if (is_null($tag2108Value) || $tag2108Value === '')
		{
			$tag2108Value = $this->getValueFromSettings('MEASURE', 'DEFAULT');
		}

		return (is_null($tag2108Value) || $tag2108Value === '') ? null : (int)$tag2108Value;
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
