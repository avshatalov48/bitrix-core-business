<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization;
use Bitrix\Main;
use Bitrix\Main\Request;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class YandexCheckout
 * @package Sale\Handlers\PaySystem
 */
class YandexCheckoutHandler extends PaySystem\ServiceHandler implements PaySystem\IRefund, PaySystem\IHold
{
	const PAYMENT_STATUS_WAITING_FOR_CAPTURE = 'waiting_for_capture';
	const PAYMENT_STATUS_SUCCEEDED = 'succeeded';
	const PAYMENT_STATUS_CANCELED = 'canceled';
	const PAYMENT_STATUS_PENDING = 'pending';

	const PAYMENT_METHOD_SMART = '';
	const PAYMENT_METHOD_ALFABANK = 'alfabank';
	const PAYMENT_METHOD_BANK_CARD = 'bank_card';
	const PAYMENT_METHOD_YANDEX_MONEY = 'yandex_money';
	const PAYMENT_METHOD_SBERBANK= 'sberbank';
	const PAYMENT_METHOD_QIWI = 'qiwi';
	const PAYMENT_METHOD_WEBMONEY = 'webmoney';
	const PAYMENT_METHOD_CASH = 'cash';

	const URL = 'https://payment.yandex.net/api/v3';

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		if ($request === null)
		{
			$request = Main\Context::getCurrent()->getRequest();
		}

		if ($this->isSetExternalPaymentType())
		{
			$result = $this->initiateExternalPay($payment, $request);
		}
		else
		{
			$result = $this->initiateRedirectPay($payment, $request);
		}

