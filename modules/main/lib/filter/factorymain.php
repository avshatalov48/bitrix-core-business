<?php
namespace Bitrix\Main\Filter;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class FactoryMain
{
	public static function onBuildFilterFactoryMethods(Event $event)
	{
		$result = new EventResult(
			EventResult::SUCCESS,
			[
				'callbacks' => [
					\Bitrix\Main\UserTable::getUfId() => function($entityTypeName, array $settingsParams, array $additionalParams = null) {

						if ($entityTypeName == \Bitrix\Main\UserTable::getUfId())
						{
							$settings = new \Bitrix\Main\Filter\UserSettings($settingsParams);
							$filterID = $settings->getID();

							return new \Bitrix\Main\Filter\Filter(
								$filterID,
								new \Bitrix\Main\Filter\UserDataProvider($settings),
								[ new \Bitrix\Main\Filter\UserUFDataProvider($settings) ]
							);

						}
					}
				]
			],
			'main'
		);

		return $result;
	}
}