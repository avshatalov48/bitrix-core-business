<?php

namespace Bitrix\Location\Infrastructure\Service\Config;

use \Bitrix\Location\Exception\ErrorCodes;

final class Container
{
	private $configValues = [];

	public function __construct(array $configValues)
	{
		$this->configValues = $configValues;
	}

	public function get($id)
	{
		if(!$this->has($id))
		{
			throw new \LogicException(
				"Sevice configuration container does not contain '{$id}' value",
				ErrorCodes::SERVICE_CONFIG_VALUE_NOT_FOUND
			);
		}

		return $this->configValues[$id];
	}

	public function has($id)
	{
		return array_key_exists($id, $this->configValues);
	}
}