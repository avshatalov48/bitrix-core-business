<?php
namespace Bitrix\Sale\PaySystem\Internals\Analytics;

use Bitrix\Sale;
use Bitrix\Main;
use Bitrix\Sale\BusinessValue;

/**
 * Class Provider
 * @package Bitrix\Sale\PaySystem\Internals\Analytics
 * @internal
 */
final class Provider extends Sale\Internals\Analytics\Provider
{
	private const TYPE = 'paysystem';

	private const PAY_SYSTEM_HANDLERS = [
		\Sale\Handlers\PaySystem\YandexCheckoutHandler::class,
		\Sale\Handlers\PaySystem\WooppayHandler::class,
		\Sale\Handlers\PaySystem\RoboxchangeHandler::class,
		\Sale\Handlers\PaySystem\PlatonHandler::class,
	];

	/** @var Sale\Payment */
	private $payment;

	/** @var Sale\PaySystem\Service */
	private $paySystemService;

	public function __construct(Sale\Payment $payment)
	{
		$this->payment = $payment;
		$this->paySystemService = $this->payment->getPaySystem();
	}

	/**
	 * @return string
	 */
	public static function getCode(): string
	{
		return self::TYPE;
	}

	/**
	 * @return bool
	 */
	protected function needProvideData(): bool
	{
		if (!$this->paySystemService)
		{
			return false;
		}

		$actionFile = $this->paySystemService->getField('ACTION_FILE');
		$paySystemClassName = strtolower(Sale\PaySystem\Manager::getClassNameFromPath($actionFile));

		$isPaySystemExists = (bool)array_filter(
			self::PAY_SYSTEM_HANDLERS,
			static function ($paySystemHandler) use ($paySystemClassName) {
				return strtolower($paySystemHandler) === $paySystemClassName;
			}
		);

		return $isPaySystemExists && $this->payment->isPaid();
	}

	/**
	 * @return array
	 */
	protected function getProviderData(): array
	{
		$result = [];

		$paymentData = $this->getPaymentData();
		if ($paymentData)
		{
			$result = [
				'pay_system' => $this->paySystemService->getField('ACTION_FILE'),
				'transactions' => $paymentData,
				'ps_mode' => $this->paySystemService->getField('PS_MODE'),
				'sum' => $this->payment->getField('SUM'),
				'shopId' => $this->getShopId(),
			];
		}

		return $result;
	}

	private function getShopId(): ?string
	{
		$actionFile = $this->paySystemService->getField('ACTION_FILE');
		if ($actionFile === 'yandexcheckout')
		{
			$paysytemId = $this->paySystemService->getField('ID');
			$shopId = BusinessValue::getMapping(
				'YANDEX_CHECKOUT_SHOP_ID',
				'PAYSYSTEM_' . $paysytemId,
				null,
				[
					'MATCH' => BusinessValue::MATCH_EXACT
				]
			);
			if (empty($shopId))
			{
				$shopId = BusinessValue::getMapping(
					'YANDEX_CHECKOUT_SHOP_ID',
					'PAYSYSTEM_' . $paysytemId,
					null,
					[
						'MATCH' => BusinessValue::MATCH_COMMON
					]
				);
			}
			if (isset($shopId['PROVIDER_VALUE']) && $shopId['PROVIDER_VALUE'])
			{
				return (int)$shopId['PROVIDER_VALUE'];
			}

			if (!\Bitrix\Main\Loader::includeModule('seo'))
			{
				return null;
			}

			$yookassa = new \Bitrix\Seo\Checkout\Services\AccountYookassa();
			$yookassa->setService(\Bitrix\Seo\Checkout\Service::getInstance());

			return $yookassa->getProfile() ? $yookassa->getProfile()['ID'] : null;
		}

		if ($actionFile === 'roboxchange')
		{
			$robokassaShopSettings = (new \Bitrix\Sale\PaySystem\Robokassa\ShopSettings())->get();

			return $robokassaShopSettings['ROBOXCHANGE_SHOPLOGIN'] ?? null;
		}

		return null;
	}

	/**
	 * @return array
	 */
	private function getPaymentData(): array
	{
		$result = [];

		$externalId = $this->payment->getField('PS_INVOICE_ID');
		$date = $this->payment->getField('PS_RESPONSE_DATE') ?: $this->payment->getField('DATE_PAID');

		if (!($date instanceof Main\Type\DateTime))
		{
			$date = new Main\Type\DateTime();
		}

		$date = $date->format('Y-m-d H:i:s');

		$result[] = [
			'id' => $externalId ?: $this->payment->getField('XML_ID'),
			'date_time' => $date,
		];

		return $result;
	}
}
