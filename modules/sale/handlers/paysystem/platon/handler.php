<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Main\Entity\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PaySystem\Logger;
use Bitrix\Sale\PaySystem\ServiceResult;
use Bitrix\Sale\PriceMaths;

class PlatonHandler extends PaySystem\ServiceHandler implements PaySystem\IRefund
{
	private const PAYMENT_METHOD_CODE = 'CC';
	private const REFUND_ACTION = 'CREDITVOID';

	private const ANALYTICS_TAG = 'api_bitrix24ua';

	/*
	 * order: payment id
	 * ext1: the name of the handler
	 * ext2: pay system service ID
	 * ext3: analytics tag
	 */
	private const CALLBACK_ORDER_PARAM = 'order';
	private const CALLBACK_EXT1_PARAM = 'ext1';
	private const CALLBACK_EXT2_PARAM = 'ext2';

	private const TRANSACTION_STATUS_SALE = 'SALE';
	private const TRANSACTION_STATUS_REFUND = 'REFUND';

	private const REFUND_STATUS_ACCEPTED = 'ACCEPTED';
	private const REFUND_STATUS_ERROR = 'ERROR';

	private const PS_MODE_BANK_CARD = 'bank_card';
	private const PS_MODE_GOOGLE_PAY = 'google_pay';
	private const PS_MODE_APPLE_PAY = 'apple_pay';
	private const PS_MODE_PRIVAT24 = 'privat24';

	/**
	 * @inheritDoc
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$result = new PaySystem\ServiceResult();

		$params = [
			'CURRENCY' => $payment->getField('CURRENCY'),
			'SUM' => PriceMaths::roundPrecision($payment->getSum()),
			'FORM_ACTION_URL' => $this->getUrl($payment, "formActionUrl"),
			'FORM_DATA' => $this->getFormData($payment),
		];
		$this->setExtraParams($params);

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
	 * forms the data that will be sent with a POST request via a form
	 * @param Payment $payment
	 * @return array
	 */
	private function getFormData(Payment $payment): array
	{
		$apiKey = $this->getBusinessValue($payment, 'PLATON_API_KEY');
		$paymentMethodCode = self::PAYMENT_METHOD_CODE;
		$paymentData = $this->getPaymentData($payment);
		$encodedPaymentData = $this->encodePaymentData($paymentData);
		$successUrl = $this->getSuccessUrl($payment);
		$password = $this->getBusinessValue($payment, 'PLATON_PASSWORD');

		$sign = $this->getPaymentFormSignature(
			$apiKey,
			$paymentMethodCode,
			$encodedPaymentData,
			$successUrl,
			$password
		);

		$paymentNumber = $payment->getField('ACCOUNT_NUMBER');
		$paySystemId = $this->service->getField('ID');

		$formData = [
			'KEY' => $apiKey,
			'PAYMENT' => $paymentMethodCode,
			'DATA' => $encodedPaymentData,
			'URL' => $successUrl,
			'REQ_TOKEN' => 'N',
			'SIGN' => $sign,
			'ORDER' => $paymentNumber,
			'EXT_1' => 'PLATON',
			'EXT_2' => $paySystemId,
			'EXT_3' => self::ANALYTICS_TAG,
		];

		$email = $this->getUserEmailValue($payment);
		if ($email)
		{
			$formData['EMAIL'] = $email;
		}

		return $formData;
	}

