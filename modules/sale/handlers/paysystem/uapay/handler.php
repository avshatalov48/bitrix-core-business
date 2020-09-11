<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Request,
	Bitrix\Sale\Payment,
	Bitrix\Sale\PaySystem,
	Bitrix\Main\Web\HttpClient,
	Bitrix\Sale\PaymentCollection,
	Bitrix\Sale\PriceMaths;

Loc::loadMessages(__FILE__);

/**
 * Class UaPayHandler
 * @package Sale\Handlers\PaySystem
 */
class UaPayHandler
	extends PaySystem\ServiceHandler
	implements PaySystem\IRefund
{
	const PAYMENT_STATUS_FINISHED = "FINISHED";

	const INVOICE_ID_DELIMITER = "#";

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$result = new PaySystem\ServiceResult();

		$invoiceResult = $this->createInvoice($payment);
		if ($invoiceResult->isSuccess())
		{
			$result->setPsData($invoiceResult->getPsData());

			$invoiceData = $invoiceResult->getData();
			$params = [
				"CURRENCY" => $payment->getField("CURRENCY"),
				"SUM" => PriceMaths::roundPrecision($payment->getSum()),
				"URL" => $invoiceData["paymentPageUrl"],
			];
			$this->setExtraParams($params);

			$showTemplateResult = $this->showTemplate($payment, "template");
			if ($showTemplateResult->isSuccess())
			{
				$result->setTemplate($showTemplateResult->getTemplate());
			}
			else
			{
				$result->addErrors($showTemplateResult->getErrors());
			}

			if ($params["URL"])
			{
				$result->setPaymentUrl($params["URL"]);
			}
		}
		else
		{
			$result->addErrors($invoiceResult->getErrors());
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
	private function createSession(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();

		$clientId = $this->getBusinessValue($payment, "UAPAY_CLIENT_ID");
		if (!$clientId)
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_CLIENT_ID")));
			return $result;
		}

		$url = $this->getUrl($payment, "sessionCreate");
		$requestParams = [
			"params" => [
				"clientId" => $this->getBusinessValue($payment, "UAPAY_CLIENT_ID")
			]
		];

		$sendResult = $this->send($payment, $url, $requestParams);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$sendData = $sendResult->getData();

		$validationResult = $this->validationResponse($payment, $sendData);
		if (!$validationResult->isSuccess())
		{
			$result->addErrors($validationResult->getErrors());
			return $result;
		}

		$payloadData = self::getPayload($sendData["data"]["token"]);
		if (!$payloadData)
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_PARSE_JWT")));
			return $result;
		}

		PaySystem\Logger::addDebugInfo(__CLASS__.": createSession payload: ".self::encode($payloadData));

		$result->setData(["id" => $payloadData["id"]]);
		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	private function createInvoice(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();

		$sessionResult = $this->createSession($payment);
		if (!$sessionResult->isSuccess())
		{
			$result->addErrors($sessionResult->getErrors());
			return $result;
		}

		$sessionData = $sessionResult->getData();

		$url = $this->getUrl($payment, "invoicesCreate");
		$extraInfo = [
			"paySystemId" => $this->service->getField("ID"),
		];
		$requestParams = [
			"params" => [
				"sessionId" => $sessionData["id"],
				"systemType" => "ECOM"
			],
			"data" => [
				"externalId" => $payment->getField("ACCOUNT_NUMBER"),
				"reusability" => false,
				"type" => "PAY",
				"callbackUrl" => $this->getBusinessValue($payment, "UAPAY_CALLBACK_URL"),
				"description" => $this->getPaymentDescription($payment),
				"amount" => (int)PriceMaths::roundPrecision($payment->getSum() * 100),
				"redirectUrl" => $this->getRedirectUrl($payment),
				"extraInfo" => self::encode($extraInfo),
			],
		];

		if ($userEmail = $payment->getOrder()->getPropertyCollection()->getUserEmail())
		{
			$requestParams["data"]["email"] = $userEmail->getValue();
		}

		$sendResult = $this->send($payment, $url, $requestParams);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$sendData = $sendResult->getData();

		$validationResult = $this->validationResponse($payment, $sendData);
		if (!$validationResult->isSuccess())
		{
			$result->addErrors($validationResult->getErrors());
			return $result;
		}

		$payloadData = self::getPayload($sendData["data"]["token"]);
		if (!$payloadData)
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_PARSE_JWT")));
			return $result;
		}

		PaySystem\Logger::addDebugInfo(__CLASS__.": createInvoice payload: ".self::encode($payloadData));

		$result->setPsData(["PS_INVOICE_ID" => $payloadData["id"]]);
		$result->setData($payloadData);
		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getRedirectUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'UAPAY_REDIRECT_URL') ?: $this->service->getContext()->getUrl();
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
	public function refund(Payment $payment, $refundableSum)
	{
		$result = new PaySystem\ServiceResult();

		$sessionResult = $this->createSession($payment);
		if ($sessionResult->isSuccess())
		{
			$sessionData = $sessionResult->getData();

			$psInvoiceId = $payment->getField("PS_INVOICE_ID");
			$psInvoiceIdList = explode(self::INVOICE_ID_DELIMITER, $psInvoiceId);
			if (count($psInvoiceIdList) == 2)
			{
				$url = $this->getUrl($payment, "paymentReverse");
				$requestParams = [
					"params" => [
						"sessionId" => $sessionData["id"],
						"invoiceId" => $psInvoiceIdList[0],
						"paymentId" => $psInvoiceIdList[1],
					],
				];

				$sendResult = $this->send($payment, $url, $requestParams);
				if (!$sendResult->isSuccess())
				{
					$result->addErrors($sendResult->getErrors());
				}

				if ($sendResult->isSuccess())
				{
					$sendData = $sendResult->getData();
					$validationResult = $this->validationResponse($payment, $sendData);
					if (!$validationResult->isSuccess())
					{
						$result->addErrors($validationResult->getErrors());
					}

					if ($validationResult->isSuccess())
					{
						$payloadData = self::getPayload($sendData["data"]["token"]);
						if ($payloadData)
						{
							PaySystem\Logger::addDebugInfo(__CLASS__.": refund payload: ".self::encode($payloadData));
						}
						else
						{
							$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_PARSE_JWT")));
						}
					}
				}
			}
			else
			{
				$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_PAYMENT_ID")));
			}
		}
		else
		{
			$result->addErrors($sessionResult->getErrors());
		}

		if ($result->isSuccess())
		{
			$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
		}
		else
		{
			PaySystem\Logger::addError(__CLASS__.": refund: ".join("\n", $result->getErrorMessages()));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param $url
	 * @param array $params
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function send(Payment $payment, $url, array $params)
	{
		$result = new PaySystem\ServiceResult();
		$httpClient = new HttpClient();

		$headers = $this->getHeaders();
		foreach ($headers as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		$params["iat"] = time();
		$params["token"] = $this->getJwt($payment, $params);
		$postData = self::encode($params);

		PaySystem\Logger::addDebugInfo(__CLASS__.": request data: ".$postData);

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

		PaySystem\Logger::addDebugInfo(__CLASS__.": response data: ".$response);

		$httpStatus = $httpClient->getStatus();
		if ($httpStatus !== 200)
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_HTTP_STATUS", [
				"#STATUS_CODE#" => $httpStatus
			])));
			return $result;
		}

		$response = self::decode($response);
		if ($response)
		{
			$result->setData($response);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param $response
	 * @return PaySystem\ServiceResult
	 */
	private function validationResponse(Payment $payment, $response)
	{
		$result = new PaySystem\ServiceResult();

		if (!is_array($response))
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_RESPONSE")));
			return $result;
		}

		if (isset($response["status"]) && !$response["status"])
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_RESPONSE_STATUS")));
			return $result;
		}

		if (isset($response["error"]))
		{
			$result->addError(new Main\Error($response["error"]["message"], $response["error"]["code"]));
			return $result;
		}

		if (!$this->isTokenCorrect($response["data"]["token"], $payment))
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_CHECK_SUM")));
			return $result;
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getHeaders()
	{
		$headers = [
			"Content-Type" => "application/json",
		];

		return $headers;
	}

	/**
	 * @param $token
	 * @return mixed|null
	 */
	private static function getPayload($token)
	{
		$tokenPathList = explode(".", $token);
		if (count($tokenPathList) != 3)
		{
			return null;
		}

		$payload = self::decode(self::urlSafeBase64Decode($tokenPathList[1]));
		return $payload;
	}

	/**
	 * @param $token
	 * @param Payment $payment
	 * @return bool
	 */
	private function isTokenCorrect($token, Payment $payment)
	{
		$signKey = (string)$this->getBusinessValue($payment, "UAPAY_SIGN_KEY");

		$tokenPathList = explode(".", $token);
		if (count($tokenPathList) != 3)
		{
			return false;
		}

		list($headBase64, $bodyBase64, $cryptoBase64) = $tokenPathList;
		$signature = self::urlSafeBase64Decode($cryptoBase64);

		$hash = hash_hmac("sha256", $headBase64.".".$bodyBase64, $signKey, true);
		if ($hash)
		{
			return hash_equals($signature, $hash);
		}

		return false;
	}

	/**
	 * @param Payment $payment
	 * @param array $payload
	 * @return string
	 * @throws Main\ArgumentException
	 */
	private function getJwt(Payment $payment, array $payload)
	{
		$signKey = (string)$this->getBusinessValue($payment, "UAPAY_SIGN_KEY");

		$search = ["+", "/", "="];
		$replace = ["-", "_", ""];

		$header = self::encode(["alg" => "HS256", "typ" => "JWT"]);
		$base64UrlHeader = str_replace($search, $replace, base64_encode($header));

		$payload = self::encode($payload);
		$base64UrlPayload = str_replace($search, $replace, base64_encode($payload));

		$signature = hash_hmac("sha256", $base64UrlHeader.".".$base64UrlPayload, $signKey, true);
		$base64UrlSignature = str_replace($search, $replace, base64_encode($signature));
		$jwt = $base64UrlHeader.".".$base64UrlPayload.".".$base64UrlSignature;
		return $jwt;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return ["UAH"];
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
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$inputStream = self::readFromStream();
		$data = self::decode($inputStream);
		if ($payloadData = self::getPayload($data["token"]))
		{
			PaySystem\Logger::addDebugInfo(__CLASS__.": processRequest payloadData: ".self::encode($payloadData));
			if ($this->isTokenCorrect($data["token"], $payment))
			{
				$paymentId = $payloadData["paymentId"] ?? $payloadData["id"];
				if ($payloadData["paymentStatus"] === self::PAYMENT_STATUS_FINISHED && $paymentId)
				{
					$description = Loc::getMessage("SALE_HPS_UAPAY_TRANSACTION", [
						"#ID#" => $payloadData["id"],
						"#PAYMENT_NUMBER#" => $payloadData["paymentNumber"]
					]);
					$invoiceId = $payloadData["invoiceId"] ?? $payloadData["orderId"];
					$fields = array(
						"PS_INVOICE_ID" => $invoiceId.self::INVOICE_ID_DELIMITER.$paymentId,
						"PS_STATUS_CODE" => $payloadData["paymentStatus"],
						"PS_STATUS_DESCRIPTION" => $description,
						"PS_SUM" => $payloadData["amount"] / 100,
						"PS_STATUS" => "N",
						"PS_RESPONSE_DATE" => new Main\Type\DateTime()
					);

					if ($this->isSumCorrect($payment, $payloadData["amount"] / 100))
					{
						$fields["PS_STATUS"] = "Y";

						PaySystem\Logger::addDebugInfo(
							__CLASS__.": PS_CHANGE_STATUS_PAY=".$this->getBusinessValue($payment, "PS_CHANGE_STATUS_PAY")
						);

						if ($this->getBusinessValue($payment, "PS_CHANGE_STATUS_PAY") === "Y")
						{
							$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
						}
					}
					else
					{
						$error = Loc::getMessage("SALE_HPS_UAPAY_ERROR_SUM");
						$fields["PS_STATUS_DESCRIPTION"] .= " ".$error;
						$result->addError(new Main\Error($error));
					}

					$result->setPsData($fields);
				}
			}
			else
			{
				$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_CHECK_SUM")));
			}
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage("SALE_HPS_UAPAY_ERROR_PARSE_JWT")));
		}

		if (!$result->isSuccess())
		{
			$error = __CLASS__.": processRequest: ".join("\n", $result->getErrorMessages());
			PaySystem\Logger::addError($error);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param $amount
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function isSumCorrect(Payment $payment, $amount)
	{
		PaySystem\Logger::addDebugInfo(
			__CLASS__.": sum=".PriceMaths::roundPrecision($amount)."; paymentSum=".PriceMaths::roundPrecision($payment->getSum())
		);

		return PriceMaths::roundPrecision($amount) === PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Request $request
	 * @param int $paySystemId
	 * @return bool
	 */
	public static function isMyResponse(Request $request, $paySystemId)
	{
		$inputStream = self::readFromStream();
		if ($inputStream)
		{
			$data = self::decode($inputStream);
			if ($data !== false && isset($data["token"]))
			{
				$payloadData = self::getPayload($data["token"]);
				if (!$payloadData)
				{
					return false;
				}

				if (isset($payloadData["extraInfo"]) && !is_array($payloadData["extraInfo"]))
				{
					$payloadData["extraInfo"] = self::decode($payloadData["extraInfo"]);
				}

				if (isset($payloadData["extraInfo"]["paySystemId"]) && ((int)$payloadData["extraInfo"]["paySystemId"] === (int)$paySystemId))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		$inputStream = self::readFromStream();
		$data = self::decode($inputStream);
		$payloadData = self::getPayload($data["token"]);
		if (!$payloadData)
		{
			return false;
		}

		if (isset($payloadData["externalId"]))
		{
			return $payloadData["externalId"];
		}

		return false;
	}

	/**
	 * @return array
	 */
	protected function getUrlList()
	{
		$testUrl = "https://api.demo.uapay.ua/api/";
		$activeUrl = "https://api.uapay.ua/api/";

		return [
			"sessionCreate" => [
				self::TEST_URL => $testUrl."sessions/create",
				self::ACTIVE_URL => $activeUrl."sessions/create",
			],
			"invoicesCreate" => [
				self::TEST_URL => $testUrl."invoicer/invoices/create",
				self::ACTIVE_URL => $activeUrl."invoicer/invoices/create",
			],
			"paymentReverse" => [
				self::TEST_URL => $testUrl."invoicer/payments/reverse",
				self::ACTIVE_URL => $activeUrl."invoicer/payments/reverse",
			]
		];
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return ($this->getBusinessValue($payment, "UAPAY_TEST_MODE") === "Y");
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	private function getPaymentDescription(Payment $payment)
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();
		$userEmail = $order->getPropertyCollection()->getUserEmail();

		$description =  str_replace(
			[
				"#PAYMENT_NUMBER#",
				"#ORDER_NUMBER#",
				"#PAYMENT_ID#",
				"#ORDER_ID#",
				"#USER_EMAIL#"
			],
			[
				$payment->getField("ACCOUNT_NUMBER"),
				$order->getField("ACCOUNT_NUMBER"),
				$payment->getId(),
				$order->getId(),
				($userEmail) ? $userEmail->getValue() : ""
			],
			$this->getBusinessValue($payment, "UAPAY_INVOICE_DESCRIPTION")
		);

		return $description;
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
	 * @param $input
	 * @return bool|string
	 */
	private static function urlSafeBase64Decode($input)
	{
		$remainder = mb_strlen($input) % 4;
		if ($remainder)
		{
			$padLength = 4 - $remainder;
			$input .= str_repeat("=", $padLength);
		}
		return base64_decode(strtr($input, "-_", "+/"));
	}

	/**
	 * @return bool|string
	 */
	private static function readFromStream()
	{
		return file_get_contents("php://input");
	}
}