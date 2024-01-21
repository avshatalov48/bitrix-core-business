<?php
namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Sale;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class WooppayHandler
 * @package Sale\Handlers\PaySystem
 */
class WooppayHandler extends Sale\PaySystem\ServiceHandler implements Sale\PaySystem\IRefund
{
	private const API_VERSION_V1 = 'v1';

	private const MODE_CHECKOUT = 'checkout';

	private const SEND_METHOD_GET = 'get';
	private const SEND_METHOD_POST = 'post';

	private const HTTP_CODE_OK = 200;
	private const HTTP_CODE_UNAUTHORIZED = 401;
	private const HTTP_CODE_UNPROCESSABLE_ENTITY = 422;
	private const HTTP_CODE_INTERNAL_SERVER_ERROR = 500;

	private const BITRIX_LABEL = 'wp_bitrix';

	private const REQUEST_PAY_SYSTEM_ID_PARAM = 'paySystemId';
	private const REQUEST_PAYMENT_ID_PARAM = 'paymentId';
	private const REQUEST_SOURCE_PARAM = 'source';

	private const HISTORY_STATUS_PAID = 14;

	private const INVOICE_ID_DELIMITER = '#';
	private const REFERENCE_ID_DELIMITER = '_';

	/**
	 * @return array
	 */
	public static function getHandlerModeList(): array
	{
		return Sale\PaySystem\Manager::getHandlerDescription('Wooppay')['HANDLER_MODE_LIST'];
	}

	/**
	 * @return bool
	 */
	private function isCheckoutMode(): bool
	{
		return $this->service->getField('PS_MODE') === self::MODE_CHECKOUT;
	}

	/**
	 * @param Sale\Payment $payment
	 * @param Main\Request|null $request
	 * @return Sale\PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function initiatePay(Sale\Payment $payment, Main\Request $request = null): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$authResult = $this->auth($payment);
		if (!$authResult->isSuccess())
		{
			$result->addErrors($authResult->getErrors());
			return $result;
		}

		$createInvoiceResult = $this->createInvoice($payment, $authResult->getData()['token']);
		if (!$createInvoiceResult->isSuccess())
		{
			$result->addErrors($createInvoiceResult->getErrors());
			return $result;
		}

		$createInvoiceData = $createInvoiceResult->getData();

		$result->setPsData([
			'PS_INVOICE_ID' => implode(
				self::INVOICE_ID_DELIMITER,
				[
					$createInvoiceData['response']['invoice_id'],
					$createInvoiceData['response']['operation_id'],
				]
			)
		]);

		$templateParams = $this->getTemplateParams($payment);
		$templateParams['url'] = $createInvoiceData['operation_url'];
		$this->setExtraParams($templateParams);

		$showTemplateResult = $this->showTemplate($payment, $this->getTemplateName());
		if ($showTemplateResult->isSuccess())
		{
			$result->setTemplate($showTemplateResult->getTemplate());
		}
		else
		{
			$result->addErrors($showTemplateResult->getErrors());
		}

		if ($this->isCheckoutMode())
		{
			$result->setPaymentUrl($createInvoiceData['operation_url']);
		}

		return $result;
	}

	/**
	 * @param Sale\Payment $payment
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	private function getTemplateParams(Sale\Payment $payment): array
	{
		return [
			'sum' => Sale\PriceMaths::roundPrecision($payment->getSum()),
			'currency' => $payment->getField('CURRENCY'),
		];
	}

	/**
	 * @return string
	 */
	private function getTemplateName(): string
	{
		return (string)$this->service->getField('PS_MODE');
	}

