<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\ServiceResult;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PriceMaths;
use Bitrix\Main\Context;

Loc::loadMessages(__FILE__);

/**
 * Class BePaidEripHandler
 * @package Sale\Handlers\PaySystem
 *
 * @see https://docs.bepaid.by/ru/erip/intro
 */
class BePaidEripHandler extends PaySystem\ServiceHandler
{
	private const API_URL = 'https://api.bepaid.by';
	private const TRACKING_ID_DELIMITER = '#';
	private const STATUS_SUCCESSFUL_CODE = 'successful';

	/**
	 * @inheritDoc
	 */
	public function initiatePay(Payment $payment, Request $request = null): ServiceResult
	{
		$result = new ServiceResult();

		$createInvoiceResult = $this->createInvoice($payment);
		if (!$createInvoiceResult->isSuccess())
		{
			$result->addErrors($createInvoiceResult->getErrors());
			return $result;
		}

		$invoiceData = $createInvoiceResult->getData();
		if (!empty($invoiceData['transaction']['uid']))
		{
			$result->setPsData(['PS_INVOICE_ID' => $invoiceData['transaction']['uid']]);
		}

		$this->setExtraParams([
			'sum' => PriceMaths::roundPrecision($payment->getSum()),
			'currency' => $payment->getField('CURRENCY'),
			'instruction' => $invoiceData['transaction']['erip']['instruction'],
			'qr_code' => $invoiceData['transaction']['erip']['qr_code'],
			'account_number' =>  $invoiceData['transaction']['erip']['account_number'],
			'service_no_erip' => $invoiceData['transaction']['erip']['service_no_erip'],
		]);

		$showTemplateResult = $this->showTemplate($payment, 'template');
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
	 * @inheritDoc
	 */
	public function processRequest(Payment $payment, Request $request): ServiceResult
	{
		$result = new ServiceResult();

		$inputStream = self::readFromStream();
		$data = self::decode($inputStream);
		if ($data === false)
		{
			return $result->addError(
				PaySystem\Error::create(
					Loc::getMessage('SALE_HPS_BEPAID_ERIP_RESPONSE_ERROR')
				)
			);
		}
		$transaction = $data['transaction'];

		if (!$this->isSignatureCorrect($payment, $inputStream))
		{
			return $result->addError(
				PaySystem\Error::create(
					Loc::getMessage('SALE_HPS_BEPAID_ERIP_ERROR_SIGNATURE')
				)
			);
		}

		$getInvoiceResult = $this->getInvoice($payment);
		if (!$getInvoiceResult->isSuccess())
		{
			return $result->addErrors($getInvoiceResult->getErrors());
		}

		$invoiceData = $getInvoiceResult->getData();
		if ($invoiceData['transaction']['status'] === self::STATUS_SUCCESSFUL_CODE)
		{
			$fields = [
				'PS_STATUS_CODE' => $transaction['status'],
				'PS_STATUS_DESCRIPTION' => Loc::getMessage('SALE_HPS_BEPAID_ERIP_TRANSACTION', [
					'#ID#' => $transaction['uid'],
				]),
				'PS_SUM' => $transaction['amount'] / 100,
				'PS_STATUS' => 'N',
				'PS_CURRENCY' => $transaction['currency'],
				'PS_RESPONSE_DATE' => new Main\Type\DateTime()
			];
			if ($this->isSumCorrect($payment, $transaction['amount'] / 100))
			{
				$fields['PS_STATUS'] = 'Y';

				PaySystem\Logger::addDebugInfo(
					sprintf(
						'%s: PS_CHANGE_STATUS_PAY=%s',
						__CLASS__,
						$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
					)
				);
				if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y')
				{
					$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
				}
			}
			else
			{
				$error = Loc::getMessage('SALE_HPS_BEPAID_ERIP_ERROR_SUM');
				$fields['PS_STATUS_DESCRIPTION'] .= '. ' . $error;
				$result->addError(PaySystem\Error::create($error));
			}

			$result->setPsData($fields);
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		$inputStream = self::readFromStream();
		if (!$inputStream)
		{
			return false;
		}

		$data = self::decode($inputStream);
		if ($data === false)
		{
			return false;
		}

		if (isset($data['transaction']['tracking_id']))
		{
			[$trackingPaymentId] = explode(self::TRACKING_ID_DELIMITER, $data['transaction']['tracking_id']);
			return (int)$trackingPaymentId;
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getCurrencyList(): array
	{
		return ['BYN'];
	}

	/**
	 * @inheritDoc
	 */
	public static function isMyResponse(Request $request, $paySystemId): bool
	{
		$inputStream = self::readFromStream();
		if ($inputStream)
		{
			$data = self::decode($inputStream);
			if ($data === false)
			{
				return false;
			}

			if (isset($data['transaction']['tracking_id']))
			{
				[, $trackingPaySystemId] = explode(
					self::TRACKING_ID_DELIMITER,
					$data['transaction']['tracking_id']
				);
				return (int)$trackingPaySystemId === (int)$paySystemId;
			}
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrl(Payment $payment = null, $action): string
	{
		return str_replace(
			'#invoice-id#',
			$payment ? $payment->getField('PS_INVOICE_ID') : '',
			parent::getUrl($payment, $action)
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function getUrlList(): array
	{
		return [
			'createInvoice' => self::API_URL . '/beyag/payments',
			'getInvoice' => self::API_URL . '/beyag/payments/#invoice-id#',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function isTestMode(Payment $payment = null): bool
	{
		return ($this->getBusinessValue($payment, 'PS_IS_TEST') === 'Y');
	}

	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 */
	private function createInvoice(Payment $payment): ServiceResult
	{
		$result = new ServiceResult();

		$params = [
			'request' => [
				'test' => $this->isTestMode($payment),
				'amount' => (string)(PriceMaths::roundPrecision($payment->getSum()) * 100),
				'currency' => $payment->getField('CURRENCY'),
				'description' => $this->getInvoiceDescription($payment),
				'tracking_id' => $payment->getId() . self::TRACKING_ID_DELIMITER . $this->service->getField('ID'),
				'notification_url' => $this->getBusinessValue($payment, 'BEPAID_ERIP_NOTIFICATION_URL'),
				'language' => LANGUAGE_ID,
				'email' => $this->getUserEmail($payment),
				'ip' => Context::getCurrent()->getServer()->getRemoteAddr(),
				'payment_method' => [
					'type' => 'erip',
					'account_number' => $payment->getId(),
				],
				'additional_data' => self::getAdditionalData(),
			]
		];

		$serviceCode = $this->getBusinessValue($payment, 'BEPAID_ERIP_SERVICE_CODE');
		if (isset($serviceCode) && !empty($serviceCode))
		{
			$params['request']['payment_method']['service_no'] = $serviceCode;
		}

		$sendResult = $this->send(
			HttpClient::HTTP_POST,
			$this->getUrl($payment, 'createInvoice'),
			$params,
			$this->getHeaders($payment)
		);
		if ($sendResult->isSuccess())
		{
			$invoiceData = $sendResult->getData();
			$verifyResponseResult = $this->verifyResponse($invoiceData);
			if ($verifyResponseResult->isSuccess())
			{
				$result->setData($invoiceData);
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
	 */
	private function getInvoice(Payment $payment): ServiceResult
	{
		$result = new ServiceResult();

		$sendResult = $this->send(
			HttpClient::HTTP_GET,
			$this->getUrl($payment, 'getInvoice'),
			[],
			$this->getHeaders($payment)
		);
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
	 * @param $inputStream
	 * @return bool
	 */
	private function isSignatureCorrect(Payment $payment, $inputStream): bool
	{
		PaySystem\Logger::addDebugInfo(
			sprintf(
				'%s: Signature: %s; Webhook: %s',
				__CLASS__,
				$_SERVER['HTTP_CONTENT_SIGNATURE'],
				$inputStream
			)
		);

		$signature  = base64_decode($_SERVER['HTTP_CONTENT_SIGNATURE']);
		if (!$signature)
		{
			return false;
		}

		$publicKey = sprintf(
			"-----BEGIN PUBLIC KEY-----\n%s-----END PUBLIC KEY-----",
			chunk_split(
				str_replace(
					[
						"\r\n",
						"\n",
					],
					'',
					$this->getBusinessValue($payment, 'BEPAID_ERIP_PUBLIC_KEY')
				),
				64
			)
		);
		$key = openssl_pkey_get_public($publicKey);

		return openssl_verify($inputStream, $signature, $key, OPENSSL_ALGO_SHA256) === 1;
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	private function getHeaders(Payment $payment): array
	{
		return [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => 'Basic ' . $this->getBasicAuthString($payment),
			'RequestID' => $this->getIdempotenceKey(),
		];
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $params
	 * @param array $headers
	 * @return ServiceResult
	 */
	private function send(string $method, string $url, array $params = [], array $headers = []): ServiceResult
	{
		$result = new ServiceResult();

		$httpClient = new HttpClient();
		foreach ($headers as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		PaySystem\Logger::addDebugInfo(__CLASS__.': request url: '.$url);

		if ($method === HttpClient::HTTP_GET)
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

			PaySystem\Logger::addDebugInfo(__CLASS__.': request data: '.$postData);

			$response = $httpClient->query($method, $url, $postData);
			if ($response)
			{
				$response = $httpClient->getResult();
			}
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

		$response = self::decode($response);
		if ($response === false)
		{
			return $result->addError(PaySystem\Error::create(
				Loc::getMessage('SALE_HPS_BEPAID_ERIP_RESPONSE_ERROR')
			));
		}

		$result->setData($response);

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

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 */
	private function getInvoiceDescription(Payment $payment)
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();

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
				$this->getUserEmail($payment)
			],
			$this->getBusinessValue($payment, 'BEPAID_ERIP_PAYMENT_DESCRIPTION')
		);

		return $description;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getUserEmail(Payment $payment): string
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();
		$userEmail = $order->getPropertyCollection()->getUserEmail();

		return $userEmail ? (string)$userEmail->getValue() : '';
	}

	/**
	 * @param Payment $payment
	 * @param $sum
	 * @return bool
	 */
	private function isSumCorrect(Payment $payment, $sum): bool
	{
		PaySystem\Logger::addDebugInfo(
			sprintf( '%s: bePaidSum=%s; paymentSum=%s',
				__CLASS__,
				PriceMaths::roundPrecision($sum),
				PriceMaths::roundPrecision($payment->getSum())
			)
		);

		return PriceMaths::roundPrecision($sum) === PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getBasicAuthString(Payment $payment): string
	{
		return base64_encode(
			sprintf(
				'%s:%s',
				$this->getBusinessValue($payment, 'BEPAID_ERIP_ID'),
				$this->getBusinessValue($payment, 'BEPAID_ERIP_SECRET_KEY')
			)
		);
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
	 * @return bool|string
	 */
	private static function readFromStream()
	{
		return file_get_contents('php://input');
	}

	/**
	 * @param array $data
	 * @return mixed
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

	/**
	 * @return string[]
	 */
	private static function getAdditionalData(): array
	{
		$result = [
			'platform_data' => self::getPlatformData(),
		];

		$integrationData = self::getIntegrationData();
		if ($integrationData)
		{
			$result['integration_data'] = $integrationData;
		}

		return $result;
	}

	/**
	 * @return string
	 */
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

	/**
	 * @return string|null
	 */
	private static function getIntegrationData(): ?string
	{
		$version = self::getSaleModuleVersion();
		if (!$version)
		{
			return null;
		}

		return 'bePaid system module v' . $version;
	}

	/**
	 * @return string|null
	 */
	private static function getSaleModuleVersion(): ?string
	{
		$modulePath = getLocalPath('modules/sale/install/version.php');
		if ($modulePath === false)
		{
			return null;
		}

		$arModuleVersion = [];
		include $_SERVER['DOCUMENT_ROOT'] . $modulePath;
		return isset($arModuleVersion['VERSION']) ? (string)$arModuleVersion['VERSION'] : null;
	}
}
