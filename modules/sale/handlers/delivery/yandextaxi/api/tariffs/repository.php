<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\Tariffs;

use Bitrix\Main\Web\Json;

/**
 * Class Repository
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\Tariffs
 * @internal
 */
final class Repository
{
	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getTariffs(): array
	{
		$tariffs = Json::decode(file_get_contents(__DIR__ . '/tariffs.json'));

		if (!is_array($tariffs))
		{
			return [];
		}

		if (!is_array($tariffs['available_tariffs']))
		{
			return [];
		}

		return $tariffs['available_tariffs'];
	}
}
