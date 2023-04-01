<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Result;
use Bitrix\Sale\Helpers\Admin\Product;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\ResultWarning;
use Bitrix\Catalog;

Loc::loadMessages(__FILE__);

/**
 * Class CashboxCheckbox
 * @package Bitrix\Sale\Cashbox
 */
class CashboxCheckbox extends Cashbox implements IPrintImmediately, ICheckable
{
	private const HANDLER_MODE_TEST = 'TEST';
	private const HANDLER_MODE_ACTIVE = 'ACTIVE';

	private const API_VERSION = 'v1';
	private const HANDLER_TEST_URL = 'https://dev-api.checkbox.in.ua/api';
	private const HANDLER_ACTIVE_URL = 'https://api.checkbox.in.ua/api';

	private const OPERATION_SIGN_IN = 'cashier/signin';
	private const OPERATION_CREATE_SHIFT = 'shifts';
	private const OPERATION_CHECK_SHIFTS = 'cashier/shift';
	private const OPERATION_CLOSE_SHIFT = 'shifts/close';
	private const OPERATION_CREATE_CHECK = 'receipts/sell';
	private const OPERATION_GET_CHECK = 'receipts';

	private const MAX_CODE_LENGTH = 256;
	private const MAX_NAME_LENGTH = 256;

	private const HTTP_METHOD_GET = 'get';
	private const HTTP_METHOD_POST = 'post';
	private const HTTP_NO_REDIRECT = false;
	private const HTTP_RESPONSE_CODE_201 = 201;
	private const HTTP_RESPONSE_CODE_202 = 202;
	private const HTTP_RESPONSE_CODE_400 = 400;
	private const HTTP_RESPONSE_CODE_401 = 401;
	private const HTTP_RESPONSE_CODE_403 = 403;
	private const HTTP_RESPONSE_CODE_422 = 422;

	private const SHIFT_STATUS_OPENED = 'OPENED';

	private const CHECK_STATUS_DONE = 'DONE';
	private const CHECK_STATUS_ERROR = 'ERROR';

	private const HEADER_TOKEN_TYPE = 'Bearer';
	private const TOKEN_OPTION_NAME = 'cashbox_checkbox_token';

	private const QUANTITY_MULTIPLIER = 1000;
	private const PRICE_MULTIPLIER = 100;

	private const DPS_URL = 'https://cabinet.tax.gov.ua/cashregs/check?';

	private const CODE_NO_VAT = '0';
	private const CODE_VAT_0 = '4';
	private const CODE_VAT_7 = '2';
	private const CODE_VAT_20 = '1';

	private const OPEN_SHIFT_WAIT_SECONDS = 5;
	private const OPEN_SHIFT_WAIT_ATTEMPTS = 2;

	private const BITRIX_CLIENT_NAME = 'api_1c-bitrix';

	public static function getName()
	{
		return Loc::getMessage('SALE_CASHBOX_CHECKBOX_TITLE');
	}

	private static function isCheckReturn(Check $check)
	{
		return ($check instanceof SellReturnCheck || $check instanceof SellReturnCashCheck);
	}

