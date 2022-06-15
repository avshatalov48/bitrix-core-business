<?php

namespace Bitrix\Sale\Delivery\Internals\Analytics;

use Bitrix\Sale\Internals\Analytics;

/**
 * Class Provider
 * @package Bitrix\Sale\Delivery\Internals\Analytics
 * @internal
 */
final class Provider extends Analytics\Provider
{
	/** @var string */
	private $code;

	/** @var array */
	private $orders;

	/**
	 * @param string $code
	 * @param array $orders
	 */
	public function __construct(string $code, array $orders)
	{
		$this->code = $code;
		$this->orders = $orders;
	}

	/**
	 * @inheritDoc
	 */
	public static function getCode(): string
	{
		return 'delivery';
	}

	/**
	 * @inheritDoc
	 */
	protected function needProvideData(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function getProviderData(): array
	{
		if (!$this->orders)
		{
			return [];
		}

		return [
			'delivery' => $this->code,
			'orders' => $this->orders,
		];
	}
}
