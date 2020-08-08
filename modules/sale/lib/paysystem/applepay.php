<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Payment,
	Bitrix\Sale\PriceMaths;

/**
 * Class ApplePay
 * @package Bitrix\Sale\PaySystem
 */
final class ApplePay
{
	private const MERCHANT_SESSION_GATEWAY = "https://apple-pay-gateway.apple.com/paymentservices/paymentSession";
	private const FILE_PREFIX = "apple_pay_cert_";

	private const HTTP_RESPONSE_CODE_OK = 200;

	/** @var string */
	private $merchantIdentifier;
	/** @var string */
	private $displayName;
	/** @var string */
	private $domainName;
	/** @var string */
	private $initiativeContext;
	/** @var string */
	private $applePayCert;

	/**
	 * ApplePay constructor.
	 *
	 * @param $merchantIdentifier
	 * @param $displayName
	 * @param $domainName
	 * @param $applePayCert
	 */
	public function __construct(string $merchantIdentifier, string $displayName, string $domainName, string $applePayCert)
	{
		$this->merchantIdentifier = $merchantIdentifier;
		$this->displayName = $displayName;
		$this->domainName = $domainName;
		$this->applePayCert = $applePayCert;
	}

	/**
	 * @param string $initiativeContext
	 */
	public function setInitiativeContext(string $initiativeContext): void
	{
		$this->initiativeContext = $initiativeContext;
	}

	/**
	 * @param $url
	 * @return ServiceResult
	 */
	public function getWebSession($url): ServiceResult
	{
		$result = new ServiceResult();

		$requestParameters = [
			"merchantIdentifier" => $this->merchantIdentifier,
			"displayName" => $this->displayName,
			"initiativeContext" => $this->domainName,
			"initiative" => "web",
		];

		try
		{
			$result = $this->sendRequest($url, $requestParameters);
		}
		catch (Main\SystemException $ex)
		{
			$result->addError(new Main\Error("Failed to get web session"));
		}

		return $result;
	}

