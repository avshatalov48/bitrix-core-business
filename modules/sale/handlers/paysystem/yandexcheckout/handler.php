<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization;
use Bitrix\Main;
use Bitrix\Main\Request;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class YandexCheckout
 * @package Sale\Handlers\PaySystem
 */
class YandexCheckoutHandler
	extends PaySystem\ServiceHandler
	implements PaySystem\IRefund, PaySystem\IPartialHold
{
	const CMS_NAME = 'api_1c-bitrix';

	const PAYMENT_STATUS_WAITING_FOR_CAPTURE = 'waiting_for_capture';
	const PAYMENT_STATUS_SUCCEEDED = 'succeeded';
	const PAYMENT_STATUS_CANCELED = 'canceled';
	const PAYMENT_STATUS_PENDING = 'pending';

	const PAYMENT_METHOD_SMART = '';
	const PAYMENT_METHOD_ALFABANK = 'alfabank';
	const PAYMENT_METHOD_BANK_CARD = 'bank_card';
	const PAYMENT_METHOD_YANDEX_MONEY = 'yandex_money';
	const PAYMENT_METHOD_SBERBANK = 'sberbank';
	const PAYMENT_METHOD_QIWI = 'qiwi';
	const PAYMENT_METHOD_WEBMONEY = 'webmoney';
	const PAYMENT_METHOD_CASH = 'cash';
	const PAYMENT_METHOD_MOBILE_BALANCE = 'mobile_balance';
	const PAYMENT_METHOD_EMBEDDED = 'embedded';

	const MODE_SMART = '';
	const MODE_ALFABANK = 'alfabank';
	const MODE_BANK_CARD = 'bank_card';
	const MODE_YANDEX_MONEY = 'yandex_money';
	const MODE_SBERBANK = 'sberbank';
	const MODE_SBERBANK_SMS = 'sberbank_sms';
	const MODE_QIWI = 'qiwi';
	const MODE_WEBMONEY = 'webmoney';
	const MODE_CASH = 'cash';
	const MODE_MOBILE_BALANCE = 'mobile_balance';
	const MODE_EMBEDDED = 'embedded';

	const URL = 'https://payment.yandex.net/api/v3';

	const AUTH_TYPE = 'yandex';

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
		if ($request === null)
		{
			$request = Main\Context::getCurrent()->getRequest();
		}

		$result = new PaySystem\ServiceResult();
		$createYandexPaymentData = [];

		if ($this->needCreateYandexPayment($payment, $request))
		{
			$createYandexPaymentResult = $this->createYandexPayment($payment, $request);
			if (!$createYandexPaymentResult->isSuccess())
			{
				return $createYandexPaymentResult;
			}

			$createYandexPaymentData = $createYandexPaymentResult->getData();
		}

		if (isset($createYandexPaymentData['id']))
		{
			$result->setPsData(['PS_INVOICE_ID' => $createYandexPaymentData['id']]);
		}

		$template = $this->getTemplateName($request);

		$this->setExtraParams(
			$this->getTemplateParams($payment, $template, $createYandexPaymentData)
		);

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
	 * @param Payment $payment
	 * @param $request
	 * @return bool
	 */
	protected function needCreateYandexPayment(Payment $payment, $request) : bool
	{
		$template = $this->getTemplateName($request);

		return $template !== 'template_query';
	}

	/**
	 * @param Payment $payment
	 * @param $template
	 * @param array $additionalParams
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	protected function getTemplateParams(Payment $payment, $template, $additionalParams = []) : array
	{
		$params = [
			'SUM' => PriceMaths::roundPrecision($payment->getSum()),
			'CURRENCY' => $payment->getField('CURRENCY'),
		];

		if ($template === 'template')
		{
			$params['URL'] = $additionalParams['confirmation']['confirmation_url'] ?? '';
		}
		elseif ($template === 'template_query')
		{
			$phoneFields = $this->getPhoneFields();
			$phoneFields = isset($phoneFields[$this->service->getField('PS_MODE')])
				? $phoneFields[$this->service->getField('PS_MODE')]
				: [];

			$params['FIELDS'] = $this->getPaymentMethodFields();
			$params['PHONE_FIELDS'] = $phoneFields;
			$params['PAYMENT_METHOD'] = $this->service->getField('PS_MODE');
			$params['PAYMENT_ID'] = $payment->getId();
			$params['PAYSYSTEM_ID'] = $this->service->getField('ID');
		}
		elseif ($template === 'template_embedded')
		{
			$params['CONFIRMATION_TOKEN'] = $additionalParams['confirmation']['confirmation_token'] ?? '';
			$params['RETURN_URL'] = $this->getBusinessValue($payment, 'YANDEX_CHECKOUT_RETURN_URL');
		}

		return $params;
	}

	/**
	 * @param Request $request
	 * @return string
	 */
	protected function getTemplateName(Request $request) : string
	{
		$template = "template";

		if ($this->hasPaymentMethodFields() &&
			!$this->isFillPaymentMethodFields($request)
		)
		{
			$template .= "_query";
		}
		elseif ($this->isSetExternalPaymentType())
		{
			$template .= "_success";
		}
		elseif ($this->isSetEmbeddedPaymentType())
		{
			$template .= "_embedded";
		}

		return $template;
	}

	/**
	 * @return bool
	 */
	private function isSetExternalPaymentType()
	{
		$externalPayment = array(
			static::MODE_ALFABANK,
			static::MODE_SBERBANK_SMS
		);

		return in_array($this->service->getField('PS_MODE'), $externalPayment);
	}

	/**
	 * @return bool
	 */
	private function isSetEmbeddedPaymentType()
	{
		return $this->service->getField('PS_MODE') === static::MODE_EMBEDDED;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
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

		$verificationResult = $this->verifyCreateYandexPaymentResponse($response);
		if ($verificationResult->isSuccess())
		{
			$result->setData($response);
		}
		else
		{
			$result->addErrors($verificationResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param $response
	 * @return PaySystem\ServiceResult
	 */
	private function verifyCreateYandexPaymentResponse($response)
	{
		$result = new PaySystem\ServiceResult();

		if ($response['status'] === static::PAYMENT_STATUS_CANCELED)
		{
			$result->addError(
				new Main\Error(
					Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_PAYMENT_CANCELED')
				)
			);
		}

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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
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

		PaySystem\Logger::addDebugInfo('Yandex.Checkout: request data: '.$postData);

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

		PaySystem\Logger::addDebugInfo('Yandex.Checkout: response data: '.$response);

		$response = static::decode($response);

		$httpStatus = $httpClient->getStatus();
		if ($httpStatus === 200)
		{
			$result->setData($response);
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
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function getYandexPaymentQueryParams(Payment $payment, Request $request)
	{
		$query = array(
			'description' => $this->getPaymentDescription($payment),
			'amount' => array(
				'value' => (string)PriceMaths::roundPrecision($payment->getSum()),
				'currency' => $payment->getField('CURRENCY')
			),
			'capture' => true,
			'confirmation' => array(
				'type' => 'redirect',
				'return_url' => $this->getBusinessValue($payment, 'YANDEX_CHECKOUT_RETURN_URL')
			),
			'metadata' => array(
				'BX_PAYMENT_NUMBER' => $payment->getId(),
				'BX_PAYSYSTEM_CODE' => $this->service->getField('ID'),
				'BX_HANDLER' => 'YANDEX_CHECKOUT',
				'cms_name' => static::CMS_NAME,
			)
		);

		$articleId = $this->getBusinessValue($payment, 'YANDEX_CHECKOUT_SHOP_ARTICLE_ID');
		if ($articleId)
		{
			$query['recipient'] = ['gateway_id' => $articleId];
		}

		if ($this->service->getField('PS_MODE') === static::MODE_EMBEDDED)
		{
			$query['confirmation'] = array(
				'type' => 'embedded'
			);
		}
		elseif ($this->service->getField('PS_MODE') !== static::MODE_SMART)
		{
			$query['capture'] = true;
			$query['payment_method_data'] = array(
				'type' => $this->getYandexHandlerType($this->service->getField('PS_MODE'))
			);

			if ($this->isSetExternalPaymentType())
			{
				$query['confirmation'] = array(
					'type' => 'external'
				);
			}

			if ($this->hasPaymentMethodFields())
			{
				$fields = $this->getPaymentMethodFields();
				if ($fields)
				{
					foreach ($fields as $field)
					{
						$fieldValue = $request->get($field);
						if ($this->isPhone($field))
						{
							$fieldValue = $this->normalizePhone($request->get($field));
						}
						$query['payment_method_data'][$field] = $fieldValue;
					}
				}
			}
		}

		return $query;
	}

	/**
	 * @param Payment $payment
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 */
	protected function getPaymentDescription(Payment $payment)
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
			$this->getBusinessValue($payment, 'YANDEX_CHECKOUT_DESCRIPTION')
		);

		return substr($description, 0, 128);
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getBasicAuthString(Payment $payment)
	{
		return base64_encode(
			trim($this->getBusinessValue($payment, 'YANDEX_CHECKOUT_SHOP_ID')).
			':'.
			trim($this->getBusinessValue($payment, 'YANDEX_CHECKOUT_SECRET_KEY'))
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
	 * @throws Main\ObjectException
	 * @throws \Exception
	 *
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$inputStream = static::readFromStream();

		$data = static::decode($inputStream);
		if ($data !== false)
		{
			$response = $data['object'];

			if ($response['status'] === static::PAYMENT_STATUS_SUCCEEDED)
			{
				$description = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_TRANSACTION').$response['id'];
				$fields = array(
					'PS_INVOICE_ID' => $response['id'],
					"PS_STATUS_CODE" => substr($response['status'], 0, 5),
					"PS_STATUS_DESCRIPTION" => $description,
					"PS_SUM" => $response['amount']['value'],
					"PS_STATUS" => 'N',
					"PS_CURRENCY" => $response['amount']['currency'],
					"PS_RESPONSE_DATE" => new Main\Type\DateTime()
				);

				if ($this->isSumCorrect($payment, $response))
				{
					$fields["PS_STATUS"] = 'Y';

					PaySystem\Logger::addDebugInfo(
						'Yandex.Checkout: PS_CHANGE_STATUS_PAY='.$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
					);

					if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y')
					{
						$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
					}
				}
				else
				{
					$error = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_SUM');
					$fields['PS_STATUS_DESCRIPTION'] .= ' '.$error;
					$result->addError(new Main\Error($error));
				}

				$result->setPsData($fields);
			}
			else
			{
				$error = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_STATUS').': '.$response['status'];
				$result->addError(new Main\Error($error));
			}
		}
		else
		{
			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_QUERY')));
		}

		return $result;
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
		PaySystem\Logger::addDebugInfo(
			'Yandex.Checkout: yandexSum='.PriceMaths::roundPrecision($paymentData['amount']['value'])."; paymentSum=".PriceMaths::roundPrecision($payment->getSum())
		);

		return PriceMaths::roundPrecision($paymentData['amount']['value']) === PriceMaths::roundPrecision($payment->getSum());
	}

	/**
	 * @param Payment $payment
	 * @param $refundableSum
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentNullException
	 * @throws \Exception
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

			$error = 'Yandex.Checkout: refund: '.join("\n", $sendResult->getErrorMessages());
			PaySystem\Logger::addError($error);

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
	 * @throws \Exception
	 */
	public function cancel(Payment $payment)
	{
		$url = $this->getUrl($payment, 'cancel');
		$headers = $this->getHeaders($payment);
		$headers['Idempotence-Key'] = $this->getIdempotenceKey();

		$sendResult = $this->send($url, $headers);
		if (!$sendResult->isSuccess())
		{
			$error = 'Yandex.Checkout: cancel: '.join("\n", $sendResult->getErrorMessages());
			PaySystem\Logger::addError($error);
		}

		return $sendResult;
	}

	/**
	 * @param Payment $payment
	 * @param int $sum
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public function confirm(Payment $payment, $sum = 0)
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'confirm');
		$headers = $this->getHeaders($payment);
		$headers['Idempotence-Key'] = $this->getIdempotenceKey();

		if ($sum == 0)
		{
			$sum = $payment->getSum();
		}

		$params = array(
			'amount' => array(
				'value' => (string)PriceMaths::roundPrecision($sum),
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
			$error = 'Yandex.Checkout: confirm: '.join("\n", $sendResult->getErrorMessages());
			PaySystem\Logger::addError($error);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	private function getHeaders(Payment $payment)
	{
		$headers = [
			'Content-Type' => 'application/json',
		];

		try
		{
			$headers['Authorization'] = $this->getAuthorizationHeader($payment);
		}
		catch (\Exception $ex)
		{
			$headers['Authorization'] = 'Basic '.$this->getBasicAuthString($payment);
		}

		return $headers;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 */
	private function getAuthorizationHeader(Payment $payment)
	{
		if (Main\Config\Option::get('sale', 'YANDEX_CHECKOUT_OAUTH', false) == true)
		{
			$token = $this->getYandexToken(self::AUTH_TYPE);
			return 'Bearer '.$token;
		}

		return 'Basic '.$this->getBasicAuthString($payment);
	}

	/**
	 * @param $authType
	 * @return mixed|null
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 */
	private function getYandexToken($authType)
	{
		if (!Main\Loader::includeModule('seo'))
		{
			return null;
		}

		$authAdapter = \Bitrix\Seo\Checkout\Service::getAuthAdapter($authType);
		return $authAdapter->getToken();
	}

	/**
	 * @param Payment $payment
	 * @param $refundableSum
	 * @return array
	 * @throws Main\ArgumentNullException
	 */
	private function getRefundQueryParams(Payment $payment, $refundableSum)
	{
		return array(
			'payment_id' => $payment->getField('PS_INVOICE_ID'),
			'amount' => array(
				'value' => (string)PriceMaths::roundPrecision($refundableSum),
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
			{
				return false;
			}

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
			static::MODE_SMART => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SMART'),
			static::MODE_BANK_CARD=> Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_BANK_CARDS'),
			static::MODE_YANDEX_MONEY => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_YANDEX_MONEY'),
			static::MODE_SBERBANK => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SBERBANK'),
			static::MODE_SBERBANK_SMS => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_SBERBANK_SMS'),
			static::MODE_QIWI => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_QIWI'),
			static::MODE_WEBMONEY => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_WEBMONEY'),
			static::MODE_ALFABANK => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ALFABANK'),
			static::MODE_CASH => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_CASH'),
			static::MODE_EMBEDDED => Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_EMBEDDED'),
		);
	}

	/**
	 * @param $psMode
	 * @return mixed
	 */
	protected function getYandexHandlerType($psMode)
	{
		$handlersMap = array(
			static::MODE_SMART => static::PAYMENT_METHOD_SMART,
			static::MODE_ALFABANK => static::PAYMENT_METHOD_ALFABANK,
			static::MODE_BANK_CARD => static::PAYMENT_METHOD_BANK_CARD,
			static::MODE_YANDEX_MONEY => static::PAYMENT_METHOD_YANDEX_MONEY,
			static::MODE_SBERBANK => static::PAYMENT_METHOD_SBERBANK,
			static::MODE_SBERBANK_SMS => static::PAYMENT_METHOD_SBERBANK,
			static::MODE_QIWI => static::PAYMENT_METHOD_QIWI,
			static::MODE_WEBMONEY => static::PAYMENT_METHOD_WEBMONEY,
			static::MODE_CASH => static::PAYMENT_METHOD_CASH,
			static::MODE_EMBEDDED => static::PAYMENT_METHOD_EMBEDDED,
		);

		if (array_key_exists($psMode, $handlersMap))
		{
			return $handlersMap[$psMode];
		}

		return $psMode;
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
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\ObjectException
	 */
	public static function isMyResponse(Request $request, $paySystemId)
	{
		$inputStream = static::readFromStream();

		if ($inputStream)
		{
			$data = static::decode($inputStream);
			if ($data === false)
			{
				return false;
			}

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
			static::MODE_ALFABANK => array('login'),
			static::MODE_QIWI => array('phone'),
			static::MODE_MOBILE_BALANCE => array('phone'),
			static::MODE_SBERBANK_SMS => array('phone'),
		);

		if (isset($paymentMethodFields[$this->service->getField('PS_MODE')]))
		{
			return $paymentMethodFields[$this->service->getField('PS_MODE')];
		}

		return [];
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
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param $field
	 * @return bool
	 */
	private function isPhone($field)
	{
		$paymentMethodPhoneFields = $this->getPhoneFields();

		$phoneFields = [];
		if (isset($paymentMethodPhoneFields[$this->service->getField('PS_MODE')]))
		{
			$phoneFields = $paymentMethodPhoneFields[$this->service->getField('PS_MODE')];
		}

		return in_array($field, $phoneFields);
	}

	/**
	 * @return array
	 */
	private function getPhoneFields()
	{
		return [
			static::MODE_QIWI => ['phone'],
			static::MODE_MOBILE_BALANCE => ['phone'],
			static::MODE_SBERBANK_SMS => ['phone'],
		];
	}

	/**
	 * @param $number
	 * @return bool|string|string[]|null
	 */
	private function normalizePhone($number)
	{
		$normalizedNumber = \NormalizePhone($number);

		if ($normalizedNumber)
		{
			return $normalizedNumber;
		}

		return $number;
	}

	/**
	 * @return bool
	 */
	private function hasPaymentMethodFields()
	{
		$fields = $this->getPaymentMethodFields();
		return (bool)$fields;
	}
}
