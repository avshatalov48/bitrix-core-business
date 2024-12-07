<?php

namespace Bitrix\Catalog\EventDispatcher;

use Bitrix\Main\Config\Configuration;
use Bitrix\Rest\Event\EventBindInterface;

class EventDispatcher
{
	public const PLACEMENT_EXTERNAL_PRODUCT = 'CATALOG_EXTERNAL_PRODUCT';
	const SCOPE_CATALOG = 'catalog';
	const MODULE_ID = 'catalog';

	/**
	 *
	 *  Handler of `rest/onRestServiceBuildDescription` event.
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	public static function onRestServiceBuildDescription(): array
	{
		return (new EventDispatcher())->dispatch();
	}

	/**
	 *
	 * Collect all PHP module events
	 *
	 * @return \array[][]
	 * @throws \ReflectionException
	 */
	public function dispatch(): array
	{
		$bindings = [];
		$classes = $this->collectEntityEventBind();

		foreach ($classes as $class)
		{
			$reflection = new \ReflectionClass($class);
			if (
				!$reflection->isInterface()
				&& !$reflection->isAbstract()
				&& !$reflection->isTrait())
			{
				if ($reflection->implementsInterface('\\Bitrix\\Rest\\Event\\EventBindInterface'))
				{
					$bindings += $this->getBindings($class);
				}
			}
		}
		return [
			self::SCOPE_CATALOG => [
				\CRestUtil::EVENTS => $bindings,
				\CRestUtil::PLACEMENTS => [
					self::PLACEMENT_EXTERNAL_PRODUCT => [],
				],
			]
		];
	}

	protected function collectEntityEventBind(): array
	{
		$controllersConfig = Configuration::getInstance(self::MODULE_ID);

		if (!$controllersConfig['controllers'] || !$controllersConfig['controllers']['restIntegration'] || !$controllersConfig['controllers']['restIntegration']['eventBind'])
		{
			return [];
		}

		return $controllersConfig['controllers']['restIntegration']['eventBind'];
	}

	/**
	 *
	 * Get config, handlers and bindings PHP events to REST events
	 *
	 * @param string|EventBindInterface $class
	 * @return array
	 */
	public function getBindings(string $class): array
	{
		return $class::getHandlers();
	}
}
