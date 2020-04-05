<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\Internals\YandexSettingsTable;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;

Loc::loadMessages(__FILE__);

class YandexInvoiceHandler extends PaySystem\ServiceHandler
{
	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$errors = '';

		if ($request === null)
		{
			$instance = Application::getInstance();
			$context = $instance->getContext();
			$request = $context->getRequest();
		}

		$serviceResult = new PaySystem\ServiceResult();

		if ($request->get('phone') !== null)
		{
			$payload = array(
				'payer' => array('phone' => $request->get('phone')),
				'recipient' => array(
					'shopId' => $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ID'),
					'shopArticleId' => $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ARTICLE_ID'),
				),
				'order' => array(
					'clientOrderId' => $this->getBusinessValue($payment, 'PAYMENT_ID'),
					'customerId' => $request->get('phone'),
					'amount' => $this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY'),
					'currency' => $payment->getField('CURRENCY'),
					'parameters' => array(
						'pay_system_id' => $this->service->getField('ID')
					)
				),
				'schemes' => array($this->service->getField('PS_MODE'))
			);

			$params = $this->prepareRequest($payment, $payload);

			$http = new Web\HttpClient();
			$http->setCharset("utf-8");

			$result = $http->post($this->getUrl($payment, 'payments'), array('request' => $params));
			if ($result)
			{
				try
				{
					$result = Web\Json::decode($result);
				}
				catch (ArgumentException $e)
				{
					$errors .= $e->getMessage();
				}

				if ($errors === '')
				{
					if (in_array($result['status'], array('Created', 'Approved')))
					{
						$scheme = current($result['schemes']);
						if ($scheme)
						{
							$billUrl = $this->getUrl($payment, 'bill');
							$payload = array(
								'payer' => array('phone' => $request->get('phone')),
								'scheme' => $scheme['scheme'],
								'orderId' => $result['orderId'],
							);

							$http = new Web\HttpClient();
							$http->setCharset("utf-8");
							$result = $http->post($billUrl, array('request' => $this->prepareRequest($payment, $payload)));
							if ($result)
							{
								try
								{
									$result = Web\Json::decode($result);
								}
								catch (ArgumentException $e)
								{
									$errors .= $e->getMessage();
								}

								if (in_array($result['status'], array('Authorized', 'Processing')))
									$serviceResult->setPsData(array('PS_INVOICE_ID' => $result['orderId']));
								else
									$errors .= $result['error'];
							}
							else
							{
								$errors .= implode("\n", $http->getError());
							}
						}
					}
					else if ($result['status'] == 'Refused')
					{
						$errors .= $result['error'];
					}
				}
			}
			else
			{
				$errors .= implode("\n", $http->getError());
			}

			if ($errors === '')
			{
				$templateName = 'success';
				$this->setExtraParams(array('PAYMENT_SUM' => $payment->getSum()));
			}
			else
			{
				$serviceResult->addError(new Error($errors));
				$templateName = 'failure';
			}

			$template = $this->showTemplate($payment, $templateName);
			$serviceResult->setTemplate($template->getTemplate());
		}
		else
		{
			/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
			$paymentCollection = $payment->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			$order = $paymentCollection->getOrder();

			$this->setExtraParams(
				array(
					'ORDER_ID' => $order->getId(),
					'ACCOUNT_NUMBER' => $order->getField('ACCOUNT_NUMBER'),
					'PAYSYSTEM_ID' => $this->service->getField('ID')
				)
			);

			return $this->showTemplate($payment, 'template');
		}

		return $serviceResult;
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
	 * @return mixed
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$serviceResult = new PaySystem\ServiceResult();

		if ($request->get('request') === null)
			return $serviceResult;

		list($header, $payload, $sign) = explode('.', $request->get('request'));
		if (!$this->isSignCorrect($payment, $header.'.'.$payload, $sign))
		{
			$payload = Web\Json::decode(self::base64Decode($payload));

			$data = array(
				'notificationType' => 'PaymentAviso',
				'orderId' => $payload['orderId'],
				'shopId' => $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ID'),
				'shopArticleId' => $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ARTICLE_ID'),
				'status' => 'Refused',
				'error' => 'IllegalSignature'
			);

			$serviceResult->setData(array('response' => $this->prepareRequest($payment, $data)));

			return $serviceResult;
		}

