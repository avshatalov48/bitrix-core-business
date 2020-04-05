<?php
namespace Bitrix\Main\Filter;

use Bitrix\Main;

class Factory
{
	protected static $methods = null;

	/**
	 * Prepare Entity Filter Factory Methods
	 * Function will rise event "OnBuildFilterFactoryMethods" if it required.
	 * @return array
	 */
	protected static function prepareMethods()
	{
		if(self::$methods === null)
		{
			self::$methods = [];

			$event = new Main\Event('main', 'OnBuildFilterFactoryMethods', []);
			$event->send();

			foreach($event->getResults() as $eventResult)
			{
				if($eventResult->getType() != Main\EventResult::SUCCESS)
				{
					continue;
				}

				$resultParams = $eventResult->getParameters();
				if(isset($resultParams['callbacks']) && is_array($resultParams['callbacks']))
				{
					foreach($resultParams['callbacks'] as $entityTypeName => $callback)
					{
						if(is_callable($callback))
						{
							self::$methods[$entityTypeName] = $callback;
						}
					}
				}
			}
		}
		return self::$methods;
	}
	/**
	 * Create Entity Filter by Entity Type Name.
	 * @param string $entityTypeName Entity Type Name
	 * @param array $settingsParams Filter Settings Params
	 * @param array $additionalParams Factory Method Additional Params.
	 * @return Filter
	 * @throws Main\NotSupportedException
	 */
	public static function createEntityFilter($entityTypeName, array $settingsParams, array $additionalParams = null)
	{
		$methods = self::prepareMethods();
		if(isset($methods[$entityTypeName]))
		{
			$result = call_user_func_array($methods[$entityTypeName], [ $entityTypeName, $settingsParams, $additionalParams ]);
			if($result instanceof Filter)
			{
				return $result;
			}
		}

		throw new Main\NotSupportedException(
			"Entity type: '{$entityTypeName}' is not supported in current context."
		);
	}
}