<?php

namespace Bitrix\Catalog\Controller\Product;

use Bitrix\Catalog\Product\Sku;
use Bitrix\Main\Application;
use Bitrix\Main\Engine\Action;
use Closure;

/**
 * Helper for working with deferred SKU calculations in REST.
 */
trait SkuDeferredCalculations
{
	private static bool $isBackgroundJobAdded = false;

	protected function isActionWithDefferedCalculation(Action $action): bool
	{
		$actions = [
			'add',
			'update',
			'delete',
		];

		return in_array($action->getName(), $actions, true);
	}

	/**
	 * Must be called BEFORE the action is executed.
	 *
	 * @see \Bitrix\Main\Engine\Controller method `processBeforeAction`
	 *
	 * @return void
	 */
	final protected function processBeforeDeferredCalculationAction(): void
	{
		Sku::enableDeferredCalculation();
	}

	/**
	 * Must be called AFTER the action is executed.
	 *
	 * @see \Bitrix\Main\Engine\Controller method `processAfterAction`
	 *
	 * @return void
	 */
	final protected function processAfterDeferredCalculationAction(): void
	{
		Sku::disableDeferredCalculation();
		self::addBackgroundJob();
	}

	private static function addBackgroundJob(): void
	{
		if (self::$isBackgroundJobAdded)
		{
			return;
		}
		self::$isBackgroundJobAdded = true;

		$callback = Closure::fromCallable([
			Sku::class,
			'calculate'
		]);

		Application::getInstance()->addBackgroundJob($callback);
	}
}
