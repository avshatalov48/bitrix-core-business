<?php
namespace Bitrix\Sale\Cashbox\Internals\Analytics;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class Provider
 * @package Bitrix\Sale\Cashbox\Internals\Analytics
 * @internal
 */
final class Provider extends Sale\Internals\Analytics\Provider
{
	private const TYPE = 'cashbox';

	private const CASHBOX_HANDLERS = [
		Sale\Cashbox\CashboxOrangeData::class,
		Sale\Cashbox\CashboxCheckbox::class,
		Sale\Cashbox\CashboxBusinessRu::class,
		Sale\Cashbox\CashboxBusinessRuV5::class,
		Sale\Cashbox\CashboxOrangeDataFfd12::class,
	];

	/** @var Sale\Cashbox\Check */
	private $check;

	/** @var Sale\Cashbox\Cashbox */
	private $cashboxHandler;

	public function __construct(Sale\Cashbox\AbstractCheck $check)
	{
		$this->check = $check;

		$cashboxId = $check->getField('CASHBOX_ID');
		$this->cashboxHandler = Sale\Cashbox\Manager::getObjectById($cashboxId);
	}

	/**
	 * @return string
	 */
	public static function getCode(): string
	{
		return self::TYPE;
	}

	protected function needProvideData(): bool
	{
		$cashboxHandlerCode = $this->cashboxHandler::getCode();

		$isCashboxHandlerExists = (bool)array_filter(
			self::CASHBOX_HANDLERS,
			static function ($cashboxHandler) use ($cashboxHandlerCode) {
				return $cashboxHandlerCode === $cashboxHandler::getCode();
			}
		);

		return $isCashboxHandlerExists && $this->check->getField('STATUS') === 'Y';
	}

	/**
	 * @return array
	 */
	protected function getProviderData(): array
	{
		$checkData = $this->getCheckData();
		return [
			'cashbox' => $this->cashboxHandler::getCode(),
			'date_time' => $checkData['date_time'],
		];
	}

	/**
	 * @return array
	 */
	private function getCheckData(): array
	{
		$dateTime = $this->check->getField('DATE_PRINT_END');
		if (!($dateTime instanceof Main\Type\DateTime))
		{
			$dateTime = new Main\Type\DateTime();
		}

		return [
			'date_time' => $dateTime->format('Y-m-d H:i:s'),
		];
	}
}
