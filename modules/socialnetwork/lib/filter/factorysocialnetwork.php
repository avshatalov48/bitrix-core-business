<?php

namespace Bitrix\Socialnetwork\Filter;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class FactorySocialnetwork
{
	public static function onBuildFilterFactoryMethods(Event $event)
	{
		return new EventResult(
			EventResult::SUCCESS,
			[
				'callbacks' => [
					\Bitrix\Socialnetwork\UserToGroupTable::getUfId() => function($entityTypeName, array $settingsParams, array $additionalParams = null) {

						if ($entityTypeName === \Bitrix\Socialnetwork\UserToGroupTable::getUfId())
						{
							$settings = new \Bitrix\Socialnetwork\Filter\UserToGroupSettings($settingsParams);
							$filterID = $settings->getId();

							return new \Bitrix\Main\Filter\Filter(
								$filterID,
								new \Bitrix\Socialnetwork\Filter\UserToGroupDataProvider($settings),
								[ ]
							);

						}
					},
					\Bitrix\Socialnetwork\WorkgroupTable::getUfId() => function($entityTypeName, array $settingsParams, array $additionalParams = null) {

						if ($entityTypeName === \Bitrix\Socialnetwork\WorkgroupTable::getUfId())
						{
							$settings = new \Bitrix\Socialnetwork\Filter\WorkgroupSettings($settingsParams);
							$filterID = $settings->getId();

							return new \Bitrix\Main\Filter\Filter(
								$filterID,
								new \Bitrix\Socialnetwork\Filter\WorkgroupDataProvider($settings, $additionalParams),
								[ ]
							);

						}
					},
				]
			],
			'socialnetwork'
		);
	}
}
