<?php

namespace Bitrix\Catalog\v2\Tests;

use Bitrix\Catalog\v2\IoC\ContainerBuilder;
use Bitrix\Catalog\v2\IoC\ContainerContract;
use Bitrix\Main\Loader;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
	/** @var \Bitrix\Catalog\v2\IoC\ContainerContract container */
	protected static $container;

	public static function loadContainer(): ContainerContract
	{
		if (static::$container === null)
		{
			static::$container = ContainerBuilder::buildFromConfig(__DIR__ . '/.test.container.php');
		}

		return static::$container;
	}

	public static function setUpBeforeClass()
	{
		Loader::includeModule('catalog');
		static::loadContainer();
	}
}
