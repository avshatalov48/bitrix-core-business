<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main,
	Bitrix\Main\Request,
	Bitrix\Main\Localization,
	Bitrix\Main\Web\HttpClient,
	Bitrix\Sale\Payment,
	Bitrix\Sale\PaySystem,
	Bitrix\Sale\PriceMaths,
	Bitrix\Sale\PaymentCollection,
	Bitrix\Sale\PaySystem\ServiceResult;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class SberbankOnlineHandler
 * @package Sale\Handlers\PaySystem
 */
class SberbankOnlineHandler
	extends PaySystem\ServiceHandler
	implements PaySystem\IRefund
{
	const PAYMENT_OPERATION_DEPOSITED = 'deposited';
	const PAYMENT_STATUS_SUCCESS = 1;
	const PAYMENT_STATUS_FAIL = 0;

	const RESPONSE_CODE_SUCCESS = 0;

	const PAYMENT_STATE_CREATED = 'CREATED';

	const PAYMENT_DELIMITER = '_';

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
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
		$params = [];
		$checkOrderData = [];

		if ($payment->getField('PS_INVOICE_ID'))
		{
			$checkOrderResult = $this->checkOrder($payment);
			$checkOrderData = $checkOrderResult->getData();
		}

		if (!isset($checkOrderData['URL']))
		{
			$orderAttemptNumber = 0;
			if (isset($checkOrderData['ORDER_ATTEMPT_NUMBER']))
			{
				$orderAttemptNumber = $checkOrderData['ORDER_ATTEMPT_NUMBER'];
				$orderAttemptNumber = (int)$orderAttemptNumber + 1;
			}

			$createOrderResult = $this->createOrder($payment, $orderAttemptNumber);
			if (!$createOrderResult->isSuccess())
			{
				$result->addErrors($createOrderResult->getErrors());
				return $result;
			}

			$createOrderData = $createOrderResult->getData();
			$params['URL'] = $createOrderData['URL'];
			$result->setPsData($createOrderResult->getPsData());
		}
		else
		{
			$params['URL'] = $checkOrderData['URL'];
		}

		$urlComponentList = parse_url($params['URL']);
		parse_str($urlComponentList['query'], $formParams);
		$params['FORM_PARAMS'] = $formParams;

		$params['CURRENCY'] = $payment->getField('CURRENCY');
		$params['SUM'] = PriceMaths::roundPrecision($payment->getSum());
		$this->setExtraParams($params);

		$template = "template_bank_card";
		$showTemplateResult = $this->showTemplate($payment, $template);
		if ($showTemplateResult->isSuccess())
		{
			$result->setTemplate($showTemplateResult->getTemplate());
		}
		else
		{
			$result->addErrors($showTemplateResult->getErrors());
		}

		if ($params['URL'])
		{
			$result->setPaymentUrl($params['URL']);
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
	private function checkOrder(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();

		$orderStatus = $this->getOrderStatus($payment);
		if ($orderStatus->isSuccess())
		{
			$orderStatusData = $orderStatus->getData();
			if (isset($orderStatusData['paymentAmountInfo']['paymentState']))
			{
				$paymentState = $orderStatusData['paymentAmountInfo']['paymentState'];
				if (($paymentState === self::PAYMENT_STATE_CREATED)
					&& ((int)$payment->getSum() === (int)($orderStatusData["amount"] / 100))
				)
				{
					$formUrl = $this->getUrl($payment, 'formUrl');
					$params['URL'] = $formUrl.$payment->getField('PS_INVOICE_ID');
				}
			}

			$orderNumber = $orderStatusData['orderNumber'];
			$orderNumber = explode(self::PAYMENT_DELIMITER, $orderNumber);
			$params['ORDER_ATTEMPT_NUMBER'] = (isset($orderNumber[1]) ? $orderNumber[1] : 0);

			$result->setData($params);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param $orderAttemptNumber
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	private function createOrder(Payment $payment, $orderAttemptNumber)
	{
		$result = new PaySystem\ServiceResult();

		$registerOrderResult = $this->registerOrder($payment, $orderAttemptNumber);
		if (!$registerOrderResult->isSuccess())
		{
			$result->addErrors($registerOrderResult->getErrors());
			return $result;
		}

		$sberbankResultData = $registerOrderResult->getData();
		$result->setPsData(['PS_INVOICE_ID' => $sberbankResultData['orderId']]);
		$params['URL'] = $sberbankResultData['formUrl'];

		$result->setData($params);
		return $result;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return ['RUB'];
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
	public function processRequest(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$inputJson = self::encode($request->toArray());
		PaySystem\Logger::addDebugInfo('Sberbank: request: '.$inputJson);

		$secretKey = $this->getBusinessValue($payment, 'SBERBANK_SECRET_KEY');
		if ($secretKey && !$this->isCheckSumCorrect($request, $secretKey))
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_HPS_SBERBANK_ERROR_CHECK_SUM')));
			return $result;
		}

		if ($request->get('operation') == static::PAYMENT_OPERATION_DEPOSITED
			&& $request->get('status') == static::PAYMENT_STATUS_SUCCESS
		)
		{
			$orderStatus = $this->getOrderStatus($payment);
			if ($orderStatus->isSuccess())
			{
				$orderStatusData = $orderStatus->getData();
				$description = Localization\Loc::getMessage('SALE_HPS_SBERBANK_ORDER_ID', [
					'#ORDER_ID#' => $request->get('mdOrder')
				]);
				$fields = array(
					'PS_INVOICE_ID' => $request->get('mdOrder'),
					"PS_STATUS_CODE" => $request->get('operation'),
					"PS_STATUS_DESCRIPTION" => $description,
					"PS_SUM" => $orderStatusData['amount'] / 100,
					"PS_STATUS" => 'N',
					"PS_CURRENCY" => $orderStatusData['currency'],
					"PS_RESPONSE_DATE" => new Main\Type\DateTime()
				);

				if ($this->isSumCorrect($payment, $orderStatusData))
				{
					$fields["PS_STATUS"] = 'Y';

					PaySystem\Logger::addDebugInfo(
						'Sberbank: PS_CHANGE_STATUS_PAY='.$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
					);

					if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y')
					{
						$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
					}
				}
				else
				{
					$error = Localization\Loc::getMessage('SALE_HPS_SBERBANK_ERROR_SUM');
					$fields['PS_STATUS_DESCRIPTION'] .= '. '.$error;
					$result->addError(new Main\Error($error));
				}

				$result->setPsData($fields);
			}
			else
			{
				$result->addErrors($orderStatus->getErrors());
			}
		}
		else
		{
			$error = Localization\Loc::getMessage('SALE_HPS_SBERBANK_ERROR_OPERATION', [
				'#OPERATION#' => $request->get('operation'),
				'#STATUS#' => $request->get('status')
			]);
			$result->addError(new Main\Error($error));
		}

		return $result;
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		$paymentId = $request->get('orderNumber');
		list($paymentId) = explode(self::PAYMENT_DELIMITER, $paymentId);

		return $paymentId;
	}

	/**
	 * @return array
	 */
	static public function getIndicativeFields()
	{
		return ['mdOrder', 'orderNumber', 'checksum', 'operation', 'status'];
	}

	/**
	 * @param Request $request
	 * @param $paySystemId
	 * @return bool
	 */
	protected static function isMyResponseExtended(Request $request, $paySystemId)
	{
		$bxPaySystemCode = (int)$request->get('bx_paysystem_code');
		if ((int)$paySystemId === $bxPaySystemCode)
		{
			return true;
		}

		return false;
	}

	/**
	 * @param Payment $payment
	 * @param array $paymentData
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	private function isSumCorrect(Payment $payment, array $paymentData)
	{
		$sberbankAmount = $paymentData['amount'] / 100;
		PaySystem\Logger::addDebugInfo(
			'Sberbank: sberbankSum='.PriceMaths::roundPrecision($sberbankAmount)."; paymentSum=".PriceMaths::roundPrecision($payment->getSum())
		);

		return PriceMaths::roundPrecision($sberbankAmount) === PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Request $request
	 * @param $secretKey
	 * @return bool
	 */
	protected function isCheckSumCorrect(Request $request, $secretKey)
	{
		$requestParamList = $request->toArray();
		$checksum = $requestParamList['checksum'];

		unset($requestParamList['checksum']);
		ksort($requestParamList);

		$requestParam = '';
		foreach ($requestParamList as $param => $value)
		{
			$requestParam .= $param.';'.$value.';';
		}

		$result = hash_hmac('sha256' , $requestParam , $secretKey);
		if ($result === false)
		{
			return false;
		}

		$result = mb_strtoupper($result);
		return ($result === mb_strtoupper($checksum));
	}

	/**
	 * @param Payment $payment
	 * @param int $attempt
	 * @return ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 */
	protected function registerOrder(Payment $payment, $attempt = 0)
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'register.do');
		$params = $this->getMerchantParams($payment);
		$params = array_merge($params, $this->getRegisterOrderParams($payment, $attempt));

		$sendResult = $this->send($url, $params);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$response = $sendResult->getData();
		$result->setData($response);

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
	protected function getOrderStatus(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'getOrderStatusExtended.do');
		$params = $this->getMerchantParams($payment);
		$params['orderId'] = $payment->getField('PS_INVOICE_ID');

		$sendResult = $this->send($url, $params);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$response = $sendResult->getData();
		$result->setData($response);

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
	public function refund(Payment $payment, $refundableSum)
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'refund.do');
		$params = $this->getMerchantParams($payment);
		$params['orderId'] = $payment->getField('PS_INVOICE_ID');
		$params['amount'] = $refundableSum * 100;

		$sendResult = $this->send($url, $params);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());

			$error = 'Sberbank: refund: '.join('\n', $sendResult->getErrorMessages());
			PaySystem\Logger::addError($error);

			return $result;
		}

		$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);

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
	private function send($url, array $params = array(), array $headers = array())
	{
		$result = new PaySystem\ServiceResult();

		$httpClient = new HttpClient();
		foreach ($headers as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		$postData = static::encode($params);
		PaySystem\Logger::addDebugInfo('Sberbank: request data: '.$postData);

		$response = $httpClient->post($url, $params);
		if ($response === false)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Main\Error($message, $code));
			}

			return $result;
		}

		PaySystem\Logger::addDebugInfo('Sberbank: response data: '.$response);

		$response = static::decode($response);
		if ($response)
		{
			if (isset($response['errorCode']) && $response['errorCode'] != self::RESPONSE_CODE_SUCCESS)
			{
				$result->addError(new Main\Error($response['errorMessage'], $response['errorCode']));
			}
			else
			{
				$result->setData($response);
			}
		}
		else
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage("SALE_HPS_SBERBANK_ERROR_DECODE_RESPONSE")));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return ($this->getBusinessValue($payment, 'SBERBANK_TEST_MODE') == 'Y');
	}

	/**
	 * @return mixed
	 */
	protected function getUrlList()
	{
		$testUrl = 'https://3dsec.sberbank.ru/payment/';
		$activeUrl = 'https://securepayments.sberbank.ru/payment/';

		return [
			'register.do' => [
				self::TEST_URL => $testUrl.'rest/register.do',
				self::ACTIVE_URL => $activeUrl.'rest/register.do'
			],
			'getOrderStatusExtended.do' => [
				self::TEST_URL => $testUrl.'rest/getOrderStatusExtended.do',
				self::ACTIVE_URL => $activeUrl.'rest/getOrderStatusExtended.do'
			],
			'refund.do' => [
				self::TEST_URL => $testUrl.'rest/refund.do',
				self::ACTIVE_URL => $activeUrl.'rest/refund.do'
			],
			'formUrl' => [
				self::TEST_URL => $testUrl.'merchants/sbersafe_cardholder/payment_ru.html?mdOrder=',
				self::ACTIVE_URL => $activeUrl.'merchants/sbersafe_cardholder/payment_ru.html?mdOrder=',
			]
		];
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	protected function getMerchantParams(Payment $payment)
	{
		return [
			'userName' => $this->getBusinessValue($payment, 'SBERBANK_LOGIN'),
			'password' => $this->getBusinessValue($payment, 'SBERBANK_PASSWORD'),
		];
	}

	/**
	 * @param Payment $payment
	 * @param int $attempt
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function getRegisterOrderParams(Payment $payment, $attempt)
	{
		$jsonParams = [
			'bx_paysystem_code' => $this->service->getField('ID'),
		];

		$orderNumber = $payment->getId();
		if ($attempt)
		{
			$orderNumber = $orderNumber.self::PAYMENT_DELIMITER.$attempt;
		}
		$params = [
			'orderNumber' => $orderNumber,
			'amount' => (int)PriceMaths::roundPrecision($payment->getSum() * 100),
			'returnUrl' => $this->getSuccessUrl($payment),
			'failUrl' => $this->getFailUrl($payment),
			'jsonParams' => self::encode($jsonParams)
		];

		$params['language'] = LANGUAGE_ID;
		$params['description'] = $this->getOrderDescription($payment);

		return $params;
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getSuccessUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'SBERBANK_RETURN_SUCCESS_URL') ?: $this->service->getContext()->getUrl();
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getFailUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'SBERBANK_RETURN_FAIL_URL') ?: $this->service->getContext()->getUrl();
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function getOrderDescription(Payment $payment)
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
			$this->getBusinessValue($payment, 'SBERBANK_ORDER_DESCRIPTION')
		);

		$description = Main\Text\Encoding::convertEncoding($description, LANG_CHARSET, "UTF-8");
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
}