	/**
	 * @param Sale\Payment $payment
	 * @return Sale\PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function auth(Sale\Payment $payment): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'auth');
		$params = [
			'login' => $this->getBusinessValue($payment, 'WOOPPAY_LOGIN'),
			'password' => $this->getBusinessValue($payment, 'WOOPPAY_PASSWORD'),
		];
		$headers = $this->getHeaders();

		$sendResult = $this->send(self::SEND_METHOD_POST, $url, $headers, $params);
		if ($sendResult->isSuccess())
		{
			$sendData = $sendResult->getData();
			if (empty($sendData['token']))
			{
				$result->addError(
					new Main\Error(
						Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_ERROR_TOKEN_NOT_FOUND')
					)
				);
			}
			else
			{
				$result->setData($sendData);
			}
		}
		else
		{
			$result->addErrors($sendResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Sale\Payment $payment
	 * @param string $token
	 * @return Sale\PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function createInvoice(Sale\Payment $payment, string $token): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'createInvoice');
		$params = [
			'reference_id' => $this->getReferenceId($payment),
			'amount' => (string)$payment->getSum(),
			'merchant_name' => $this->getBusinessValue($payment, 'WOOPPAY_LOGIN'),
			'add_info' => $this->getPaymentDescription($payment),
			'request_url' => $this->getRequestUrl($payment),
			'back_url' => $this->getBackUrl($payment),
		];

		if ($phoneNumber = $this->getPhoneNumber($payment))
		{
			$params['user_phone'] = $phoneNumber;
		}

		if ($serviceName = $this->getBusinessValue($payment, 'SERVICE_NAME'))
		{
			$params['service_name'] = $serviceName;
		}

		$headers = $this->getHeaders();
		$headers['Authorization'] = $token;

		$sendResult = $this->send(self::SEND_METHOD_POST, $url, $headers, $params);
		if ($sendResult->isSuccess())
		{
			$result->setData($sendResult->getData());
		}
		else
		{
			$result->addErrors($sendResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Sale\Payment $payment
	 * @return string
	 */
	private function getReferenceId(Sale\Payment $payment): string
	{
		return implode(self::REFERENCE_ID_DELIMITER, [self::BITRIX_LABEL, $payment->getId(), uniqid()]);
	}

	/**
	 * @param Sale\Payment $payment
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function getPaymentDescription(Sale\Payment $payment)
	{
		/** @var Sale\PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();
		$userEmail = $order->getPropertyCollection()->getUserEmail();

		return str_replace(
			[
				'#PAYMENT_NUMBER#',
				'#ORDER_NUMBER#',
				'#PAYMENT_ID#',
				'#ORDER_ID#',
				'#USER_EMAIL#'
			],
			[
				$payment->getField('ACCOUNT_NUMBER'),
				$order->getField('ACCOUNT_NUMBER'),
				$payment->getId(),
				$order->getId(),
				($userEmail) ? $userEmail->getValue() : ''
			],
			$this->getBusinessValue($payment, 'WOOPPAY_PAYMENT_DESCRIPTION')
		);
	}

	/**
	 * @param Sale\Payment $payment
	 * @return string
	 */
	private function getRequestUrl(Sale\Payment $payment): string
	{
		$requestUrl = $this->getBusinessValue($payment, 'WOOPPAY_REQUEST_URL');
		$uri = new Main\Web\Uri($requestUrl);
		$uri->addParams([
			self::REQUEST_PAY_SYSTEM_ID_PARAM => $this->service->getField('ID'),
			self::REQUEST_PAYMENT_ID_PARAM => $payment->getId(),
			self::REQUEST_SOURCE_PARAM => self::BITRIX_LABEL,
		]);

		return $uri->getLocator();
	}

	/**
	 * @param Sale\Payment $payment
	 * @return mixed|string
	 */
	private function getBackUrl(Sale\Payment $payment)
	{
		return $this->getBusinessValue($payment, 'WOOPPAY_BACK_URL')?: $this->service->getContext()->getUrl();
	}

