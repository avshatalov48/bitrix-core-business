<?php
namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Error;
use Bitrix\Main\Request;
use Bitrix\Main\Context;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PriceMaths;
use Bitrix\Sale\Cashbox;

Loc::loadMessages(__FILE__);

/**
 * Class RoboxchangeHandler
 * @package Sale\Handlers\PaySystem
 */
class RoboxchangeHandler extends PaySystem\ServiceHandler implements PaySystem\Cashbox\ISupportPrintCheck
{
	use PaySystem\Cashbox\CheckTrait;

	public const TEMPLATE_TYPE_CHECKOUT = 'checkout';
	public const TEMPLATE_TYPE_IFRAME = 'iframe';

	protected const DEFAULT_TEMPLATE_NAME = 'template';

	private const ANALYTICS_LABEL_RU_VALUE = 'api_1c-bitrix';
	private const ANALYTICS_LABEL_KZ_VALUE = 'api_1c-bitrix_kz';

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		if ($request === null)
		{
			$request = Context::getCurrent()->getRequest();
		}

		$receipt = null;
		if ($this->service->canPrintCheckSelf($payment))
		{
			$receiptResult = $this->getReceipt($payment);
			if (!$receiptResult->isSuccess())
			{
				$result = new PaySystem\ServiceResult();
				$result->addErrors($receiptResult->getErrors());
				return $result;
			}

			$receipt = self::encode($receiptResult->getData());

			PaySystem\Logger::addDebugInfo(__CLASS__.": receipt = {$receipt}");
		}

		$additionalUserFields = $this->getAdditionalUserFields($payment, $request);

		$params = [
			'URL' => $this->getUrl($payment, 'pay'),
			'PS_MODE' => self::getHandlerModeAlias($this->service->getField('PS_MODE')),
			'SIGNATURE_VALUE' => $this->getSignatureValue($payment, $receipt, $additionalUserFields),
			'ROBOXCHANGE_ORDERDESCR' => $this->getOrderDescription($payment),
			'PAYMENT_ID' => $this->getBusinessValue($payment, 'PAYMENT_ID'),
			'SUM' => PriceMaths::roundPrecision($payment->getSum()),
			'CURRENCY' => $payment->getField('CURRENCY'),
			'OUT_SUM_CURRENCY' => $this->getOutSumCurrency($payment),
			'ADDITIONAL_USER_FIELDS' => $additionalUserFields,
			'RECEIPT' => $receipt,
		];
		$this->setExtraParams($params);

