<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

use Bitrix\Main\Text\Encoding;

/**
 * Trait RequestEntityTrait
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
trait RequestEntityTrait
{
	/**
	 * @inheritdoc
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
	private function castToUnderscore(string $name)
	{
		return strtolower(preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $name));
	}
}