	/**
	 * @todo use business value in future
	 *
	 * @param Sale\Payment $payment
	 * @return string|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getPhoneNumber(Sale\Payment $payment): ?string
	{
		$phoneNumber = null;

		/** @var Sale\PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();

		if ($order instanceof \Bitrix\Crm\Order\Order
			&& $clientCollection = $order->getContactCompanyCollection()
		)
		{
			$primaryClient = $clientCollection->getPrimaryContact();
			$entityId = \CCrmOwnerType::ContactName;

			if ($primaryClient === null)
			{
				$primaryClient = $clientCollection->getPrimaryCompany();
				$entityId = \CCrmOwnerType::CompanyName;
			}

			if ($primaryClient)
			{
				$clientId = $primaryClient->getField('ENTITY_ID');
				$crmFieldMultiResult = \CCrmFieldMulti::GetList(
					['ID' => 'desc'],
					[
						'ENTITY_ID' => $entityId,
						'ELEMENT_ID' => $clientId,
						'TYPE_ID' => 'PHONE',
					]
				);
				while ($crmFieldMultiData = $crmFieldMultiResult->Fetch())
				{
					$phoneNumber = $crmFieldMultiData['VALUE'];
					if ($phoneNumber)
					{
						break;
					}
				}
			}
		}

		if (!$phoneNumber)
		{
			$phoneNumberProp = $order->getPropertyCollection()->getPhone();
			if ($phoneNumberProp)
			{
				$phoneNumber = $phoneNumberProp->getValue();
			}
		}

		return $phoneNumber ? $this->normalizePhone($phoneNumber) : null;
	}

	/**
	 * @param $phoneNumber
	 * @return string|string[]|null
	 */
	private function normalizePhone($phoneNumber)
	{
		$result = null;

		$parsedNumber = Main\PhoneNumber\Parser::getInstance()->parse($phoneNumber, 'KZ');
		if ($parsedNumber->isValid())
		{
			$parsedNumber = $parsedNumber->format(Main\PhoneNumber\Format::E164);
			$result = str_replace('+', '', $parsedNumber);
		}

		return $result;
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $headers
	 * @param array $params
	 * @return Sale\PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function send(string $method, string $url, array $headers, array $params = []): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$httpClient = new Main\Web\HttpClient();
		$httpClient->setHeaders($headers);

		if ($method === self::SEND_METHOD_GET)
		{
			$response = $httpClient->get($url);
		}
		else
		{
			$postData = null;
			if ($params)
			{
				$postData = self::encode($params);
			}

			Sale\PaySystem\Logger::addDebugInfo(__CLASS__.': request data: '.$postData);

			$response = $httpClient->post($url, $postData);
		}

		if ($response === false)
		{
			$errors = $httpClient->getError();
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_ERROR_EMPTY_RESPONSE')));
			foreach ($errors as $code => $message)
			{
				$result->addError(new Main\Error($message, $code));
			}

			return $result;
		}

		Sale\PaySystem\Logger::addDebugInfo(__CLASS__.': response data: '.$response);

		$httpStatus = $httpClient->getStatus();
		$verificationResult = $this->verifyResponse($response, $httpStatus);
		if (!$verificationResult->isSuccess())
		{
			$result->addErrors($verificationResult->getErrors());
			return $result;
		}

		$result->setData(self::decode($response));

		return $result;
	}

