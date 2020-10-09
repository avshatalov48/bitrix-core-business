<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main,
	Bitrix\Main\Request,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Web\HttpClient,
	Bitrix\Sale\Payment,
	Bitrix\Sale\PaySystem,
	Bitrix\Sale\PriceMaths,
	Bitrix\Sale\PaymentCollection,
	Bitrix\Sale\BusinessValue;

Loc::loadMessages(__FILE__);

/**
 * Class SkbHandler
 * @package Sale\Handlers\PaySystem
 */
class SkbHandler
	extends PaySystem\ServiceHandler
	implements PaySystem\IRefund
{
	private const MODE_SKB = 'skb';
	private const MODE_DELOBANK = 'delobank';
	private const MODE_GAZENERGOBANK = 'gazenergobank';

	private const RESPONSE_CODE_SUCCESS = [
		'0',
		'RQ00000'
	];

	private const HTTP_CODE_OK = 200;
	private const HTTP_CODE_LOCKED = 423;

	private const PAYMENT_STATUS_ACCEPTED = 'ACWP';

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
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function initiatePay(Payment $payment, Request $request = null): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$createPaymentResult = $this->createPayment($payment);
		if (!$createPaymentResult->isSuccess())
		{
			$result->addErrors($createPaymentResult->getErrors());
			return $result;
		}

		$result->setPsData($createPaymentResult->getPsData());
		$paymentData = $createPaymentResult->getData();

		$params['CURRENCY'] = $payment->getField('CURRENCY');
		$params['SUM'] = PriceMaths::roundPrecision($payment->getSum());
		$params['URL'] = $paymentData['payload'];
		$params['QR_CODE_IMAGE'] = $paymentData['qrImage'];
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
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function changeUserPassword(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$params = [
			'messageId' => self::getMessageId(),
			'login' => $this->getBusinessValue($payment, 'SKB_LOGIN'),
			'password' => $this->getBusinessValue($payment, 'SKB_PASSWORD'),
			'newPassword' => Main\Security\Random::getString(10, true),
		];

		$sendResult = $this->send($payment, 'changeUserPassword', $params);
		if ($sendResult->isSuccess())
		{
			$updatePasswordResult = $this->updatePassword($payment, $params['newPassword']);
			if (!$updatePasswordResult->isSuccess())
			{
				$result->addErrors($updatePasswordResult->getErrors());
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
	 * @param string $password
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function updatePassword(Payment $payment, string $password): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$oldMapping = BusinessValue::getMapping('SKB_PASSWORD', $this->service->getConsumerName(), $payment->getPersonTypeId());
		$updateMappingResult = BusinessValue::updateMapping(
			'SKB_PASSWORD',
			$oldMapping,
			[
				'PROVIDER_KEY' => 'VALUE',
				'PROVIDER_VALUE' => $password,
			]
		);
		if (!$updateMappingResult->isSuccess())
		{
			$result->addErrors($updateMappingResult->getErrors());
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
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function createPayment(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$params = [
			'messageId' => self::getMessageId(),
			'agentId' => $this->getAgentId(),
			'merchantId' => $this->getBusinessValue($payment, 'SKB_MERCHANT_ID'),
			'paymentId' => (string)$payment->getId(),
			'amount' => (string)($payment->getSum() * 100),
			'currency' => $payment->getField('CURRENCY'),
			'paymentPurpose' => $this->getAdditionalInfo($payment),
			'qrcType' => '02',
		];

		$sendResult = $this->send($payment, 'register', $params);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$sendData = $sendResult->getData();
		$result->setPsData(['PS_INVOICE_ID' => $sendData['qrcId']]);
		$result->setData($sendData);

		return $result;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList(): array
	{
		return ['RUB'];
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
	public function processRequest(Payment $payment, Request $request): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$secretKey = $this->getBusinessValue($payment, 'SKB_SECRET_KEY');
		if ($secretKey && !$this->isSignCorrect($request, $secretKey))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_SKB_ERROR_SIGN')));
			return $result;
		}

		$paymentStatusResult = $this->getSkbPaymentStatus($payment);
		if (!$paymentStatusResult->isSuccess())
		{
			$result->addErrors($paymentStatusResult->getErrors());
			return $result;
		}

		$paymentStatusData = $paymentStatusResult->getData();
		$skbPayment = current($paymentStatusData['payments']);
		if ($skbPayment['status'] === self::PAYMENT_STATUS_ACCEPTED)
		{
			$description = Loc::getMessage('SALE_HPS_SKB_PAYMENT_ID', [
				'#TX_ID#' => $request->get('txId')
			]);
			$sum = $request->get('amount') / 100;
			$fields = array(
				'PS_INVOICE_ID' => $skbPayment['qrcId'],
				'PS_STATUS_CODE' => $skbPayment['status'],
				'PS_STATUS_DESCRIPTION' => $description,
				'PS_SUM' => $sum,
				'PS_STATUS' => 'N',
				'PS_CURRENCY' => $payment->getField('CURRENCY'),
				'PS_RESPONSE_DATE' => new Main\Type\DateTime()
			);

			if ($this->isSumCorrect($payment, $sum))
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
				$error = Loc::getMessage('SALE_HPS_SKB_ERROR_SUM');
				$fields['PS_STATUS_DESCRIPTION'] .= '. '.$error;
				$result->addError(new Main\Error($error));
			}

			$result->setPsData($fields);
		}
		else
		{
			$result->addError(
				new Main\Error(
					Loc::getMessage(
						'SALE_HPS_SKB_ERROR_STATUS',
						[
							'#STATUS#' => $paymentStatusData['payments']['status'],
						]
					)
				)
			);
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
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getSkbPaymentStatus(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$params = [
			'messageId' => self::getMessageId(),
			'agentId' => $this->getAgentId(),
			'qrcIds' => [
				$payment->getField('PS_INVOICE_ID'),
			],
		];

		$sendResult = $this->send($payment, 'getPaymentsStatus', $params);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$sendData = $sendResult->getData();
		if (empty($sendData['payments']))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_SKB_ERROR_EMPTY_PAYMENTS')));
		}

		$result->setData($sendData);

		return $result;
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return $request->get('paymentId');
	}

	/**
	 * @return array
	 */
	public static function getIndicativeFields(): array
	{
		return ['qrcId', 'paymentId', 'txStatus', 'txId', 'debitorId', 'amount', 'timestamp', 'sign'];
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
			__CLASS__.': skbSum = '.PriceMaths::roundPrecision($sum).'; paymentSum = '.PriceMaths::roundPrecision($payment->getSum())
		);

		return PriceMaths::roundPrecision($sum) === PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Request $request
	 * @param $secretKey
	 * @return bool
	 */
	protected function isSignCorrect(Request $request, $secretKey): bool
	{
		$hash = $request->get('qrcId')
			.$request->get('timestamp')
			.$request->get('txId')
			.$request->get('amount')
			.$secretKey;

		return md5($hash) === $request->get('sign');
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

		$checkRefundTransferResult = $this->checkRefundTransfer($payment, $refundableSum);
		if (!$checkRefundTransferResult->isSuccess())
		{
			$result->addErrors($checkRefundTransferResult->getErrors());
			return $result;
		}

		$checkRefundTransferData = $checkRefundTransferResult->getData();
		if (!$checkRefundTransferData['corelationId'])
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_SKB_ERROR_CORELATION_ID')));
			return $result;
		}

		$approveRefundTransferResult = $this->approveRefundTransfer($payment, $checkRefundTransferData['corelationId']);
		if (!$approveRefundTransferResult->isSuccess())
		{
			$result->addErrors($approveRefundTransferResult->getErrors());
			return $result;
		}

		$approveRefundTransferData = $approveRefundTransferResult->getData();
		if ($approveRefundTransferData['status'] === static::PAYMENT_STATUS_ACCEPTED)
		{
			$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
		}

		return $result;
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
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function checkRefundTransfer(Payment $payment, $refundableSum): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$paymentStatusResult = $this->getSkbPaymentStatus($payment);
		if (!$paymentStatusResult->isSuccess())
		{
			$result->addErrors($paymentStatusResult->getErrors());
			return $result;
		}

		$paymentStatusData = $paymentStatusResult->getData();
		$skbPayment = current($paymentStatusData['payments']);

		$params = [
			'messageId' => self::getMessageId(),
			'trxId' => $skbPayment['trxId'],
			'amount' => (string)($refundableSum * 100),
		];

		$sendResult = $this->send($payment, 'checkRefundTransfer', $params);
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
	 * @param Payment $payment
	 * @param string $corelationId
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function approveRefundTransfer(Payment $payment, string $corelationId): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$params = [
			'messageId' => self::getMessageId(),
			'corelationId' => $corelationId,
		];

		$sendResult = $this->send($payment, 'approveRefundTransfer', $params);
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
	 * @param Payment $payment
	 * @param $action
	 * @param array $params
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function send(Payment $payment, $action, array $params = []): PaySystem\ServiceResult
	{
		$url = $this->getUrl($payment, $action);

		$result = $this->makeQuery($url, $params, $this->getHeaders($payment));
		if (!$result->isSuccess() && $result->getErrorCollection()->getErrorByCode(self::HTTP_CODE_LOCKED))
		{
			$changeUserPasswordResult = $this->changeUserPassword($payment);
			if (!$changeUserPasswordResult->isSuccess())
			{
				$result->addErrors($changeUserPasswordResult->getErrors());
				return $result;
			}

			$result = $this->makeQuery($url, $params, $this->getHeaders($payment));
		}

		if (!$result->isSuccess())
		{
			return $result;
		}

		$sendData = $result->getData();
		$verifyResult = $this->verifyResponse($sendData['response']);
		if ($verifyResult->isSuccess())
		{
			$result->setData(static::decode($sendData['response']));
		}
		else
		{
			$result->addErrors($verifyResult->getErrors());
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
	private function makeQuery($url, array $params = [], array $headers = []): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$httpClient = new HttpClient();
		$httpClient->setHeaders($headers);

		$postData = static::encode($params);
		PaySystem\Logger::addDebugInfo(__CLASS__.': request data: '.$postData);

		$response = $httpClient->post($url, $postData);
		if ($response === false)
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_SKB_ERROR_EMPTY_RESPONSE')));
			foreach ($httpClient->getError() as $code => $message)
			{
				$result->addError(new Main\Error($message, $code));
			}

			return $result;
		}

		PaySystem\Logger::addDebugInfo(__CLASS__.': response data: '.Main\Text\Encoding::convertEncoding($response, "UTF-8", LANG_CHARSET));

		$httpStatus = $httpClient->getStatus();
		if ($httpStatus === self::HTTP_CODE_OK)
		{
			$result->setData(['response' => $response]);
		}
		else
		{
			$errorMessage = Loc::getMessage('SALE_HPS_SKB_ERROR_STATUS_'.$httpStatus);
			if (!$errorMessage)
			{
				$errorMessage = Loc::getMessage(
					'SALE_HPS_SKB_ERROR_STATUS_UNKNOWN',
					[
						'#STATUS#' => $httpStatus,
					]
				);
			}

			$result->addError(new Main\Error($errorMessage, $httpStatus));
		}

		return $result;
	}

	/**
	 * @param $response
	 * @return PaySystem\ServiceResult
	 */
	private function verifyResponse($response): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$responseData = static::decode($response);
		if ($response && !$responseData)
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_SKB_ERROR_DECODE_RESPONSE')));
			return $result;
		}

		if (isset($responseData['errCode']) && !in_array($responseData['errCode'], self::RESPONSE_CODE_SUCCESS, true))
		{
			$result->addError(new Main\Error($responseData['errMess'], $responseData['errCode']));
		}
		elseif (isset($responseData['moreInformation'], $responseData['httpCode']))
		{
			$result->addError(new Main\Error($responseData['moreInformation'], $responseData['httpCode']));
		}

		return $result;
	}

	/**
	 * @param Payment|null $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null): bool
	{
		return $this->getBusinessValue($payment, 'SKB_TEST_MODE') === 'Y';
	}

	/**
	 * @return array
	 */
	protected function getUrlList(): array
	{
		$testUrl = 'https://sbp.test-api.skbbank.ru:443/';
		$activeUrl = 'https://sbp.api.skbbank.ru:443/';

		return [
			'register' => [
				self::TEST_URL => $testUrl.'qr/register',
				self::ACTIVE_URL => $activeUrl.'qr/register'
			],
			'getPaymentsStatus' => [
				self::TEST_URL => $testUrl.'qr/getpaymentsstatus',
				self::ACTIVE_URL => $activeUrl.'qr/getpaymentsstatus'
			],
			'checkRefundTransfer' => [
				self::TEST_URL => $testUrl.'refund/CheckRefundTransfer',
				self::ACTIVE_URL => $activeUrl.'refund/CheckRefundTransfer'
			],
			'approveRefundTransfer' => [
				self::TEST_URL => $testUrl.'refund/ApproveRefundTransfer',
				self::ACTIVE_URL => $activeUrl.'refund/ApproveRefundTransfer'
			],
			'changeUserPassword' => [
				self::TEST_URL => $testUrl.'user/changeUserPassword',
				self::ACTIVE_URL => $activeUrl.'user/changeUserPassword'
			],
		];
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
	protected function getAdditionalInfo(Payment $payment)
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
			$this->getBusinessValue($payment, 'SKB_ADDITIONAL_INFO')
		);

		return substr($description, 0, 140);
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getBasicAuthString(Payment $payment): string
	{
		return base64_encode(
			$this->getBusinessValue($payment, 'SKB_LOGIN')
			.':'
			. $this->getBusinessValue($payment, 'SKB_PASSWORD')
		);
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	private function getHeaders(Payment $payment): array
	{
		return [
			'Authorization' => 'Basic '.$this->getBasicAuthString($payment),
			'Content-Type' => 'application/json',
		];
	}

	/**
	 * @return string
	 */
	private static function getMessageId(): string
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
	 * @return string
	 */
	private function getAgentId(): string
	{
		$agentList = [
			self::MODE_SKB => 'A00000000001',
			self::MODE_DELOBANK => 'A00000000001',
			self::MODE_GAZENERGOBANK => 'A00000000023',
		];

		return $agentList[$this->service->getField('PS_MODE')];
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList(): array
	{
		return array(
			self::MODE_SKB => Loc::getMessage('SALE_HPS_SKB_MODE_SKB'),
			self::MODE_DELOBANK => Loc::getMessage('SALE_HPS_SKB_MODE_DELOBANK'),
			self::MODE_GAZENERGOBANK => Loc::getMessage('SALE_HPS_SKB_MODE_GAZENERGOBANK'),
		);
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