<?

class CPushDescription
{
	const TYPE_APPLE = 'APPLE';
	const TYPE_APPLE_VOIP = 'APPLE/VOIP';
	const TYPE_GOOGLE = 'GOOGLE';
	const TYPE_GOOGLE_REV2 = 'GOOGLE/REV2';
	const TYPE_HUAWEI = 'HUAWEI';

	public static function GetDescription()
	{
		return [
			[
				'ID' => static::TYPE_APPLE,
				'CLASS' => 'CApplePush',
				'NAME' => 'Apple Push Notifications'
			],
			[
				'ID' => static::TYPE_APPLE_VOIP,
				'CLASS' => 'CApplePushVoip',
				'NAME' => 'Apple Push Notifications (Voip Service)'
			],
			[
				'ID' => static::TYPE_GOOGLE_REV2,
				'CLASS' => 'CGooglePushInteractive',
				'NAME' => 'Google Cloud Messages rev.2'
			],
			[
				'ID' => static::TYPE_GOOGLE,
				'CLASS' => 'CGooglePush',
				'NAME' => 'Google Cloud Messages'
			],
			[
				'ID' => static::TYPE_HUAWEI,
				'CLASS' => 'CHuaweiPushKitService',
				'NAME' => 'Huawei Cloud Messages'
			]
		];
	}
}

AddEventHandler('pull', 'OnPushServicesBuildList', array('CPushDescription', 'GetDescription'));
?>