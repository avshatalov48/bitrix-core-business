<?php
namespace Bitrix\Im;

use Bitrix\Im\V2\Chat\ChannelChat;
use Bitrix\Main\Config\Option;

class Promotion
{
	const DEVICE_TYPE_WEB = "web"; // browser + desktop
	const DEVICE_TYPE_BROWSER = "browser";
	const DEVICE_TYPE_DESKTOP = "desktop";
	const DEVICE_TYPE_MOBILE = "mobile";
	const DEVICE_TYPE_ALL = "all";

	const USER_TYPE_OLD = "OLD";
	const USER_TYPE_NEW = "NEW";
	const USER_TYPE_ALL = "ALL";

	private static function getConfig()
	{
		$result = [];

		if (!\Bitrix\Main\Loader::includeModule('ui'))
		{
			return $result;
		}

		if (self::isDisable())
		{
			return $result;
		}

/*
		$result[] = [
			"ID" => 'im:video:01042020:web',
			"USER_TYPE" => self::USER_TYPE_OLD,
			"DEVICE_TYPE" => self::DEVICE_TYPE_WEB
		];

		$result[] = [
			"ID" => 'ol:crmform:17092021:web',
			"USER_TYPE" => self::USER_TYPE_OLD,
			"DEVICE_TYPE" => self::DEVICE_TYPE_WEB
		];

		$result[] = [
			"ID" => 'im:call-document:16102021:web',
			"USER_TYPE" => self::USER_TYPE_ALL,
			"DEVICE_TYPE" => self::DEVICE_TYPE_WEB
		];

		$result[] = [
			"ID" => 'imbot:support24:25112021:web',
			"USER_TYPE" => self::USER_TYPE_OLD,
			"DEVICE_TYPE" => self::DEVICE_TYPE_WEB
		];
		$result[] = [
			"ID" => 'im:mask:06122022:desktop',
			"USER_TYPE" => self::USER_TYPE_OLD,
			"DEVICE_TYPE" => self::DEVICE_TYPE_DESKTOP
		];
*/
		$result[] = [
			"ID" => 'im:ai:15062023:all',
			"USER_TYPE" => self::USER_TYPE_ALL,
			"DEVICE_TYPE" => self::DEVICE_TYPE_ALL
		];

		$result[] = [
			"ID" => 'im:group-chat-create:20062023:all',
			"USER_TYPE" => self::USER_TYPE_ALL,
			"DEVICE_TYPE" => self::DEVICE_TYPE_ALL
		];

		$result[] = [
			"ID" => 'im:conference-create:24082023:all',
			"USER_TYPE" => self::USER_TYPE_ALL,
			"DEVICE_TYPE" => self::DEVICE_TYPE_ALL
		];

		$result[] = [
			"ID" => 'im:channel-create:04032024:all',
			"USER_TYPE" => self::USER_TYPE_ALL,
			"DEVICE_TYPE" => self::DEVICE_TYPE_ALL
		];

		$result[] = [
			"ID" => 'im:add-users-to-copilot-chat:09042024:all',
			"USER_TYPE" => self::USER_TYPE_ALL,
			"DEVICE_TYPE" => self::DEVICE_TYPE_ALL
		];

		$result[] = [
			"ID" => 'im:change-role-copilot-chat:09042024:all',
			"USER_TYPE" => self::USER_TYPE_ALL,
			"DEVICE_TYPE" => self::DEVICE_TYPE_ALL
		];

		if (!\Bitrix\Im\Settings::isLegacyChatActivated())
		{
			$result[] = [
				"ID" => 'immobile:chat-v2:16112023:mobile',
				"USER_TYPE" => self::USER_TYPE_ALL,
				"DEVICE_TYPE" => self::DEVICE_TYPE_MOBILE,
			];
		}

		$result[] = [
			"ID" => 'immobile:chat-v2:26042024:mobile',
			"USER_TYPE" => self::USER_TYPE_ALL,
			"DEVICE_TYPE" => self::DEVICE_TYPE_MOBILE,
		];

		$settings = \Bitrix\Main\Config\Configuration::getValue('im');
		if (isset($settings['promotion']) && is_array($settings['promotion']))
		{
			$result = array_merge($result, $settings['promotion']);
		}

		return $result;
	}

	public static function getActive($type = self::DEVICE_TYPE_ALL)
	{
		$result = [];

		if (!\Bitrix\Main\Loader::includeModule('ui'))
		{
			return $result;
		}

		foreach (self::getConfig() as $config)
		{
			$tour = self::getTour($config, $type);
			if (!$tour || !$tour->isAvailable())
			{
				continue;
			}

			$result[] = $tour->getId();
		}

		return $result;
	}

	public static function read($id)
	{
		$tour = self::getTourById($id);
		if (!$tour || !$tour->isAvailable())
		{
			return false;
		}

		$userId = Common::getUserId();

		$tour->setViewDate($userId);

		if (\Bitrix\Main\Loader::includeModule('pull'))
		{
			\Bitrix\Pull\Event::add($userId, [
				'module_id' => 'im',
				'command' => 'promotionRead',
				'params' => ['id' => $id],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return true;
	}

	public static function getDeviceTypes()
	{
		return [
			self::DEVICE_TYPE_ALL,
			self::DEVICE_TYPE_WEB,
			self::DEVICE_TYPE_BROWSER,
			self::DEVICE_TYPE_DESKTOP,
			self::DEVICE_TYPE_MOBILE,
		];
	}

	private static function getTour($config, $type = self::DEVICE_TYPE_ALL)
	{
		if (!\Bitrix\Main\Loader::includeModule('ui'))
		{
			return null;
		}

		if ($type === self::DEVICE_TYPE_WEB)
		{
			if (!(
				$config['DEVICE_TYPE'] === self::DEVICE_TYPE_ALL
				|| $config['DEVICE_TYPE'] === self::DEVICE_TYPE_BROWSER
				|| $config['DEVICE_TYPE'] === self::DEVICE_TYPE_DESKTOP
			))
			{
				return false;
			}
		}
		else if ($type === self::DEVICE_TYPE_MOBILE)
		{
			if (
				$config['DEVICE_TYPE'] !== self::DEVICE_TYPE_MOBILE
				&& $config['DEVICE_TYPE'] !== self::DEVICE_TYPE_ALL
			)
			{
				return false;
			}
		}
		else if ($type !== self::DEVICE_TYPE_ALL)
		{
			if (
				$config['DEVICE_TYPE'] !== self::DEVICE_TYPE_ALL
				&& $config['DEVICE_TYPE'] !== self::DEVICE_TYPE_WEB
				&& $config['DEVICE_TYPE'] !== $type
			)
			{
				return false;
			}
		}

		$tour = new \Bitrix\Main\UI\Tour($config["ID"]);

		$params = array(
			"USER_TYPE" => "setUserType",
			"USER_TIMESPAN" => "setUserTimeSpan",
			"LIFETIME" => "setLifetime",
			"START_DATE" => "setStartDate",
			"END_DATE" => "setEndDate",
		);

		foreach ($params as $param => $setter)
		{
			if (isset($config[$param]))
			{
				$tour->$setter($config[$param]);
			}
		}

		return $tour;
	}

	private static function getTourById($id)
	{
		foreach (self::getConfig() as $config)
		{
			if ($config['ID'] === $id)
			{
				return self::getTour($config);
			}
		}

		return null;
	}

	private static function isDisable(): bool
	{
		return Option::get('im', 'promo_disabled', 'N') === 'Y';
	}
}