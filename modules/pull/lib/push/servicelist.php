<?php

namespace Bitrix\Pull\Push;

class ServiceList
{
	const TYPE_APPLE = 'APPLE';
	const TYPE_APPLE_VOIP = 'APPLE/VOIP';
	const TYPE_GOOGLE = 'GOOGLE';
	const TYPE_GOOGLE_REV2 = 'GOOGLE/REV2';
	const TYPE_HUAWEI = 'HUAWEI';

	public static function getServiceList(): array
	{
		$result = [
			static::TYPE_APPLE => [
				'ID' => static::TYPE_APPLE,
				'CLASS' => Service\Apple::class,
				'NAME' => 'Apple Push Notifications'
			],
			static::TYPE_APPLE_VOIP => [
				'ID' => static::TYPE_APPLE_VOIP,
				'CLASS' => Service\AppleVoip::class,
				'NAME' => 'Apple Push Notifications (Voip Service)'
			],
			static::TYPE_GOOGLE_REV2 => [
				'ID' => static::TYPE_GOOGLE_REV2,
				'CLASS' => Service\GoogleInteractive::class,
				'NAME' => 'Google Cloud Messages rev.2'
			],
			static::TYPE_GOOGLE => [
				'ID' => static::TYPE_GOOGLE,
				'CLASS' => Service\Google::class,
				'NAME' => 'Google Cloud Messages'
			],
			static::TYPE_HUAWEI => [
				'ID' => static::TYPE_HUAWEI,
				'CLASS' => Service\HuaweiPushKit::class,
				'NAME' => 'Huawei Cloud Messages'
			]
		];

		foreach (GetModuleEvents("pull", "OnPushServicesBuildList", true) as $arEvent)
		{
			$res = ExecuteModuleEventEx($arEvent);
			if (is_array($res))
			{
				if (!is_array($res[0]))
				{
					$res = [$res];
				}
				foreach ($res as $serv)
				{
					if (!class_exists($serv['CLASS']))
					{
						trigger_error('Class ' . $serv['CLASS'] . ' does not exists', E_USER_WARNING);
						continue;
					}
					if (!($serv['CLASS'] instanceof PushService))
					{
						trigger_error('Class ' . $serv['CLASS'] . ' must implement ' . PushService::class .' interface', E_USER_WARNING);
						continue;
					}

					$result[$serv["ID"]] = $serv;
				}
			}
		}


		return $result;
	}
}
