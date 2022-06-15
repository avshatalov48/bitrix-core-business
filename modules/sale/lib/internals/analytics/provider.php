<?php
namespace Bitrix\Sale\Internals\Analytics;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

/**
 * Class Provider
 * @package Bitrix\Sale\Internals\Analytics
 * @internal
 */
abstract class Provider
{
	/**
	 * @return string
	 */
	abstract public static function getCode(): string;

	/**
	 * @return bool
	 */
	abstract protected function needProvideData(): bool;

	/**
	 * @return array
	 */
	abstract protected function getProviderData(): array;

	/**
	 * @return array
	 */
	public function getData(): array
	{
		$result = [];

		if ($this->needProvideData())
		{
			$data = $this->getProviderData();
			if ($data)
			{
				$result = $data;
			}
		}

		return $result;
	}
}
