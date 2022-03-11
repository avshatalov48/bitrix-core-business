<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxBitrixV2
 * @package Bitrix\Sale\Cashbox
 */
class CashboxBitrixV2 extends CashboxBitrix
{
	const CODE_VAT_0 = 'vat0';
	const CODE_VAT_10 = 'vat10';
	const CODE_VAT_20 = 'vat20';
	const CODE_CALC_VAT_10 = 'vat110';
	const CODE_CALC_VAT_20 = 'vat120';

	/**
	 * @param Check $check
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function buildCheckQuery(Check $check)
	{
		$data = $check->getDataForCheck();

		/** @var Main\Type\DateTime $dateTime */
		$dateTime = $data['date_create'];

		$phone = \NormalizePhone($data['client_phone']);
		if (is_string($phone))
		{
			if ($phone[0] === '7')
				$phone = '+'.$phone;
		}
		else
		{
			$phone = '';
		}

		$client = $data['client_email'];
		if ($this->getValueFromSettings('CLIENT', 'INFO') === 'PHONE'
			&& $phone
		)
		{
			$client = $phone;
		}

		$result = [
			'type' => $check::getCalculatedSign() === Check::CALCULATED_SIGN_INCOME ? 'sell' : 'sellReturn',
			'timestamp' => $dateTime->format('d.m.Y H:i:s'),
			'external_id' => static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']),
			'taxationType' => $this->getValueFromSettings('TAX', 'SNO'),
			'zn' => (string)$this->getField('NUMBER_KKM'),
			'clientInfo' => [
				'emailOrPhone' => $client,
			],
			'payments' => [],
			'items' => [],
			'total' => (float)$data['total_sum']
		];

		foreach ($data['payments'] as $payment)
		{
			$result['payments'][] = [
				'type' => $this->getValueFromSettings('PAYMENT_TYPE', $payment['type']),
				'sum' => (float)$payment['sum']
			];
		}

		foreach ($data['items'] as $item)
		{
			$result['items'][] = $this->buildPosition($data, $item);
		}

