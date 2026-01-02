<?php
namespace Sale\Handlers\PaySystem;

use Bitrix\Crm;
use Bitrix\Crm\EntityPreset;
use Bitrix\Crm\EntityRequisite;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\PhoneNumber;
use Bitrix\Main\Request;
use Bitrix\Main\Web;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\PayableBasketItem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaymentCollection;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Services\Base\RestrictionInfoCollection;
use Bitrix\Sale\Services\PaySystem\Restrictions\RestrictableServiceHandler;
use Bitrix\Sale\Services\PaySystem\Restrictions\RestrictionCurrencyTrait;
use Bitrix\Sale\Services\PaySystem\Restrictions\RestrictionPersonTypeTrait;

class TBankBusinessHandler extends PaySystem\BaseServiceHandler implements RestrictableServiceHandler
{
	use RestrictionCurrencyTrait;

	protected const SANDBOX_URL = 'https://business.tbank.ru/openapi/sandbox/';
	protected const PRODUCTION_URL = 'https://business.tbank.ru/openapi/';

	public const ERROR_CODE_SYSTEM_FAILURE = -1;
	public const ERROR_CODE_RESPONSE_FAILURE = -2;
	public const ERROR_CODE_BAD_INVOICE_SUM = -3;
	public const ERROR_CODE_BAD_INVOICE_PARAMS = -4;

	protected const ERROR_STATUS_BAD_JSON = -1;

	protected const PAYMENT_STATUS_DRAFT = 'DRAFT';
	protected const PAYMENT_STATUS_SUBMITTED = 'SUBMITTED';
	protected const PAYMENT_STATUS_EXECUTED = 'EXECUTED';

	protected const PAYMENT_CURRENCY_RUB = 'RUB';

	protected const RESPONSE_STATUS_200 = 200;
	protected const RESPONSE_STATUS_400 = 400;
	protected const RESPONSE_STATUS_401 = 401;
	protected const RESPONSE_STATUS_403 = 403;
	protected const RESPONSE_STATUS_422 = 422;
	protected const RESPONSE_STATUS_429 = 429;
	protected const RESPONSE_STATUS_500 = 500;

	protected const RESPONSE_ERROR_INVOICE_NOT_FOUND = 'INVOICE_NOT_FOUND';

	protected const TEMPLATE_INVOICE_INFO = 'template';
	protected const TEMPLATE_INVOICE_STATUS = 'template_status';
	protected const TEMPLATE_INVOICE_EXPIRED = 'template_expired';