		return $this->showTemplate($payment, $this->getTemplateName($payment));
	}

	/**
	 * @param Payment $payment
	 * @return array
	 */
	private function getAdditionalUserFields(Payment $payment, Request $request): array
	{
		$countryCode = $this->getCountryCode($payment);

		$additionalUserFields = [
			'SHP_BX_PAYMENT_CODE' => $payment->getField('XML_ID'),
			'SHP_BX_PAYSYSTEM_CODE' => $this->service->getField('ID'),
			'SHP_HANDLER' => 'ROBOXCHANGE',
			'SHP_PARTNER' => $countryCode === 'RU' ? self::ANALYTICS_LABEL_RU_VALUE : self::ANALYTICS_LABEL_KZ_VALUE,
			'SHP_BX_REDIRECT_URL' => $request->get('SHP_BX_REDIRECT_URL') ?: $this->getReturnUrl(),
		];
		ksort($additionalUserFields);

		return $additionalUserFields;
	}

	/**
	 * @param Payment $payment
	 * @param string|null $receipt
	 * @param array $additionalUserFields
	 * @return string
	 */
	private function getSignatureValue(Payment $payment, string $receipt = null, array $additionalUserFields = []): string
	{
		$passwordCode = 'ROBOXCHANGE_SHOPPASSWORD';
		if ($this->isTestMode($payment))
		{
			$passwordCode .= '_TEST';
		}

		$shopPassword1 = (string)$this->getBusinessValue($payment, $passwordCode);

		$signaturePartList = [
			$this->getBusinessValue($payment, 'ROBOXCHANGE_SHOPLOGIN'),
			$payment->getSum(),
			$this->getBusinessValue($payment, 'PAYMENT_ID'),
		];

		if ($receipt)
		{
			$signaturePartList[] = $receipt;
		}

		if ($outSumCurrency = $this->getOutSumCurrency($payment))
		{
			$signaturePartList[] = $outSumCurrency;
		}

		$signaturePartList[] = $shopPassword1;

		if ($additionalUserFields)
		{
			foreach ($additionalUserFields as $fieldName => $fieldValue)
			{
				$signaturePartList[] = implode('=', [$fieldName, $fieldValue]);
			}
		}

		return md5(implode(':', $signaturePartList));
	}

	/**
	 * @param Payment $payment
	 * @return false|string
	 */
	private function getOrderDescription(Payment $payment)
	{
		return mb_substr($this->getBusinessValue($payment, 'ROBOXCHANGE_ORDERDESCR'), 0, 100);
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getTemplateName(Payment $payment): string
	{
		$templateType = (string)$this->getBusinessValue($payment, 'ROBOXCHANGE_TEMPLATE_TYPE');
		if (empty($templateType) || $templateType === self::TEMPLATE_TYPE_CHECKOUT)
		{
			return static::DEFAULT_TEMPLATE_NAME;
		}

		return $templateType;
	}

	/**
	 * @return array
	 */
	public static function getIndicativeFields()
	{
		return ['SHP_HANDLER' => 'ROBOXCHANGE'];
	}

	/**
	 * @param Request $request
	 * @param $paySystemId
	 * @return bool
	 */
	protected static function isMyResponseExtended(Request $request, $paySystemId)
	{
		$id = (int)$request->get('SHP_BX_PAYSYSTEM_CODE');
		return $id === (int)$paySystemId;
	}

	/**
	 * @param Payment $payment
	 * @param $request
	 * @return bool
	 */
	private function isCorrectHash(Payment $payment, Request $request): bool
	{
		$passwordCode2 = 'ROBOXCHANGE_SHOPPASSWORD2';
		if ($this->isTestMode($payment))
		{
			$passwordCode2 .= '_TEST';
		}

		$shopPassword2 = (string)$this->getBusinessValue($payment, $passwordCode2);

		$signaturePartList = [
			$request->get('OutSum'),
			$request->get('InvId'),
			$shopPassword2,
		];

		foreach ($this->getAdditionalUserFields($payment, $request) as $fieldName => $fieldValue)
		{
			$signaturePartList[] = implode('=', [$fieldName, $fieldValue]);
		}

		$hash = md5(implode(':', $signaturePartList));

		return ToUpper($hash) === ToUpper($request->get('SignatureValue'));
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
		return $request->get('InvId');
	}

	/**
	 * @return mixed
	 */
	protected function getUrlList()
	{
		return [
			'pay' => [
				self::ACTIVE_URL => 'https://auth.robokassa.ru/Merchant/Index.aspx',
			],
		];
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function processRequest(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		if ($this->isCorrectHash($payment, $request))
		{
			return $this->processNoticeAction($payment, $request);
		}

		$result->addError(new Error(Loc::getMessage('SALE_HPS_ROBOXCHANGE_INCORRECT_HASH')));

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function processNoticeAction(Payment $payment, Request $request): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$psStatusDescription = Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_NUMBER').": ".$request->get('InvId');
		$psStatusDescription .= "; ".Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_DATEPAY').": ".date("d.m.Y H:i:s");

		if ($request->get("IncCurrLabel") !== null)
		{
			$psStatusDescription .= "; ".Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_PAY_TYPE').": ".$request->get("IncCurrLabel");
		}

		$result->setPsData([
			"PS_STATUS" => "Y",
			"PS_STATUS_CODE" => "-",
			"PS_STATUS_DESCRIPTION" => $psStatusDescription,
			"PS_STATUS_MESSAGE" => Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_PAYED'),
			"PS_SUM" => $payment->getSum(),
			"PS_CURRENCY" => $payment->getField('CURRENCY'),
			"PS_RESPONSE_DATE" => new DateTime(),
		]);

		PaySystem\Logger::addDebugInfo(
			__CLASS__.': PS_CHANGE_STATUS_PAY='.$this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY')
		);

		if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') === 'Y')
		{
			$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
		}

		return $result;
	}

	/**
	 * @param Payment|null $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return $this->getBusinessValue($payment, 'PS_IS_TEST') === 'Y';
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return ['RUB', 'KZT', 'USD', 'EUR'];
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getOutSumCurrency(Payment $payment): string
	{
		$countryCode = $this->getCountryCode($payment);

		$currency = (string)$payment->getField('CURRENCY');
		$currency = ($currency === 'RUB') ? 'RUR' : $currency;

		if (
			($countryCode === 'RU' && $currency === 'RUR')
			|| ($countryCode === 'KZ' && $currency === 'KZT')
		)
		{
			$currency = '';
		}

		return $currency;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	private function getCountryCode(Payment $payment): string
	{
		$countryCode = (string)$this->getBusinessValue($payment, 'ROBOXCHANGE_COUNTRY_CODE');
		return $countryCode ?: 'RU';
	}

	/**
	 * @param PaySystem\ServiceResult $result
	 * @param Request $request
	 * @return mixed|string|void
	 */
	public function sendResponse(PaySystem\ServiceResult $result, Request $request)
	{
		global $APPLICATION;
		if ($result->isResultApplied())
		{
			$APPLICATION->RestartBuffer();
			echo 'OK'.$request->get('InvId');
		}
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList()
	{
		return [
			'bank_card' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_BANKCARD_MODE'),
			'apple_pay' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_APPLEPAY_MODE'),
			'google_pay' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_GOOGLEPAY_MODE'),
			'samsung_pay' => Loc::getMessage('SALE_HPS_ROBOXCHANGE_SAMSUNGPAY_MODE'),
		];
	}

	private static function getHandlerModeAlias(string $psMode): string
	{
		$defaultAlias = 'BankCard';

		$aliases = [
			'bank_card' => 'BankCard',
			'apple_pay' => 'ApplePay',
			'google_pay' => 'GooglePay',
			'samsung_pay' => 'SamsungPay',
		];

		return $aliases[$psMode] ?? $defaultAlias;
	}

	private function getReceipt(Payment $payment): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$checkQueryResult = $this->buildCheckQuery($payment);
		if ($checkQueryResult->isSuccess())
		{
			$receiptData = $checkQueryResult->getData();
			if (!empty($receiptData['items']) && !empty($receiptData['sno']))
			{
				$result->setData([
					'sno' => $receiptData['sno'],
					'items' => $receiptData['items'],
				]);
			}
			else
			{
				$result->addError(PaySystem\Error::create(Loc::getMessage('SALE_HPS_ROBOXCHANGE_ERROR_EMPTY_RECEIPT')));
			}
		}
		else
		{
			$result->addErrors($checkQueryResult->getErrors());
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function encode(array $data)
	{
		return Web\Json::encode($data, JSON_UNESCAPED_UNICODE);
	}

	public static function getCashboxClass(): string
	{
		return '\\'.Cashbox\CashboxRobokassa::class;
	}

	private function getReturnUrl(): string
	{
		return $this->service->getContext()->getUrl();
	}
}