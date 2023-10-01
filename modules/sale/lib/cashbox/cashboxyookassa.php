<?php
namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\PhoneNumber;
use Bitrix\Main\Web\Uri;
use Bitrix\Sale;
use Bitrix\Seo;

Loc::loadMessages(__FILE__);

/**
 * Class CashboxYooKassa
 * @package Bitrix\Sale\Cashbox
 */
class CashboxYooKassa extends CashboxPaySystem
{
	private const MAX_NAME_LENGTH = 128;

	private const URL = 'https://api.yookassa.ru/v3/receipts/';

	private const CODE_NO_VAT = 1;
	private const CODE_VAT_0 = 2;
	private const CODE_VAT_10 = 3;
	private const CODE_VAT_20 = 4;

	private const SETTLEMENT_TYPE_PREPAYMENT = 'prepayment';
	private const CHECK_TYPE_PAYMENT = 'payment';

	private const MARK_CODE_TYPE_GS1M = 'gs_1m';

	public static function getName(): string
	{
		return Loc::getMessage('SALE_CASHBOX_YOOKASSA_TITLE');
	}

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
		$fields = [
			'customer' => [],
			'items' => [],
			'tax_system_code' => $this->getValueFromSettings('TAX', 'SNO'),
		];

		if (isset($checkData['client_email']))
		{
			$fields['customer']['email'] = $checkData['client_email'];
		}

		if (isset($checkData['client_phone']))
		{
			$phoneParser = PhoneNumber\Parser::getInstance();
			if ($phoneParser)
			{
				$phoneNumber = $phoneParser->parse($checkData['client_phone']);
				if ($phoneNumber->isValid())
				{
					$fields['customer']['phone'] = $phoneNumber->format(PhoneNumber\Format::E164);
				}
			}
		}

		$paymentModeMap = $this->getCheckTypeMap();
		$paymentMode = $paymentModeMap[$check::getType()];
		$paymentObjectMap = $this->getPaymentObjectMap();

		foreach ($checkData['items'] as $item)
		{
			$vat = $this->getValueFromSettings('VAT', $item['vat']);
			$vat = $vat ?? $this->getValueFromSettings('VAT', 'NOT_VAT');

			$measure = $this->getValueFromSettings('MEASURE', $item['measure_code']);
			$measure = $measure ?? $this->getValueFromSettings('MEASURE', 'DEFAULT');

			$receiptItem = [
				'description' => mb_substr($item['name'], 0, self::MAX_NAME_LENGTH),
				'amount' => [
					'value' => (string)Sale\PriceMaths::roundPrecision($item['price']),
					'currency' => (string)$item['currency'],
				],
				'vat_code' => (int)$vat,
				'quantity' => (string)$item['quantity'],
				'measure' => (string)$measure,
				'payment_subject' => $paymentObjectMap[$item['payment_object']],
				'payment_mode' => $paymentMode,
			];

			if (!empty($item['marking_code']))
			{
				$receiptItem['mark_code_info'] = $this->buildPositionMarkCode($item);
			}

			$fields['items'][] = $receiptItem;
		}

		if ($this->needDataForSecondCheck($payment))
		{
			$fields['send'] = true;
			$fields['type'] = self::CHECK_TYPE_PAYMENT;
			$fields['payment_id'] = $payment->getField('PS_INVOICE_ID');
			$fields['settlements'] = [];

			foreach ($checkData['payments'] as $paymentItem)
			{
				$fields['settlements'][] = [
					'type' => self::SETTLEMENT_TYPE_PREPAYMENT,
					'amount' => [
						'value' => (string)Sale\PriceMaths::roundPrecision($paymentItem['sum']),
						'currency' => (string)$paymentItem['currency'],
					],
				];
			}
		}