	public function initiatePay(Payment $payment, ?Request $request = null): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		if (!Loader::includeModule('crm'))
		{
			$result->addError(new Main\Error(Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_MODULE_CRM_ABSENT')));

			return $result;
		}

		$invoiceState = [];
		if ($payment->getField('PS_INVOICE_ID'))
		{
			$checkInvoiceResult = $this->checkInvoiceState($payment);
			if ($this->isInvalidCheckInvoiceResult($checkInvoiceResult))
			{
				return $checkInvoiceResult;
			}
			$invoiceState = $checkInvoiceResult->getData();
			unset($checkInvoiceResult);
		}

		if ($this->needCreateInvoice($invoiceState))
		{
			$createInvoiceResult = $this->createInvoice($payment);
			if (!$createInvoiceResult->isSuccess())
			{
				return $createInvoiceResult;
			}

			$invoiceState = $createInvoiceResult->getData();

			if (isset($invoiceState['response']['invoiceId']))
			{
				$result->setPsData(['PS_INVOICE_ID' => $invoiceState['response']['invoiceId']]);
			}
		}

		$template = $this->getTemplateName($payment, $invoiceState);

		$this->setExtraParams(
			$this->getTemplateParams($payment, $template, $invoiceState)
		);

		$result->setTemplate($this->showTemplate($payment, $template)->getTemplate());

		return $result;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList(): array
	{
		return [
			self::PAYMENT_CURRENCY_RUB,
		];
	}

	/**
	 * Returns list of restrictions that installed on service add.
	 * Add person type restriction.
	 *
	 * @return RestrictionInfoCollection
	 */
	public function getRestrictionList(): RestrictionInfoCollection
	{
		$collection = new RestrictionInfoCollection();

		$this->getRestrictionCurrency($collection);

		return $collection;
	}

	/**
	 * Returns true, if payment in test mode.
	 * @param Payment|null $payment Payment information.
	 * @return bool
	 */
	protected function isTestMode(?Payment $payment = null): bool
	{
		return ($this->getBusinessValue($payment, 'TBB_TEST_MODE') === 'Y');
	}

	/**
	 * @inheritDoc
	 */
	public function getClientType($psMode): string
	{
		return PaySystem\ClientType::B2B;
	}

	/**
	 * @return array
	 */
	protected function getUrlList(): array
	{
		return [
			'invoice' => [
				self::TEST_URL => self::SANDBOX_URL . 'api/v1/invoice/send',
				self::ACTIVE_URL => self::PRODUCTION_URL.'api/v1/invoice/send',
			],
			'invoiceCheck' => [
				self::TEST_URL => self::SANDBOX_URL . 'api/v1/openapi/invoice/{invoiceId}/info',
				self::ACTIVE_URL => self::PRODUCTION_URL . 'api/v1/openapi/invoice/{invoiceId}/info',
			],
		];
	}

	/**
	 * Returns string with T-Bank authorization token from seo-proxy server.
	 *
	 * @return string|null
	 */
	protected function getTBankAuthToken(): ?string
	{
		if (!Main\Loader::includeModule('seo'))
		{
			return null;
		}

		\Bitrix\Seo\Service::clearClientsCache();
		$authAdapter = \Bitrix\Seo\Checkout\Service::getAuthAdapter(\Bitrix\Seo\Checkout\Service::TYPE_TBANK_BUSINESS);

		return $authAdapter->getToken();
	}

	/**
	 * Returns T-Bank sandbox authorization token.
	 *
	 * @return string
	 */
	protected function getTBankTestToken(): string
	{
		return 'TinkoffOpenApiSandboxSecretToken';
	}

	/**
	 * Returns query headers.
	 *
	 * @param Payment $payment Payment information.
	 * @return array
	 */
	protected function getHeaders(Payment $payment): array
	{
		$token =
			$this->isTestMode($payment)
				? $this->getTBankTestToken()
				: $this->getTBankAuthToken()
		;

		return [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $token,
		];
	}

	/**
	 * @param Payment $payment
	 * @param array $invoiceStatus
	 * @return string
	 */
	protected function getTemplateName(Payment $payment, array $invoiceStatus = []): string
	{
		$template = null;

		if (
			$this->isExistDatePayBefore($payment)
			&& !$this->isValidDatePayBefore($payment)
		)
		{
			$template = self::TEMPLATE_INVOICE_EXPIRED;
		}
		else
		{
			$currentStatus = ($invoiceStatus['response']['status'] ?? '');
			if ($this->isValidPaymentStatus($currentStatus))
			{
				$template = self::TEMPLATE_INVOICE_STATUS;
			}
		}

		return $template ?? self::TEMPLATE_INVOICE_INFO;
	}

	protected function getTemplateParams(Payment $payment, $template, $invoiceState = []) : array
	{
		$result = [];
		switch ($template)
		{
			case self::TEMPLATE_INVOICE_INFO:
				$result = $this->getInvoiceSumTemplateParams($payment);

				if (isset($invoiceState['response']['pdfUrl']))
				{
					$result['INVOICE_PDF_URL'] = $invoiceState['response']['pdfUrl'];
				}
				if (isset($invoiceState['response']['incomingInvoiceUrl']))
				{
					$result['INVOICE_PERSONAL_ACCOUNT_URL'] = $invoiceState['response']['incomingInvoiceUrl'];
				}

				if (isset($invoiceState['params']['dueDate']))
				{
					$dueDate = new Main\Type\Date(
						$invoiceState['params']['dueDate'],
						'Y-m-d'
					);
					$result['INVOICE_DUE_DATE'] = $dueDate->toString();
				}
				break;
			case self::TEMPLATE_INVOICE_STATUS:
				$result = $this->getInvoiceSumTemplateParams($payment);

				$result['INVOICE_STATUS'] = $this->getInvoiceStatusTitle(
					$invoiceState['response']['status'] ?? ''
				);
				break;
		}

		return $result;
	}

	/**
	 * Returns invoice sum for templates.
	 *
	 * @param Payment $payment Payment description.
	 * @return array
	 */
	protected function getInvoiceSumTemplateParams(Payment $payment): array
	{
		Loader::includeModule('currency');

		$result = [];

		$invoiceSum = $this->getInvoiceSum($payment);
		if ($invoiceSum !== null)
		{
			$result['INVOICE_SUM'] = $invoiceSum;
			$result['INVOICE_SUM_FORMATTED'] = \CCurrencyLang::CurrencyFormat(
				$invoiceSum,
				self::PAYMENT_CURRENCY_RUB
			);
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return string|false
	 */
	private static function encode(array $data): bool|string
	{
		try
		{
			return Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
		}
		catch (Main\ArgumentException)
		{
			return false;
		}
	}

	/**
	 * @param string $data
	 * @return mixed
	 */
	private static function decode(string $data): mixed
	{
		try
		{
			return Web\Json::decode($data);
		}
		catch (Main\ArgumentException)
		{
			return false;
		}
	}

	protected function checkInvoiceState(Payment $payment): PaySystem\ServiceResult
	{
		$url = str_replace(
			'{invoiceId}',
			$payment->getField('PS_INVOICE_ID'),
			$this->getUrl($payment, 'invoiceCheck')
		);

		return $this->send(
			Web\Http\Method::GET,
			$url,
			$this->getHeaders($payment)
		);
	}

	protected function isInvalidCheckInvoiceResult(PaySystem\ServiceResult $result): bool
	{
		if ($result->isSuccess())
		{
			return false;
		}

		if ($this->isInvoiceNotFound($result->getData()))
		{
			return false;
		}

		return true;
	}

	protected function isInvoiceNotFound(array $invoiceState): bool
	{
		$status = (int)($invoiceState['status'] ?? 0);
		$errorCode = $invoiceState['response']['errorCode'] ?? '';

		return
			$status === self::RESPONSE_STATUS_422
			&& $errorCode === self::RESPONSE_ERROR_INVOICE_NOT_FOUND
		;
	}

	protected function needCreateInvoice(array $invoiceState = []): bool
	{
		if (empty($invoiceState))
		{
			return true;
		}
		$currentStatus = ($invoiceState['response']['status'] ?? '');
		if ($this->isValidPaymentStatus($currentStatus))
		{
			return false;
		}

		if ($this->isInvoiceNotFound($invoiceState))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 */
	protected function createInvoice(Payment $payment): PaySystem\ServiceResult
	{
		if (!$this->isInvoiceSumCorrect($payment))
		{
			$result = new PaySystem\ServiceResult();
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_BAD_INVOICE_SUM'),
				self::ERROR_CODE_BAD_INVOICE_SUM
			));

			return $result;
		}

		$invoiceQueryParams = $this->getInvoiceQueryParams($payment);
		$result = $this->checkInvoiceQueryParams($invoiceQueryParams);
		if (!$result->isSuccess())
		{
			$this->addDebugInfo(
				Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_INVALID_INVOICE_PARAMS'),
				$invoiceQueryParams
			);

			return $result;
		}

		return $this->send(
			Web\Http\Method::POST,
			$this->getUrl($payment, 'invoice'),
			$this->getHeaders($payment),
			$invoiceQueryParams
		);
	}

	protected function getInvoiceQueryParams(Payment $payment): array
	{
		$result = [
			'invoiceNumber' => (string)$payment->getId(), // digital identifier
			'items' => $this->getProductsForInvoice($payment),
			'contactPhone' => $this->getContactPhone($payment),
		];

		$email = $this->getEmail($payment);
		if ($email)
		{
			$result['contacts'] = [
				[
					'email' => $email,
				]
			];
		}

		$comment = $this->getInvoiceComment($payment);
		if ($comment !== '')
		{
			$result['comment'] = $comment;
		}

		$dueDate = $this->getDueDate($payment);
		if (!empty($dueDate))
		{
			$result['dueDate'] = $dueDate;
		}

		$payer = $this->getPayerData($payment);
		if (!empty($payer))
		{
			$result['payer'] = $payer;
		}

		return $result;
	}

	protected function checkInvoiceQueryParams(array $invoiceQueryParams): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();


		if (empty($invoiceQueryParams['payer']))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_INVALID_PAYER_DATA'),
				self::ERROR_CODE_BAD_INVOICE_PARAMS
			));
		}

