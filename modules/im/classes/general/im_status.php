<?
use Bitrix\Im as IM;

class CIMStatus
{
	public static $AVAILABLE_STATUSES = Array('online', 'dnd', 'away', 'break');
	public static $CACHE_USERS = null;
	public static $CACHE_RECENT = null;

	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/status/';

	const CACHE_ONLINE_TTL = 60;
	const CACHE_ONLINE_PATH = '/bx/im/online/';

	public static function Set($userId, $params)
	{
		global $CACHE_MANAGER;

		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		if (isset($params['STATUS']))
		{
			$params['IDLE'] = null;
		}

		if (isset($params['STATUS']) || isset($params['COLOR']))
		{
			$CACHE_MANAGER->ClearByTag("USER_NAME_".$userId);
		}

		$previousStatus = Array(
			'USER_ID' => $userId,
			'STATUS' => '',
			'COLOR' => '',
			'IDLE' => false,
			'MOBILE_LAST_DATE' => false,
			'DESKTOP_LAST_DATE' => false,
		);
		$needToUpdate = false;

		$params = self::PrepareFields($params);
		$res = IM\Model\StatusTable::getById($userId);
		if ($status = $res->fetch())
		{
			$status['IDLE'] ??= false;
			$status['MOBILE_LAST_DATE'] ??= false;
			$status['DESKTOP_LAST_DATE'] ??= false;
			//$status = CIMStatus::prepareLastDate($status);
			$previousStatus = Array(
				'USER_ID' => $status['USER_ID'],
				'STATUS' => (string)$status['STATUS'],
				'COLOR' => (string)$status['COLOR'],
				'IDLE' => $status['IDLE'],
				'MOBILE_LAST_DATE' => $status['MOBILE_LAST_DATE'],
				'DESKTOP_LAST_DATE' => $status['DESKTOP_LAST_DATE'],
			);

			foreach ($params as $key => $value)
			{
				$oldValue = is_object($status[$key])? $status[$key]->toString(): $status[$key];
				$newValue = is_object($value)? $value->toString(): $value;
				if ($oldValue != $newValue)
				{
					$status[$key] = $value;
					$needToUpdate = true;
				}
			}

			if ($needToUpdate)
			{
				IM\Model\StatusTable::update($userId, $params);
			}
		}
		else
		{
			$params['USER_ID'] = $userId;
			$update = $params;
			IM\Model\StatusTable::merge($params, $update);

			$needToUpdate = true;
			$status = $params;
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH.$userId.'/');

		if ($needToUpdate && self::Enable())
		{
			$push = static::getPushParams($status, $userId);
			//\CPullWatch::AddToStack(static::getPushTag($userId), $push);

			if (isset($params['STATUS']))
			{
				\Bitrix\Pull\Event::add([$userId], $push);
			}
		}

		$cache->CleanDir(self::CACHE_ONLINE_PATH);

		$event = new \Bitrix\Main\Event("im", "onStatusSet", array(
			'USER_ID' => $userId,
			'STATUS' => $status['STATUS'],
			'COLOR' => $status['COLOR']? $status['COLOR']: '',
			'IDLE' => $status['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $status['IDLE']: false,
			'MOBILE_LAST_DATE' => $status['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $status['MOBILE_LAST_DATE']: false,
			'DESKTOP_LAST_DATE' => $status['DESKTOP_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $status['DESKTOP_LAST_DATE']: false,
			'PREVIOUS_VALUES' => $previousStatus
		));
		$event->send();

		return true;
	}

	private static function getPushParams(array $status, int $userId): array
	{
		return [
			'module_id' => 'online',
			'command' => 'userStatus',
			'expiry' => 1,
			'params' => [
				'users' => [
					$userId => [
						'id' => $userId,
						'status' => $status['STATUS'] ?? null,
						'color' => ($status['COLOR'] ?? null)
							? \Bitrix\Im\Color::getColor($status['COLOR'])
							: \Bitrix\Im\Color::getColorByNumber($userId)
						,
						'idle' => $status['IDLE'] instanceof \Bitrix\Main\Type\DateTime
							? date('c', $status['IDLE']->getTimestamp())
							: false
						,
						'mobile_last_date' => ($status['MOBILE_LAST_DATE'] ?? null) instanceof \Bitrix\Main\Type\DateTime
							? date('c', $status['MOBILE_LAST_DATE']->getTimestamp())
							: false
						,
						'desktop_last_date' => $status['DESKTOP_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime
							? date('c', $status['DESKTOP_LAST_DATE']->getTimestamp())
							: false
						,
						'last_activity_date' => date('c', time())
					]
				]
			]
		];
	}

	public static function getPushTag(int $userId): string
	{
		return "IM_USER_ONLINE_{$userId}";
	}

	public static function SetIdle($userId, $result = true, $ago = 10)
	{
		$date = null;
		$ago = intval($ago);
		if ($result && $ago > 0)
		{
			$date = new Bitrix\Main\Type\DateTime();
			$date->add('-'.$ago.' MINUTE');
		}

		CIMStatus::Set($userId, Array('IDLE' => $date));
	}

	public static function SetMobile($userId, $result = true)
	{
		$date = null;
		if ($result)
		{
			$date = new Bitrix\Main\Type\DateTime();
		}
		CIMStatus::Set($userId, Array('MOBILE_LAST_DATE' => $date));
	}

	public static function SetColor($userId, $color)
	{
		CIMStatus::Set($userId, Array('COLOR' => $color));

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag('IM_CONTACT_LIST');
			$CACHE_MANAGER->ClearByTag('USER_NAME_'.$userId);
		}
	}

	private static function PrepareToPush($params)
	{
		foreach($params as $key => $value)
		{
			if ($key == 'STATUS')
			{
				$params[$key] = in_array($value, self::$AVAILABLE_STATUSES)? $value: 'online';
			}
			else if (in_array($key, Array('IDLE', 'DESKTOP_LAST_DATE', 'MOBILE_LAST_DATE', 'EVENT_UNTIL_DATE')))
			{
				$params[$key] = is_object($value)? $value->getTimestamp(): 0;
			}
			else if ($key == 'COLOR')
			{
				$params[$key] = IM\Color::getColor($value);
				if (!$params[$key])
				{
					unset($params[$key]);
				}
			}
			else
			{
				$params[$key] = $value;
			}
		}

		return $params;
	}

	private static function PrepareFields($params)
	{
		$arValues = Array();

		$arFields = IM\Model\StatusTable::getMap();
		foreach($params as $key => $value)
		{
			if (!isset($arFields[$key]))
				continue;

			if ($key == 'STATUS')
			{
				$arValues[$key] = in_array($value, self::$AVAILABLE_STATUSES)? $value: 'online';
			}
			else if ($key == 'COLOR')
			{
				$colors = IM\Color::getSafeColors();
				if (isset($colors[$value]))
				{
					$arValues[$key] = $value;
				}
			}
			else
			{
				$arValues[$key] = $value;
			}
		}

		return $arValues;
	}

	public static function GetList($params = Array())
	{
		if (!is_array($params))
			$params = Array();

		$userIds = Array();
		if (isset($params['ID']) && is_array($params['ID']) && !empty($params['ID']))
		{
			foreach ($params['ID'] as $key => $value)
			{
				$userIds[] = intval($value);
			}
		}
		else if (isset($params['ID']) && intval($params['ID']) > 0)
		{
			$userIds[] = intval($params['ID']);
		}

		if (isset($params['CLEAR_CACHE']) && $params['CLEAR_CACHE'] == 'Y')
		{
			$obCache = new CPHPCache();
			$obCache->CleanDir(self::CACHE_ONLINE_PATH);
		}

		global $USER;
		$userId = is_object($USER)? intval($USER->GetID()): 0;

		$users = Array();
		$loadFromDb = true;
		$loadRecent = false;
		if (empty($userIds))
		{
			$loadRecent = true;
			if (!is_null(self::$CACHE_RECENT))
			{
				$loadFromDb = false;
				$users = self::$CACHE_RECENT;
			}
		}
		else if (!empty($userIds))
		{
			foreach($userIds as $id => $uid)
			{
				if (isset(self::$CACHE_USERS[$uid]))
				{
					unset($userIds[$id]);
					$users[$uid] = self::$CACHE_USERS[$uid];
				}
			}
			if (empty($userIds))
			{
				$loadFromDb = false;
			}
		}

		if ($loadFromDb)
		{
			if ($loadRecent)
			{
				$orm = \Bitrix\Im\Model\RecentTable::getList(array(
					'select' => Array(
						'ID' => 'U.ID',
						'EXTERNAL_AUTH_ID' => 'U.EXTERNAL_AUTH_ID',
						'LAST_ACTIVITY_DATE' => 'U.LAST_ACTIVITY_DATE',
						'PERSONAL_GENDER' => 'U.PERSONAL_GENDER',
						'COLOR' => 'ST.COLOR',
						'STATUS' =>	'ST.STATUS',
						'IDLE' => 'ST.IDLE',
						'MOBILE_LAST_DATE' => 'ST.MOBILE_LAST_DATE',
						'DESKTOP_LAST_DATE' => 'ST.DESKTOP_LAST_DATE',
					 ),
					'runtime' => Array(
						new \Bitrix\Main\Entity\ReferenceField(
							'ST',
							'\Bitrix\Im\Model\StatusTable',
							array("=ref.USER_ID" => "this.ITEM_ID",),
							array("join_type"=>"LEFT")
						),
						new \Bitrix\Main\Entity\ReferenceField(
							'U',
							'\Bitrix\Main\UserTable',
							array("=ref.ID" => "this.ITEM_ID",),
							array("join_type"=>"LEFT")
						)
					),
					'filter' => Array(
						'=USER_ID' => $userId,
						"=ITEM_TYPE" => IM_MESSAGE_PRIVATE,
					)
				));
			}
			else
			{
				$orm = \Bitrix\Main\UserTable::getList(array(
					'select' => Array(
						'ID',
						'EXTERNAL_AUTH_ID',
						'LAST_ACTIVITY_DATE',
						'PERSONAL_GENDER',
						'COLOR' => 'ST.COLOR',
						'STATUS' =>	'ST.STATUS',
						'IDLE' => 'ST.IDLE',
						'MOBILE_LAST_DATE' => 'ST.MOBILE_LAST_DATE',
						'DESKTOP_LAST_DATE' => 'ST.DESKTOP_LAST_DATE',
					 ),
					'runtime' => Array(
						new \Bitrix\Main\Entity\ReferenceField(
							'ST',
							'\Bitrix\Im\Model\StatusTable',
							array(
								"=ref.USER_ID" => "this.ID",
							),
							array("join_type"=>"LEFT")
						)
					),
					'filter' => Array(
						'=ID' => $userIds,
					)
				));
			}

			while ($user = $orm->fetch())
			{
				$color = null;
				if (isset($user['COLOR']) && $user['COLOR'] <> '')
				{
					$color = IM\Color::getColor($user['COLOR']);
				}
				if (!$color)
				{
					$color = \CIMContactList::GetUserColor($user["ID"], $user['PERSONAL_GENDER'] == 'M'? 'M': 'F');
				}

				$user['LAST_ACTIVITY_DATE'] = $user['LAST_ACTIVITY_DATE'] instanceof \Bitrix\Main\Type\DateTime? $user['LAST_ACTIVITY_DATE']: false;
				$user = CIMStatus::prepareLastDate($user);

				$users[$user["ID"]] = Array(
					'id' => $user["ID"],
					//'status' => in_array($user['STATUS'], self::$AVAILABLE_STATUSES)? $user['STATUS']: 'online',
					'status' => 'online',
					'color' => $color,
					//'idle' => $user['IDLE']?: false,
					'idle' => false,
					'last_activity_date' => $user['LAST_ACTIVITY_DATE']?: false,
					//'mobile_last_date' => $user['MOBILE_LAST_DATE']?: false,
					'mobile_last_date' => false,
					'absent' => \CIMContactList::formatAbsentResult($user["ID"]),
				);

				self::$CACHE_USERS[$user["ID"]] = $users[$user["ID"]];
			}

			if ($loadRecent)
			{
				self::$CACHE_RECENT = self::$CACHE_USERS;
			}
		}

		return Array('users' => $users);
	}

	public static function GetOnline()
	{
		global $USER;
		$userId = is_object($USER)? intval($USER->GetID()): 0;

		$obCLCache = new CPHPCache;
		$cache_id = 'im_user_online_v1';
		$cache_dir = self::CACHE_ONLINE_PATH.$userId.'/';
		if($obCLCache->InitCache(self::CACHE_ONLINE_TTL, $cache_id, $cache_dir))
		{
			$arOnline = $obCLCache->GetVars();
		}
		else
		{
			$arOnline = self::GetList();

			if($obCLCache->StartDataCache())
			{
				$obCLCache->EndDataCache($arOnline);
			}
		}

		return $arOnline;
	}

	public static function GetStatus($userId = null)
	{
		$userId = IM\Common::getUserId($userId);
		if (!$userId)
		{
			return null;
		}

		$userStatus = null;
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'list_v2', self::CACHE_PATH.$userId.'/'))
		{
			$userStatus = $cache->getVars();
		}
		else
		{
			$res = IM\Model\StatusTable::getList(Array(
				'select' => Array(
					'STATUS',
					'MOBILE_LAST_DATE',
					'DESKTOP_LAST_DATE',
					'IDLE',
					'EXTERNAL_AUTH_ID' => 'USER.EXTERNAL_AUTH_ID'
				),
				'runtime' => Array(
					new \Bitrix\Main\Entity\ReferenceField(
						'USER',
						'\Bitrix\Main\UserTable',
						array("=ref.ID" => "this.USER_ID",),
						array("join_type"=>"LEFT")
					)
				),
				'filter' => Array('=USER_ID' => $userId),
			));
			if ($status = $res->fetch())
			{
				$userStatus = $status;
				$cache->startDataCache();
				$cache->endDataCache($userStatus);
			}
		}

		if ($userStatus)
		{
			$userStatus = CIMStatus::prepareLastDate($userStatus);
		}

		return $userStatus;
	}

	public static function OnUserOnlineStatusGetCustomStatus($userId, $lastseen, $now, $mode)
	{
		$result = false;
		$status = self::GetStatus($userId);
		if (!$status)
		{
			return $result;
		}

		$result = [];
		$externalUser = \Bitrix\Main\UserTable::getExternalUserTypes();
		$externalUser[] = 'network';

		if (in_array($status['EXTERNAL_AUTH_ID'], $externalUser))
		{
			$result['STATUS'] = 'online';
			$result['STATUS_TEXT'] = GetMessage('IM_STATUS_EAID_'.mb_strtoupper($status['EXTERNAL_AUTH_ID']));
			$result['LAST_SEEN_TEXT'] = '';

			return $result;
		}

		/** @var \Bitrix\Main\Type\DateTime $mobileLastDate */
		$mobileLastDate = $status['MOBILE_LAST_DATE'];
		if ($mobileLastDate)
		{
			if (
				$now - $mobileLastDate->getTimestamp() < CUser::GetSecondsForLimitOnline()
				&& $lastseen - $mobileLastDate->getTimestamp() < 300
			)
			{
				$result['STATUS'] = 'mobile';
				$result['STATUS_TEXT'] = GetMessage('IM_STATUS_MOBILE');
				$result['LAST_SEEN'] = $mobileLastDate->getTimestamp();
				$result['LAST_SEEN_TEXT'] = CUser::FormatLastActivityDate($mobileLastDate->getTimestamp(), $now);
			}
		}

		if ($mode == CUser::STATUS_OFFLINE)
		{
			return $result;
		}

		if ($result && $result['STATUS'] === 'mobile')
		{
		}
		else if (in_array($status['STATUS'], Array('dnd', 'away', 'break', 'video')))
		{
			$result['STATUS'] = $status['STATUS'];
			$result['STATUS_TEXT'] = GetMessage('IM_STATUS_'.mb_strtoupper($status['STATUS']));
		}

		/** @var \Bitrix\Main\Type\DateTime $idleDate */
		$idleDate = $status['IDLE'];
		if ($idleDate)
		{
			$result['STATUS'] = 'idle';
			$result['STATUS_TEXT'] = GetMessage('IM_STATUS_IDLE');
			$result['LAST_SEEN'] = $idleDate->getTimestamp();
			$result['LAST_SEEN_TEXT'] = CUser::FormatLastActivityDate($idleDate, $now);
		}

		return $result;
	}

	public static function getDesktopStatus($dates)
	{
		$result = [
			'ONLINE' => false,
			'IDLE' => false,
		];

		if (!($dates['DESKTOP_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime))
		{
			return $result;
		}

		$maxOnlineTime = 120;
		if (\Bitrix\Main\Loader::includeModule('pull') && CPullOptions::GetNginxStatus())
		{
			$maxOnlineTime = CIMMessenger::GetSessionLifeTime();
		}

		if ($dates['DESKTOP_LAST_DATE']->getTimestamp()+$maxOnlineTime+60 > time())
		{
			$result['ONLINE'] = true;
		}

		if (!$result['ONLINE'])
		{
			return $result;
		}

		if (
			$dates['IDLE'] instanceof \Bitrix\Main\Type\DateTime
			&& $dates['IDLE']->getTimestamp() > 0
		)
		{
			$result['IDLE'] = true;
		}

		return $result;
	}

	public static function prepareLastDate($dates)
	{
		if (!($dates['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime))
		{
			$dates['MOBILE_LAST_DATE'] = false;
		}

		if (!($dates['DESKTOP_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime))
		{
			$dates['DESKTOP_LAST_DATE'] = false;
		}

		$status = self::getDesktopStatus($dates);
		if (!$status['IDLE'])
		{
			$dates['IDLE'] = false;
		}

		return $dates;
	}

	public static function Enable()
	{
		return CModule::IncludeModule('pull') && CPullOptions::GetNginxStatus()? true: false;
	}
}
?>