	/**
	 * @param string $response
	 * @param int $httpStatus
	 * @return Sale\PaySystem\ServiceResult
	 */
	private function verifyResponse(string $response, int $httpStatus): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$responseData = self::decode($response);
		if (!$responseData)
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_ERROR_DECODE_RESPONSE')));
		}

		if ($httpStatus === self::HTTP_CODE_UNAUTHORIZED)
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_ERROR_STATUS_UNAUTHORIZED')));
			if (!empty($responseData['message']))
			{
				$result->addError(new Main\Error($responseData['message']));
			}
		}
		elseif ($httpStatus === self::HTTP_CODE_UNPROCESSABLE_ENTITY)
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_ERROR_STATUS_UNPROCESSABLE_ENTITY')));
			foreach ($responseData as $error)
			{
				if (!empty($error['field']) && !empty($error['message']))
				{
					$errorMessage = implode(": ", [$error['field'], $error['message']]);
					$result->addError(new Main\Error($errorMessage));
				}
			}
		}
		elseif ($httpStatus === self::HTTP_CODE_INTERNAL_SERVER_ERROR)
		{
			$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_ERROR_STATUS_INTERNAL_SERVER_ERROR')));
		}
		elseif ($httpStatus !== self::HTTP_CODE_OK)
		{
			$result->addError(
				new Main\Error(
					Main\Localization\Loc::getMessage(
						'SALE_HPS_WOOPPAY_ERROR_STATUS',
						[
							'#STATUS#' => $httpStatus,
						]
					)
				)
			);

			if (!empty($responseData['message']))
			{
				$result->addError(new Main\Error($responseData['message']));
			}
		}

		return $result;
	}

	/**
	 * @return string[]
	 */
	private function getHeaders(): array
	{
		return [
			'Content-Type' => 'application/json',
		];
	}

	/**
	 * @param Sale\Payment|null $payment
	 * @return bool
	 */
	protected function isTestMode(Sale\Payment $payment = null): bool
	{
		return $this->getBusinessValue($payment, 'WOOPPAY_TEST_MODE') === 'Y';
	}

	/**
	 * @return array
	 */
	protected function getUrlList(): array
	{
		$testUrl = 'https://api.yii2-stage.test.wooppay.com/';
		$activeUrl = 'https://api-core.wooppay.com/';

		return [
			'auth' => [
				self::TEST_URL => $testUrl.self::API_VERSION_V1.'/auth',
				self::ACTIVE_URL => $activeUrl.self::API_VERSION_V1.'/auth'
			],
			'createInvoice' => [
				self::TEST_URL => $testUrl.self::API_VERSION_V1.'/invoice/create',
				self::ACTIVE_URL => $activeUrl.self::API_VERSION_V1.'/invoice/create'
			],
			'history' => [
				self::TEST_URL => $testUrl.self::API_VERSION_V1.'/history/{id}',
				self::ACTIVE_URL => $activeUrl.self::API_VERSION_V1.'/history/{id}'
			],
			'historyReceipt' => [
				self::TEST_URL => $testUrl.self::API_VERSION_V1.'/history/receipt/{id}',
				self::ACTIVE_URL => $activeUrl.self::API_VERSION_V1.'/history/receipt/{id}'
			],
			'reverse' => [
				self::TEST_URL => $testUrl.self::API_VERSION_V1.'/reverse',
				self::ACTIVE_URL => $activeUrl.self::API_VERSION_V1.'/reverse'
			],
			'reverseMobile' => [
				self::TEST_URL => $testUrl.self::API_VERSION_V1.'/reverse/mobile-return',
				self::ACTIVE_URL => $activeUrl.self::API_VERSION_V1.'/reverse/mobile-return'
			],
		];
	}

	/**
	 * @param Sale\Payment|null $payment
	 * @param string $action
	 * @return string
	 */
	protected function getUrl(Sale\Payment $payment = null, $action): string
	{
		$url = parent::getUrl($payment, $action);
		if ($payment !== null && ($action === 'history' || $action === 'historyReceipt'))
		{
			$url = str_replace('{id}', $this->getOperationId($payment), $url);
		}

		return $url;
	}

	/**
	 * @return array|string[]
	 */
	public function getCurrencyList(): array
	{
		return ['KZT'];
	}

	/**
	 * @return array
	 */
	public static function getIndicativeFields(): array
	{
		return [
			self::REQUEST_PAY_SYSTEM_ID_PARAM,
			self::REQUEST_PAYMENT_ID_PARAM,
			self::REQUEST_SOURCE_PARAM,
		];
	}

	/**
	 * @param Sale\Payment $payment
	 * @param $refundableSum
	 * @return Sale\PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function refund(Sale\Payment $payment, $refundableSum): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$authResult = $this->auth($payment);
		if (!$authResult->isSuccess())
		{
			$result->addErrors($authResult->getErrors());
			return $result;
		}

		$invoiceHistoryResult = $this->getInvoiceHistory($payment, $authResult->getData()['token']);
		if (!$invoiceHistoryResult->isSuccess())
		{
			$result->addErrors($invoiceHistoryResult->getErrors());
			return $result;
		}

		if ($invoiceHistoryResult->getData()['status'] !== self::HISTORY_STATUS_PAID)
		{
			$result->addError(
				new Main\Error(
					Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_REFUND_STATUS_ERROR')
				)
			);
		}

		$invoiceHistoryReceiptResult = $this->getInvoiceHistoryReceipt($payment, $authResult->getData()['token']);
		if (!$invoiceHistoryReceiptResult->isSuccess())
		{
			$result->addErrors($invoiceHistoryReceiptResult->getErrors());
			return $result;
		}

		$url = $this->getUrl($payment, 'reverse');
		$params = [
			'operation_id' => $this->getOperationId($payment),
			'description' => $payment->getField('PAY_RETURN_COMMENT')
				?: Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_REFUND_REASON'),
		];

		if ($this->isMobileAgent($invoiceHistoryReceiptResult->getData()['agent']))
		{
			$url = $this->getUrl($payment, 'reverseMobile');
			$params['force_refund'] = true;
		}

		$headers = $this->getHeaders();
		$headers['Authorization'] = $authResult->getData()['token'];

		$sendResult = $this->send(self::SEND_METHOD_POST, $url, $headers, $params);
		if ($sendResult->isSuccess())
		{
			$result->setOperationType(Sale\PaySystem\ServiceResult::MONEY_LEAVING);
		}
		else
		{
			$result->addErrors($sendResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param string $agent
	 * @return bool
	 */
	private function isMobileAgent(string $agent): bool
	{
		$agentList = ['agentkartel2', 'agentkcell', 'agenttele2'];
		return (bool)array_filter($agentList, static function ($value) use ($agent) {
			return mb_strpos($agent, $value) !== false;
		});
	}

	/**
	 * @param Sale\Payment $payment
	 * @param Main\Request $request
	 * @return Sale\PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function processRequest(Sale\Payment $payment, Main\Request $request): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$authResult = $this->auth($payment);
		if (!$authResult->isSuccess())
		{
			$result->addErrors($authResult->getErrors());
			return $result;
		}

		$invoiceHistoryResult = $this->getInvoiceHistory($payment, $authResult->getData()['token']);
		if (!$invoiceHistoryResult->isSuccess())
		{
			$result->addErrors($invoiceHistoryResult->getErrors());
			return $result;
		}

		$invoiceHistoryData = $invoiceHistoryResult->getData();
		if ($invoiceHistoryData['status'] === self::HISTORY_STATUS_PAID)
		{
			$fields = [
				'PS_STATUS_CODE' => $invoiceHistoryData['status'],
				'PS_SUM' => $invoiceHistoryData['amount'],
				'PS_STATUS' => 'Y',
				'PS_CURRENCY' => $payment->getField('CURRENCY'),
				'PS_RESPONSE_DATE' => new Main\Type\DateTime(),
			];

			if ($this->isSumCorrect($payment, $invoiceHistoryData['amount']))
			{
				$fields['PS_STATUS'] = 'Y';

				Sale\PaySystem\Logger::addDebugInfo(
					__CLASS__.': PS_CHANGE_STATUS_PAY='.$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
				);

				if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y')
				{
					$result->setOperationType(Sale\PaySystem\ServiceResult::MONEY_COMING);
				}
			}
			else
			{
				$result->addError(new Main\Error(Main\Localization\Loc::getMessage('SALE_HPS_WOOPPAY_ERROR_SUM')));
			}

			$result->setPsData($fields);
		}
		else
		{
			$result->addError(
				new Main\Error(
					Main\Localization\Loc::getMessage(
						'SALE_HPS_WOOPPAY_ERROR_REQUEST_STATUS',
						[
							'#STATUS#' => $invoiceHistoryData['status'],
						]
					)
				)
			);
		}

		return $result;
	}

	/**
	 * @param Sale\Payment $payment
	 * @param string $token
	 * @return Sale\PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function getInvoiceHistory(Sale\Payment $payment, string $token): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'history');
		$headers['Authorization'] = $token;

		$sendResult = $this->send(self::SEND_METHOD_GET, $url, $headers);
		if ($sendResult->isSuccess())
		{
			$result->setData($sendResult->getData());
		}
		else
		{
			$result->addErrors($sendResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Sale\Payment $payment
	 * @param string $token
	 * @return Sale\PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function getInvoiceHistoryReceipt(Sale\Payment $payment, string $token): Sale\PaySystem\ServiceResult
	{
		$result = new Sale\PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'historyReceipt');
		$headers['Authorization'] = $token;

		$sendResult = $this->send(self::SEND_METHOD_GET, $url, $headers);
		if ($sendResult->isSuccess())
		{
			$result->setData($sendResult->getData());
		}
		else
		{
			$result->addErrors($sendResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Sale\Payment $payment
	 * @param $sum
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function isSumCorrect(Sale\Payment $payment, $sum): bool
	{
		Sale\PaySystem\Logger::addDebugInfo(
			__CLASS__
			.': WooppaySum = '.Sale\PriceMaths::roundPrecision($sum)
			.'; paymentSum = '.Sale\PriceMaths::roundPrecision($payment->getSum())
		);

		return Sale\PriceMaths::roundPrecision($sum) === Sale\PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Main\Request $request
	 * @return array|mixed|string|null
	 */
	public function getPaymentIdFromRequest(Main\Request $request)
	{
		return $request->get(self::REQUEST_PAYMENT_ID_PARAM);
	}

	/**
	 * @param Sale\Payment $payment
	 * @return string|null
	 */
	private function getOperationId(Sale\Payment $payment): ?string
	{
		$psInvoiceId = $payment->getField('PS_INVOICE_ID');
		if ($psInvoiceId)
		{
			[, $operationId] = explode(self::INVOICE_ID_DELIMITER, $psInvoiceId);
			return (string)$operationId;
		}

		return null;
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws Main\ArgumentException
	 */
	private static function encode(array $data)
	{
		return Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @param $data
	 * @return false|mixed
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
	 * @param Sale\PaySystem\ServiceResult $result
	 * @param Main\Request $request
	 * @return mixed|string|void
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function sendResponse(Sale\PaySystem\ServiceResult $result, Main\Request $request)
	{
		if ($result->isSuccess())
		{
			/** @noinspection PhpVariableNamingConventionInspection */
			global $APPLICATION;
			$APPLICATION->RestartBuffer();

			$result = self::encode([
				"data" => 1
			]);

			Sale\PaySystem\Logger::addDebugInfo(__CLASS__.': response: '.$result);

			echo $result;
		}
	}
}