	/**
	 * encodes the payment data as per the documentation
	 * @param array $paymentData
	 * @return string
	 */
	private function encodePaymentData(array $paymentData): string
	{
		return base64_encode(Json::encode($paymentData, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * @param $data
	 * @return false|mixed
	 */
	private function decode($data)
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
	 * @param Payment $payment
	 * @return array
	 */
	private function getPaymentData(Payment $payment)
	{
		$formattedPaymentSum = $this->getFormattedPaymentSum($payment);
		$paymentDescription = $this->getPaymentDescription($payment);

		return [
			'amount' => $formattedPaymentSum,
			'currency' => $payment->getField("CURRENCY"),
			'description' => $paymentDescription,
			'recurring' => 'N',
		];
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getFormattedPaymentSum(Payment $payment)
	{
		$paymentSum = PriceMaths::roundPrecision($payment->getSum());
		return $this->formatPaymentSum($paymentSum);
	}

	/**
	 * @param $paymentSum
	 * @return string
	 */
	private function formatPaymentSum($paymentSum): string
	{
		return number_format($paymentSum, 2, '.', '');
	}

	/**
	 * returns either the link from a handler's settings or the order confirmation page
	 * @param Payment $payment
	 * @return string
	 */
	private function getSuccessUrl(Payment $payment): string
	{
		return $this->getBusinessValue($payment, 'PLATON_SUCCESS_URL') ?: $this->service->getContext()->getUrl();
	}

	/**
	 * @param Payment $payment
	 * @return string|string[]
	 */
	private function getPaymentDescription(Payment $payment)
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();
		$userEmail = $order->getPropertyCollection()->getUserEmail();

		$descriptionTemplate = $this->getBusinessValue($payment, 'PLATON_PAYMENT_DESCRIPTION');
		$description = str_replace(
			[
				'#PAYMENT_NUMBER#',
				'#ORDER_NUMBER#',
				'#PAYMENT_ID#',
				'#ORDER_ID#',
				'#USER_EMAIL#',
			],
			[
				$payment->getField('ACCOUNT_NUMBER'),
				$order->getField('ACCOUNT_NUMBER'),
				$payment->getId(),
				$order->getId(),
				($userEmail) ? $userEmail->getValue() : '',
			],
			$descriptionTemplate
		);

		return $description;
	}

	/**
	 * calculates the control signature for the request
	 * @param string $apiKey
	 * @param string $paymentMethodCode
	 * @param string $encodedPaymentData
	 * @param string $successUrl
	 * @param string $password
	 * @return string
	 */
	private function getPaymentFormSignature(
		string $apiKey,
		string $paymentMethodCode,
		string $encodedPaymentData,
		string $successUrl,
		string $password
	): string
	{
		return md5(
			mb_strtoupper(
				strrev($apiKey)
				. strrev($paymentMethodCode)
				. strrev($encodedPaymentData)
				. strrev($successUrl)
				. strrev($password)
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getCurrencyList()
	{
		return ['UAH'];
	}

	/**
	 * @inheritDoc
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();
		Logger::addDebugInfo(__CLASS__ . ': request payload: ' . Json::encode($request->getValues(), JSON_UNESCAPED_UNICODE));

		$signatureCheckResult = $this->checkCallbackSignature($payment, $request);
		if ($signatureCheckResult->isSuccess())
		{
			$transactionId = $request->get('id');
			$transactionStatus = $request->get('status');

			if ($transactionId && $transactionStatus === self::TRANSACTION_STATUS_SALE)
			{
				$description = Loc::getMessage('SALE_HPS_PLATON_TRANSACTION_DESCRIPTION', [
					'#ID#' => $request->get('id'),
					'#PAYMENT_NUMBER#' => $request->get('order'),
				]);
				$requestSum = $request->get('amount');
				$paymentFields = [
					'PS_INVOICE_ID' => $transactionId,
					'PS_STATUS_CODE' => $transactionStatus,
					'PS_STATUS_DESCRIPTION' => $description,
					'PS_SUM' => $requestSum,
					'PS_CURRENCY' => $request->get('currency'),
					'PS_RESPONSE_DATE' => new Main\Type\DateTime(),
					'PS_STATUS' => 'N',
					'PS_CARD_NUMBER' => $request->get('card'),
				];

				if ($this->checkPaymentSum($payment, $requestSum))
				{
					$paymentFields['PS_STATUS'] = 'Y';

					$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
					$result->setPsData($paymentFields);
				}
				else
				{
					$errorMessage = Loc::getMessage('SALE_HPS_PLATON_SUM_MISMATCH');
					$paymentFields['PS_STATUS_DESCRIPTION'] .= $description . ' ' . $errorMessage;
					$result->addError(new Main\Error($errorMessage));
				}
			}
			elseif ($transactionStatus === self::TRANSACTION_STATUS_REFUND)
			{
				$oldDescription = $payment->getField('PS_STATUS_DESCRIPTION');
				$newDescription = str_replace(
					' ' . Loc::getMessage('SALE_HPS_PLATON_REFUND_IN_PROCESS'),
					'',
					$oldDescription
				);
				$payment->setField('PS_STATUS_DESCRIPTION', $newDescription);
				$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
			}
			else
			{
				$errorMessage = $request->get('error_message');
				if (!isset($errorMessage))
				{
					$errorMessage = Loc::getMessage('SALE_HPS_PLATON_REQUEST_ERROR');
				}
				$result->addError(new Main\Error($errorMessage));
			}
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_PLATON_SIGNATURE_MISMATCH')));
		}

		return $result;
	}

	/**
	 * checks the callback signature to see if the callback is genuine
	 * @param Request $request
	 * @return ServiceResult
	 */
	private function checkCallbackSignature(Payment $payment, Request $request): PaySystem\ServiceResult
	{
		$result = new ServiceResult();

		$callbackSignature = $request->get('sign');

		$email = $this->getUserEmailValue($payment);

		$password = $this->getBusinessValue($payment, 'PLATON_PASSWORD');
		$order = $payment->getField('ACCOUNT_NUMBER');
		$card = $request->get('card');
		$localSignature = $this->getCallbackSignature($email, $password, $order, $card);

		Logger::addDebugInfo(__CLASS__ . ": local signature: $localSignature, callback signature: $callbackSignature");
		if ($callbackSignature !== $localSignature)
		{
			$result->addError(new Main\Error('signature mismatch'));
		}

		return $result;
	}

	/**
	 * checks if the actual sum of the payment is equal to the sum paid by the customer
	 * @param Payment $payment
	 * @param $requestSum
	 * @return bool
	 */
	private function checkPaymentSum(Payment $payment, $requestSum): bool
	{
		$roundedRequestSum = PriceMaths::roundPrecision($requestSum);
		$roundedPaymentSum = PriceMaths::roundPrecision($payment->getSum());
		Logger::addDebugInfo(__CLASS__ . ": request sum: $roundedRequestSum, payment sum: $roundedPaymentSum");

		return $roundedRequestSum === $roundedPaymentSum;
	}

	/**
	 * @inheritDoc
	 */
	public static function getIndicativeFields()
	{
		return [
			self::CALLBACK_ORDER_PARAM,
			self::CALLBACK_EXT1_PARAM,
			self::CALLBACK_EXT2_PARAM,
		];
	}

	/**
	 * @inheritDoc
	 */
	protected static function isMyResponseExtended(Request $request, $paySystemId)
	{
		return (int)$request->get(self::CALLBACK_EXT2_PARAM) === (int)$paySystemId;
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return $request->get('order');
	}

	/**
	 * @inheritDoc
	 */
	public function refund(Payment $payment, $refundableSum)
	{
		$result = new PaySystem\ServiceResult();

		$transactionId = $payment->getField('PS_INVOICE_ID');
		$cardNumber = $payment->getField('PS_CARD_NUMBER');

		if ($cardNumber)
		{
			$formattedPaymentSum = $this->getFormattedPaymentSum($payment);
			$apiKey = $this->getBusinessValue($payment, 'PLATON_API_KEY');
			$email = $this->getUserEmailValue($payment);

			$password = $this->getBusinessValue($payment, 'PLATON_PASSWORD');

			$signature = $this->getCallbackSignature($email, $password, $transactionId, $cardNumber);

			$fields = [
				'action' => self::REFUND_ACTION,
				'client_key' => $apiKey,
				'trans_id' => $transactionId,
				'amount' => $formattedPaymentSum,
				'hash' => $signature,
			];

			$responseResult = $this->send($this->getUrl($payment, "requestUrl"), $fields);
			if (!$responseResult->isSuccess())
			{
				$result->addErrors($responseResult->getErrors());
				return $result;
			}

			$responseData = $responseResult->getData();

			Logger::addDebugInfo(__CLASS__ . ': refund payload: ' . Json::encode($responseData, JSON_UNESCAPED_UNICODE));
			switch ($responseData['result'])
			{
				case self::REFUND_STATUS_ACCEPTED:
					$newDescription = $payment->getField('PS_STATUS_DESCRIPTION') . ' ' . Loc::getMessage('SALE_HPS_PLATON_REFUND_IN_PROCESS');
					$payment->setField('PS_STATUS_DESCRIPTION', $newDescription);
					$result->setData($responseData);
					break;
				case self::REFUND_STATUS_ERROR:
					$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_PLATON_RESPONSE_ERROR', [
						'#PS_RESPONSE#' => $responseData['error_message'],
					])));
					break;
				default:
					$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_PLATON_REFUND_ERROR')));
			}
		}
		else
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_PLATON_ERROR_CARD_NOT_FOUND')));
		}

		return $result;
	}

	/**
	 * sends a request to the specified url
	 * @param string $url
	 * @param array $params
	 * @return Result
	 */
	private function send(string $url, array $params): Result
	{
		$result = new Result();

		$httpClient = new HttpClient();
		$response = $httpClient->post($url, $params);
		if ($response === false)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code =>$message)
			{
				$result->addError(new Main\Error($message, $code));
			}
		}
		else
		{
			$responseData = $this->decode($response);
			$result->setData($responseData);
		}

		return $result;
	}

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $transactionId
	 * @param string $cardNumber
	 * @return string
	 */
	private function getCallbackSignature(string $email, string $password, string $transactionId, string $cardNumber): string
	{
		return md5(
			mb_strtoupper(
				strrev($email)
				. $password
				. $transactionId
				. strrev(
					mb_substr($cardNumber, 0, 6)
					. mb_substr($cardNumber, -4)
				)
			)
		);
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getUserEmailValue(Payment $payment): string
	{
		$email = '';
		$emailProperty = $payment->getOrder()->getPropertyCollection()->getUserEmail();
		if ($emailProperty)
		{
			$email = $emailProperty->getValue();
		}

		return $email ?? '';
	}

	protected function getUrlList()
	{
		return [
			'formActionUrl' => 'https://secure.platononline.com/payment/auth',
			'requestUrl' => 'https://secure.platononline.com/post-unq/',
		];
	}

	/**
	 * @inheritDoc
	 */
	public static function getHandlerModeList(): array
	{
		return PaySystem\Manager::getHandlerDescription('Platon')['HANDLER_MODE_LIST'];
	}
}
