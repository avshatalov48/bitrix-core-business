<?php

namespace Bitrix\Vote\Attachment;

use Bitrix\Vote\Uf\VoteUserType;

class UfCompatibleChannelCreator
{
	public static function getOrCreateChannel(string $name): int
	{
		$params = [
			'SETTINGS' => [
				'CHANNEL_ID' => 0,
				'CHANNEL_TITLE' => $name,
				'CHANNEL_SYMBOLIC_NAME' => $name,
				'CHANNEL_USE_CAPTCHA' => 'N',
				'UNIQUE' => 8,
			],
		];

		VoteUserType::checkSettings($params); // get or create channel here
		if (is_string($params['SETTINGS']))
		{
			$params['SETTINGS'] = unserialize($params["SETTINGS"], ["allowed_classes" => false]);
		}

		return $params['SETTINGS']['CHANNEL_ID'] ?? 0;
	}
}