<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxBitrixV3
 * @package Bitrix\Sale\Cashbox
 */
class CashboxBitrixV3 extends CashboxBitrixV2
{
	public function buildCheckQuery(Check $check)
	{
		$result = parent::buildCheckQuery($check);

		if ($this->isContainMarkingCode($result))
		{
			$result['validateMarkingCodes'] = true;
		}

		return $result;
	}

	protected function isContainMarkingCode(array $check) : bool
	{
		if (isset($check['items']))
		{
			foreach ($check['items'] as $item)
			{
				if (
					isset($item['imcParams'])
					&& !empty($item['imcParams']['imc'])
				)
				{
					return true;
				}
			}
		}

		return false;
	}

	protected function buildPosition(array $checkData, array $item)
	{
		$position = parent::buildPosition($checkData, $item);

		$position['measurementUnit'] = (int)$this->getValueFromSettings('MEASURE', $item['measure_code']);

		if (isset($position['nomenclatureCode']))
		{
			unset($position['nomenclatureCode']);
		}

		if (isset($item['marking_code']))
		{
			$position['imcParams'] = [
				'imcType' => 'auto',
				'imc' => base64_encode($item['marking_code']),
				'itemEstimatedStatus' => $this->buildEstimatedStatus($checkData),
				'imcModeProcessing' => 0,
			];
		}

		return $position;
	}

	protected function buildEstimatedStatus(array $checkData) : string
	{
		if (mb_strpos($checkData['type'], 'sellreturn') === 0)
		{
			return 'itemPieceReturn';
		}

		return 'itemPieceSold';
	}

	/**
	 * @return array
	 */
	protected function getPaymentObjectMap()
	{
		$result = parent::getPaymentObjectMap();

		$result[Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING_EXCISE] = 'exciseWithoutMarking ';
		$result[Check::PAYMENT_OBJECT_COMMODITY_MARKING_EXCISE] = 'exciseWithMarking ';
		$result[Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING] = 'commodityWithoutMarking ';
		$result[Check::PAYMENT_OBJECT_COMMODITY_MARKING] = 'commodityWithMarking';

		return $result;
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V3_TITLE');
	}

	/**
	 * @param int $modelId
	 * @return array
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = parent::getSettings($modelId);

		$kkmList = static::getSupportedKkmModels();
		if (isset($kkmList[$modelId]))
		{
			$settings['MEASURE'] = static::getMeasureSettings();
		}

		return $settings;
	}

	protected static function getMeasureSettings(): array
	{
		$measureItems = [];
		if (Main\Loader::includeModule('catalog'))
		{
			$measuresList = \CCatalogMeasure::getList();
			while ($measure = $measuresList->fetch())
			{
				$measureItems[$measure['CODE']] = [
					'TYPE' => 'STRING',
					'LABEL' => $measure['MEASURE_TITLE'],
					'REQUIRED' => 'Y',
					'VALUE' => MeasureCodeToTag2108Mapper::getTag2108Value($measure['CODE']),
				];
			}
		}

		return [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_MEASURE_SUPPORT_SETTINGS'),
			'ITEMS' => $measureItems,
		];
	}
	/**
	 * @inheritDoc
	 */
	public static function getFfdVersion(): ?float
	{
		return 1.2;
	}
}