	/**
	 * @return ServiceResult
	 */
	public function getIMessageSession(): ServiceResult
	{
		$result = new ServiceResult();

		$requestParameters = [
			"merchantIdentifier" => hash("sha256", $this->merchantIdentifier),
			"displayName" => $this->displayName,
			"domainName" => $this->domainName,
			"initiative" => "messaging",
			"initiativeContext" => $this->initiativeContext,
		];

		try
		{
			$result = $this->sendRequest(self::MERCHANT_SESSION_GATEWAY, $requestParameters);
		}
		catch (Main\SystemException $ex)
		{
			$result->addError(new Main\Error("Failed to get messenger session"));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param array $config
	 * @return ServiceResult
	 * @throws Main\ArgumentNullException
	 */
	public function getIMessagePayment(Payment $payment, array $config): ServiceResult
	{
		$result = new ServiceResult();

		$checkConfigResult = $this->checkConfig($config);
		if (!$checkConfigResult->isSuccess())
		{
			$result->addErrors($checkConfigResult->getErrors());
			return $result;
		}

		$messengerDataResult = $this->prepareIMessageData($payment, $config);
		if (!$messengerDataResult->isSuccess())
		{
			$result->addErrors($messengerDataResult->getErrors());
			return $result;
		}

		$result->setData($messengerDataResult->getData());

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param array $config
	 * @return ServiceResult
	 * @throws Main\ArgumentNullException
	 */
	private function prepareIMessageData(Payment $payment, array $config): ServiceResult
	{
		$result = new ServiceResult();

		$merchantSessionResult = $this->getIMessageSession();
		if (!$merchantSessionResult->isSuccess())
		{
			$result->addErrors($merchantSessionResult->getErrors());
			return $result;
		}

		$orderId = $payment->getOrder()->getId();
		$paymentSum = PriceMaths::roundPrecision($payment->getSum());

		$receivedMessage = [
			"style" => "icon",
			"title" =>  $config["merchantDisplayName"],
			"subtitle" => Loc::getMessage(
				"SALE_APPLE_PAY_ORDER_SUBTITLE",
				[
					"#ORDER_ID#" => $orderId,
					"#SUM#" => \SaleFormatCurrency($paymentSum, $payment->getField('CURRENCY')),
				]
			),
		];

		$replyMessage = [
			"title" => Loc::getMessage(
				"SALE_APPLE_PAY_ORDER_SUBTITLE",
				[
					"#ORDER_ID#" => $orderId,
					"#SUM#" => \SaleFormatCurrency($paymentSum, $payment->getField('CURRENCY')),
				]
			),
		];

		$data = [
			"requestIdentifier" => self::getUuid(),
			"mspVersion" => "1.0",
			"payment" => [
				"paymentRequest" => [
					"lineItems" => [
						[
							"label" => Loc::getMessage(
								"SALE_APPLE_PAY_LINE_ITEM_ORDER",
								[
									"#ORDER_ID#" => $orderId,
								]
							),
							"amount" => (string)$paymentSum,
							"type" => "final"
						],
					],
					"total" => [
						"label" => Loc::getMessage("SALE_APPLE_PAY_LINE_ITEM_TOTAL"),
						"amount" => (string)$paymentSum,
						"type" => "final"
					],
					"applePay" => [
						"merchantIdentifier" => $this->merchantIdentifier,
						"supportedNetworks" => $config["supportedNetworks"],
						"merchantCapabilities" => $config["merchantCapabilities"] ?? ["supports3DS"],
					],
					"merchantName" => $config["merchantName"],
					"countryCode" => $config["countryCode"],
					"currencyCode" => $payment->getField("CURRENCY"),
					"requiredBillingContactFields" => $config["requiredBillingContactFields"] ?? [],
					"requiredShippingContactFields" => $config["requiredShippingContactFields"] ?? [],
				],
				"merchantSession" => $merchantSessionResult->getData(),
				"endpoints" => $config["endpoints"]
			]
		];

		$result->setData([
			"replyMessage" => $replyMessage,
			"receivedMessage" => $receivedMessage,
			"data" => $data,
		]);

		return $result;
	}

	/**
	 * @param array $config
	 * @return ServiceResult
	 */
	private function checkConfig(array $config): ServiceResult
	{
		$result = new ServiceResult();

		if (empty($config["merchantDisplayName"]))
		{
			$result->addError(new Main\Error("merchantDisplayName is empty", "merchantDisplayName"));
		}

		if (empty($config["supportedNetworks"]))
		{
			$result->addError(new Main\Error("supportedNetworks is empty", "supportedNetworks"));
		}

		if (empty($config["merchantName"]))
		{
			$result->addError(new Main\Error("merchantName is empty", "merchantName"));
		}

		if (empty($config["countryCode"]))
		{
			$result->addError(new Main\Error("countryCode is empty", "countryCode"));
		}

		if (empty($config["endpoints"]))
		{
			$result->addError(new Main\Error("endpoints is empty", "endpoints"));
		}

		return $result;
	}

	/**
	 * @param $url
	 * @param array $params
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function sendRequest($url, array $params = array()): ServiceResult
	{
		$result = new ServiceResult();

		$httpClient = new Main\Web\HttpClient();
		foreach ($this->getHeaders() as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		$httpClient->setContextOptions($this->getContextOptions($this->applePayCert));

		$postData = null;
		if ($params)
		{
			$postData = static::encode($params);
		}

		Logger::addDebugInfo("ApplePay: request data: ".$postData);

		$response = $httpClient->post($url, $postData);
		if ($response === false)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Main\Error($message, $code));
			}

			return $result;
		}

		Logger::addDebugInfo("ApplePay: response data: ".$response);

		if ($response = static::decode($response))
		{
			$httpStatus = $httpClient->getStatus();
			if ($httpStatus !== self::HTTP_RESPONSE_CODE_OK)
			{
				if (isset($response["statusMessage"]))
				{
					$result->addError(
						new Main\Error(
							Loc::getMessage(
								"SALE_APPLE_PAY_HTTP_STATUS_MESSAGE",
								[
									"#HTTP_STATUS#" => $httpStatus,
									"#STATUS_MESSAGE#" => $response["statusMessage"],
								]
							)
						)
					);
				}
				else
				{
					$result->addError(
						new Main\Error(
							Loc::getMessage(
								"SALE_APPLE_PAY_HTTP_STATUS_CODE",
								[
									"#HTTP_STATUS#" => $httpStatus,
								]
							)
						)
					);
				}
			}

			$result->setData($response);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getHeaders(): array
	{
		return [
			"Content-Type" => "application/json",
		];
	}

	/**
	 * @param $applePayCert
	 * @return array
	 */
	private function getContextOptions($applePayCert): array
	{
		if ($applePayCert)
		{
			$applePayCertPath = $this->createTmpFile($applePayCert);
			if ($applePayCertPath)
			{
				return [
					"ssl" => [
						"local_cert" => $applePayCertPath,
						"local_pk" => $applePayCertPath
					]
				];
			}
		}

		return [];
	}

	/**
	 * @param $data
	 * @return string
	 */
	private function createTmpFile($data): string
	{
		$filePath = \CTempFile::GetFileName(self::FILE_PREFIX.randString());
		CheckDirPath($filePath);
		if ($filePath && ($data !== null))
		{
			file_put_contents($filePath, $data);
		}

		return $filePath ?? "";
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

	/**
	 * @return string
	 */
	private static function getUuid(): string
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
}
