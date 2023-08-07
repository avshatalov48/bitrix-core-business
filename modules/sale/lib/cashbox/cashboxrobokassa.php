<?php
namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Catalog;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class CashboxRobokassa
 * @package Bitrix\Sale\Cashbox
 */
class CashboxRobokassa extends CashboxPaySystem
{
	public const CACHE_ID = 'BITRIX_CASHBOX_ROBOKASSA_ID';

	private const URL = 'https://ws.roboxchange.com/RoboFiscal/Receipt/';

	private const CODE_VAT_0 = 'vat0';
	private const CODE_VAT_10 = 'vat10';
	private const CODE_VAT_20 = 'vat20';

	private const CHECK_PAYMENT_TYPE = 2;

	private const MAX_NAME_LENGTH = 128;

	public static function getName(): string
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_TITLE');
	}

	protected function getPrintUrl(): string
	{
		return self::URL . 'Attach';
	}

	protected function getCheckUrl(): string
	{
		return self::URL . 'Status';
	}

	/**
	 * @param Check $check
	 * @return array
	 */
	public function buildCheckQuery(Check $check): array
	{
		$checkParamsResult = $this->checkParams($check);
		if (!$checkParamsResult->isSuccess())
		{
			return [];
		}

		$payment = CheckManager::getPaymentByCheck($check);
		if (!$payment)
		{
			return [];
		}

		$checkData = $check->getDataForCheck();

		$request = Main\Application::getInstance()->getContext()->getRequest();
		$protocol = $request->isHttps() ? 'https://' : 'http://';

		$fields = [
			'merchantId' => $this->getPaySystemSetting($payment, 'ROBOXCHANGE_SHOPLOGIN'),
			'id' => $checkData['unique_id'],
			'originId' => $payment->getId(),
			'operation' => SellCheck::getType(),
			'sno' => $this->getValueFromSettings('TAX', 'SNO'),
			'url' => \urlencode($protocol . $request->getHttpHost()),
			'total' => (string)Sale\PriceMaths::roundPrecision($checkData['total_sum']),
			'client' => [
				'email' => $checkData['client_email'],
				'phone' => $checkData['client_phone'],
			],
			'payments' => [],
			'items' => [],
			'vats' => [],
		];

		foreach ($checkData['payments'] as $paymentItem)
		{
			$fields['payments'][] = [
				'type' => self::CHECK_PAYMENT_TYPE,
				'sum' => (string)Sale\PriceMaths::roundPrecision($paymentItem['sum']),
			];
		}

		$checkTypeMap = $this->getCheckTypeMap();
		$paymentMethod = $checkTypeMap[$check::getType()];
		$paymentObjectMap = $this->getPaymentObjectMap();
		foreach ($checkData['items'] as $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['vat']);
			$tax = $vat ?? $this->getValueFromSettings('VAT', 'NOT_VAT');

			$receiptItem = [
				'name' => mb_substr($item['name'], 0, self::MAX_NAME_LENGTH),
				'quantity' => (string)$item['quantity'],
				'sum' => (string)Sale\PriceMaths::roundPrecision($item['sum']),
				'tax' => $tax,
				'payment_method' => $paymentMethod,
				'payment_object' => $paymentObjectMap[$item['payment_object']],
			];

			if (!empty($item['marking_code']))
			{
				$receiptItem['nomenclature_code'] = $item['marking_code'];
			}

			$fields['items'][] = $receiptItem;

			$fields['vats'][] = [
				'type' => $tax,
				'sum' => (string)Sale\PriceMaths::roundPrecision($item['vat_sum']),
			];
		}

		return $fields;
	}

	protected static function extractCheckData(array $data): array
	{
		$result = [];

		$id = $data['CheckId'] ?? null;
		if (!$id)
		{
			return $result;
		}

		$result['ID'] = $id;

		$check = CheckManager::getObjectById($id);
		if ($check)
		{
			$result['LINK_PARAMS'] = [
				Check::PARAM_FISCAL_DOC_ATTR => $data['FiscalDocumentAttribute'],
				Check::PARAM_FISCAL_DOC_NUMBER => $data['FiscalDocumentNumber'],
				Check::PARAM_FN_NUMBER => $data['FnNumber'],
				Check::PARAM_DOC_SUM => (float)$check->getField('SUM'),
				Check::PARAM_CALCULATION_ATTR => $check::getCalculatedSign()
			];

			if (!empty($data['FiscalDate']))
			{
				try
				{
					$dateTime = new Main\Type\DateTime($data['FiscalDate']);
					$result['LINK_PARAMS'][Check::PARAM_DOC_TIME] = $dateTime->getTimestamp();
				}
				catch (Main\ObjectException $ex)
				{}
			}
		}

		return $result;
	}

	protected function processPrintResult(Sale\Result $result): Sale\Result
	{
		$processPrintResult = new Sale\Result();

		$data = $result->getData();
		$resultCode = (int)$data['ResultCode'];
		switch ($resultCode)
		{
			case 0:
				$processPrintResult->setData(['UUID' => ($data['OpKey'] ?? '')]);
				break;

			case 1:
				$processPrintResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_PRINT_ERROR_FORMAT')));
				break;

			case 2:
				$processPrintResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_PRINT_ERROR_NOT_ENOUGH_MONEY')));
				break;

			default:
				$processPrintResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_PRINT_ERROR_INTERNAL')));
				break;
		}

		return $processPrintResult;
	}

	protected function getDataForCheck(Sale\Payment $payment): array
	{
		return [
			'merchantId' => $this->getPaySystemSetting($payment, 'ROBOXCHANGE_SHOPLOGIN'),
			'id' => $payment->getId(),
		];
	}

	protected function processCheckResult(Sale\Result $result): Sale\Result
	{
		$processCheckResult = new Sale\Result();

		$data = $result->getData();
		$resultCode = (int)$data['Code'];
		switch ($resultCode)
		{
			case 1:
				$processCheckResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_PRINT_ERROR_WAIT')));
				break;

			case 0:
			case 2:
				if (!empty($data['Statuses']))
				{
					$statuses = [];
					foreach ($data['Statuses'] as $status)
					{
						$statuses[] = $status;
					}

					$processCheckResult->setData($statuses);
				}
				break;

			case 3:
				$processCheckResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_STATUS_ERROR_REGISTER')));
				break;

			default:
				$processCheckResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_STATUS_ERROR_PROCESSING')));
				break;
		}

		return $processCheckResult;
	}

	protected function onAfterProcessCheck(Sale\Result $result, Sale\Payment $payment): Sale\Result
	{
		$onAfterProcessCheckResult = new Sale\Result();

		$checkList = CheckManager::getList([
			'select' => ['ID'],
			'filter' => [
				'ORDER_ID' => $payment->getOrder()->getId(),
			],
		])->fetchAll();

		if ($checkList)
		{
			$statuses = $result->getData();
			foreach ($statuses as $key => $status)
			{
				$resultCode = (int)$status['Code'];
				if ($resultCode === 0)
				{
					continue;
				}

				switch ($resultCode)
				{
					case 1:
						$onAfterProcessCheckResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_PRINT_ERROR_WAIT')));
						break;

					case 2:
						$status['CheckId'] = $checkList[$key]['ID'];
						$applyCheckResult = static::applyCheckResult($status);
						if (!$applyCheckResult->isSuccess())
						{
							$onAfterProcessCheckResult->addErrors($applyCheckResult->getErrors());
						}
						break;

					case 3:
						$onAfterProcessCheckResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_STATUS_ERROR_REGISTER')));
						break;

					default:
						$onAfterProcessCheckResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_STATUS_ERROR_PROCESSING')));
						break;
				}
			}
		}
		else
		{
			$onAfterProcessCheckResult->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_CHECK_NOT_FOUND')));
		}

		return $onAfterProcessCheckResult;
	}

	/**
	 * @param string $url
	 * @param Sale\Payment $payment
	 * @param array $fields
	 * @param string $method
	 * @return Sale\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	protected function send(string $url, Sale\Payment $payment, array $fields, string $method = self::SEND_METHOD_HTTP_POST): Sale\Result
	{
		$result = new Sale\Result();

		$startupHash = self::cutSign(
			\base64_encode(
				self::formatSign(
					self::encode($fields)
				)
			)
		);

		$sign = self::cutSign(
			\base64_encode(
				\md5($startupHash . $this->getPaySystemSetting($payment, 'ROBOXCHANGE_SHOPPASSWORD'))
			)
		);

		Logger::addDebugInfo(__CLASS__ . ': request data fields: ' . self::encode($fields));
		Logger::addDebugInfo(__CLASS__ . ': request data: ' . $startupHash . '.' . $sign);

		$httpClient = new Main\Web\HttpClient();
		$response = $httpClient->post($url, $startupHash . '.' . $sign);
		if ($response === false)
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_ERROR_EMPTY_RESPONSE')));

			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Main\Error($message, $code));
			}

			return $result;
		}

		Logger::addDebugInfo(__CLASS__ . ': response data: ' . $response);

		$response = static::decode($response);
		if (!$response)
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_ERROR_DECODE_RESPONSE')));
			return $result;
		}

		$result->setData($response);

		return $result;
	}

	/**
	 * @param $sign
	 * @return string
	 */
	protected static function formatSign(string $sign): string
	{
		return \strtr(
			$sign,
			[
				'+' => '-',
				'/' => '_',
			]
		);
	}

	/**
	 * @param $sign
	 * @return string
	 */
	private static function cutSign(string $sign): string
	{
		return \preg_replace('/^(.*?)(=*)$/', '$1', $sign);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function encode(array $data)
	{
		return Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	/**
	 * @param string $data
	 * @return mixed
	 */
	private static function decode($data)
	{
		try
		{
			return Main\Web\Json::decode($data);
		}
		catch (Main\ArgumentException $exception)
		{
			return false;
		}
	}

	/**
	 * @return array
	 */
	protected function getCheckTypeMap(): array
	{
		return [
			SellCheck::getType() => 'full_payment',
			AdvancePaymentCheck::getType() => 'advance',
			PrepaymentCheck::getType() => 'prepayment',
			FullPrepaymentCheck::getType() => 'full_prepayment',
		];
	}

	/**
	 * @param int $modelId
	 * @return array[]
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getSettings($modelId = 0): array
	{
		$settings['VAT'] = [
			'LABEL' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SETTINGS_VAT'),
			'REQUIRED' => 'Y',
			'ITEMS' => [
				'NOT_VAT' => [
					'TYPE' => 'STRING',
					'LABEL' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SETTINGS_VAT_LABEL_NOT_VAT'),
					'VALUE' => 'none'
				]
			]
		];

		if (Main\Loader::includeModule('catalog'))
		{
			$dbRes = Catalog\VatTable::getList(['filter' => ['ACTIVE' => 'Y']]);
			$vatList = $dbRes->fetchAll();
			if ($vatList)
			{
				$defaultVatList = [
					0 => self::CODE_VAT_0,
					10 => self::CODE_VAT_10,
					20 => self::CODE_VAT_20
				];

				foreach ($vatList as $vat)
				{
					$value = '';
					if (isset($defaultVatList[(int)$vat['RATE']]))
					{
						$value = $defaultVatList[(int)$vat['RATE']];
					}

					$settings['VAT']['ITEMS'][(int)$vat['ID']] = [
						'TYPE' => 'STRING',
						'LABEL' => $vat['NAME'].' ['.(int)$vat['RATE'].'%]',
						'VALUE' => $value
					];
				}
			}
		}

		$settings['TAX'] = [
			'LABEL' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SETTINGS_SNO'),
			'REQUIRED' => 'Y',
			'ITEMS' => [
				'SNO' => [
					'TYPE' => 'ENUM',
					'LABEL' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SETTINGS_SNO_LABEL'),
					'VALUE' => 'osn',
					'OPTIONS' => [
						'osn' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SNO_OSN'),
						'usn_income' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SNO_UI'),
						'usn_income_outcome' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SNO_UIO'),
						'envd' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SNO_ENVD'),
						'esn' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SNO_ESN'),
						'patent' => Main\Localization\Loc::getMessage('SALE_CASHBOX_ROBOKASSA_SNO_PATENT')
					]
				]
			]
		];

		return $settings;
	}

	public static function getPaySystemCodeForKkm(): string
	{
		return 'ROBOXCHANGE_SHOPLOGIN';
	}

	/**
	 * @return array
	 */
	private function getPaymentObjectMap(): array
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
}
