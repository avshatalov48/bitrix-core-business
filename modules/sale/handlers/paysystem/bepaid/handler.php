<?php
namespace Sale\Handlers\PaySystem;

use Bitrix\Main,
	Bitrix\Main\Web\HttpClient,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale,
	Bitrix\Sale\PaySystem,
	Bitrix\Main\Request,
	Bitrix\Sale\Payment,
	Bitrix\Sale\PaySystem\ServiceResult,
	Bitrix\Sale\PaymentCollection,
	Bitrix\Sale\PriceMaths;

Loc::loadMessages(__FILE__);

/**
 * Class BePaidHandler
 * @package Sale\Handlers\PaySystem
 */
class BePaidHandler extends PaySystem\ServiceHandler implements PaySystem\IRefund
{
	private const MODE_CHECKOUT = 'checkout';
	private const MODE_WIDGET = 'widget';

	private const CHECKOUT_API_URL = 'https://checkout.bepaid.by';
	private const GATEWAY_API_URL = 'https://gateway.bepaid.by';
	private const API_VERSION = '2.1';

	private const TRACKING_ID_DELIMITER = '#';

	private const STATUS_SUCCESSFUL_CODE = 'successful';
	private const STATUS_ERROR_CODE = 'error';

	private const SEND_METHOD_HTTP_POST = "POST";
	private const SEND_METHOD_HTTP_GET = "GET";

	/**
	 * @return array
	 */
	public static function getHandlerModeList(): array
	{
		return array(
			static::MODE_CHECKOUT => Loc::getMessage('SALE_HPS_BEPAID_CHECKOUT_MODE'),
			static::MODE_WIDGET => Loc::getMessage('SALE_HPS_BEPAID_WIDGET_MODE'),
		);
	}

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function initiatePay(Payment $payment, Request $request = null): ServiceResult
	{
		$result = new ServiceResult();

		$createPaymentTokenResult = $this->createPaymentToken($payment);
		if (!$createPaymentTokenResult->isSuccess())
		{
			$result->addErrors($createPaymentTokenResult->getErrors());
			return $result;
		}

		$createPaymentTokenData = $createPaymentTokenResult->getData();
		if (!empty($createPaymentTokenData['checkout']['token']))
		{
			$result->setPsData(['PS_INVOICE_ID' => $createPaymentTokenData['checkout']['token']]);
		}

		if ($this->isCheckoutMode())
		{
			$result->setPaymentUrl($createPaymentTokenData['checkout']['redirect_url']);
		}

		$this->setExtraParams($this->getTemplateParams($payment, $createPaymentTokenData));

		$showTemplateResult = $this->showTemplate($payment, $this->getTemplateName());
		if ($showTemplateResult->isSuccess())
		{
			$result->setTemplate($showTemplateResult->getTemplate());
		}
		else
		{
			$result->addErrors($showTemplateResult->getErrors());
		}

		return $result;
	}

	/**
	 * @return string
	 */
	private function getTemplateName(): string
	{
		return (string)$this->service->getField('PS_MODE');
	}

