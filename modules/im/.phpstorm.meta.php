<?php

namespace PHPSTORM_META
{
	registerArgumentsSet(
		'bitrix_im_locator_codes',
		'Im.Messenger',
		'Im.PullSender',
		'Im.Services.Message',
		'Im.Services.MessageParam',
		'Im.Services.Promotion',
		'Im.AccessService'
	);

	expectedArguments(\Bitrix\Main\DI\ServiceLocator::get(), 0, argumentsSet('bitrix_im_locator_codes'));

	override(
		\Bitrix\Main\DI\ServiceLocator::get(0),
		map(
			[
				'Im.Messenger' => \Bitrix\Im\V2\Service\Messenger::class,
				'Im.PullSender' => \Bitrix\Im\V2\Pull\Sender::class,
				'Im.Services.Message' => \Bitrix\Im\Services\Message::class,
				'Im.Services.MessageParam' => \Bitrix\Im\Services\MessageParam::class,
				'Im.Services.Promotion' => \Bitrix\Im\V2\Promotion\Promotion::class,
				'Im.AccessService' => \Bitrix\Im\V2\Service\AccessService::class,
			]
		)
	);
}