		$header = Web\Json::decode(self::base64Decode($header));
		$payload = Web\Json::decode(self::base64Decode($payload));

		if ($payload['notificationType'] === 'PaymentAviso' && $header['iss'] === 'Yandex.Money')
		{
			return $this->processNoticeAction($payment, $payload);
		}

		$data = array(
			'notificationType' => 'PaymentAviso',
			'orderId' => $payload['orderId'],
			'shopId' => $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ID'),
			'shopArticleId' => $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ARTICLE_ID'),
			'status' => 'Refused',
			'error' => 'SyntaxError'
		);

		$serviceResult->setData(array('response' => $this->prepareRequest($payment, $data)));

		return $serviceResult;
	}

	/**
	 * @param Payment $payment
	 * @param array $payload
	 * @return PaySystem\ServiceResult
	 */
	private function processNoticeAction(Payment $payment, array $payload)
	{
		$serviceResult = new PaySystem\ServiceResult();

		$data = array(
			'notificationType' => 'PaymentAviso',
			'orderId' => $payload['orderId'],
			'shopId' => $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ID'),
			'shopArticleId' => $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ARTICLE_ID'),
		);

		$paymentPrice = PriceMaths::roundPrecision($this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY'));
		$yandexPrice = PriceMaths::roundPrecision($payload['order']['amount']);
		if ($yandexPrice === $paymentPrice)
		{
			$serviceResult->setOperationType($serviceResult::MONEY_COMING);
			$psData = array(
				'PS_INVOICE_ID' => $payload['orderId'],
				'PS_STATUS' => ($payload['status'] == 'Authorized') ? 'Y' : 'N',
				'PS_SUM' => $payload['order']['amount'],
				'PS_CURRENCY' => substr($payload['order']['currency'], 0, 3),
				'PS_RESPONSE_DATE' => new DateTime(),
			);
			$serviceResult->setPsData($psData);

			$data['status'] = 'Delivered';
		}
		else
		{
			$data['status'] = 'Refused';
			$data['error'] = 'SyntaxError';
			$serviceResult->addError(new Error(Loc::getMessage('SALE_HPS_YANDEX_INVOICE_ERROR_SUM')));
		}

		$serviceResult->setData(array('response' => $this->prepareRequest($payment, $data)));
		return $serviceResult;
	}

	/**
	 * @param Request $request
	 * @return string
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		list($header, $payload, $sign) = explode('.', $request->get('request'));
		if ($payload)
		{
			$payload = Web\Json::decode(self::base64Decode($payload));
			return $payload['order']['clientOrderId'];
		}

		return '';
	}

	/**
	 * @return array
	 */
	protected function getUrlList()
	{

		return array(
			'test' => array(
				self::TEST_URL => 'http://angius.yandex.ru:8082/payment-api/json-api/api/version',
				self::ACTIVE_URL => 'https://money.yandex.ru/api/v2/version'
			),
			'payments' => array(
				self::TEST_URL => 'http://angius.yandex.ru:8082/payment-api/json-api/api/payments',
				self::ACTIVE_URL => 'https://money.yandex.ru/api/v2/payments'
			),
			'bill' => array(
				self::TEST_URL => 'http://angius.yandex.ru:8082/payment-api/json-api/api/mobileInvoice',
				self::ACTIVE_URL => 'https://money.yandex.ru/api/v2/payments/mobileInvoice'
			)
		);
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList()
	{
		return array(
			'Sberbank' => Loc::getMessage('SALE_HPS_YANDEX_INVOICE_SBERBANK'),
			'Wallet' => Loc::getMessage('SALE_HPS_YANDEX_INVOICE_WALLET')
		);
	}

	/**
	 * @param Payment $payment
	 * @param array $payload
	 * @return string
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	private function prepareRequest(Payment $payment, array $payload)
	{
		$header = array(
			"alg" => "ES256",
			"iat" => round(microtime(true) * 1000),
			"iss" => "shopId:".$this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ID'),
			"aud" => $this->isTestMode($payment) ? 'test' : 'production'
		);

		$jsonHeader = Web\Json::encode((object)$header);
		$jsonPayload = Web\Json::encode((object)$payload);

		$data = self::base64Encode($jsonHeader).'.'.self::base64Encode($jsonPayload);

		$sign = '';
		$shopId = $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ID');
		if ($shopId)
		{
			$dbRes = YandexSettingsTable::getById($shopId);
			if ($settings = $dbRes->fetch())
				$sign = $this->sign($data, $this->getKeyResource($settings['PKEY']));
		}
		return $data.'.'.self::base64Encode($sign);
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	static private function base64Encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	static private function base64Decode($data)
	{
		return base64_decode(strtr($data, '-_', '+/'));
	}

	/**
	 * @param PaySystem\ServiceResult $result
	 * @param Request $request
	 * @return mixed
	 */
	public function sendResponse(PaySystem\ServiceResult $result, Request $request)
	{
		global $APPLICATION;
		$APPLICATION->RestartBuffer();

		$data = $result->getData();
		echo $data['response'];

		$APPLICATION->FinalActions();
		die();
	}

	/**
	 * @param Payment $payment
	 * @param $data
	 * @param $sign
	 * @return bool
	 */
	private function isSignCorrect(Payment $payment, $data, $sign)
	{
		$binary = self::base64Decode($sign);
		list($r, $s) = str_split($binary, (int) (strlen($binary) / 2));

		$r = ltrim($r, "\x00");
		$s = ltrim($s, "\x00");

		if (ord($r[0]) > 0x7f) $r = "\x00" . $r;
		if (ord($s[0]) > 0x7f) $s = "\x00" . $s;

		$binary = PaySystem\ASN1::encodeDER(
			PaySystem\ASN1::SEQUENCE,
			PaySystem\ASN1::encodeDER(PaySystem\ASN1::INTEGER_TYPE, $r).PaySystem\ASN1::encodeDER(PaySystem\ASN1::INTEGER_TYPE, $s),
			false
		);

		$shopId = $this->getBusinessValue($payment, 'YANDEX_INVOICE_SHOP_ID');
		$dbRes = YandexSettingsTable::getById($shopId);
		if ($settings = $dbRes->fetch())
		{
			$verify = openssl_verify($data, $binary, $settings['PUB_KEY'], 'SHA256');
			return $verify === 1;
		}

		return false;
	}

	/**
	 * @param $input
	 * @param $keyResource
	 * @return null|string
	 */
	private function sign($input, $keyResource)
	{
		$signature = null;
		$signAlgo = version_compare(phpversion(), '5.4.8', '<') ? 'SHA256' : OPENSSL_ALGO_SHA256;

		$r = openssl_sign($input, $signature, $keyResource, $signAlgo);

		if ($r === true)
		{
			$offset = 0;
			$offset += PaySystem\ASN1::readDER($signature, $offset, $value);
			$offset += PaySystem\ASN1::readDER($signature, $offset, $r);
			$offset += PaySystem\ASN1::readDER($signature, $offset, $s);

			$r = ltrim($r, "\x00");
			$s = ltrim($s, "\x00");

//			$r = str_pad($r, $keyResource->getSize() / 8, "\x00", STR_PAD_LEFT);
//			$s = str_pad($s, $keyResource->getSize() / 8, "\x00", STR_PAD_LEFT);

			$signature = $r . $s;
		}

		return $signature;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	private function getKeyResource($key)
	{
		if (is_resource($key))
			return $key;

		return openssl_pkey_get_public($key) ?: openssl_pkey_get_private($key);
	}

	/**
	 * @param Payment|null $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return $this->getBusinessValue($payment, 'PS_IS_TEST') == 'Y';
	}

	/**
	 * @return array
	 */
	static public function getIndicativeFields()
	{
		return array('request');
	}

	/**
	 * @param Request $request
	 * @param $paySystemId
	 * @return bool
	 */
	protected static function isMyResponseExtended(Request $request, $paySystemId)
	{
		list($header, $payload, $sign) = explode('.', $request->get('request'));
		if ($payload)
		{
			$payload = Web\Json::decode(self::base64Decode($payload));

			if (!array_key_exists('parameters', $payload['order']))
				return false;

			if (!array_key_exists('pay_system_id', $payload['order']['parameters']))
				return false;

			return $paySystemId == $payload['order']['parameters']['pay_system_id'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isTuned()
	{
		$personTypeList = PaySystem\Manager::getPersonTypeIdList($this->service->getField('ID'));
		$personTypeId = array_shift($personTypeList);
		$shopId = BusinessValue::get('YANDEX_INVOICE_SHOP_ID', $this->service->getConsumerName(), $personTypeId);

		return !empty($shopId);
	}
}