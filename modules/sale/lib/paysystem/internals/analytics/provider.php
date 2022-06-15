<?php
namespace Bitrix\Sale\PaySystem\Internals\Analytics;

use Bitrix\Sale;
use Bitrix\Main;

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
			];
		}

		return $result;
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