	/**
	 * @param Payment $payment
	 * @param array $paymentTokenData
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getTemplateParams(Payment $payment, array $paymentTokenData): array
	{
		$params = [
			'sum' => (string)(PriceMaths::roundPrecision($payment->getSum())),
			'currency' => $payment->getField('CURRENCY'),
		];

		if ($this->isWidgetMode())
		{
			$params['checkout_url'] = self::CHECKOUT_API_URL;
			$params['token'] = $paymentTokenData['checkout']['token'];
			$params['checkout'] = [
				'iframe' => true,
				'test' => $this->isTestMode($payment),
				'transaction_type' => 'payment',
				'order' => [
					'amount' => (string)(PriceMaths::roundPrecision($payment->getSum()) * 100),
					'currency' => $payment->getField('CURRENCY'),
					'description' => $this->getPaymentDescription($payment),
					'tracking_id' => $payment->getId().self::TRACKING_ID_DELIMITER.$this->service->getField('ID'),
					'additional_data' => self::getAdditionalData(),
				],
				'settings' => [
					'success_url' => $this->getSuccessUrl($payment),
					'decline_url' => $this->getDeclineUrl($payment),
					'notification_url' => $this->getBusinessValue($payment, 'BEPAID_NOTIFICATION_URL'),
					'language' => LANGUAGE_ID,
				],
			];
		}
		else
		{
			$params['url'] = $paymentTokenData['checkout']['redirect_url'];
		}

		return $params;
	}

	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function createPaymentToken(Payment $payment): ServiceResult
	{
		$result = new ServiceResult();

		$url = $this->getUrl($payment, 'getPaymentToken');
		$params = [
			'checkout' => [
				'version' => self::API_VERSION,
				'test' => $this->isTestMode($payment),
				'transaction_type' => 'payment',
				'order' => [
					'amount' => (string)(PriceMaths::roundPrecision($payment->getSum()) * 100),
					'currency' => $payment->getField('CURRENCY'),
					'description' => $this->getPaymentDescription($payment),
					'tracking_id' => $payment->getId().self::TRACKING_ID_DELIMITER.$this->service->getField('ID'),
					'additional_data' => self::getAdditionalData(),
				],
				'settings' => [
					'success_url' => $this->getSuccessUrl($payment),
					'decline_url' => $this->getDeclineUrl($payment),
					'fail_url' => $this->getFailUrl($payment),
					'cancel_url' => $this->getCancelUrl($payment),
					'notification_url' => $this->getBusinessValue($payment, 'BEPAID_NOTIFICATION_URL'),
					'language' => LANGUAGE_ID,
				],
			],
		];
		$headers = $this->getHeaders($payment);

		$sendResult = $this->send(self::SEND_METHOD_HTTP_POST, $url, $params, $headers);
		if ($sendResult->isSuccess())
		{
			$paymentTokenData = $sendResult->getData();
			$verifyResponseResult = $this->verifyResponse($paymentTokenData);
			if ($verifyResponseResult->isSuccess())
			{
				$result->setData($paymentTokenData);
			}
			else
			{
				$result->addErrors($verifyResponseResult->getErrors());
			}
		}
		else
		{
			$result->addErrors($sendResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function getBePaidPayment(Payment $payment): ServiceResult
	{
		$result = new ServiceResult();

		$url = $this->getUrl($payment, 'getPaymentStatus');
		$headers = $this->getHeaders($payment);

		$sendResult = $this->send(self::SEND_METHOD_HTTP_GET, $url, [], $headers);
		if ($sendResult->isSuccess())
		{
			$paymentData = $sendResult->getData();
			$verifyResponseResult = $this->verifyResponse($paymentData);
			if ($verifyResponseResult->isSuccess())
			{
				$result->setData($paymentData);
			}
			else
			{
				$result->addErrors($verifyResponseResult->getErrors());
			}
		}
		else
		{
			$result->addErrors($sendResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param $refundableSum
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function refund(Payment $payment, $refundableSum): ServiceResult
	{
		$result = new ServiceResult();

		$bePaidPaymentResult = $this->getBePaidPayment($payment);
		if (!$bePaidPaymentResult->isSuccess())
		{
			$result->addErrors($bePaidPaymentResult->getErrors());
			return $result;
		}

		$bePaidPaymentData = $bePaidPaymentResult->getData();

		$url = $this->getUrl($payment, 'refund');
		$params = [
			'request' => [
				'parent_uid' => $bePaidPaymentData['checkout']['gateway_response']['payment']['uid'],
				'amount' => (string)(PriceMaths::roundPrecision($refundableSum) * 100),
				'reason' => $payment->getField('PAY_RETURN_COMMENT') ?: Loc::getMessage('SALE_HPS_BEPAID_REFUND_REASON'),
			],
		];
		$headers = $this->getHeaders($payment);

		$sendResult = $this->send(self::SEND_METHOD_HTTP_POST, $url, $params, $headers);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$refundData = $sendResult->getData();
		$verifyResponseResult = $this->verifyResponse($refundData);
		if ($verifyResponseResult->isSuccess())
		{
			if ($refundData['transaction']['status'] === static::STATUS_SUCCESSFUL_CODE
				&& PriceMaths::roundPrecision($refundData['transaction']['amount'] / 100) === PriceMaths::roundPrecision($refundableSum)
			)
			{
				$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
			}
		}
		else
		{
			$result->addErrors($verifyResponseResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $params
	 * @param array $headers
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function send(string $method, string $url, array $params = [], array $headers = []): ServiceResult
	{
		$result = new ServiceResult();

		$httpClient = new HttpClient();
		foreach ($headers as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		if ($method === self::SEND_METHOD_HTTP_GET)
		{
			$response = $httpClient->get($url);
		}
		else
		{
			$postData = null;
			if ($params)
			{
				$postData = static::encode($params);
			}

			PaySystem\Logger::addDebugInfo(__CLASS__.': request data: '.$postData);

			$response = $httpClient->post($url, $postData);
		}

		if ($response === false)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(PaySystem\Error::create($message, $code));
			}

			return $result;
		}

		PaySystem\Logger::addDebugInfo(__CLASS__.': response data: '.$response);

		$response = static::decode($response);
		if ($response)
		{
			$result->setData($response);
		}
		else
		{
			$result->addError(PaySystem\Error::create(Loc::getMessage('SALE_HPS_BEPAID_RESPONSE_DECODE_ERROR')));
		}

		return $result;
	}

	/**
	 * @param array $response
	 * @return ServiceResult
	 */
	private function verifyResponse(array $response): ServiceResult
	{
		$result = new ServiceResult();

		if (!empty($response['errors']))
		{
			$result->addError(PaySystem\Error::create($response['message']));
		}
		elseif (!empty($response['response']['errors']))
		{
			$result->addError(PaySystem\Error::create($response['response']['message']));
		}
		elseif (!empty($response['response']['status']) && $response['response']['status'] === self::STATUS_ERROR_CODE)
		{
			$result->addError(PaySystem\Error::create($response['response']['message']));
		}
		elseif (!empty($response['checkout']['status']) && $response['checkout']['status'] === self::STATUS_ERROR_CODE)
		{
			$result->addError(PaySystem\Error::create($response['checkout']['message']));
		}

		return $result;
	}

