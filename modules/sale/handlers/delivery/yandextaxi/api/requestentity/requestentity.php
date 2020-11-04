<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

use Bitrix\Main\Text\Encoding;

/**
 * Class Base
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
abstract class RequestEntity implements \JsonSerializable
{
	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		$result = [];

		$vars = get_object_vars($this);

		foreach ($vars as $name => $value)
		{
			if (is_null($value))
			{
				continue;
			}

			$result[$this->castToUnderscore($name)] = Encoding::convertEncoding(
				$value,
				SITE_CHARSET,
				'UTF-8'
			);
		}

		return $result;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function castToUnderscore(string $name)
	{
		return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $name));
	}
}
