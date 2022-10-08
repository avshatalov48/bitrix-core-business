<?php

namespace Bitrix\Location\Infrastructure\Service\Config;

/**
 * Interface IServiceConfigFactory
 * @package Bitrix\Location\Infrastructure\Service\Config
 */
interface IFactory
{
	/**
	 * @param string $serviceType Service class
	 * @return Container|bool
	 */
	public static function createConfig(string $serviceType): Container;
}