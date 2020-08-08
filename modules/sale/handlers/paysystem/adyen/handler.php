<?php
namespace Sale\Handlers\PaySystem;

use Bitrix\Main,
	Bitrix\Main\Web,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\PaySystem,
	Bitrix\Main\Request,
	Bitrix\Sale,
	Bitrix\Sale\Payment,
	Bitrix\Sale\PriceMaths;

Loc::loadMessages(__FILE__);

/**
 * Class AdyenHandler
 * @package Sale\Handlers\PaySystem
 */
class AdyenHandler
	extends PaySystem\ServiceHandler
	implements PaySystem\IRefundExtended, PaySystem\Domain\Verification\IVerificationable
{
	private const APPLE_PAY_INITIATIVE_CONTEXT = '/bitrix/tools/sale/applepay_gateway.php';

	/** @var string version of Payment Service API */
	private const PAYMENT_API_VERSION = "v51";
	/** @var string version of Checkout Service API */
	private const CHECKOUT_API_VERSION = "v51";

	/** @var string field informs you of the outcome of a payment request */
	private const EVENT_CODE_AUTHORISATION = "AUTHORISATION";

	private const HTTP_RESPONSE_CODE_OK = 200;

	private const RESULT_CODE_AUTHORISED = "Authorised";
	private const RESULT_CODE_ERROR = "Error";
	private const RESULT_CODE_REFUSED = "Refused";

	private const RESPONSE_REFUND_RECEIVED = "[refund-received]";
	private const NOTIFICATION_RESPONSE_ACCEPTED = "[accepted]";

	private const MAX_REQUEST_ATTEMPT = 3;

	public const PAYMENT_METHOD_APPLE_PAY = "apple_pay";

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function initiatePay(Payment $payment, Request $request = null): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		if ($request === null)
		{
			$request = Main\Context::getCurrent()->getRequest();
		}

		if ($action = $request->get("action"))
		{
			if ($this->isActionExists($action))
			{
				$result = $this->$action($payment, $request);
			}
			else
			{
				$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_ERROR_ACTION", [
					"#ACTION#" => $action,
				])));
			}
		}
		else
		{
			$result = $this->initiatePayInternal($payment);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function initiatePayInternal(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$params = array(
			"PAYMENT_ID" => $payment->getId(),
			"PAYSYSTEM_ID" => $this->service->getField("ID"),
			"MERCHANT_ID" => $this->getBusinessValue($payment, "APPLE_PAY_MERCHANT_ID"),
			"ORDER_ID" => $payment->getOrder()->getId(),
			"TOTAL_SUM" => PriceMaths::roundPrecision($payment->getSum()),
			"CURRENCY" => $payment->getField("CURRENCY"),
			"MAKE_PAYMENT_ACTION" => "makePaymentAction",
		);

		$modeParams = $this->getModeParams($payment);
		if (isset($modeParams["ERRORS"]))
		{
			$result->addErrors($modeParams["ERRORS"]);
			return $result;
		}

		$params = array_merge($params, $modeParams);
		$this->setExtraParams($params);

		$template = "template_".$this->service->getField("PS_MODE");
		$showTemplateResult = $this->showTemplate($payment, $template);
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
	 * Checks whether component implements selected action.
	 *
	 * @param $action
	 * @return bool
	 */
	private function isActionExists($action): bool
	{
		return is_callable([$this, $action]);
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList(): array
	{
		return [
			self::PAYMENT_METHOD_APPLE_PAY => "Apple Pay",
		];
	}

	/**
	 * @param Request $request
	 * @param int $paySystemId
	 * @return bool
	 */
	public static function isMyResponse(Request $request, $paySystemId): bool
	{
		$notificationItem = static::getNotificationItem();
		return isset($notificationItem["additionalData"]["metadata.paySystemId"])
			&& ((int)$notificationItem["additionalData"]["metadata.paySystemId"] === (int)$paySystemId);
	}

	/**
	 * @return array|bool
	 */
	private static function getNotificationItem()
	{
		$inputStream = static::readFromStream();
		$data = static::decode($inputStream);
		if (isset($data["notificationItems"][0]["NotificationRequestItem"])
			&& is_array($data["notificationItems"][0]["NotificationRequestItem"])
		)
		{
			return $data["notificationItems"][0]["NotificationRequestItem"];
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList(): array
	{
		return ["USD", "EUR"];
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function processRequest(Payment $payment, Request $request): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$notificationItem = static::getNotificationItem();
		if ($notificationItem)
		{
			$secretKey = $this->getBusinessValue($payment, "ADYEN_HMAC_KEY");
			if ($secretKey
				&& isset($notificationItem["additionalData"]["hmacSignature"])
				&& !$this->isSignatureCorrect($notificationItem, $secretKey)
			)
			{
				$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_ERROR_CHECK_SUM")));
				return $result;
			}

			if (isset($notificationItem["eventCode"])
				&& $notificationItem["eventCode"] === self::EVENT_CODE_AUTHORISATION
				&& filter_var($notificationItem["success"], FILTER_VALIDATE_BOOLEAN)
			)
			{
				$description = Loc::getMessage("SALE_HPS_ADYEN_TRANSACTION").$notificationItem["pspReference"];
				$fields = [
					"PS_INVOICE_ID" => $notificationItem["pspReference"],
					"PS_STATUS_CODE" => $notificationItem["eventCode"],
					"PS_STATUS_DESCRIPTION" => $description,
					"PS_SUM" => $notificationItem["amount"]["value"] / 100,
					"PS_STATUS" => "N",
					"PS_CURRENCY" => $notificationItem["amount"]["currency"],
					"PS_RESPONSE_DATE" => new Main\Type\DateTime()
				];

				if ($this->isSumCorrect($payment, $notificationItem["amount"]["value"] / 100))
				{
					$fields["PS_STATUS"] = "Y";
					PaySystem\Logger::addDebugInfo(
						"Adyen: PS_CHANGE_STATUS_PAY=".$this->getBusinessValue($payment, "PS_CHANGE_STATUS_PAY")
					);

					if ($this->getBusinessValue($payment, "PS_CHANGE_STATUS_PAY") === "Y")
					{
						$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
					}
				}
				else
				{
					$error = Loc::getMessage("SALE_HPS_ADYEN_ERROR_SUM");
					$fields["PS_STATUS_DESCRIPTION"] .= " ".$error;
					$result->addError(new Main\Error($error));
				}

				$result->setPsData($fields);
			}
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_ERROR_QUERY")));
		}

		return $result;
	}

	/**
	 * @param $notificationItem
	 * @param $secretKey
	 * @return bool
	 */
	private function isSignatureCorrect($notificationItem, $secretKey): bool
	{
		$data = $this->getDataToSign($notificationItem);

		$result = hash_hmac("sha256", $data, pack("H*", $secretKey), true);
		if ($result === false)
		{
			return false;
		}

		$result = base64_encode($result);

		return $result === $notificationItem["additionalData"]["hmacSignature"];
	}

	/**
	 * @param $notificationItem
	 * @return string
	 */
	private function getDataToSign($notificationItem): string
	{
		$data = [
			$notificationItem["pspReference"] ?? "",
			$notificationItem["originalReference"] ?? "",
			$notificationItem["merchantAccountCode"] ?? "",
			$notificationItem["merchantReference"] ?? "",
			$notificationItem["amount"]["value"] ?? "",
			$notificationItem["amount"]["currency"] ?? "",
			$notificationItem["eventCode"] ?? "",
			$notificationItem["success"] ?? "",
		];

		// escape backslash and colon
		$data = array_map(static function($value) {
			return str_replace(":", "\\:", str_replace("\\", "\\\\", $value));
		}, $data);
		$data = Main\Text\Encoding::convertEncoding($data, LANG_CHARSET, "UTF-8");

		return implode(":", $data);
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
			"Adyen: adyenSum=".PriceMaths::roundPrecision($sum)."; paymentSum=".PriceMaths::roundPrecision($payment->getSum())
		);

		return PriceMaths::roundPrecision($sum) === PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		$notificationItem = static::getNotificationItem();
		return $notificationItem["merchantReference"] ?? false;
	}

	/**
	 * @param Payment $payment
	 * @param string $channel
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function getPaymentMethods(Payment $payment, string $channel): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$headers = $this->getHeaders($payment);
		$requestParameters = [
			"merchantAccount" => $this->getBusinessValue($payment, "ADYEN_MERCHANT_ID"),
			"amount" => $this->getAmount($payment),
			"channel" => $channel,
		];

		$url = $this->getUrl($payment, "paymentMethod");
		if (!$url)
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_ERROR_URL", [
				"ACTION" => "paymentMethod",
			])));
			return $result;
		}

		$sendResult = $this->send($url, $requestParameters, $headers);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$paymentMethods = [];
		$response = $sendResult->getData();
		if ($response && isset($response["groups"]))
		{
			foreach ($response["groups"] as $group)
			{
				if (isset($group["types"]))
				{
					$paymentMethods[] = $group["types"];
				}
			}
		}

		$paymentMethods = array_merge(...$paymentMethods);
		$result->setData($paymentMethods);
		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @noinspection PhpUnused
	 */
	private function getApplePayWebSessionAction(Payment $payment, Request $request): PaySystem\ServiceResult
	{
		$applePay = new PaySystem\ApplePay(
			$this->getBusinessValue($payment, "APPLE_PAY_MERCHANT_ID"),
			$this->getBusinessValue($payment, "APPLE_PAY_MERCHANT_DISPLAY_NAME"),
			$this->getBusinessValue($payment, "APPLE_PAY_DOMAIN"),
			$this->getBusinessValue($payment, "APPLE_PAY_CERT_FILE")
		);

		return $applePay->getWebSession($request->get("url"));
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 * @noinspection PhpUnused
	 */
	public function makePaymentAction(Payment $payment, Request $request): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$headers = $this->getHeaders($payment);
		$requestParameters = [
			"amount" => $this->getAmount($payment),
			"reference" => $payment->getId(),
			"merchantAccount" => $this->getBusinessValue($payment, "ADYEN_MERCHANT_ID"),
			"paymentMethod" => $this->getRequestPaymentMethod($request),
			"metadata" => [
				"paySystemId" => $this->service->getField("ID")
			]
		];

		$url = $this->getUrl($payment, "payment");
		if (!$url)
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_ERROR_URL", [
				"ACTION" => "payment",
			])));
			return $result;
		}

		$sendResult = $this->send($url, $requestParameters, $headers);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$response = $sendResult->getData();
		$result->setPsData(array("PS_INVOICE_ID" => $response["pspReference"]));

		$result->setData($response);
		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function getIMessagePaymentAction(Payment $payment, Request $request): PaySystem\ServiceResult
	{
		$paymentMethodResult = $this->getPaymentMethods($payment, "iOS");
		if (!$paymentMethodResult->isSuccess())
		{
			$result = new PaySystem\ServiceResult();
			$result->addErrors($paymentMethodResult->getErrors());
			return $result;
		}

		$applePay = new PaySystem\ApplePay(
			$this->getBusinessValue($payment, "APPLE_PAY_MERCHANT_ID"),
			$this->getBusinessValue($payment, "APPLE_PAY_MERCHANT_DISPLAY_NAME"),
			$this->getBusinessValue($payment, "APPLE_PAY_DOMAIN"),
			$this->getBusinessValue($payment, "APPLE_PAY_CERT_FILE")
		);

		$host = $request->isHttps() ? 'https://' : 'http://';
		$gateWayUrl = new Main\Web\Uri($host.$request->getHttpHost().static::APPLE_PAY_INITIATIVE_CONTEXT);
		$gateWayUrl->addParams([
			'PAYMENT_ID' => $payment->getId(),
			'PAYSYSTEM_ID' => $this->service->getField('ID'),
			'action' => 'makePaymentAction'
		]);

		$applePay->setInitiativeContext($gateWayUrl->getLocator());

		$config = [
			"merchantDisplayName" => $this->getBusinessValue($payment, "APPLE_PAY_MERCHANT_DISPLAY_NAME"),
			"supportedNetworks" => $paymentMethodResult->getData(),
			"merchantName" => $this->getBusinessValue($payment, "APPLE_PAY_MERCHANT_DISPLAY_NAME"),
			"countryCode" => mb_strtoupper($this->getBusinessValue($payment, "APPLE_PAY_COUNTRY_CODE")),
			"endpoints" => [
				"paymentGatewayUrl" => $gateWayUrl->getLocator(),
			]
		];

		return $applePay->getIMessagePayment($payment, $config);
	}

	/**
	 * @param Request $request
	 * @return array
	 */
	private function getRequestPaymentMethod(Request $request): array
	{
		$result = [];

		$psMode = $this->service->getField("PS_MODE");
		if ($psMode === self::PAYMENT_METHOD_APPLE_PAY)
		{
			$result = [
				"type" => "applepay",
				"applepay.token" => static::decode($request->get("paymentData")),
			];
		}

		return $result;
	}

	/**
	 * @param Payment|null $payment
	 * @param string $action
	 * @return mixed|string
	 */
	protected function getUrl(Payment $payment = null, $action)
	{
		$url = parent::getUrl($payment, $action);
		if ($payment !== null && !$this->isTestMode($payment))
		{
			$liveEndpointUrlPrefix = $this->getBusinessValue($payment, "ADYEN_LIVE_URL_PREFIX");
			if ($liveEndpointUrlPrefix)
			{
				$url = str_replace("#LIVE_URL_PREFIX#", $liveEndpointUrlPrefix, $url);
			}
			else
			{
				$url = "";
			}
		}

		return $url;
	}

	/**
	 * @return array
	 */
	protected function getUrlList(): array
	{
		$testCheckoutUrl = "https://checkout-test.adyen.com/".self::CHECKOUT_API_VERSION;
		$activeCheckoutUrl = "https://#LIVE_URL_PREFIX#-checkout-live.adyenpayments.com/checkout/".self::CHECKOUT_API_VERSION;

		$testPaymentUrl = "https://pal-test.adyen.com/pal/servlet/Payment/".self::PAYMENT_API_VERSION;
		$activePaymentUrl = "https://#LIVE_URL_PREFIX#-pal-live.adyenpayments.com/pal/servlet/Payment/".self::PAYMENT_API_VERSION;

		return [
			"paymentMethod" => [
				self::TEST_URL => $testCheckoutUrl."/paymentMethods",
				self::ACTIVE_URL => $activeCheckoutUrl."/paymentMethods"
			],
			"payment" => [
				self::TEST_URL => $testCheckoutUrl."/payments",
				self::ACTIVE_URL => $activeCheckoutUrl."/payments"
			],
			"refund" => [
				self::TEST_URL => $testPaymentUrl."/refund",
				self::ACTIVE_URL => $activePaymentUrl."/refund"
			],
		];
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null): bool
	{
		return ($this->getBusinessValue($payment, "PS_IS_TEST") === "Y");
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	private function getHeaders(Payment $payment): array
	{
		return [
			"Content-Type" => "application/json",
			"x-API-key" => $this->getBusinessValue($payment, "ADYEN_X_API_KEY"),
			"Idempotency-Key" => $this->getIdempotenceKey(),
		];
	}

	/**
	 * @return string
	 */
	private function getIdempotenceKey(): string
	{
		return sprintf("%04x%04x-%04x-%04x-%04x-%04x%04x%04x",
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	/**
	 * @param Payment $payment
	 * @param $refundableSum
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function refund(Payment $payment, $refundableSum): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$headers = $this->getHeaders($payment);
		$requestParameters = [
			"originalReference" => $payment->getField("PS_INVOICE_ID"),
			"modificationAmount" => [
				"value" => PriceMaths::roundPrecision($refundableSum * 100),
				"currency" => $payment->getField("CURRENCY"),
			],
			"reference" => $payment->getId(),
			"merchantAccount" => $this->getBusinessValue($payment, "ADYEN_MERCHANT_ID"),
		];

		$url = $this->getUrl($payment, "refund");
		if ($url)
		{
			$sendResult = $this->send($url, $requestParameters, $headers);
			if ($sendResult->isSuccess())
			{
				$response = $sendResult->getData();
				if ($response["response"] === self::RESPONSE_REFUND_RECEIVED)
				{
					$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
				}
			}
			else
			{
				$result->addErrors($sendResult->getErrors());
			}
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_ERROR_URL", [
				"ACTION" => "refund",
			])));
		}

		if (!$result->isSuccess())
		{
			PaySystem\Logger::addError("Adyen: refund: ".implode("\n", $result->getErrorMessages()));
		}

		return $result;
	}

	/**
	 * @param $url
	 * @param array $params
	 * @param array $headers
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function send($url, array $params, array $headers = array()): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$httpClient = new Web\HttpClient();
		foreach ($headers as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		$postData = null;
		if ($params)
		{
			$postData = static::encode($params);
		}

		PaySystem\Logger::addDebugInfo("Adyen: request data: ".$postData);

		$response = $httpClient->post($url, $postData);
		if ($response === false)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code =>$message)
			{
				$result->addError(new Main\Error($message, $code));
			}

			return $result;
		}

		PaySystem\Logger::addDebugInfo("Adyen: response data: ".$response);

		static $attempt = 0;
		$responseHeader = $httpClient->getHeaders();
		if ($responseHeader->get("Transient-Error") && $attempt < self::MAX_REQUEST_ATTEMPT)
		{
			$second = 2 ** $attempt;
			sleep($second);

			$attempt++;
			$result = $this->send($url, $headers, $params);
		}

		$response = static::decode($response);

		$httpStatus = $httpClient->getStatus();
		if ($httpStatus === self::HTTP_RESPONSE_CODE_OK)
		{
			$result->setData($response);

			if (isset($response["resultCode"]) && $response["resultCode"] !== self::RESULT_CODE_AUTHORISED)
			{
				if ($response["resultCode"] === self::RESULT_CODE_ERROR || $response["resultCode"] === self::RESULT_CODE_REFUSED)
				{
					$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_SEND_REFUSAL_REASON_ERROR", [
						"#RESULT_CODE#" => $response["resultCode"],
						"#REFUSAL_REASON#" => $response["refusalReason"],
					])));
				}
				else
				{
					$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_SEND_RESULT_CODE_ERROR", [
						"#RESULT_CODE#" => $response["resultCode"],
					])));
				}
			}
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_ADYEN_HTTP_STATUS", [
				"#HTTP_STATUS#" => $httpStatus
			]), $httpStatus));

			if (isset($response["errorCode"], $response["message"]))
			{
				$result->addError(new Main\Error($response["message"], $response["errorCode"]));
			}
		}

		return $result;
	}

	/**
	 * @return bool
	 */
	public function isRefundableExtended(): bool
	{
		return true;
	}

	/**
	 * @param Payment $payment
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function getModeParams(Payment $payment): array
	{
		$result = [];

		$psMode = $this->service->getField("PS_MODE");
		if ($psMode === self::PAYMENT_METHOD_APPLE_PAY)
		{
			$result = $this->getApplePayParams($payment);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function getApplePayParams(Payment $payment): array
	{
		$paymentMethodResult = $this->getPaymentMethods($payment, "Web");
		if (!$paymentMethodResult->isSuccess())
		{
			return [
				"ERRORS" => $paymentMethodResult->getErrors(),
			];
		}

		$paymentMethodData = $paymentMethodResult->getData();
		return [
			"SUPPORTED_METHOD" => "https://apple.com/apple-pay",
			"DISPLAY_NAME" => $this->getBusinessValue($payment, "APPLE_PAY_MERCHANT_DISPLAY_NAME"),
			"DOMAIN_NAME" => $this->getBusinessValue($payment, "APPLE_PAY_DOMAIN"),
			"COUNTRY_CODE" => mb_strtoupper($this->getBusinessValue($payment, "APPLE_PAY_COUNTRY_CODE")),
			"GET_SESSION_ACTION" => "getApplePayWebSessionAction",
			"MERCHANT_CAPABILITIES" => ["supports3DS"],
			"SUPPORTED_NETWORKS" => $paymentMethodData,
		];
	}

	/**
	 * @param Payment $payment
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	private function getAmount(Payment $payment): array
	{
		return [
			"value" => PriceMaths::roundPrecision($payment->getSum() * 100),
			"currency" => $payment->getField("CURRENCY"),
		];
	}

	/**
	 * @return bool|string
	 */
	private static function readFromStream()
	{
		return file_get_contents("php://input");
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
	 * @param PaySystem\ServiceResult $result
	 * @param Request $request
	 * @return mixed|void
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function sendResponse(PaySystem\ServiceResult $result, Request $request)
	{
		if ($result->isSuccess())
		{
			/** @noinspection PhpVariableNamingConventionInspection */
			global $APPLICATION;
			$APPLICATION->RestartBuffer();

			$result = static::encode([
				"notificationResponse" => self::NOTIFICATION_RESPONSE_ACCEPTED
			]);
			PaySystem\Logger::addDebugInfo("Adyen: response: ".$result);

			echo $result;
		}
	}

	/**
	 * @inheritDoc
	 */
	public static function getModeList(): array
	{
		return [
			self::PAYMENT_METHOD_APPLE_PAY
		];
	}
}