		if (!$result->isSuccess())
		{
			PaySystem\ErrorLog::add(array(
				'ACTION' => 'Yandex.Checkout: initiatePay',
				'MESSAGE' => join('\n', $result->getErrorMessages())
			));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function initiateRedirectPay(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$createResult = $this->createYandexPayment($payment, $request);
		if (!$createResult->isSuccess())
		{
			$result->addErrors($createResult->getErrors());
			return $createResult;
		}

		$yandexPaymentData = $createResult->getData();
		if ($yandexPaymentData['status'] === static::PAYMENT_STATUS_CANCELED)
		{
			return $result->addError(
				new Main\Error(
					Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_PAYMENT_CANCELED')
				)
			);
		}

		$result->setPsData(array('PS_INVOICE_ID' => $yandexPaymentData['id']));

		$params = array(
			'URL' => $yandexPaymentData['confirmation']['confirmation_url'],
			'CURRENCY' => $payment->getField('CURRENCY'),
			'SUM' => $payment->getSum(),
		);
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

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function initiateExternalPay(Payment $payment, Request $request)
	{
		if (!$this->isFillPaymentMethodFields($request))
		{
			$params = array(
				'SUM' => $payment->getSum(),
				'CURRENCY' => $payment->getField('CURRENCY'),
				'FIELDS' => $this->getPaymentMethodFields(),
				'PAYMENT_METHOD' => $this->service->getField('PS_MODE')
			);
			$this->setExtraParams($params);

			return $this->showTemplate($payment, "template_external");
		}

		$result = new PaySystem\ServiceResult();

		$createResult = $this->createYandexPayment($payment, $request);
		if (!$createResult->isSuccess())
		{
			$result->addErrors($createResult->getErrors());
			return $createResult;
		}

		$yandexPaymentData = $createResult->getData();

		if ($yandexPaymentData['paid'] === true
			&& $yandexPaymentData['status'] === static::PAYMENT_STATUS_PENDING
		)
		{
			$result->setPsData(array('PS_INVOICE_ID' => $yandexPaymentData['id']));
		}
		else
		{
			$result->addError(
				new Main\Error(
					Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_EXTERNAL_PAYMENT')
				)
			);
		}

		return $result;

	}

	/**
	 * @return bool
	 */
	private function isSetExternalPaymentType()
	{
		$externalPayment = array(static::PAYMENT_METHOD_ALFABANK);

		return in_array($this->service->getField('PS_MODE'), $externalPayment);
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function createYandexPayment(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'pay');

		$params = $this->getYandexPaymentQueryParams($payment, $request);

		$headers = $this->getHeaders($payment);
		$headers['Idempotence-Key'] = $this->getIdempotenceKey();

		$sendResult = $this->send($url, $headers, $params);
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
	 * @return string
	 */
	private function getIdempotenceKey()
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
	 * @param $url
	 * @param array $headers
	 * @param array $params
	 * @return PaySystem\ServiceResult
	 */
	private function send($url, array $headers, array $params = array())
	{
		$result = new PaySystem\ServiceResult();

		$httpClient = new HttpClient();
		foreach ($headers as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		$postData = null;
		if ($params)
		{
			$postData = static::encode($params);
		}

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

		$response = static::decode($response);

		$httpStatus = $httpClient->getStatus();
		if ($httpStatus === 200)
		{
			$result->setData($response);
		}
		elseif ($httpStatus === 202)
		{
			$secondsToSleep = ceil($response['retry_after'] / 1000);
			sleep($secondsToSleep);

			$result = $this->send($url, $headers, $params);
		}
		elseif ($httpStatus !== 201)
		{
			$error = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_HTTP_STATUS_'.$httpStatus);
			if ($error)
			{
				$result->addError(new Main\Error($error));
			}
			else
			{
				if (isset($response['type']) && $response['type'] === 'error')
				{
					$result->addError(new Main\Error($response['description']));
				}
			}
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return array
	 */
	private function getYandexPaymentQueryParams(Payment $payment, Request $request)
	{
		$query = array(
			'amount' => array(
				'value' => $payment->getSum(),
				'currency' => $payment->getField('CURRENCY')
			),
			'confirmation' => array(
				'type' => 'redirect',
				'return_url' => $this->getBusinessValue($payment, 'YANDEX_CHECKOUT_RETURN_URL')
			),
			'metadata' => array(
				'BX_PAYMENT_NUMBER' => $payment->getId(),
				'BX_PAYSYSTEM_CODE' => $payment->getPaymentSystemId(),
				'BX_HANDLER' => 'YANDEX_CHECKOUT',
			)
		);

		if ($this->service->getField('PS_MODE') !== static::PAYMENT_METHOD_SMART)
		{
			$query['payment_method_data'] = array(
				'type' => $this->service->getField('PS_MODE')
			);

			if ($this->isSetExternalPaymentType())
			{
				$query['confirmation']['type'] = 'external';
				$fields = $this->getPaymentMethodFields();
				if ($fields)
				{
					foreach ($fields as $field)
					{
						$query['payment_method_data'][$field] = $request->get($field);
					}
				}
			}
		}

		return $query;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getBasicAuthString(Payment $payment)
	{
		return base64_encode(
			$this->getBusinessValue($payment, 'YANDEX_CHECKOUT_SHOP_ID').
			':'.
			$this->getBusinessValue($payment, 'YANDEX_CHECKOUT_SECRET_KEY')
		);
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
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB');
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$inputStream = static::readFromStream();

		$data = static::decode($inputStream);
		if ($data !== false)
		{
			$paymentData = $data['object'];

			if ($paymentData['status'] === static::PAYMENT_STATUS_WAITING_FOR_CAPTURE)
			{
				if ($paymentData['paid'] === true
					&& $this->isSumCorrect($payment, $paymentData)
				)
				{
					$confirm = $this->confirm($payment);
					if (!$confirm->isSuccess())
					{
						$result->addErrors($confirm->getErrors());
						return $result;
					}

					$result->setPsData($confirm->getPsData());
					if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y'
						&& $confirm->getOperationType() === PaySystem\ServiceResult::MONEY_COMING
					)
					{
						$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
					}
				}
				else
				{
					$cancel = $this->cancel($payment);
					if (!$cancel->isSuccess())
					{
						$result->addErrors($cancel->getErrors());
						return $result;
					}

					$result->setPsData($cancel->getPsData());
				}
			}
			else
			{
				$result->addError(
					new Main\Error(
						Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_STATUS')
					)
				);
			}
		}
		else
		{
			$result->addError(new Main\Error('SALE_HPS_YANDEX_CHECKOUT_ERROR_QUERY'));
		}

		if (!$result->isSuccess())
		{
			PaySystem\ErrorLog::add(array(
				'ACTION' => 'Yandex.Checkout: processRequest',
				'MESSAGE' => join('\n', $result->getErrorMessages())
			));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param array $paymentData
	 * @return bool
	 */
	private function isSumCorrect(Payment $payment, array $paymentData)
	{
		return PriceMaths::roundPrecision($paymentData['amount']['value']) === PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Payment $payment
	 * @param $refundableSum
	 * @return PaySystem\ServiceResult
	 */
	public function refund(Payment $payment, $refundableSum)
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'refund');
		$params = $this->getRefundQueryParams($payment, $refundableSum);
		$headers = $this->getHeaders($payment);
		$headers['Idempotence-Key'] = $this->getIdempotenceKey();

		$sendResult = $this->send($url, $headers, $params);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());

			PaySystem\ErrorLog::add(array(
				'ACTION' => 'Yandex.Checkout: refund',
				'MESSAGE' => join('\n', $sendResult->getErrorMessages())
			));

			return $result;
		}

		$response = $sendResult->getData();

		if ($response['status'] === static::PAYMENT_STATUS_SUCCEEDED
			&& PriceMaths::roundPrecision($response['amount']['value']) === PriceMaths::roundPrecision($refundableSum)
		)
		{
			$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 */
	public function cancel(Payment $payment)
	{
		$url = $this->getUrl($payment, 'cancel');
		$headers = $this->getHeaders($payment);
		$headers['Idempotence-Key'] = $this->getIdempotenceKey();

		$sendResult = $this->send($url, $headers);
		if (!$sendResult->isSuccess())
		{
			PaySystem\ErrorLog::add(array(
				'ACTION' => 'Yandex.Checkout: cancel',
				'MESSAGE' => join('\n', $sendResult->getErrorMessages())
			));
		}

		return $sendResult;
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 */
	public function confirm(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'confirm');
		$headers = $this->getHeaders($payment);
		$headers['Idempotence-Key'] = $this->getIdempotenceKey();
		$params = array(
			'amount' => array(
				'value' => $payment->getSum(),
				'currency' => $payment->getField('CURRENCY')
			)
		);

		$sendResult = $this->send($url, $headers, $params);
		if ($sendResult->isSuccess())
		{
			$response = $sendResult->getData();
			$description = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_TRANSACTION').$response['id'];

			$fields = array(
				"PS_STATUS_CODE" => substr($response['status'], 0, 5),
				"PS_STATUS_DESCRIPTION" => $description,
				"PS_SUM" => $response['amount']['value'],
				"PS_CURRENCY" => $response['amount']['currency'],
				"PS_RESPONSE_DATE" => new Main\Type\DateTime()
			);

			if ($response['status'] === static::PAYMENT_STATUS_SUCCEEDED)
			{
				$fields["PS_STATUS"] = "Y";
				$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
			}
			else
			{
				$fields["PS_STATUS"] = "N";
			}

			$result->setPsData($fields);
		}
		else
		{
			PaySystem\ErrorLog::add(array(
				'ACTION' => 'Yandex.Checkout: confirm',
				'MESSAGE' => join('\n', $sendResult->getErrorMessages())
			));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	private function getHeaders(Payment $payment)
	{
		return array(
			'Content-Type' => 'application/json',
			'Authorization' => 'Basic '.$this->getBasicAuthString($payment)
		);
	}

	/**
	 * @param Payment $payment
	 * @param $refundableSum
	 * @return array
	 */
	private function getRefundQueryParams(Payment $payment, $refundableSum)
	{
		return array(
			'payment_id' => $payment->getField('PS_INVOICE_ID'),
			'amount' => array(
				'value' => $refundableSum,
				'currency' => $payment->getField('CURRENCY'),
			),
		);
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		$inputStream = static::readFromStream();

		if ($inputStream)
		{
			$data = static::decode($inputStream);
			if ($data === false)
				return false;

			return $data['object']['metadata']['BX_PAYMENT_NUMBER'];

		}

		return false;
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList()
	{
		return array(
			static::PAYMENT_METHOD_SMART => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SMART'),
			static::PAYMENT_METHOD_BANK_CARD=> Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_BANK_CARDS'),
			static::PAYMENT_METHOD_YANDEX_MONEY => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_YANDEX_MONEY'),
			static::PAYMENT_METHOD_SBERBANK => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SBERBANK'),
			static::PAYMENT_METHOD_QIWI => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_QIWI'),
			static::PAYMENT_METHOD_WEBMONEY => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_WEBMONEY'),
			static::PAYMENT_METHOD_ALFABANK => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ALFABANK'),
			static::PAYMENT_METHOD_CASH => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_CASH')
		);
	}

	/**
	 * @return array
	 */
	protected function getUrlList()
	{
		return array(
			'pay' => static::URL.'/payments',
			'refund' =>  static::URL.'/refunds',
			'confirm' =>  static::URL.'/payments/#payment_id#/capture',
			'cancel' =>  static::URL.'/payments/#payment_id#/cancel'
		);
	}

	/**
	 * @param Request $request
	 * @param int $paySystemId
	 * @return bool
	 */
	public static function isMyResponse(Request $request, $paySystemId)
	{
		$inputStream = static::readFromStream();

		if ($inputStream)
		{
			$data = static::decode($inputStream);
			if ($data === false)
				return false;

			if (isset($data['object']['metadata']['BX_HANDLER'])
				&& $data['object']['metadata']['BX_HANDLER'] === 'YANDEX_CHECKOUT'
				&& isset($data['object']['metadata']['BX_PAYSYSTEM_CODE'])
				&& (int)$data['object']['metadata']['BX_PAYSYSTEM_CODE'] === (int)$paySystemId
			)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	private static function readFromStream()
	{
		return file_get_contents('php://input');
	}

	/**
	 * @param Payment $payment
	 * @param string $action
	 * @return string
	 */
	protected function getUrl(Payment $payment = null, $action)
	{
		$url = parent::getUrl($payment, $action);
		if ($payment !== null &&
			(
				$action === 'cancel'
				|| $action === 'confirm'
			)
		)
		{
			$url = str_replace('#payment_id#', $payment->getField('PS_INVOICE_ID'), $url);
		}

		return $url;
	}

	/**
	 * @return array
	 */
	private function getPaymentMethodFields()
	{
		$paymentMethodFields = array(
			static::PAYMENT_METHOD_ALFABANK => array('login')
		);

		return $paymentMethodFields[$this->service->getField('PS_MODE')];
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	private function isFillPaymentMethodFields(Request $request)
	{
		$fields = $this->getPaymentMethodFields();
		if ($fields)
		{
			foreach ($fields as $field)
			{
				if (!$request->get($field))
					return false;
			}
		}

		return true;
	}
}