		return $result;
	}

	protected function buildPosition(array $checkData, array $item)
	{
		$checkTypeMap = $this->getCheckTypeMap();
		$paymentObjectMap = $this->getPaymentObjectMap();

		$vat = $this->getValueFromSettings('VAT', $item['vat']);
		if ($vat === null)
		{
			$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');
		}

		$position = [
			'type' => 'position',
			'name' => $item['name'],
			'price' => (float)$item['price'],
			'quantity' => $item['quantity'],
			'amount' => (float)$item['sum'],
			'paymentMethod' => $checkTypeMap[$checkData['type']],
			'paymentObject' => $paymentObjectMap[$item['payment_object']],
			'tax' => [
				'type' => $this->mapVatValue($checkData['type'], $vat)
			],
		];

		if (isset($item['supplier_info']))
		{
			$position['supplierInfo'] = [
				'phones' => $item['supplier_info']['phones'],
				'name' => $item['supplier_info']['name'],
				'vatin' => $item['supplier_info']['inn']
			];
		}

		if (isset($item['nomenclature_code']))
		{
			$position['nomenclatureCode'] = base64_encode($item['nomenclature_code']);
		}

		return $position;
	}

	/**
	 * @param $checkType
	 * @param $vat
	 * @return mixed
	 */
	protected function mapVatValue($checkType, $vat)
	{
		$map = [
			self::CODE_VAT_10 => [
				PrepaymentCheck::getType() => self::CODE_CALC_VAT_10,
				PrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_10,
				PrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_10,
			],
			self::CODE_VAT_20 => [
				PrepaymentCheck::getType() => self::CODE_CALC_VAT_20,
				PrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_20,
				PrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_20,
				FullPrepaymentCheck::getType() => self::CODE_CALC_VAT_20,
				FullPrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_20,
				FullPrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_20,
			],
		];

		return $map[$vat][$checkType] ?? $vat;
	}

	/**
	 * @return array
	 */
	protected function getPaymentObjectMap()
	{
		return [
			Check::PAYMENT_OBJECT_COMMODITY => 'commodity',
			Check::PAYMENT_OBJECT_SERVICE => 'service',
			Check::PAYMENT_OBJECT_JOB => 'job',
			Check::PAYMENT_OBJECT_EXCISE => 'excise',
			Check::PAYMENT_OBJECT_PAYMENT => 'payment',
			Check::PAYMENT_OBJECT_GAMBLING_BET => 'gamblingBet',
			Check::PAYMENT_OBJECT_GAMBLING_PRIZE => 'gamblingPrize',
			Check::PAYMENT_OBJECT_LOTTERY => 'lottery',
			Check::PAYMENT_OBJECT_LOTTERY_PRIZE => 'lotteryPrize',
			Check::PAYMENT_OBJECT_INTELLECTUAL_ACTIVITY => 'intellectualActivity',
			Check::PAYMENT_OBJECT_AGENT_COMMISSION => 'agentCommission',
			Check::PAYMENT_OBJECT_COMPOSITE => 'composite',
			Check::PAYMENT_OBJECT_ANOTHER => 'another',
			Check::PAYMENT_OBJECT_PROPERTY_RIGHT => 'proprietaryLaw',
			Check::PAYMENT_OBJECT_NON_OPERATING_GAIN => 'nonOperatingIncome',
			Check::PAYMENT_OBJECT_SALES_TAX => 'merchantTax',
			Check::PAYMENT_OBJECT_RESORT_FEE => 'resortFee',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING_EXCISE => 'excise',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_EXCISE => 'excise',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING => 'commodity',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING => 'commodity',
		];
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_TITLE');
	}


	/**
	 * @return array
	 */
	protected function getCheckTypeMap()
	{
		return [
			SellCheck::getType() => 'fullPayment',
			SellReturnCashCheck::getType() => 'fullPayment',
			SellReturnCheck::getType() => 'fullPayment',
			AdvancePaymentCheck::getType() => 'advance',
			AdvanceReturnCashCheck::getType() => 'advance',
			AdvanceReturnCheck::getType() => 'advance',
			CreditCheck::getType() => 'credit',
			CreditReturnCheck::getType() => 'credit',
			CreditPaymentCheck::getType() => 'creditPayment',
			CreditPaymentReturnCashCheck::getType() => 'creditPayment',
			CreditPaymentReturnCheck::getType() => 'creditPayment',
			PrepaymentCheck::getType() => 'prepayment',
			PrepaymentReturnCheck::getType() => 'prepayment',
			PrepaymentReturnCashCheck::getType() => 'prepayment',
			FullPrepaymentCheck::getType() => 'fullPrepayment',
			FullPrepaymentReturnCheck::getType() => 'fullPrepayment',
			FullPrepaymentReturnCashCheck::getType() => 'fullPrepayment',
		];
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
			$settings['TAX'] = [
				'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_SNO'),
				'REQUIRED' => 'Y',
				'ITEMS' => [
					'SNO' => [
						'TYPE' => 'ENUM',
						'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_SNO_LABEL'),
						'VALUE' => 'osn',
						'OPTIONS' => [
							'osn' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_OSN'),
							'usnIncome' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_UI'),
							'usnIncomeOutcome' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_UIO'),
							'envd' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_ENVD'),
							'esn' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_ESN'),
							'patent' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SNO_PATENT')
						]
					]
				]
			];
		}

		$settings['CLIENT'] = [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_CLIENT'),
			'ITEMS' => [
				'INFO' => [
					'TYPE' => 'ENUM',
					'VALUE' => 'NONE',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_CLIENT_INFO'),
					'OPTIONS' => [
						'EMAIL' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_CLIENT_EMAIL'),
						'PHONE' => Localization\Loc::getMessage('SALE_CASHBOX_BITRIX_V2_SETTINGS_CLIENT_PHONE'),
					]
				],
			]
		];

		return $settings;
	}

	/**
	 * @param Main\HttpRequest $request
	 * @return array
	 */
	public static function extractSettingsFromRequest(Main\HttpRequest $request)
	{
		return $request->get('SETTINGS');
	}

	/**
	 * @return array
	 */
	public static function getSupportedKkmModels()
	{
		return [
			'atol' => [
				'NAME' => 'ATOL',
				'SETTINGS' => [
					'VAT' => [
						'NOT_VAT' => 'none',
						0 => self::CODE_VAT_0,
						10 => self::CODE_VAT_10,
						20 => self::CODE_VAT_20
					],
					'PAYMENT_TYPE' => [
						Check::PAYMENT_TYPE_CASH => 'cash',
						Check::PAYMENT_TYPE_CASHLESS => 'electronically',
						Check::PAYMENT_TYPE_ADVANCE => 'prepaid',
						Check::PAYMENT_TYPE_CREDIT => 'credit'
					]
				]
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function getFfdVersion(): ?float
	{
		return 1.05;
	}

	/**
	 * @param array $data
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getCashboxList(array $data)
	{
		$result = parent::getCashboxList($data);

		foreach ($result as $zn => $cashbox)
		{
			if (!isset($cashbox['ID']))
			{
				continue;
			}

			$current = Manager::getCashboxFromCache($cashbox['ID']);

			if ($current['HANDLER'] !== '\\'.static::class)
			{
				$cashbox['HANDLER'] = '\\'.static::class;
				$cashbox['SETTINGS'] = static::convertSettings($cashbox['KKM_ID'], $cashbox['SETTINGS']);

				$result[$zn] = $cashbox;
			}
		}

		return $result;
	}

	/**
	 * @param $kkmId
	 * @param array $settings
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected static function convertSettings($kkmId, array $settings)
	{
		$result = [];

		$currentModel = static::getSupportedKkmModels()[$kkmId];

		foreach ($settings as $key => $setting)
		{
			if ($key === 'PAYMENT_TYPE')
			{
				$result[$key] = $currentModel['SETTINGS']['PAYMENT_TYPE'];
			}
			elseif ($key === 'VAT')
			{
				$result[$key]['NOT_VAT']= $currentModel['SETTINGS']['VAT']['NOT_VAT'];

				if (Main\Loader::includeModule('catalog'))
				{
					$dbRes = Catalog\VatTable::getList(array('filter' => array('ACTIVE' => 'Y')));
					$vatList = $dbRes->fetchAll();
					if ($vatList)
					{
						foreach ($vatList as $vat)
						{
							if (isset($currentModel['SETTINGS']['VAT'][(int)$vat['RATE']]))
							{
								$result[$key][(int)$vat['ID']] = $currentModel['SETTINGS']['VAT'][(int)$vat['RATE']];
							}
							else
							{
								$result[$key][(int)$vat['ID']] = $currentModel['SETTINGS']['VAT']['NOT_VAT'];
							}
						}
					}
				}
			}
			else
			{
				$result[$key] = $setting;
			}
		}

		return $result;
	}
}