	/**
	 * @return array|string[]
	 */
	public function getCurrencyList(): array
	{
		return ['BYN', 'USD', 'EUR', 'RUB'];
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function processRequest(Payment $payment, Request $request): ServiceResult
	{
		$result = new ServiceResult();

		$inputStream = static::readFromStream();
		$data = static::decode($inputStream);
		$transaction = $data['transaction'];

		$bePaidPaymentResult = $this->getBePaidPayment($payment);
		if ($bePaidPaymentResult->isSuccess())
		{
			$bePaidPaymentData = $bePaidPaymentResult->getData();
			if ($bePaidPaymentData['checkout']['status'] === self::STATUS_SUCCESSFUL_CODE)
			{
				$description = Loc::getMessage('SALE_HPS_BEPAID_TRANSACTION', [
					'#ID#' => $transaction['uid'],
				]);
				$fields = [
					'PS_STATUS_CODE' => $transaction['status'],
					'PS_STATUS_DESCRIPTION' => $description,
					'PS_SUM' => $transaction['amount'] / 100,
					'PS_STATUS' => 'N',
					'PS_CURRENCY' => $transaction['currency'],
					'PS_RESPONSE_DATE' => new Main\Type\DateTime()
				];

				if ($this->isSumCorrect($payment, $transaction['amount'] / 100))
				{
					$fields['PS_STATUS'] = 'Y';

					PaySystem\Logger::addDebugInfo(
						__CLASS__.': PS_CHANGE_STATUS_PAY='.$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
					);

					if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y')
					{
						$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
					}
				}
				else
				{
					$error = Loc::getMessage('SALE_HPS_BEPAID_ERROR_SUM');
					$fields['PS_STATUS_DESCRIPTION'] .= '. '.$error;
					$result->addError(PaySystem\Error::create($error));
				}

				$result->setPsData($fields);
			}
		}
		else
		{
			$result->addErrors($bePaidPaymentResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param $sum
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function isSumCorrect(Payment $payment, $sum): bool
	{
		PaySystem\Logger::addDebugInfo(
			__CLASS__.': bePaidSum='.PriceMaths::roundPrecision($sum)."; paymentSum=".PriceMaths::roundPrecision($payment->getSum())
		);

		return PriceMaths::roundPrecision($sum) === PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Request $request
	 * @param int $paySystemId
	 * @return bool
	 */
	public static function isMyResponse(Request $request, $paySystemId): bool
	{
		$inputStream = static::readFromStream();
		if ($inputStream)
		{
			$data = static::decode($inputStream);
			if ($data === false)
			{
				return false;
			}

			if (isset($data['transaction']['tracking_id']))
			{
				[, $trackingPaySystemId] = explode(self::TRACKING_ID_DELIMITER, $data['transaction']['tracking_id']);
				return (int)$trackingPaySystemId === (int)$paySystemId;
			}
		}

		return false;
	}

	/**
	 * @param Request $request
	 * @return bool|int|mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		$inputStream = static::readFromStream();
		if ($inputStream)
		{
			$data = static::decode($inputStream);
			if (isset($data['transaction']['tracking_id']))
			{
				[$trackingPaymentId] = explode(self::TRACKING_ID_DELIMITER, $data['transaction']['tracking_id']);
				return (int)$trackingPaymentId;
			}
		}

		return false;
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getPaymentDescription(Payment $payment)
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();
		$userEmail = $order->getPropertyCollection()->getUserEmail();

		$description =  str_replace(
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
			$this->getBusinessValue($payment, 'BEPAID_PAYMENT_DESCRIPTION')
		);

		return $description;
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getSuccessUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'BEPAID_SUCCESS_URL') ?: $this->service->getContext()->getUrl();
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getDeclineUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'BEPAID_DECLINE_URL') ?: $this->service->getContext()->getUrl();
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getFailUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'BEPAID_FAIL_URL') ?: $this->service->getContext()->getUrl();
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getCancelUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'BEPAID_CANCEL_URL') ?: $this->service->getContext()->getUrl();
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	private function getHeaders(Payment $payment): array
	{
		$headers = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => 'Basic '.$this->getBasicAuthString($payment),
			'RequestID' => $this->getIdempotenceKey(),
		];

		if ($this->isWidgetMode())
		{
			$headers['X-API-Version'] = 2;
		}

		return $headers;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getBasicAuthString(Payment $payment): string
	{
		return base64_encode(
			$this->getBusinessValue($payment, 'BEPAID_ID')
			. ':'
			. $this->getBusinessValue($payment, 'BEPAID_SECRET_KEY')
		);
	}

	/**
	 * @return bool
	 */
	private function isWidgetMode(): bool
	{
		return $this->service->getField('PS_MODE') === self::MODE_WIDGET;
	}

	/**
	 * @return bool
	 */
	private function isCheckoutMode(): bool
	{
		return $this->service->getField('PS_MODE') === self::MODE_CHECKOUT;
	}

	/**
	 * @return string
	 */
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

	/**
	 * @param Payment $payment
	 * @param string $action
	 * @return string
	 */
	protected function getUrl(Payment $payment = null, $action): string
	{
		$url = parent::getUrl($payment, $action);
		if ($payment !== null && $action === 'getPaymentStatus')
		{
			$url = str_replace('#payment_token#', $payment->getField('PS_INVOICE_ID'), $url);
		}

		return $url;
	}

	/**
	 * @return array
	 */
	protected function getUrlList(): array
	{
		return [
			'getPaymentToken' => self::CHECKOUT_API_URL.'/ctp/api/checkouts',
			'getPaymentStatus' => self::CHECKOUT_API_URL.'/ctp/api/checkouts/#payment_token#',
			'refund' => self::GATEWAY_API_URL.'/transactions/refunds',
		];
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null): bool
	{
		return ($this->getBusinessValue($payment, 'PS_IS_TEST') === 'Y');
	}

	/**
	 * @return bool|string
	 */
	private static function readFromStream()
	{
		return file_get_contents('php://input');
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

	private static function getAdditionalData(): array
	{
		$additionalData = [
			'platform_data' => self::getPlatformData(),
		];

		$integrationData = self::getIntegrationData();
		if ($integrationData)
		{
			$additionalData['integration_data'] = $integrationData;
		}

		return $additionalData;
	}

	private static function getPlatformData(): string
	{
		if (Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$platformType = 'Bitrix24';
		}
		elseif (Main\ModuleManager::isModuleInstalled('intranet'))
		{
			$platformType = 'Self-hosted';
		}
		else
		{
			$platformType = 'Bitrix Site Manager';
		}

		return $platformType;
	}

	private static function getIntegrationData(): ?string
	{
		$version = self::getSaleModuleVersion();
		if (!$version)
		{
			return null;
		}

		return 'bePaid system module v' . $version;
	}

	private static function getSaleModuleVersion(): ?string
	{
		$modulePath = getLocalPath('modules/sale/install/version.php');
		if ($modulePath === false)
		{
			return null;
		}

		$arModuleVersion = array();
		include $_SERVER['DOCUMENT_ROOT'].$modulePath;
		return (isset($arModuleVersion['VERSION']) ? (string)$arModuleVersion['VERSION'] : null);
	}
}
