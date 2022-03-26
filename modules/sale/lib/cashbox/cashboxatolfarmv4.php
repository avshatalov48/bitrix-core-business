<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Localization;
use Bitrix\Sale\Result;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxAtolFarmV4
 * @package Bitrix\Sale\Cashbox
 */
class CashboxAtolFarmV4 extends CashboxAtolFarm implements ICorrection
{
	const SERVICE_URL = 'https://online.atol.ru/possystem/v4';
	const SERVICE_TEST_URL = 'https://testonline.atol.ru/possystem/v4';

	const HANDLER_MODE_ACTIVE = 'ACTIVE';
	const HANDLER_MODE_TEST = 'TEST';

	const CODE_CALC_VAT_10 = 'vat110';
	const CODE_CALC_VAT_20 = 'vat120';

	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check)
	{
		$data = $check->getDataForCheck();

		/** @var Main\Type\DateTime $dateTime */
		$dateTime = $data['date_create'];

		$serviceEmail = $this->getValueFromSettings('SERVICE', 'EMAIL');
		if (!$serviceEmail)
		{
			$serviceEmail = static::getDefaultServiceEmail();
		}

		$result = [
			'timestamp' => $dateTime->format('d.m.Y H:i:s'),
			'external_id' => static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']),
			'service' => [
				'callback_url' => $this->getCallbackUrl(),
			],
			'receipt' => [
				'client' => [],
				'company' => [
					'email' => $serviceEmail,
					'sno' => $this->getValueFromSettings('TAX', 'SNO'),
					'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
					'payment_address' => $this->getValueFromSettings('SERVICE', 'P_ADDRESS'),
				],
				'payments' => [],
				'items' => [],
				'total' => (float)$data['total_sum']
			]
		];

		$email = $data['client_email'] ?: '';

		$phone = \NormalizePhone($data['client_phone']);
		if (is_string($phone))
		{
			if ($phone[0] !== '7')
			{
				$phone = '7'.$phone;
			}

			$phone = '+'.$phone;
		}
		else
		{
			$phone = '';
		}

		$clientInfo = $this->getValueFromSettings('CLIENT', 'INFO');
		if ($clientInfo === 'PHONE')
		{
			$result['receipt']['client'] = ['phone' => $phone];
		}
		elseif ($clientInfo === 'EMAIL')
		{
			$result['receipt']['client'] = ['email' => $email];
		}
		else
		{
			$result['receipt']['client'] = [];

			if ($email)
			{
				$result['receipt']['client']['email'] = $email;
			}

			if ($phone)
			{
				$result['receipt']['client']['phone'] = $phone;
			}
		}

		if (isset($data['payments']))
		{
			$paymentTypeMap = $this->getPaymentTypeMap();
			foreach ($data['payments'] as $payment)
			{
				$result['receipt']['payments'][] = [
					'type' => $paymentTypeMap[$payment['type']],
					'sum' => (float)$payment['sum']
				];
			}
		}

		foreach ($data['items'] as $item)
		{
			$result['receipt']['items'][] = $this->buildPosition($data, $item);
		}

		return $result;
	}

	/**
	 * @param array $checkData
	 * @param array $item
	 * @return array
	 */
	protected function buildPosition(array $checkData, array $item): array
	{
		$result = [
			'name' => $this->buildPositionName($item),
			'price' => $this->buildPositionPrice($item),
			'sum' => $this->buildPositionSum($item),
			'quantity' => $this->buildPositionQuantity($item),
			'payment_method' => $this->buildPositionPaymentMethod($checkData),
			'payment_object' => $this->buildPositionPaymentObject($item),
			'vat' => [
				'type' => $this->buildPositionVatType($checkData, $item)
			],
		];

		if (isset($item['nomenclature_code']))
		{
			$result['nomenclature_code'] = $this->buildPositionNomenclatureCode($item['nomenclature_code']);
		}

		return $result;
	}

	/**
	 * @param array $item
	 * @return string
	 */
	protected function buildPositionName(array $item)
	{
		return mb_substr($item['name'], 0, static::MAX_NAME_LENGTH);
	}

	/**
	 * @param array $item
	 * @return float
	 */
	protected function buildPositionPrice(array $item)
	{
		return (float)$item['price'];
	}

	/**
	 * @param array $item
	 * @return float
	 */
	protected function buildPositionSum(array $item)
	{
		return (float)$item['sum'];
	}

	/**
	 * @param array $item
	 * @return mixed
	 */
	protected function buildPositionQuantity(array $item)
	{
		return $item['quantity'];
	}

	/**
	 * @param array $checkData
	 * @return mixed|string
	 */
	protected function buildPositionPaymentMethod(array $checkData)
	{
		$checkTypeMap = $this->getCheckTypeMap();

		return $checkTypeMap[$checkData['type']];
	}

	/**
	 * @param array $item
	 * @return mixed|string
	 */
	protected function buildPositionPaymentObject(array $item)
	{
		$paymentObjectMap = $this->getPaymentObjectMap();

		return $paymentObjectMap[$item['payment_object']];
	}

	/**
	 * @param array $checkData
	 * @param array $item
	 * @return mixed|string
	 */
	protected function buildPositionVatType(array $checkData, array $item)
	{
		$vat = $this->getValueFromSettings('VAT', $item['vat']);
		if ($vat === null)
		{
			$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');
		}

		return $this->mapVatValue($checkData['type'], $vat);
	}

	/**
	 * @param $code
	 * @return string
	 */
	protected function buildPositionNomenclatureCode($code)
	{
		$hexCode = bin2hex($code);
		$hexCodeArray = str_split($hexCode, 2);
		$hexCodeArray = array_map('ToUpper', $hexCodeArray);

		return join(' ', $hexCodeArray);
	}

	/**
	 * @param CorrectionCheck $check
	 * @return Result
	 * @throws Main\SystemException
	 */
	public function printCorrectionImmediately(CorrectionCheck $check)
	{
		$checkQuery = $this->buildCorrectionCheckQuery($check);

		$operation = 'sell_correction';
		if ($check::getCalculatedSign() === Check::CALCULATED_SIGN_CONSUMPTION)
		{
			$operation = 'sell_refund';
		}

		return $this->registerCheck($operation, $checkQuery);
	}

	/**
	 * @param CorrectionCheck $check
	 * @return array
	 */
	public function buildCorrectionCheckQuery(CorrectionCheck $check)
	{
		$data = $check->getDataForCheck();

		/** @var Main\Type\DateTime $dateTime */
		$dateTime = $data['date_create'];

		$documentDate = $data['correction_info']['document_date'];
		if (!$documentDate instanceof Main\Type\Date)
		{
			$documentDate = new Main\Type\Date($documentDate);
		}

		$result = [
			'timestamp' => $dateTime->format('d.m.Y H:i:s'),
			'external_id' => static::buildUuid(static::UUID_TYPE_CHECK, $data['unique_id']),
			'service' => [
				'callback_url' => $this->getCallbackUrl(),
			],
			'correction' => [
				'company' => [
					'sno' => $this->getValueFromSettings('TAX', 'SNO'),
					'inn' => $this->getValueFromSettings('SERVICE', 'INN'),
					'payment_address' => $this->getValueFromSettings('SERVICE', 'P_ADDRESS'),
				],
				'correction_info' => [
					'type' => $data['correction_info']['type'],
					'base_date' => $documentDate->format('d.m.Y H:i:s'),
					'base_number' => $data['correction_info']['document_number'],
					'base_name' => mb_substr(
						$data['correction_info']['description'],
						0,
						255
					),
				],
				'payments' => [],
				'vats' => []
			]
		];

		if (isset($data['payments']))
		{
			$paymentTypeMap = $this->getPaymentTypeMap();
			foreach ($data['payments'] as $payment)
			{
				$result['correction']['payments'][] = [
					'type' => $paymentTypeMap[$payment['type']],
					'sum' => (float)$payment['sum']
				];
			}
		}

		if (isset($data['vats']))
		{
			foreach ($data['vats'] as $item)
			{
				$vat = $this->getValueFromSettings('VAT', $item['type']);
				if (is_null($vat) || $vat === '')
				{
					$vat = $this->getValueFromSettings('VAT', 'NOT_VAT');
				}

				$result['correction']['vats'][] = [
					'type' => $vat,
					'sum' => (float)$item['sum']
				];
			}
		}

		return $result;
	}

	public function checkCorrection(CorrectionCheck $check)
	{
		return $this->checkByUuid(
			$check->getField('EXTERNAL_UUID')
		);
	}

	/**
	 * @param $checkType
	 * @param $vat
	 * @return mixed
	 */
	private function mapVatValue($checkType, $vat)
	{
		$map = [
			self::CODE_VAT_10 => [
				PrepaymentCheck::getType() => self::CODE_CALC_VAT_10,
				PrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_10,
				PrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentReturnCheck::getType() => self::CODE_CALC_VAT_10,
				FullPrepaymentReturnCashCheck::getType() => self::CODE_CALC_VAT_10
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
			Check::PAYMENT_OBJECT_GAMBLING_BET => 'gambling_bet',
			Check::PAYMENT_OBJECT_GAMBLING_PRIZE => 'gambling_prize',
			Check::PAYMENT_OBJECT_LOTTERY => 'lottery',
			Check::PAYMENT_OBJECT_LOTTERY_PRIZE => 'lottery_prize',
			Check::PAYMENT_OBJECT_INTELLECTUAL_ACTIVITY => 'intellectual_activity',
			Check::PAYMENT_OBJECT_AGENT_COMMISSION => 'agent_commission',
			Check::PAYMENT_OBJECT_COMPOSITE => 'composite',
			Check::PAYMENT_OBJECT_ANOTHER => 'another',
			Check::PAYMENT_OBJECT_PROPERTY_RIGHT => 'property_right',
			Check::PAYMENT_OBJECT_NON_OPERATING_GAIN => 'non-operating_gain',
			Check::PAYMENT_OBJECT_SALES_TAX => 'sales_tax',
			Check::PAYMENT_OBJECT_RESORT_FEE => 'resort_fee',
			Check::PAYMENT_OBJECT_DEPOSIT => 'deposit',
			Check::PAYMENT_OBJECT_EXPENSE => 'expense',
			Check::PAYMENT_OBJECT_PENSION_INSURANCE_IP => 'pension_insurance_ip',
			Check::PAYMENT_OBJECT_PENSION_INSURANCE => 'pension_insurance',
			Check::PAYMENT_OBJECT_MEDICAL_INSURANCE_IP => 'medical_insurance_ip',
			Check::PAYMENT_OBJECT_MEDICAL_INSURANCE => 'medical_insurance',
			Check::PAYMENT_OBJECT_SOCIAL_INSURANCE => 'social_insurance',
			Check::PAYMENT_OBJECT_CASINO_PAYMENT => 'casino_payment',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING_EXCISE => 'excise',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_EXCISE => 'excise',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING => 'commodity',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING => 'commodity',
		];
	}

	/**
	 * @return array
	 */
	private function getPaymentTypeMap()
	{
		return array(
			Check::PAYMENT_TYPE_CASH => 0,
			Check::PAYMENT_TYPE_CASHLESS => 1,
			Check::PAYMENT_TYPE_ADVANCE => 2,
			Check::PAYMENT_TYPE_CREDIT => 3,
		);
	}

	/**
	 * @return string
	 */
	private static function getDefaultServiceEmail()
	{
		return Main\Config\Option::get('main', 'email_from');
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_V4_TITLE');
	}

	/**
	 * @return array
	 */
	protected function getCheckTypeMap()
	{
		return array(
			SellCheck::getType() => 'full_payment',
			SellReturnCashCheck::getType() => 'full_payment',
			SellReturnCheck::getType() => 'full_payment',
			AdvancePaymentCheck::getType() => 'advance',
			AdvanceReturnCashCheck::getType() => 'advance',
			AdvanceReturnCheck::getType() => 'advance',
			PrepaymentCheck::getType() => 'prepayment',
			PrepaymentReturnCheck::getType() => 'prepayment',
			PrepaymentReturnCashCheck::getType() => 'prepayment',
			FullPrepaymentCheck::getType() => 'full_prepayment',
			FullPrepaymentReturnCheck::getType() => 'full_prepayment',
			FullPrepaymentReturnCashCheck::getType() => 'full_prepayment',
			CreditCheck::getType() => 'credit',
			CreditReturnCheck::getType() => 'credit',
			CreditPaymentCheck::getType() => 'credit_payment',
			CreditPaymentReturnCashCheck::getType() => 'credit_payment',
			CreditPaymentReturnCheck::getType() => 'credit_payment',
		);
	}

	/**
	 * @param $operation
	 * @param $token
	 * @param array $queryData
	 * @return string
	 * @throws Main\SystemException
	 */
	protected function getRequestUrl($operation, $token, array $queryData = array())
	{
		$serviceUrl = static::SERVICE_URL;

		if ($this->getValueFromSettings('INTERACTION', 'MODE_HANDLER') === static::HANDLER_MODE_TEST)
		{
			$serviceUrl = static::SERVICE_TEST_URL;
		}

		$groupCode = $this->getField('NUMBER_KKM');

		if ($operation === static::OPERATION_CHECK_REGISTRY)
		{
			return $serviceUrl.'/'.$groupCode.'/'.$queryData['CHECK_TYPE'].'?token='.$token;
		}
		elseif ($operation === static::OPERATION_CHECK_CHECK)
		{
			return $serviceUrl.'/'.$groupCode.'/report/'.$queryData['EXTERNAL_UUID'].'?token='.$token;
		}
		elseif ($operation === static::OPERATION_GET_TOKEN)
		{
			return $serviceUrl.'/getToken';
		}

		throw new Main\SystemException();
	}

	/**
	 * @param int $modelId
	 * @return array
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = parent::getSettings($modelId);
		unset($settings['PAYMENT_TYPE']);

		$settings['SERVICE']['ITEMS']['EMAIL'] = [
			'TYPE' => 'STRING',
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_SERVICE_EMAIL_LABEL'),
			'VALUE' => static::getDefaultServiceEmail()
		];

		$settings['INTERACTION'] = [
			'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_INTERACTION'),
			'ITEMS' => [
				'MODE_HANDLER' => [
					'TYPE' => 'ENUM',
					'LABEL' => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_SETTINGS_MODE_HANDLER_LABEL'),
					'OPTIONS' => [
						static::HANDLER_MODE_ACTIVE => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_MODE_ACTIVE'),
						static::HANDLER_MODE_TEST => Localization\Loc::getMessage('SALE_CASHBOX_ATOL_FARM_MODE_TEST'),
					]
				]
			]
		];

		return $settings;
	}

	/**
	 * @param array $checkData
	 * @return Result
	 */
	protected function validateCheckQuery(array $checkData)
	{
		$result = new Result();

		if ($checkData['receipt']['client']['email'] === '' && $checkData['receipt']['client']['phone'] === '')
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_ERR_EMPTY_PHONE_EMAIL')));
		}

		foreach ($checkData['receipt']['items'] as $item)
		{
			if ($item['vat'] === null)
			{
				$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_CASHBOX_ATOL_ERR_EMPTY_TAX')));
				break;
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public static function getFfdVersion(): ?float
	{
		return 1.05;
	}
}
