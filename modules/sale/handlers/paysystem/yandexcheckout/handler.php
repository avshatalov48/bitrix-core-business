<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Localization;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Cashbox;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Services\Base\RestrictionInfo;
use Bitrix\Sale\Services\Base\RestrictionInfoCollection;
use Bitrix\Seo;

Localization\Loc::loadMessages(__FILE__);

/**
 * Class YandexCheckout
 * @package Sale\Handlers\PaySystem
 */
class YandexCheckoutHandler
	extends PaySystem\ServiceHandler
	implements
	PaySystem\IRefund,
	PaySystem\IPartialHold,
	PaySystem\IRecurring,
	PaySystem\Cashbox\ISupportPrintCheck,
	PaySystem\Cashbox\IFiscalizationAware
{
	const CMS_NAME = 'api_1c-bitrix';

	/**
	 * @deprecated
	 */
	public const PAYMENT_STATUS_WAITING_FOR_CAPTURE = 'waiting_for_capture';
	/**
	 * @deprecated
	 */
	public const PAYMENT_STATUS_PENDING = 'pending';
	/**
	 * @deprecated
	 */
	public const AUTH_TYPE = 'yandex';

	public const PAYMENT_STATUS_SUCCEEDED = 'succeeded';
	public const PAYMENT_STATUS_CANCELED = 'canceled';

	public const PAYMENT_METHOD_SMART = '';
	public const PAYMENT_METHOD_ALFABANK = 'alfabank';
	public const PAYMENT_METHOD_BANK_CARD = 'bank_card';
	public const PAYMENT_METHOD_YANDEX_MONEY = 'yoo_money';
	public const PAYMENT_METHOD_SBERBANK = 'sberbank';
	public const PAYMENT_METHOD_QIWI = 'qiwi';
	public const PAYMENT_METHOD_CASH = 'cash';
	public const PAYMENT_METHOD_EMBEDDED = 'embedded';
	public const PAYMENT_METHOD_TINKOFF_BANK = 'tinkoff_bank';
	public const PAYMENT_METHOD_SBP = 'sbp';
	public const PAYMENT_METHOD_INSTALLMENTS = 'installments';

	public const MODE_SMART = '';
	public const MODE_ALFABANK = 'alfabank';
	public const MODE_BANK_CARD = 'bank_card';
	public const MODE_YANDEX_MONEY = 'yoo_money';
	public const MODE_SBERBANK = 'sberbank';
	public const MODE_SBERBANK_SMS = 'sberbank_sms';
	public const MODE_SBERBANK_QR = 'sberbank_qr';
	public const MODE_QIWI = 'qiwi';
	public const MODE_CASH = 'cash';
	public const MODE_MOBILE_BALANCE = 'mobile_balance';
	public const MODE_EMBEDDED = 'embedded';
	public const MODE_TINKOFF_BANK = 'tinkoff_bank';
	public const MODE_SBP = 'sbp';
	public const MODE_INSTALLMENTS = 'installments';

	public const URL = 'https://api.yookassa.ru/v3';

	private const CALLBACK_IP_LIST = [
		'185.71.76.0/27',
		'185.71.77.0/27',
		'77.75.153.0/25',
		'77.75.154.128/25',
		'77.75.156.11',
		'77.75.156.35',
	];

	private const CONFIRMATION_TYPE_REDIRECT = "redirect";
	private const CONFIRMATION_TYPE_EXTERNAL = "external";
	private const CONFIRMATION_TYPE_EMBEDDED = "embedded";
	private const CONFIRMATION_TYPE_QR = 'qr';

	private const SEND_METHOD_HTTP_POST = "POST";
	private const SEND_METHOD_HTTP_GET = "GET";

	use PaySystem\Cashbox\CheckTrait;

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
	public function initiatePay(Payment $payment, Request $request = null)
	{
		if ($request === null)
		{
			$request = Main\Context::getCurrent()->getRequest();
		}

		$result = new PaySystem\ServiceResult();

		$yandexPaymentData = [];

		if ($payment->getField("PS_INVOICE_ID"))
		{
			$yandexPaymentResult = $this->getYandexPayment($payment);
			if ($yandexPaymentResult->isSuccess())
			{
				$yandexPaymentData = $yandexPaymentResult->getData();
			}
		}

		$isNeedCreate = $this->needCreateYandexPayment($payment, $request, $yandexPaymentData);
		if ($isNeedCreate)
		{
			$createYandexPaymentResult = $this->createYandexPayment($payment, $request);
			if (!$createYandexPaymentResult->isSuccess())
			{
				return $createYandexPaymentResult;
			}

			$yandexPaymentData = $createYandexPaymentResult->getData();

			if (isset($yandexPaymentData['id']))
			{
				$result->setPsData(['PS_INVOICE_ID' => $yandexPaymentData['id']]);
			}
		}

		$template = $this->getTemplateName($request, $yandexPaymentData);

		$this->setExtraParams(
			$this->getTemplateParams($payment, $template, $yandexPaymentData)
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

		if ($isNeedCreate && !empty($yandexPaymentData['confirmation']['confirmation_url']))
		{
			$result->setPaymentUrl($yandexPaymentData['confirmation']['confirmation_url']);
		}

		if (!empty($yandexPaymentData['confirmation']['confirmation_data']) && $this->isQrPaymentType())
		{
			$qrCode = self::generateQrCode($yandexPaymentData['confirmation']['confirmation_data']);
			if ($qrCode)
			{
				$result->setQr(base64_encode($qrCode));
			}
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @param array $additionalParams
	 * @return bool
	 */
	protected function needCreateYandexPayment(Payment $payment, Request $request, $additionalParams = []): bool
	{
		if (($additionalParams['status'] ?? '') === self::PAYMENT_STATUS_SUCCEEDED)
		{
			return false;
		}

		$template = $this->getTemplateName($request, $additionalParams);

		return $template !== 'template_query';
	}

	/**
	 * @param Payment $payment
	 * @param $template
	 * @param array $additionalParams
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
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
			$phoneFields = $phoneFields[$this->service->getField('PS_MODE')] ?? [];

			$params['FIELDS'] = $this->getPaymentMethodFields();
			$params['PHONE_FIELDS'] = $phoneFields;
			$params['PHONE_NUMBER'] = $this->getPhoneNumber($payment) ?? "";
			$params['PAYMENT_METHOD'] = $this->service->getField('PS_MODE');
			$params['PAYMENT_ID'] = $payment->getId();
			$params['PAYSYSTEM_ID'] = $this->service->getField('ID');
			$params['RETURN_URL'] = $this->getReturnUrl($payment);
		}
		elseif ($template === 'template_embedded')
		{
			$params['CONFIRMATION_TOKEN'] = $additionalParams['confirmation']['confirmation_token'] ?? '';
			$params['RETURN_URL'] = $this->getReturnUrl($payment);
		}
		elseif ($template === 'template_qr')
		{
			$params['URL'] = $additionalParams['confirmation']['confirmation_data'] ?? '';
			$params['QR_CODE_IMAGE'] = '';

			$qrCode = self::generateQrCode($params['URL']);
			if ($qrCode)
			{
				$params['QR_CODE_IMAGE'] = base64_encode($qrCode);
			}
		}

		return $params;
	}

	/**
	 * @param Request $request
	 * @param array $additionalParams
	 * @return string
	 */
	protected function getTemplateName(Request $request, $additionalParams = []): string
	{
		$template = null;

		if (isset($additionalParams["status"])
			&& $additionalParams["status"] === self::PAYMENT_STATUS_SUCCEEDED
		)
		{
			return "template_success";
		}

		if ($this->hasPaymentMethodFields() &&
			!$this->isFillPaymentMethodFields($request)
		)
		{
			$template = "template_query";
		}
		elseif ($this->isSetExternalPaymentType())
		{
			$template = "template_success";
		}
		elseif ($this->isSetEmbeddedPaymentType())
		{
			$template = "template_embedded";
		}
		elseif ($this->isQrPaymentType())
		{
			$template = "template_qr";
		}

		return $template ?? "template";
	}

	/**
	 * @return bool
	 */
	private function isSetExternalPaymentType(): bool
	{
		$externalPayment = [
			static::MODE_ALFABANK,
			static::MODE_SBERBANK_SMS
		];

		return in_array($this->service->getField('PS_MODE'), $externalPayment, true);
	}

	/**
	 * @return bool
	 */
	private function isSetEmbeddedPaymentType(): bool
	{
		return $this->service->getField('PS_MODE') === static::MODE_EMBEDDED;
	}

	private function isQrPaymentType(): bool
	{
		$qrPayment = [
			static::MODE_SBP,
			static::MODE_SBERBANK_QR,
		];

		return in_array($this->service->getField('PS_MODE'), $qrPayment, true);
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @param bool $isRepeated
	 * @return PaySystem\ServiceResult
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectException
	 * @throws Main\SystemException
	 */
	private function createYandexPayment(Payment $payment, Request $request, bool $isRepeated = false): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'pay');
		$headers = $this->getHeaders($payment);

		if ($isRepeated)
		{
			$params = $this->getYandexRepeatedPaymentQueryParams($payment, $request);
		}
		else
		{
			$params = $this->getYandexPaymentQueryParams($payment, $request);
		}

		if ($this->service->canPrintCheckSelf($payment))
		{
			$receiptResult = $this->getReceipt($payment);
			if (!$receiptResult->isSuccess())
			{
				$result->addErrors($receiptResult->getErrors());
				return $result;
			}

			$receiptData = $receiptResult->getData();
			$params['receipt'] = $receiptData['receipt'];

			PaySystem\Logger::addDebugInfo(__CLASS__ . ": receipt = " . self::encode($receiptData['receipt']));
		}

		$sendResult = $this->send(self::SEND_METHOD_HTTP_POST, $url, $headers, $params);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
			return $result;
		}

		$response = $sendResult->getData();

		$verificationResult = $this->verifyYandexPayment($response);
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
	private function verifyYandexPayment($response): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		if ($response['status'] === static::PAYMENT_STATUS_CANCELED)
		{
			$error = Localization\Loc::getMessage(
				'SALE_HPS_YANDEX_CHECKOUT_RESPONSE_ERROR_' . mb_strtoupper($response['cancellation_details']['reason'])
			);
			if ($error)
			{
				$result->addError(
					PaySystem\Error::createForBuyer($error, $response['cancellation_details']['party'])
				);
			}
			else
			{
				$result->addError(
					PaySystem\Error::create(Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_PAYMENT_CANCELED'))
				);
			}
		}

		return $result;
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
	 * @param $method
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
	private function send($method, $url, array $headers, array $params = array()): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$httpClient = new HttpClient();
		foreach ($headers as $name => $value)
		{
			$httpClient->setHeader($name, $value);
		}

		if ($method === self::SEND_METHOD_HTTP_GET)
		{
			$response = $httpClient->get($url);
		}
		else
		{
			$postData = null;
			if ($params)
			{
				$postData = static::encode($params);
			}

			PaySystem\Logger::addDebugInfo(__CLASS__.': request data: '.$postData);

			$response = $httpClient->post($url, $postData);
		}

		if ($response === false)
		{
			$errors = $httpClient->getError();
			if ($errors)
			{
				$errorMessages = [];
				foreach ($errors as $code => $message)
				{
					$errorMessages[] = "{$code}={$message}";
				}

				PaySystem\Logger::addDebugInfo(
					__CLASS__ . ': response error: ' . implode(', ', $errorMessages)
				);
			}

			$result->addError(new Main\Error(Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_QUERY')));
			return $result;
		}

		PaySystem\Logger::addDebugInfo(__CLASS__.': response data: '.$response);

		$response = static::decode($response);

		$httpStatus = $httpClient->getStatus();
		if ($httpStatus === 200)
		{
			$result->setData($response);
		}
		elseif ($httpStatus !== 201)
		{
			if ($httpStatus === 401 && self::isOAuth())
			{
				$error = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_HTTP_STATUS_OAUTH_'.$httpStatus.'');
			}
			else
			{
				$error = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_HTTP_STATUS_'.$httpStatus);
			}

			if ($error)
			{
				$result->addError(PaySystem\Error::create($error));
			}
			elseif (isset($response['type']) && $response['type'] === 'error')
			{
				$result->addError(PaySystem\Error::create($response['description']));
			}
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	private function getYandexBasePaymentQueryParams(Payment $payment): array
	{
		return [
			'description' => $this->getPaymentDescription($payment),
			'amount' => [
				'value' => (string)PriceMaths::roundPrecision($payment->getSum()),
				'currency' => $payment->getField('CURRENCY'),
			],
			'capture' => true,
			'metadata' => [
				'BX_PAYMENT_NUMBER' => $payment->getId(),
				'BX_PAYSYSTEM_CODE' => $this->service->getField('ID'),
				'BX_HANDLER' => 'YANDEX_CHECKOUT',
				'cms_name' => static::CMS_NAME,
			],
		];
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	protected function getYandexPaymentQueryParams(Payment $payment, Request $request)
	{
		$query = $this->getYandexBasePaymentQueryParams($payment);

		$query['confirmation'] = [
			'type' => self::CONFIRMATION_TYPE_REDIRECT,
			'return_url' => $this->getReturnUrl($payment),
		];

		$articleId = $this->getBusinessValue($payment, 'YANDEX_CHECKOUT_SHOP_ARTICLE_ID');
		if ($articleId)
		{
			$query['recipient'] = ['gateway_id' => $articleId];
		}

		if ($this->isSetEmbeddedPaymentType())
		{
			$query['confirmation'] = [
				'type' => self::CONFIRMATION_TYPE_EMBEDDED,
			];
		}
		elseif ($this->isQrPaymentType())
		{
			$query['confirmation'] = [
				'type' => self::CONFIRMATION_TYPE_QR,
			];
			$query['payment_method_data'] = [
				'type' => $this->getYandexHandlerType($this->service->getField('PS_MODE')),
			];
		}
		elseif ($this->service->getField('PS_MODE') !== static::MODE_SMART)
		{
			$query['capture'] = true;
			$query['payment_method_data'] = [
				'type' => $this->getYandexHandlerType($this->service->getField('PS_MODE'))
			];

			if ($this->isSetExternalPaymentType())
			{
				$query['confirmation'] = [
					'type' => self::CONFIRMATION_TYPE_EXTERNAL,
				];
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

		if ($this->isRecurring($payment) && !self::isOAuth())
		{
			$query['save_payment_method'] = true;
		}

		return $query;
	}

	private function getReceipt(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$checkQueryResult = $this->buildCheckQuery($payment);
		if ($checkQueryResult->isSuccess())
		{
			$receiptData = $checkQueryResult->getData();
			if (!empty($receiptData['items']) && !empty($receiptData['customer']))
			{
				$result->setData([
					'receipt' => $receiptData,
				]);
			}
			else
			{
				$result->addError(PaySystem\Error::create(Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_EMPTY_RECEIPT')));
			}
		}
		else
		{
			$result->addErrors($checkQueryResult->getErrors());
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
	 * @throws Main\SystemException
	 */
	private function getYandexRepeatedPaymentQueryParams(Payment $payment, Request $request): array
	{
		$query = $this->getYandexBasePaymentQueryParams($payment);
		$query['payment_method_id'] = $payment->getField('PS_RECURRING_TOKEN');

		return $query;
	}

	/**
	 * @param Payment $payment
	 * @return mixed|string
	 */
	private function getReturnUrl(Payment $payment)
	{
		return $this->getBusinessValue($payment, 'YANDEX_CHECKOUT_RETURN_URL') ?: $this->service->getContext()->getUrl();
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

		return mb_substr($description, 0, 128);
	}

	/**
	 * @param Payment $payment
	 * @return string|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getPhoneNumber(Payment $payment): ?string
	{
		$phoneNumber = null;

		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();

		if ($order instanceof \Bitrix\Crm\Order\Order
			&& $clientCollection = $order->getContactCompanyCollection()
		)
		{
			$primaryClient = $clientCollection->getPrimaryContact();
			$entityId = \CCrmOwnerType::ContactName;

			if ($primaryClient === null)
			{
				$primaryClient = $clientCollection->getPrimaryCompany();
				$entityId = \CCrmOwnerType::CompanyName;
			}

			if ($primaryClient)
			{
				$clientId = $primaryClient->getField('ENTITY_ID');
				$crmFieldMultiResult = \CCrmFieldMulti::GetList(
					['ID' => 'desc'],
					[
						'ENTITY_ID' => $entityId,
						'ELEMENT_ID' => $clientId,
						'TYPE_ID' => 'PHONE',
					]
				);
				while ($crmFieldMultiData = $crmFieldMultiResult->Fetch())
				{
					$phoneNumber = $crmFieldMultiData['VALUE'];
					if ($phoneNumber)
					{
						break;
					}
				}
			}
		}

		if (!$phoneNumber)
		{
			$phoneNumberProp = $order->getPropertyCollection()->getPhone();
			if ($phoneNumberProp)
			{
				$phoneNumber = $phoneNumberProp->getValue();
			}
		}

		return $phoneNumber ? $this->normalizePhone($phoneNumber) : null;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getBasicAuthString(Payment $payment): string
	{
		return base64_encode(
			trim((string)$this->getBusinessValue($payment, 'YANDEX_CHECKOUT_SHOP_ID'))
			. ':'
			. trim((string)$this->getBusinessValue($payment, 'YANDEX_CHECKOUT_SECRET_KEY'))
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
	 * @return array|string[]
	 */
	public function getCurrencyList()
	{
		return ['RUB'];
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$checkIpResult = $this->checkIpAddress();
		if (!$checkIpResult->isSuccess())
		{
			$result->addErrors($checkIpResult->getErrors());
			return $result;
		}

		$inputStream = static::readFromStream();

		$data = static::decode($inputStream);
		if ($data !== false)
		{
			$response = $data['object'];
			if ($response['status'] === static::PAYMENT_STATUS_SUCCEEDED)
			{
				$this->processSuccessRequest($payment, $response, $result);
			}
			elseif (
				$response['status'] === static::PAYMENT_STATUS_CANCELED
				&& $payment->getField('PS_INVOICE_ID') === $response['id']
			)
			{
				$this->processCancelRequest($response, $result);
			}
		}
		else
		{
			$result->addError(PaySystem\Error::create(Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_QUERY')));
		}

		return $result;
	}

	private function processSuccessRequest(Payment $payment, array $response, PaySystem\ServiceResult $result): void
	{
		$description = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_TRANSACTION') . $response['id'];
		$fields = [
			'PS_INVOICE_ID' => $response['id'],
			'PS_STATUS_CODE' => $response['status'],
			'PS_STATUS_DESCRIPTION' => $description,
			'PS_SUM' => $response['amount']['value'],
			'PS_STATUS' => 'N',
			'PS_CURRENCY' => $response['amount']['currency'],
			'PS_RESPONSE_DATE' => new Main\Type\DateTime()
		];

		if ($response['payment_method']['saved'])
		{
			$fields['PS_RECURRING_TOKEN'] = $response['payment_method']['id'];
		}

		if ($this->isSumCorrect($payment, $response))
		{
			$fields["PS_STATUS"] = 'Y';

			PaySystem\Logger::addDebugInfo(
				__CLASS__ . ': PS_CHANGE_STATUS_PAY=' . $this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
			);

			if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y')
			{
				$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
			}
		}
		else
		{
			$error = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_SUM');
			$fields['PS_STATUS_DESCRIPTION'] .= ' ' . $error;
			$result->addError(PaySystem\Error::create($error));
		}

		$result->setPsData($fields);
	}

	private function processCancelRequest(array $response, PaySystem\ServiceResult $result): void
	{
		$cancellationParty = $response['cancellation_details']['party'];
		$cancellationReason = $response['cancellation_details']['reason'];

		$party = Localization\Loc::getMessage(
			'SALE_HPS_YANDEX_CHECKOUT_REQUEST_CANCEL_PARTY_' . mb_strtoupper($cancellationParty)
		);
		if (!$party)
		{
			$party = $cancellationParty;
		}

		$reason = Localization\Loc::getMessage(
			'SALE_HPS_YANDEX_CHECKOUT_REQUEST_CANCEL_REASON_' . mb_strtoupper($cancellationReason)
		);
		if (!$reason)
		{
			$reason = $cancellationReason;
		}

		$description = implode(
			'. ',
			[
				Localization\Loc::getMessage(
					'SALE_HPS_YANDEX_CHECKOUT_REQUEST_CANCEL_PARTY',
					[
						'#PARTY#' => $party,
					]
				),
				Localization\Loc::getMessage(
					'SALE_HPS_YANDEX_CHECKOUT_REQUEST_CANCEL_REASON',
					[
						'#REASON#' => $reason,
					]
				)
			]
		);

		$fields = [
			'PS_STATUS_CODE' => $response['status'],
			'PS_STATUS' => 'N',
			'PS_RESPONSE_DATE' => new Main\Type\DateTime(),
			'PS_STATUS_DESCRIPTION' => $description,
		];

		$result->setPsData($fields);
		$result->addError(
			PaySystem\Error::create(Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_PAYMENT_CANCELED'))
		);
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
			__CLASS__.': yandexSum='.PriceMaths::roundPrecision($paymentData['amount']['value'])."; paymentSum=".PriceMaths::roundPrecision($payment->getSum())
		);

		return PriceMaths::roundPrecision($paymentData['amount']['value']) === PriceMaths::roundPrecision($payment->getSum());
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
	private function getYandexPayment(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$url = $this->getUrl($payment, 'payment');
		$headers = $this->getHeaders($payment);

		$sendResult = $this->send(self::SEND_METHOD_HTTP_GET, $url, $headers);
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

		$sendResult = $this->send(self::SEND_METHOD_HTTP_POST, $url, $headers, $params);
		if (!$sendResult->isSuccess())
		{
			$result->addErrors($sendResult->getErrors());
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

		$sendResult = $this->send(self::SEND_METHOD_HTTP_POST, $url, $headers);
		if (!$sendResult->isSuccess())
		{
			$error = __CLASS__.': cancel: '.implode("\n", $sendResult->getErrorMessages());
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

		$sendResult = $this->send(self::SEND_METHOD_HTTP_POST, $url, $headers, $params);
		if ($sendResult->isSuccess())
		{
			$response = $sendResult->getData();
			$description = Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_TRANSACTION').$response['id'];

			$fields = array(
				"PS_STATUS_CODE" => mb_substr($response['status'], 0, 5),
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
			$error = __CLASS__.': confirm: '.join("\n", $sendResult->getErrorMessages());
			PaySystem\Logger::addError($error);
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	private function getHeaders(Payment $payment): array
	{
		$headers = [
			'Content-Type' => 'application/json',
			'Idempotence-Key' => $this->getIdempotenceKey(),
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
	private function getAuthorizationHeader(Payment $payment): string
	{
		if (self::isOAuth())
		{
			$token = $this->getYandexToken();
			return 'Bearer '.$token;
		}

		return 'Basic '.$this->getBasicAuthString($payment);
	}

	/**
	 * @return mixed|null
	 * @throws Main\LoaderException
	 * @throws Main\SystemException
	 */
	private function getYandexToken()
	{
		if (!Main\Loader::includeModule('seo'))
		{
			return null;
		}

		$authAdapter = Seo\Checkout\Service::getAuthAdapter(Seo\Checkout\Service::TYPE_YOOKASSA);
		$token = $authAdapter->getToken();
		if (!$token)
		{
			$authAdapter = Seo\Checkout\Service::getAuthAdapter(Seo\Checkout\Service::TYPE_YANDEX);
			$token = $authAdapter->getToken();
		}

		return $token;
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
		return PaySystem\Manager::getHandlerDescription('YandexCheckout')['HANDLER_MODE_LIST'];
	}

	/**
	 * @param $psMode
	 * @return mixed
	 */
	protected function getYandexHandlerType($psMode)
	{
		$handlersMap = [
			static::MODE_SMART => static::PAYMENT_METHOD_SMART,
			static::MODE_ALFABANK => static::PAYMENT_METHOD_ALFABANK,
			static::MODE_BANK_CARD => static::PAYMENT_METHOD_BANK_CARD,
			static::MODE_YANDEX_MONEY => static::PAYMENT_METHOD_YANDEX_MONEY,
			static::MODE_SBERBANK => static::PAYMENT_METHOD_SBERBANK,
			static::MODE_SBERBANK_SMS => static::PAYMENT_METHOD_SBERBANK,
			static::MODE_SBERBANK_QR => static::PAYMENT_METHOD_SBERBANK,
			static::MODE_QIWI => static::PAYMENT_METHOD_QIWI,
			static::MODE_CASH => static::PAYMENT_METHOD_CASH,
			static::MODE_EMBEDDED => static::PAYMENT_METHOD_EMBEDDED,
			static::MODE_TINKOFF_BANK => static::PAYMENT_METHOD_TINKOFF_BANK,
			static::MODE_INSTALLMENTS => static::PAYMENT_METHOD_INSTALLMENTS,
			static::MODE_SBP => static::PAYMENT_METHOD_SBP,
		];

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
			'refund' => static::URL.'/refunds',
			'confirm' => static::URL.'/payments/#payment_id#/capture',
			'cancel' => static::URL.'/payments/#payment_id#/cancel',
			'payment' => static::URL.'/payments/#payment_id#',
			'settings' => static::URL.'/me',
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
				|| $action === 'payment'
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
	private function getPaymentMethodFields(): array
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
	private function isFillPaymentMethodFields(Request $request): bool
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
	private function isPhone($field): bool
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
	private function getPhoneFields(): array
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
	private function hasPaymentMethodFields(): bool
	{
		$fields = $this->getPaymentMethodFields();
		return (bool)$fields;
	}

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
	 * @throws Main\SystemException
	 */
	public function repeatRecurrent(Payment $payment, Request $request = null): PaySystem\ServiceResult
	{
		if ($request === null)
		{
			$request = Main\Context::getCurrent()->getRequest();
		}

		return $this->createYandexPayment($payment, $request, true);
	}

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function cancelRecurrent(Payment $payment, Request $request = null): PaySystem\ServiceResult
	{
		return (new PaySystem\ServiceResult());
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	public function isRecurring(Payment $payment): bool
	{
		$modeList = [
			self::MODE_BANK_CARD,
			self::MODE_YANDEX_MONEY,
			self::MODE_EMBEDDED,
		];

		$isPsModeSupport = in_array($this->service->getField("PS_MODE"), $modeList, true);

		return $this->getBusinessValue($payment, 'YANDEX_CHECKOUT_RECURRING') === 'Y'
			&& $isPsModeSupport;
	}

	/**
	 * @return PaySystem\ServiceResult
	 */
	private function checkIpAddress(): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$isFound = false;
		$yandexIp = Main\Context::getCurrent()->getRequest()->getRemoteAddress();
		foreach (self::CALLBACK_IP_LIST as $callbackIp)
		{
			$ipAddress = new Main\Web\IpAddress($yandexIp);
			if ($ipAddress->matchRange($callbackIp))
			{
				$isFound = true;
				break;
			}
		}

		if (!$isFound)
		{
			$result->addError(
				PaySystem\Error::create(
					Localization\Loc::getMessage('SALE_HPS_YANDEX_CHECKOUT_ERROR_CHECK_IP', [
						'#IP_ADDRESS#' => $yandexIp,
					])
				)
			);
			return $result;
		}

		return $result;
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private static function isOAuth(): bool
	{
		/** @noinspection TypeUnsafeComparisonInspection */
		return Main\Config\Option::get('sale', 'YANDEX_CHECKOUT_OAUTH', false) == true;
	}

	public function getRestrictionList(): RestrictionInfoCollection
	{
		$psMode = $this->service->getField('PS_MODE');

		$baseRestrictions = parent::getRestrictionList();
		if ($psMode === self::PAYMENT_METHOD_INSTALLMENTS)
		{
			$restrictionInfo = new RestrictionInfo('Price', [
				'MIN_VALUE' => 3000,
				'MAX_VALUE' => 150000,
			]);

			$baseRestrictions->add($restrictionInfo);
		}
		elseif (
			$psMode === self::PAYMENT_METHOD_SBP
			|| $this->getYandexHandlerType($psMode) === self::PAYMENT_METHOD_SBERBANK
		)
		{
			$restrictionInfo = new RestrictionInfo('Price', [
				'MIN_VALUE' => 1,
				'MAX_VALUE' => 1000000,
			]);

			$baseRestrictions->add($restrictionInfo);
		}

		return $baseRestrictions;
	}

	private static function generateQrCode(string $data): ?string
	{
		return (new PaySystem\BarcodeGenerator())->generate($data);
	}

	public static function getCashboxClass(): string
	{
		return '\\'.Cashbox\CashboxYooKassa::class;
	}

	public function isFiscalizationEnabled(Payment $payment): bool
	{
		$url = $this->getUrl($payment, 'settings');
		$headers = $this->getHeaders($payment);

		$sendResult = $this->send(self::SEND_METHOD_HTTP_GET, $url, $headers);
		if ($sendResult->isSuccess())
		{
			$data = $sendResult->getData();

			return $data['fiscalization']['enabled'] ?? false;
		}

		return false;
	}
}
