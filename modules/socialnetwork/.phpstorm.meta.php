<?php
namespace PHPSTORM_META
{
	registerArgumentsSet('bitrix_socialnetwork_locator_codes',
		'socialnetwork.group.service',
	);

	expectedArguments(\Bitrix\Main\DI\ServiceLocator::get(), 0, argumentsSet('bitrix_socialnetwork_locator_codes'));

	override(\Bitrix\Main\DI\ServiceLocator::get(0), map([
		'socialnetwork.group.service' => \Bitrix\Socialnetwork\Control\GroupService::class,
	]));
}