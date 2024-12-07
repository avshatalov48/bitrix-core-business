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
	/** @var array */
	private $options = [];

	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		$result = [];

		$vars = get_object_vars($this);

		foreach ($vars as $name => $value)
		{
			if ($name === 'options')
			{
				continue;
			}

			if (is_null($value))
			{
				continue;
			}

			$result[$this->castToUnderscore($name)] = $value;
		}

		foreach ($this->options as $optionCode => $optionValue)
		{
			$result[$optionCode] = $optionValue;
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

	/**
	 * @param array $options
	 * @return RequestEntity
	 */
	public function setOptions(array $options): RequestEntity
	{
		$this->options = $options;

		return $this;
	}
}