		return $fields;
	}

	private function buildPositionMarkCode(array $item): array
	{
		return [
			self::MARK_CODE_TYPE_GS1M => $item['marking_code'],
		];
	}

	protected function getPrintUrl(): string
	{
		return self::URL;
	}

	protected function getCheckUrl(): string
	{
		return self::URL;
	}

	protected function getDataForCheck(Sale\Payment $payment): array
	{
		return [
			'payment_id' => $payment->getField('PS_INVOICE_ID'),
		];
	}

	protected function send(string $url, Sale\Payment $payment, array $fields, string $method = self::SEND_METHOD_HTTP_POST): Sale\Result
	{
		$result = new Sale\Result();

		$httpClient = new Main\Web\HttpClient();
		$headers = $this->getHeaders($payment);
		foreach ($headers as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		if ($method === self::SEND_METHOD_HTTP_POST)
		{
			$data = self::encode($fields);
			Logger::addDebugInfo(__CLASS__ . ': request data: ' . $data);
			$response = $httpClient->post($url, $data);
		}
		else
		{
			$uri = new Uri($url);
			$uri->addParams($fields);
			$response = $httpClient->get($uri->getUri());
		}

		if ($response === false || $response === '')
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_YOOKASSA_ERROR_EMPTY_RESPONSE')));

			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Error($message, $code));
			}

			return $result;
		}

		Logger::addDebugInfo(__CLASS__ . ': response data: ' . $response);

		$response = static::decode($response);
		if (!$response)
		{
			$result->addError(new Error(Loc::getMessage('SALE_CASHBOX_YOOKASSA_ERROR_DECODE_RESPONSE')));
			return $result;
		}

		$result->setData($response);

		return $result;
	}

	protected function processPrintResult(Sale\Result $result): Sale\Result
	{
		return new Sale\Result();
	}

	protected function processCheckResult(Sale\Result $result): Sale\Result
	{
		$processCheckResult = new Sale\Result();
		$data = $result->getData();

		/**
		 * @see https://yookassa.ru/developers/using-api/response-handling/response-format
		 */
		if (isset($data['type']) && $data['type'] === 'error')
		{
			$errorCode = $data['code'] ?? '';
			switch ($errorCode)
			{
				case 'internal_server_error':
				case 'too_many_requests':
					$processCheckResult->addError(new Error(Loc::getMessage('SALE_CASHBOX_YOOKASSA_ERROR_CHECK_WAIT')));
					break;

				default:
					$processCheckResult->addError(new Error(Loc::getMessage('SALE_CASHBOX_YOOKASSA_ERROR_CHECK_PROCESSING')));
					break;
			}

			return $processCheckResult;
		}

		$processCheckResult->setData($data);

		return $processCheckResult;
	}

	protected function onAfterProcessCheck(Sale\Result $result, Sale\Payment $payment): Sale\Result
	{
		$onAfterProcessCheckResult = new Sale\Result();
		$checkList = CheckManager::getList([
			'select' => ['ID'],
			'filter' => [
				'ORDER_ID' => $payment->getOrderId(),
			],
			'order' => ['ID' => 'DESC'],
		])->fetchAll();

		$data = $result->getData();
		$checkData = [];
		if (isset($data['type']) && $data['type'] === 'list')
		{
			$checkData = $data['items'] ?? [];
		}

		if ($checkList)
		{
			if (!$checkData)
			{
				$externalCheck = [
					'checkId' => $checkList[0]['ID'],
					'error' => [
						'MESSAGE' => Loc::getMessage('SALE_CASHBOX_YOOKASSA_ERROR_CHECK_NOT_FOUND'),
						'TYPE' => Errors\Error::TYPE,
					],
				];
				$applyCheckResult = static::applyCheckResult($externalCheck);
				$onAfterProcessCheckResult->addErrors($applyCheckResult->getErrors());
			}

			foreach ($checkData as $key => $externalCheck)
			{
				$checkStatus = $externalCheck['status'] ?? '';
				switch ($checkStatus)
				{
					case 'pending':
						$externalCheck['error'] = [
							'MESSAGE' => Loc::getMessage('SALE_CASHBOX_YOOKASSA_STATUS_CHECK_PENDING'),
							'TYPE' => Errors\Warning::TYPE,
						];
						break;

					case 'canceled':
						$externalCheck['error'] = [
							'MESSAGE' => Loc::getMessage('SALE_CASHBOX_YOOKASSA_STATUS_CHECK_CANCELLED'),
							'TYPE' => Errors\Error::TYPE,
						];
						break;
				}

				$externalCheck['checkId'] = $checkList[$key]['ID'];
				$applyCheckResult = static::applyCheckResult($externalCheck);
				if (!$applyCheckResult->isSuccess())
				{
					$onAfterProcessCheckResult->addErrors($applyCheckResult->getErrors());
				}
			}
		}
		else
		{
			$onAfterProcessCheckResult->addError(new Error(Loc::getMessage('SALE_CASHBOX_YOOKASSA_ERROR_CHECK_NOT_FOUND')));
		}

		return $onAfterProcessCheckResult;
	}

	protected static function extractCheckData(array $data): array
	{
		$result = [];

		$id = $data['checkId'] ?? null;
		if (!$id)
		{
			return $result;
		}
		$result['ID'] = $id;

		if ($data['error'])
		{
			$result['ERROR'] = $data['error'];
		}

		if ($data['id'])
		{
			$result['EXTERNAL_UUID'] = $data['id'];
		}

		$check = CheckManager::getObjectById($id);
		if ($check)
		{
			$result['LINK_PARAMS'] = [
				AbstractCheck::PARAM_FISCAL_DOC_ATTR => $data['fiscal_attribute'],
				AbstractCheck::PARAM_FISCAL_DOC_NUMBER => $data['fiscal_document_number'],
				AbstractCheck::PARAM_FN_NUMBER => $data['fiscal_storage_number'],
				AbstractCheck::PARAM_FISCAL_RECEIPT_NUMBER => $data['fiscal_provider_id'],
				AbstractCheck::PARAM_DOC_SUM => (float)$check->getField('SUM'),
				AbstractCheck::PARAM_CALCULATION_ATTR => $check::getCalculatedSign()
			];

			if (!empty($data['registered_at']))
			{
				try
				{
					// ISO 8601 datetime
					$dateTime = new Main\Type\DateTime($data['registered_at'], 'Y-m-d\TH:i:s.v\Z');
					$result['LINK_PARAMS'][AbstractCheck::PARAM_DOC_TIME] = $dateTime->getTimestamp();
				}
				catch (Main\ObjectException $ex)
				{}
			}
		}

		return $result;
	}

	public static function getPaySystemCodeForKkm(): string
	{
		return 'YANDEX_CHECKOUT_SHOP_ID';
	}

	public static function getKkmValue(Sale\PaySystem\Service $service): array
	{
		if (self::isOAuth())
		{
			return [md5(static::getYandexToken())];
		}

		return parent::getKkmValue($service);
	}

	public static function getSettings($modelId = 0): array
	{
		$settings = [];

		$settings['TAX'] = [
			'LABEL' => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SETTINGS_SNO'),
			'REQUIRED' => 'Y',
			'ITEMS' => [
				'SNO' => [
					'TYPE' => 'ENUM',
					'LABEL' => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SETTINGS_SNO_LABEL'),
					'VALUE' => 1,
					'OPTIONS' => [
						1 => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SNO_OSN'),
						2 => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SNO_UI'),
						3 => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SNO_UIO'),
						4 => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SNO_ENVD'),
						5 => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SNO_ESN'),
						6 => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SNO_PATENT'),
					],
				],
			],
		];

		$settings['VAT'] = [
			'LABEL' => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SETTINGS_VAT'),
			'REQUIRED' => 'Y',
			'COLLAPSED' => 'Y',
			'ITEMS' => [
				'NOT_VAT' => [
					'TYPE' => 'STRING',
					'LABEL' => Loc::getMessage('SALE_CASHBOX_YOOKASSA_SETTINGS_VAT_LABEL_NOT_VAT'),
					'VALUE' => self::CODE_NO_VAT,
				],
			],
		];

		if (Loader::includeModule('catalog'))
		{
			$dbRes = \Bitrix\Catalog\VatTable::getList(['filter' => [
				'ACTIVE' => 'Y',
				'EXCLUDE_VAT' => 'N',
			]]);
			$vatList = $dbRes->fetchAll();
			if ($vatList)
			{
				$defaultVatList = [
					0 => self::CODE_VAT_0,
					10 => self::CODE_VAT_10,
					20 => self::CODE_VAT_20,
				];

				foreach ($vatList as $vat)
				{
					$value = $defaultVatList[(int)$vat['RATE']] ?? '';

					$settings['VAT']['ITEMS'][(int)$vat['ID']] = [
						'TYPE' => 'STRING',
						'LABEL' => $vat['NAME'] . ' (' . (int)$vat['RATE'] . '%)',
						'VALUE' => $value,
					];
				}
			}
		}

		$measureItems = [
			'DEFAULT' => [
				'TYPE' => 'STRING',
				'LABEL' => Loc::getMessage('SALE_CASHBOX_MEASURE_SUPPORT_SETTINGS_DEFAULT_VALUE'),
				'VALUE' => 'piece',
			],
		];
		if (Loader::includeModule('catalog'))
		{
			$measuresList = \CCatalogMeasure::getList();
			while ($measure = $measuresList->fetch())
			{
				$measureItems[$measure['CODE']] = [
					'TYPE' => 'STRING',
					'LABEL' => $measure['MEASURE_TITLE'],
					'VALUE' => MeasureCodeToTag2108MapperYooKassa::getTag2108Value($measure['CODE']),
				];
			}
		}

		$settings['MEASURE'] = [
			'LABEL' => Loc::getMessage('SALE_CASHBOX_MEASURE_SUPPORT_SETTINGS'),
			'REQUIRED' => 'Y',
			'COLLAPSED' => 'Y',
			'ITEMS' => $measureItems,
		];

		return $settings;
	}

	/**
	 * @return float|null
	 */
	public static function getFfdVersion(): ?float
	{
		return 1.2;
	}

	/**
	 * @return array
	 */
	protected function getCheckTypeMap(): array
	{
		return [
			FullPrepaymentCheck::getType() => 'full_prepayment',
			PrepaymentCheck::getType() => 'partial_prepayment',
			AdvancePaymentCheck::getType() => 'advance',
			SellCheck::getType() => 'full_payment',
			CreditCheck::getType() => 'credit',
			CreditPaymentCheck::getType() => 'credit_payment',
		];
	}

	/**
	 * @return array
	 */
	private function getPaymentObjectMap(): array
	{
		return [
			// FFD 1.05
			Check::PAYMENT_OBJECT_COMMODITY => 'commodity',
			Check::PAYMENT_OBJECT_EXCISE => 'excise',
			Check::PAYMENT_OBJECT_JOB => 'job',
			Check::PAYMENT_OBJECT_SERVICE => 'service',
			Check::PAYMENT_OBJECT_PAYMENT => 'payment',
			Check::PAYMENT_OBJECT_CASINO_PAYMENT => 'casino',
			Check::PAYMENT_OBJECT_GAMBLING_BET => 'gambling_bet',
			Check::PAYMENT_OBJECT_GAMBLING_PRIZE => 'gambling_prize',
			Check::PAYMENT_OBJECT_LOTTERY => 'lottery',
			Check::PAYMENT_OBJECT_LOTTERY_PRIZE => 'lottery_prize',
			Check::PAYMENT_OBJECT_INTELLECTUAL_ACTIVITY => 'intellectual_activity',
			Check::PAYMENT_OBJECT_AGENT_COMMISSION => 'agent_commission',
			Check::PAYMENT_OBJECT_PROPERTY_RIGHT => 'property_right',
			Check::PAYMENT_OBJECT_NON_OPERATING_GAIN => 'non_operating_gain',
			Check::PAYMENT_OBJECT_INSURANCE_PREMIUM => 'insurance_premium',
			Check::PAYMENT_OBJECT_SALES_TAX => 'sales_tax',
			Check::PAYMENT_OBJECT_RESORT_FEE => 'resort_fee',
			Check::PAYMENT_OBJECT_COMPOSITE => 'composite',
			Check::PAYMENT_OBJECT_ANOTHER => 'another',

			// FFD 1.2
			Check::PAYMENT_OBJECT_COMMODITY_MARKING => 'marked',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING => 'non_marked',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_EXCISE => 'marked_excise',
			Check::PAYMENT_OBJECT_COMMODITY_MARKING_NO_MARKING_EXCISE => 'non_marked_excise',
			Check::PAYMENT_OBJECT_FINE => 'fine',
			Check::PAYMENT_OBJECT_TAX => 'tax',
			Check::PAYMENT_OBJECT_DEPOSIT => 'lien',
			Check::PAYMENT_OBJECT_EXPENSE => 'cost',
			Check::PAYMENT_OBJECT_AGENT_WITHDRAWALS => 'agent_withdrawals',
			Check::PAYMENT_OBJECT_PENSION_INSURANCE_IP => 'pension_insurance_without_payouts',
			Check::PAYMENT_OBJECT_PENSION_INSURANCE => 'pension_insurance_with_payouts',
			Check::PAYMENT_OBJECT_MEDICAL_INSURANCE_IP => 'health_insurance_without_payouts',
			Check::PAYMENT_OBJECT_MEDICAL_INSURANCE => 'health_insurance_with_payouts',
			Check::PAYMENT_OBJECT_SOCIAL_INSURANCE => 'health_insurance',
		];
	}

	protected function getCheckHttpMethod(): string
	{
		return self::SEND_METHOD_HTTP_GET;
	}

	private function needDataForSecondCheck(Sale\Payment $payment): bool
	{
		return (bool)$payment->getField('PS_INVOICE_ID');
	}

	private function getHeaders(Sale\Payment $payment): array
	{
		$headers = [
			'Content-Type' => 'application/json',
			'Idempotence-Key' => $this->getIdempotenceKey(),
		];

		try
		{
			$headers['Authorization'] = $this->getAuthorizationHeader($payment);
		}
		catch (\Exception $ex)
		{
			$headers['Authorization'] = 'Basic '.$this->getBasicAuthString($payment);
		}

		return $headers;
	}

	private function getIdempotenceKey(): string
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	private function getAuthorizationHeader(Sale\Payment $payment)
	{
		if (self::isOAuth())
		{
			$token = static::getYandexToken();
			return 'Bearer '.$token;
		}

		return 'Basic '.$this->getBasicAuthString($payment);
	}

	private static function isOAuth(): bool
	{
		return Main\Config\Option::get('sale', 'YANDEX_CHECKOUT_OAUTH', false) == true;
	}

	private static function getYandexToken()
	{
		if (!Main\Loader::includeModule('seo'))
		{
			return null;
		}

		$authAdapter = Seo\Checkout\Service::getAuthAdapter(Seo\Checkout\Service::TYPE_YOOKASSA);
		$token = $authAdapter->getToken();
		if (!$token)
		{
			$authAdapter = Seo\Checkout\Service::getAuthAdapter(Seo\Checkout\Service::TYPE_YANDEX);
			$token = $authAdapter->getToken();
		}

		return $token;
	}

	private function getBasicAuthString(Sale\Payment $payment)
	{
		return base64_encode(
			trim((string)$this->getPaySystemSetting($payment, 'YANDEX_CHECKOUT_SHOP_ID'))
			. ':'
			. trim((string)$this->getPaySystemSetting($payment, 'YANDEX_CHECKOUT_SECRET_KEY'))
		);
	}

	/**
	 * @param string $data
	 * @return mixed
	 */
	private static function decode(string $data)
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

	private static function encode(array $data)
	{
		return Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
	}
}