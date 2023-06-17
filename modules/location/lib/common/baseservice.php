<?php

namespace Bitrix\Location\Common;

use Bitrix\Location\Exception\RuntimeException;
use Bitrix\Location\Infrastructure\Service\Config\Container;
use Bitrix\Location\Infrastructure\Service\Config\Factory;
use Bitrix\Location\Infrastructure\Service\ErrorService;
use Bitrix\Main\Error;

abstract class BaseService
{
	/** @var static */
	protected static $instance;

	public function __clone(){}
	public function __wakeup(){}

	/**
	 * @return static
	 */
	public static function getInstance()
	{
		if (empty(static::$instance))
		{
			static::$instance = static::createInstance(
				static::getConfig(static::class)
			);
		}

		return static::$instance;
	}

	protected static function createInstance(Container $config)
	{
		return new static($config);
	}

	protected function processException(\Exception $exception): void
	{
		ErrorService::getInstance()->addError(
			new Error($exception->getMessage(), $exception->getCode())
		);
	}

	protected function __construct(Container $config)
	{
	}

	protected static function getConfig(string $class)
	{
		return Factory::createConfig($class);
	}
}