		if (empty($invoiceQueryParams['items']))
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_INVALID_INVOICE_ITEMS'),
				self::ERROR_CODE_BAD_INVOICE_PARAMS
			));
		}

		return $result;
	}

	protected function getDueDate(Payment $payment): string
	{
		if (!$this->isValidDatePayBefore($payment))
		{
			return '';
		}
		/** @var Main\Type\Date $datePayBefore */
		$datePayBefore = $payment->getField('DATE_PAY_BEFORE');

		return $datePayBefore->format('Y-m-d');
	}

	protected function getPayerData(Payment $payment): ?array
	{
		$payerData = $this->getCompanyPayerData($payment);

		if (empty($payerData))
		{
			$payerData = $this->getContactPayerData($payment);
		}

		return !empty($payerData) ? $payerData : null;
	}

	protected function getContactPayerData(Payment $payment): ?array
	{
		$result = [];

		$contactFullName = $this->getContactFullName($payment);

		if (!empty($contactFullName))
		{
			$result['name'] = $contactFullName;
		}

		return !empty($result) ? $result : null;
	}

	protected function getCompanyPayerData(Payment $payment): ?array
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();

		$clientCollection = $this->getClientCollection($order);
		if ($clientCollection === null)
		{
			return null;
		}

		$company = $clientCollection->getPrimaryCompany();
		if ($company === null)
		{
			return null;
		}

		$requisite =
			(
				new Crm\Requisite\DefaultRequisite(
					new Crm\ItemIdentifier(
						\CCrmOwnerType::Company,
						(int)$company->getField('ENTITY_ID')
					)
				)
			)
			->setCheckPermissions(false)
		;
		$requisiteValues = $requisite->get();

		$title = trim((string)$company->getCustomerName());
		$inn = trim((string)($requisiteValues['RQ_INN'] ?? ''));
		$kpp = trim((string)($requisiteValues['RQ_KPP'] ?? ''));

		if ($title === '' || $inn === '')
		{
			return null;
		}

		if ($kpp === '')
		{
			if (!isset($requisiteValues['PRESET_ID']))
			{
				return null;
			}

			$companyPreset = EntityPreset::getSingleInstance()->getById($requisiteValues['PRESET_ID']);

			if (
				is_array($companyPreset)
				&& isset($companyPreset['XML_ID'])
				&& (string)$companyPreset['XML_ID'] === EntityRequisite::XML_ID_DEFAULT_PRESET_RU_COMPANY
			)
			{
				return null;
			}

			$kpp = '0';
		}

		return [
			'name' => $title,
			'inn' => $inn,
			'kpp' => $kpp,
		];
	}

	protected function getProductsForInvoice(Payment $payment): array
	{
		$catalogIncluded = Loader::includeModule('catalog');
		/** @var PaymentCollection $paymentCollection */
		$paymentCollection = $payment->getCollection();

		$order = $paymentCollection->getOrder();
		$basket = $order->getBasket();

		$defaultMeasure = '';
		if ($catalogIncluded)
		{
			$measureDescription = \CCatalogMeasure::getDefaultMeasure(true, false);
			$defaultMeasure = $measureDescription['SYMBOL_RUS'];
			unset($measureDescription);
		}

		$result = [];
		// /** @var BasketItem $basketItem */
		/*
		foreach ($basket->getBasketItems() as $basketItem)
		{
			$row = [
				'name' => $basketItem->getField('NAME'),
				'amount' => $basketItem->getQuantity(),
				'price' => $basketItem->getPriceWithVat(),
			];

			$vat = $basketItem->getVatRate();
			if ($vat === null)
			{
				$vat = 'None';
			}
			else
			{
				$vat = (string)((int)((float)$vat * 100));
			}
			$row['vat'] = $vat;
			$row['unit'] = (string)$basketItem->getField('MEASURE_NAME') ?: $defaultMeasure;

			$result[] = $row;
		}
		*/
		/** @var PayableBasketItem $payableBasketItem */
		foreach ($payment->getPayableItemCollection()->getBasketItems() as $payableBasketItem)
		{
			/** @var BasketItem $basketItem */
			$basketItem = $basket->getItemById($payableBasketItem->getField('ENTITY_ID'));
			if ($basketItem)
			{
				$row = [
					'name' => $basketItem->getField('NAME'),
					'amount' => $basketItem->getQuantity(),
					'price' => $basketItem->getPriceWithVat(),
				];

				$vat = $basketItem->getVatRate();
				if ($vat === null)
				{
					$vat = 'None';
				}
				else
				{
					$vat = (string)((int)((float)$vat * 100));
				}
				$row['vat'] = $vat;
				$row['unit'] = (string)$basketItem->getField('MEASURE_NAME') ?: $defaultMeasure;

				$result[] = $row;
			}
		}

		return $result;
	}

	/**
	 * Returns contact phone for sms notification.
	 *
	 * @param Payment $payment Payment description.
	 * @return string
	 */
	protected function getContactPhone(Payment $payment): string
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$phoneNumber = $this->getClientPhoneNumber($collection->getOrder());

		return
			$phoneNumber
				? $this->normalizePhoneNumber($phoneNumber)
				: ''
		;
	}

	protected function getContactFullName(Payment $payment): ?string
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();

		$clientCollection = $this->getClientCollection($order);
		if (!$clientCollection)
		{
			return null;
		}

		$clientFullName = null;

		$clientId = $this->getPrimaryContactId($clientCollection);

		$factory = Crm\Service\Container::getInstance()->getFactory(\CCrmOwnerType::Contact);
		if ($factory)
		{
			$contactItem = $factory->getItem($clientId, ['FULL_NAME']);
			$clientFullName = $contactItem->getFullName();
		}

		return $clientFullName;
	}

	/**
	 * Returns client or company phone number, if exists.
	 *
	 * @param Order $order Crm order.
	 * @return string|null
	 */
	protected function getClientPhoneNumber(Order $order): ?string
	{
		$clientCollection = $this->getClientCollection($order);
		if (!$clientCollection)
		{
			return null;
		}

		$phoneNumber = null;

		$clientId = $this->getPrimaryContactId($clientCollection);
		$entityId = \CCrmOwnerType::ContactName;

		if ($clientId === null)
		{
			$clientId = $this->getPrimaryCompanyId($clientCollection);
			$entityId = \CCrmOwnerType::CompanyName;
		}

		if ($clientId)
		{
			$crmFieldMultiResult = \CCrmFieldMulti::GetList(
				[
					'ID' => 'desc',
				],
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
			unset(
				$crmFieldMultiData,
				$crmFieldMultiResult,
			);
		}

		return $phoneNumber;
	}

	/**
	 * Returns empty string or phone number in E164 format - +71234567890.
	 *
	 * @param string $phoneNumber Raw phone number.
	 * @return string
	 */
	protected function normalizePhoneNumber(string $phoneNumber): string
	{
		$phoneNumber = trim($phoneNumber);
		if ($phoneNumber === '')
		{
			return '';
		}

		$parser = PhoneNumber\Parser::getInstance();
		$number = $parser->parse($phoneNumber);

		return (string)$number->format(PhoneNumber\Format::E164);
	}

	protected function getEmail(Payment $payment): ?string
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();
		$userEmail = $order->getPropertyCollection()->getUserEmail();

		return $userEmail?->getValue();
	}

	protected function getInvoiceComment(Payment $payment): string
	{
		/** @var PaymentCollection $collection */
		$collection = $payment->getCollection();
		$order = $collection->getOrder();

		$description =  str_replace(
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
				(string)$this->getEmail($payment),
			],
			$this->getBusinessValue($payment, 'TBB_COMMENT_TEMPLATE') ?? ''
		);

		return mb_substr(trim($description), 0, 1000);
	}

	protected function getInvoiceSum(Payment $payment): ?float
	{
		/** @var PaymentCollection $paymentCollection */
		$paymentCollection = $payment->getCollection();

		$order = $paymentCollection->getOrder();
		$basket = $order->getBasket();

		$found = false;
		$price = 0.0;
		// /** @var BasketItem $basketItem */
		/*
		foreach ($basket->getBasketItems() as $basketItem)
		{
			$found = true;
			$price += $basketItem->getFinalPrice();
		}
		*/
		/** @var PayableBasketItem $payableBasketItem */
		foreach ($payment->getPayableItemCollection()->getBasketItems() as $payableBasketItem)
		{
			/** @var BasketItem $basketItem */
			$basketItem = $basket->getItemById($payableBasketItem->getField('ENTITY_ID'));
			if ($basketItem)
			{
				$found = true;
				$price += $basketItem->getFinalPrice();
			}
		}

		return
			$found
				? $price
				: null
		;
	}

	/**
	 * Returns true, if payment sum equals calculated basket sum.
	 *
	 * @param Payment $payment Payment object.
	 * @return bool
	 */
	protected function isInvoiceSumCorrect(Payment $payment): bool
	{
		$paymentSum = PriceMaths::roundPrecision($payment->getSum());
		$invoiceSum = $this->getInvoiceSum($payment);

		$this->addDebugInfo(
			'invoiceSum',
			[
				'invoiceSum' => $invoiceSum,
				'paymentSum' => $paymentSum,
			]
		);

		return $paymentSum === $invoiceSum;
	}

	protected function send(string $method, string $url, array $headers, array $params = []): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$httpClient = new Web\HttpClient();
		$httpClient->setHeaders($headers);

		switch ($method)
		{
			case Web\Http\Method::GET:
				$response = $httpClient->get($url);
				break;
			case Web\Http\Method::POST:
				$postData = null;
				if ($params)
				{
					$postData = static::encode($params);
				}
				$this->addDebugInfo('request data', $postData);
				$response = $httpClient->post($url, $postData);
				break;
			default:
				$response = null;
				break;
		}

		if ($response === null)
		{
			$result->addError(new Main\Error(
				Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_UNSUPPORTED_PROTOCOL'),
				self::ERROR_CODE_SYSTEM_FAILURE
			));

			return $result;
		}

		$this->addDebugInfo('response data', $response);

		$status = $httpClient->getStatus();
		$response = static::decode($response);
		if (!is_array($response))
		{
			$status = self::ERROR_STATUS_BAD_JSON;
		}

		switch ($status)
		{
			case self::ERROR_STATUS_BAD_JSON:
				$result->setData([
					'params' => $params,
					'status' => $status,
				]);
				$result->addError(new Main\Error(
					Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_UNKNOWN_ANSWER'),
					self::ERROR_CODE_SYSTEM_FAILURE
				));
				break;
			case self::RESPONSE_STATUS_200:
				$result->setData([
					'params' => $params,
					'response' => $response,
					'status' => $status,
				]);
				break;
			case self::RESPONSE_STATUS_400:
			case self::RESPONSE_STATUS_401:
			case self::RESPONSE_STATUS_403:
			case self::RESPONSE_STATUS_422:
			case self::RESPONSE_STATUS_429:
			case self::RESPONSE_STATUS_500:
			default:
				$result->setData([
					'params' => $params,
					'response' => $response,
					'status' => $status,
				]);
				$result->addError($this->getResponseError($status, $response));
				$this->addDebugInfo('invalid request', $response);
				break;
		}

		return $result;
	}

	protected function addDebugInfo(string $message, mixed $data): void
	{
		if ($message === '')
		{
			return;
		}
		if (is_array($data) || is_bool($data))
		{
			$data = mydump($data);
		}
		elseif (is_object($data))
		{
			if (method_exists($data, '__toString'))
			{
				$data = (string)$data;
			}
			else
			{
				$data = mydump($data);
			}
		}
		PaySystem\Logger::addDebugInfo(__CLASS__ . ': ' . $message . ': ' . $data);
	}

	protected function getResponseError(int $status, mixed $response): Main\Error
	{
		$error = match ($status)
		{
			self::RESPONSE_STATUS_400 => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_400_INVALID_REQUEST'),
			self::RESPONSE_STATUS_401 => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_401_BAD_AUTHENTICATION'),
			self::RESPONSE_STATUS_403 => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_403_BAD_AUTHORIZATION'),
			self::RESPONSE_STATUS_422 => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_422_BAD_DATA'),
			self::RESPONSE_STATUS_429 => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_429_TOO_MANY_REQUESTS'),
			self::RESPONSE_STATUS_500 => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_ERROR_500_SERVER'),
			default => Loc::getMessage(
				'SALE_HPS_TBANK_BUSINESS_ERROR_UNKNOWN_STATUS',
				[
					'#STATUS#' => $status,
				]
			),
		};

		$message = '';
		if (isset($response['errorMessage']))
		{
			$message = $response['errorMessage'];
			if (isset($response['errorDetails']) && is_array($response['errorDetails']))
			{
				$message .= '. ' . implode(', ', $response['errorDetails']);
			}
		}

		return new Main\Error(
			$message ?: $error,
			$response['errorCode'] ?? self::ERROR_CODE_RESPONSE_FAILURE
		);
	}

	protected function getInvoiceStatusTitle(string $status): string
	{
		return (string)match ($status)
		{
			self::PAYMENT_STATUS_DRAFT => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_INVOICE_STATUS_DRAFT'),
			self::PAYMENT_STATUS_SUBMITTED => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_INVOICE_STATUS_SUBMITTED'),
			self::PAYMENT_STATUS_EXECUTED => Loc::getMessage('SALE_HPS_TBANK_BUSINESS_INVOICE_STATUS_EXECUTED'),
			default => '',
		};
	}

	protected function isValidPaymentStatus(string $status): bool
	{
		return
			$status === self::PAYMENT_STATUS_DRAFT
			|| $status === self::PAYMENT_STATUS_SUBMITTED
			|| $status === self::PAYMENT_STATUS_EXECUTED
		;
	}

	protected function isValidDatePayBefore(Payment $payment): bool
	{
		$datePayBefore = $payment->getField('DATE_PAY_BEFORE');
		if ($datePayBefore instanceof Main\Type\Date)
		{
			$currentDate = new Main\Type\Date();
			if ($datePayBefore->getTimestamp() >= $currentDate->getTimestamp())
			{
				return true;
			}
		}

		return false;
	}

	protected function isExistDatePayBefore(Payment $payment): bool
	{
		return $payment->getField('DATE_PAY_BEFORE') instanceof Main\Type\Date;
	}

	protected function getClientCollection(Order $order): ?Crm\Order\ContactCompanyCollection
	{
		if ($order instanceof Crm\Order\Order)
		{
			return $order->getContactCompanyCollection();
		}

		return null;
	}

	protected function getPrimaryCompanyId(Crm\Order\ContactCompanyCollection $collection): ?int
	{
		$company = $collection->getPrimaryCompany();
		if ($company !== null)
		{
			$companyId = $company->getField('ENTITY_ID');

			return $companyId === null ? null : (int)$companyId;
		}

		return null;
	}

	protected function getPrimaryContactId(Crm\Order\ContactCompanyCollection $collection): ?int
	{
		$contact = $collection->getPrimaryContact();
		if ($contact !== null)
		{
			$contactId = $contact->getField('ENTITY_ID');

			return $contactId === null ? null : (int)$contactId;
		}

		return null;
	}
}