	public function buildCheckQuery(Check $check)
	{
		$checkData = $check->getDataForCheck();

		$isReturn = self::isCheckReturn($check);

		$goods = [];
		foreach ($checkData['items'] as $item)
		{
			$goodEntry = [];

			$itemId = $item['entity']->getField('PRODUCT_ID');
			$code = $item['properties']['ARTNUMBER'];
			if (!$code)
			{
				$code = $itemId;
			}
			if (!$code)
			{
				$code = 'delivery' . $item['entity']->getField('ID');
			}

			$vat = $this->getValueFromSettings('VAT', $item['vat']);
			$goodEntry['good'] = [
				'code' => mb_substr($code, 0, static::MAX_CODE_LENGTH),
				'name' => mb_substr($item['name'], 0, static::MAX_NAME_LENGTH),
				'price' => PriceMaths::roundPrecision($item['price'] * static::PRICE_MULTIPLIER),
			];

			if ($vat && $vat !== static::CODE_NO_VAT)
			{
				$goodEntry['good']['tax'] = [$vat];
			}

			if ($item['barcode'])
			{
				$goodEntry['good']['barcode'] = $item['barcode'];
			}

			$goodEntry['quantity'] = $item['quantity'] * static::QUANTITY_MULTIPLIER;
			$goodEntry['is_return'] = $isReturn;
			$goods[] = $goodEntry;
		}

		$delivery = [];

		if ($checkData['client_email'])
		{
			$delivery['email'] = $checkData['client_email'];
		}

		$payments = [];
		foreach ($checkData['payments'] as $payment)
		{
			$paymentType = $payment['type'] === Check::PAYMENT_TYPE_CASH ? 'CASH' : 'CARD';
			$paymentEntry = [
				'type' => $paymentType,
				'value' => PriceMaths::roundPrecision($payment['sum'] * static::PRICE_MULTIPLIER),
			];
			$payments[] = $paymentEntry;
		}

		$result = [
			'goods' => $goods,
			'delivery' => $delivery,
			'payments' => $payments,
		];

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function buildZReportQuery($id)
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function check(Check $check)
	{
		$url = $this->getRequestUrl(static::OPERATION_GET_CHECK, ['CHECK_ID' => $check->getField('EXTERNAL_UUID')]);
		$token = $this->getAccessToken();

		$requestHeaders = [
			'ACCESS_TOKEN' => $token,
		];

		$requestBody = [];

		$checkResult = $this->sendRequestWithAuthorization(self::HTTP_METHOD_GET, $url, $requestHeaders, $requestBody);
		if (!$checkResult->isSuccess())
		{
			return $checkResult;
		}

		$response = $checkResult->getData();
		$responseStatus = $response['status'];

		switch ($responseStatus)
		{
			case static::CHECK_STATUS_DONE:
				return static::applyCheckResult($response);
			case static::CHECK_STATUS_ERROR:
				$checkResult->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_CHECK_PRINT_ERROR')));
				return $checkResult;
			default:
				return new Result();
		}
	}

	protected static function extractCheckData(array $data)
	{
		$result = [];

		if (!isset($data['id']))
		{
			return $result;
		}

		$checkInfo = CheckManager::getCheckInfoByExternalUuid($data['id']);
		if (empty($checkInfo))
		{
			return $result;
		}

		$result['ID'] = $checkInfo['ID'];
		$result['CHECK_TYPE'] = $checkInfo['TYPE'];

		$check = CheckManager::getObjectById($checkInfo['ID']);
		$dateTime = new Main\Type\DateTime($data['fiscal_date'], 'Y-m-d\TH:i:s.u');
		$result['LINK_PARAMS'] = [
			Check::PARAM_REG_NUMBER_KKT => $data['shift']['cash_register']['id'],
			Check::PARAM_FISCAL_DOC_NUMBER => $data['fiscal_code'],
			Check::PARAM_FN_NUMBER => $data['shift']['cash_register']['fiscal_number'],
			Check::PARAM_SHIFT_NUMBER => $data['shift']['serial'],
			Check::PARAM_DOC_SUM => (float)$checkInfo['SUM'],
			Check::PARAM_DOC_TIME => $dateTime->getTimestamp(),
			Check::PARAM_CALCULATION_ATTR => $check::getCalculatedSign()
		];

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function printImmediately(Check $check)
	{
		$url = $this->getRequestUrl(static::OPERATION_CREATE_CHECK);
		$token = $this->getAccessToken();

		$requestHeaders = [
			'ACCESS_TOKEN' => $token,
		];

		$requestBody = $this->buildCheckQuery($check);

		$printResult = $this->sendRequestWithAuthorization(self::HTTP_METHOD_POST, $url, $requestHeaders, $requestBody, self::HTTP_NO_REDIRECT);
		if (!$printResult->isSuccess())
		{
			return $printResult;
		}

		$response = $printResult->getData();

		if ($response['http_code'] === self::HTTP_RESPONSE_CODE_400)
		{
			$openShiftResult = $this->openShift();
			if (!$openShiftResult->isSuccess())
			{
				return $openShiftResult;
			}
			$this->addCloseShiftAgent();

			$printResult = $this->sendRequestWithAuthorization(self::HTTP_METHOD_POST, $url, $requestHeaders, $requestBody, self::HTTP_NO_REDIRECT);
			if (!$printResult->isSuccess())
			{
				return $printResult;
			}
			$response = $printResult->getData();
		}

		$responseCode = $response['http_code'];
		switch ($responseCode)
		{
			case self::HTTP_RESPONSE_CODE_201:
				if ($response['id'])
				{
					$printResult->setData(['UUID' => $response['id']]);
				}
				else
				{
					$printResult->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_CHECK_PRINT_ERROR')));
				}
				break;
			case self::HTTP_RESPONSE_CODE_422:
				if ($response['detail'])
				{
					foreach ($response['detail'] as $errorDetail)
					{
						$printResult->addError(new Main\Error($errorDetail['msg']));
					}
				}
				else
				{
					$printResult->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_CHECK_PRINT_ERROR')));
				}
				break;
			default:
				if ($response['message'])
				{
					$printResult->addError(new Main\Error($response['message']));
				}
				else
				{
					$printResult->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_CHECK_PRINT_ERROR')));
				}
		}

		return $printResult;
	}

	private function addCloseShiftAgent()
	{
		$agentName = 'Bitrix\Sale\Cashbox\CashboxCheckbox::closeShiftAgent(' . $this->getField('ID') .');';
		$agentTime = Main\Type\DateTime::createFromPhp(date_create('today 23:50'));
		\CAgent::AddAgent($agentName, 'sale', 'Y', 0, '', 'Y', $agentTime);
	}

	/**
	 * @param $cashboxId
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectException
	 */
	public static function closeShiftAgent($cashboxId)
	{
		$cashbox = Manager::getObjectById($cashboxId);
		if ($cashbox && $cashbox instanceof self)
		{
			$closeShiftResult = $cashbox->closeShift();
			if (!$closeShiftResult->isSuccess())
			{
				$closeShiftErrors = $closeShiftResult->getErrorCollection();
				foreach ($closeShiftErrors as $error)
				{
					if ($error instanceof Errors\Warning)
					{
						Logger::addWarning($error->getMessage(), $cashbox->getField('ID'));
					}
					else
					{
						Logger::addError($error->getMessage(), $cashbox->getField('ID'));
					}
				}
			}
		}
	}

	private function sendRequest(string $method, string $url, array $headersData = [], array $bodyData = [], bool $allowRedirect = true): Result
	{
		$result = new Result();

		$requestHeaders = static::getHeaders($headersData);
		$requestBody = static::encode($bodyData);

		$httpClient = new HttpClient();
		$httpClient->setRedirect($allowRedirect);
		$httpClient->setHeaders($requestHeaders);

		if ($method === self::HTTP_METHOD_POST)
		{
			$response = $httpClient->post($url, $requestBody);
		}
		else
		{
			$response = $httpClient->get($url);
		}
		if ($response)
		{
			$responseData = static::decode($response);
			$responseData['http_code'] = $httpClient->getStatus();
			$result->addData($responseData);
		}
		else
		{
			$error = $httpClient->getError();
			foreach ($error as $code =>$message)
			{
				$result->addError(new Main\Error($message, $code));
			}
		}

		return $result;
	}

	private function sendRequestWithAuthorization(string $method, string $url, array $headersData = [], array $bodyData = [], bool $allowRedirect = true): Result
	{
		$firstRequestResult = $this->sendRequest($method, $url, $headersData, $bodyData, $allowRedirect);
		if (!$firstRequestResult->isSuccess())
		{
			return $firstRequestResult;
		}

		$firstRequestResponse = $firstRequestResult->getData();
		$badResponseCodes = [self::HTTP_RESPONSE_CODE_401, self::HTTP_RESPONSE_CODE_403];
		if (!in_array($firstRequestResponse['http_code'], $badResponseCodes))
		{
			return $firstRequestResult;
		}

		$headersDataWithNewToken = $headersData;
		$requestTokenResult = $this->requestAccessToken();
		if (!$requestTokenResult->isSuccess())
		{
			return $requestTokenResult;
		}
		$newToken = $requestTokenResult->get('token');
		$headersDataWithNewToken['ACCESS_TOKEN'] = $newToken;

		$secondRequestResult = $this->sendRequest($method, $url, $headersDataWithNewToken, $bodyData, $allowRedirect);
		return $secondRequestResult;
	}

	private static function getAuthorizationHeaderValue(string $token): ?string
	{
		if ($token)
		{
			return static::HEADER_TOKEN_TYPE . ' ' . $token;
		}

		return null;
	}

	private static function getHeaders(array $headersData): array
	{
		$accessToken = $headersData['ACCESS_TOKEN'] ?? '';
		return [
			'Authorization' => static::getAuthorizationHeaderValue($accessToken),
			'X-License-Key' => $headersData['LICENSE_KEY'],
			'X-Client-Name' => static::BITRIX_CLIENT_NAME,
			'X-Client-Version' => '1.0',
		];
	}

	private static function encode(array $data)
	{
		return Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
	}

	private static function decode(string $data)
	{
		return Main\Web\Json::decode($data);
	}

	private function getRequestUrl(string $action, array $requestParams = []): string
	{
		$url = static::HANDLER_ACTIVE_URL;

		if ($this->getValueFromSettings('INTERACTION', 'HANDLER_MODE') === self::HANDLER_MODE_TEST)
		{
			$url = static::HANDLER_TEST_URL;
		}

		$url .= '/' . static::API_VERSION;

		switch ($action)
		{
			case static::OPERATION_CREATE_SHIFT:
				$url .= '/' . static::OPERATION_CREATE_SHIFT;
				break;
			case static::OPERATION_CHECK_SHIFTS:
				$url .= '/' . static::OPERATION_CHECK_SHIFTS;
				break;
			case static::OPERATION_CLOSE_SHIFT:
				$url .= '/' . static::OPERATION_CLOSE_SHIFT;
				break;
			case static::OPERATION_CREATE_CHECK:
				$url .= '/' . static::OPERATION_CREATE_CHECK;
				break;
			case static::OPERATION_GET_CHECK:
				$url .= '/' . static::OPERATION_GET_CHECK . '/' . $requestParams['CHECK_ID'];
				break;
			case static::OPERATION_SIGN_IN:
				$url .= '/' . static::OPERATION_SIGN_IN;
				break;
			default:
				throw new Main\SystemException();
		}

		return $url;
	}

	private function getTokenOptionName(): string
	{
		return static::TOKEN_OPTION_NAME . '_' . $this->getField('ID');
	}

	private function getAccessToken(): string
	{
		$optionName = $this->getTokenOptionName();
		return Main\Config\Option::get('sale', $optionName, '');
	}

	private function setAccessToken(string $token): void
	{
		$optionName = $this->getTokenOptionName();
		Main\Config\Option::set('sale', $optionName, $token);
	}

	private function requestAccessToken(): Result
	{
		$result = new Result();

		$url = $this->getRequestUrl(static::OPERATION_SIGN_IN);

		$requestData = [
			'login' => $this->getValueFromSettings('AUTH', 'LOGIN'),
			'password' => $this->getValueFromSettings('AUTH', 'PASSWORD'),
		];

		$headersData = [];

		$requestResult = $this->sendRequest(self::HTTP_METHOD_POST, $url, $headersData, $requestData);

		if (!$requestResult->isSuccess())
		{
			return $requestResult;
		}

		$response = $requestResult->getData();

		if ($response['http_code'] === self::HTTP_RESPONSE_CODE_403)
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_AUTHORIZATION_ERROR')));
			return $result;
		}

		if ($response['access_token'])
		{
			$token = $response['access_token'];
			$this->setAccessToken($token);
			$result->set('token', $token);
			return $result;
		}

		$result->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_TOKEN_ERROR')));
		return $result;
	}

	private function getCurrentShift(): Result
	{
		$url = $this->getRequestUrl(static::OPERATION_CHECK_SHIFTS);
		$token = $this->getAccessToken();

		$requestHeaders = [
			'ACCESS_TOKEN' => $token,
		];

		$requestBody = [];

		$result = $this->sendRequestWithAuthorization(self::HTTP_METHOD_GET, $url, $requestHeaders, $requestBody);
		return $result;
	}

	private function openShift(): Result
	{
		$url = $this->getRequestUrl(static::OPERATION_CREATE_SHIFT);
		$token = $this->getAccessToken();

		$requestHeaders = [
			'ACCESS_TOKEN' => $token,
			'LICENSE_KEY' => $this->getValueFromSettings('AUTH', 'LICENSE_KEY'),
		];

		$requestBody = [];

		$openShiftResult = $this->sendRequestWithAuthorization(self::HTTP_METHOD_POST, $url, $requestHeaders, $requestBody);
		if (!$openShiftResult->isSuccess())
		{
			return $openShiftResult;
		}

		$response = $openShiftResult->getData();

		switch ($response['http_code'])
		{
			case self::HTTP_RESPONSE_CODE_202:
				$waitAttempts = 0;
				$openShiftSuccess = false;
				while ($waitAttempts < static::OPEN_SHIFT_WAIT_ATTEMPTS)
				{
					sleep(static::OPEN_SHIFT_WAIT_SECONDS);
					$currentShiftResult = $this->getCurrentShift();
					if (!$currentShiftResult->isSuccess())
					{
						return $currentShiftResult;
					}

					$currentShiftStatus = $currentShiftResult->getData()['status'];
					if ($currentShiftStatus === static::SHIFT_STATUS_OPENED)
					{
						$openShiftSuccess = true;
						break;
					}
					$waitAttempts++;
				}

				if (!$openShiftSuccess)
				{
					$openShiftResult->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_SHIFT_OPEN_ERROR')));
					return $openShiftResult;
				}

				return $openShiftResult;
			case self::HTTP_RESPONSE_CODE_400:
				$currentShiftResult = $this->getCurrentShift();
				if (!$currentShiftResult->isSuccess())
				{
					return $currentShiftResult;
				}

				$currentShift = $currentShiftResult->getData();
				if ($currentShift['status'] && $currentShift['status'] === static::SHIFT_STATUS_OPENED)
				{
					$openShiftResult->addWarning(new ResultWarning(Loc::getMessage('SALE_CASHBOX_CHECKBOX_SHIFT_ALREADY_OPENED')));
					return $openShiftResult;
				}

				$openShiftResult->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_SHIFT_OPEN_ERROR')));
				return $openShiftResult;
			default:
				$openShiftResult->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_SHIFT_OPEN_ERROR')));
				return $openShiftResult;
		}
	}

	private function closeShift(): Result
	{
		$url = $this->getRequestUrl(static::OPERATION_CLOSE_SHIFT);
		$token = $this->getAccessToken();

		$requestHeaders = [
			'ACCESS_TOKEN' => $token,
		];

		$requestBody = [];

		$closeShiftResult = $this->sendRequestWithAuthorization(self::HTTP_METHOD_POST, $url, $requestHeaders, $requestBody);
		if (!$closeShiftResult->isSuccess())
		{
			return $closeShiftResult;
		}

		$response = $closeShiftResult->getData();

		if ($response['http_code'] !== self::HTTP_RESPONSE_CODE_202)
		{
			$closeShiftResult->addError(new Main\Error(Loc::getMessage('SALE_CASHBOX_CHECKBOX_SHIFT_CLOSE_ERROR')));
		}

		return $closeShiftResult;
	}

	/**
	 * @inheritDoc
	 */
	public static function getSettings($modelId = 0)
	{
		$settings = [
			'AUTH' => [
				'LABEL' => Loc::getMessage('SALE_CASHBOX_CHECKBOX_SETTINGS_AUTH'),
				'REQUIRED' => 'Y',
				'ITEMS' => [
					'LOGIN' => [
						'TYPE' => 'STRING',
						'LABEL' => Loc::getMessage('SALE_CASHBOX_CHECKBOX_SETTINGS_AUTH_LOGIN_LABEL'),
					],
					'PASSWORD' => [
						'TYPE' => 'STRING',
						'LABEL' => Loc::getMessage('SALE_CASHBOX_CHECKBOX_SETTINGS_AUTH_PASSWORD_LABEL'),
					],
					'LICENSE_KEY' => [
						'TYPE' => 'STRING',
						'LABEL' => Loc::getMessage('SALE_CASHBOX_CHECKBOX_SETTINGS_AUTH_LICENSE_KEY_LABEL'),
					],
				],
			],
			'INTERACTION' => [
				'LABEL' => Loc::getMessage('SALE_CASHBOX_CHECKBOX_SETTINGS_INTERACTION'),
				'ITEMS' => [
					'HANDLER_MODE' => [
						'TYPE' => 'ENUM',
						'LABEL' => Loc::getMessage('SALE_CASHBOX_CHECKBOX_SETTINGS_HANDLER_MODE_LABEL'),
						'OPTIONS' => [
							self::HANDLER_MODE_ACTIVE => Loc::getMessage('SALE_CASHBOX_CHECKBOX_MODE_ACTIVE'),
							self::HANDLER_MODE_TEST => Loc::getMessage('SALE_CASHBOX_CHECKBOX_MODE_TEST'),
						],
					],
				],
			],
		];

		$settings['VAT'] = [
			'LABEL' => Loc::getMessage('SALE_CASHBOX_CHECKBOX_SETTINGS_VAT'),
			'REQUIRED' => 'Y',
			'ITEMS' => [
				'NOT_VAT' => [
					'TYPE' => 'STRING',
					'LABEL' => Loc::getMessage('SALE_CASHBOX_CHECKBOX_SETTINGS_VAT_LABEL_NOT_VAT'),
					'VALUE' => static::CODE_NO_VAT,
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
					0 => static::CODE_VAT_0,
					7 => static::CODE_VAT_7,
					20 => static::CODE_VAT_20,
				];

				foreach ($vatList as $vat)
				{
					$value = $defaultVatList[(int)$vat['RATE']] ?? '';

					$settings['VAT']['ITEMS'][(int)$vat['ID']] = [
						'TYPE' => 'STRING',
						'LABEL' => $vat['NAME'].' ['.(int)$vat['RATE'].'%]',
						'VALUE' => $value
					];
				}
			}
		}

		return $settings;
	}

	/**
	 * @inheritDoc
	 */
	public function getCheckLink(array $linkParams)
	{
		$queryParams = [
			'id=' . $linkParams[Check::PARAM_FISCAL_DOC_NUMBER],
			'fn=' . $linkParams[Check::PARAM_FN_NUMBER],
			'date=' . date("Y-m-d",$linkParams[Check::PARAM_DOC_TIME]),
		];

		return static::DPS_URL.implode('&', $queryParams);
	}
}