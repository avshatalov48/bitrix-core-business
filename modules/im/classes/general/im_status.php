<?
use Bitrix\Im as IM;

class CIMStatus
{
	public static $AVAILABLE_STATUSES = Array('online', 'dnd', 'away');
	public static $CACHE_USERS = null;
	public static $CACHE_RECENT = null;

	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/status/';

	const CACHE_ONLINE_TTL = 60;
	const CACHE_ONLINE_PATH = '/bx/im/online/';

	public static function Set($userId, $params)
	{
		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		if (isset($params['STATUS']))
			$params['IDLE'] = null;

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
			$previousStatus = Array(
				'USER_ID' => $status['USER_ID'],
				'STATUS' => (string)$status['STATUS'],
				'COLOR' => (string)$status['COLOR'],
				'IDLE' => $status['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $status['IDLE']: false,
				'MOBILE_LAST_DATE' => $status['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $status['MOBILE_LAST_DATE']: false,
				'DESKTOP_LAST_DATE' => $status['DESKTOP_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $status['DESKTOP_LAST_DATE']: false,
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
			IM\Model\StatusTable::add($params);

			$needToUpdate = true;
			$status = $params;
		}

		if ($needToUpdate && self::Enable())
		{
			CPullStack::AddShared(Array(
				'module_id' => 'online',
				'command' => 'userStatus',
				'expiry' => 1,
				'params' => Array(
					'users' => Array(
						$userId => Array(
							'id' => $userId,
							'status' => $status['STATUS'],
							'color' => $status['COLOR']? \Bitrix\Im\Color::getColor($status['COLOR']): \Bitrix\Im\Color::getColorByNumber($userId),
							'idle' => $status['IDLE'] instanceof \Bitrix\Main\Type\DateTime? date('c', $status['IDLE']->getTimestamp()): false,
							'mobile_last_date' => $status['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? date('c', $status['MOBILE_LAST_DATE']->getTimestamp()): false,
							'desktop_last_date' => $status['DESKTOP_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? date('c', $status['DESKTOP_LAST_DATE']->getTimestamp()): false,
							'last_activity_date' => date('c', time())
						)
					)
				)
			));
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		$cache->cleanDir(self::CACHE_PATH.$userId.'/');
		$cache->CleanDir(self::CACHE_ONLINE_PATH);

		$event = new \Bitrix\Main\Event("im", "onStatusSet", array(
			'USER_ID' => $userId,
			'STATUS' => $status['STATUS'],
			'COLOR' => $status['COLOR']? $status['COLOR']: '',
			'IDLE' => $status['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $status['IDLE']: false,
			'MOBILE_LAST_DATE' => $status['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $status['MOBILE_LAST_DATE']: false,
			'PREVIOUS_VALUES' => $previousStatus
		));
		$event->send();

		return true;
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
				if (isset($user['COLOR']) && strlen($user['COLOR']) > 0)
				{
					$color = IM\Color::getColor($user['COLOR']);
				}
				if (!$color)
				{
					$color = \CIMContactList::GetUserColor($user["ID"], $user['PERSONAL_GENDER'] == 'M'? 'M': 'F');
				}

				$user['IDLE'] = $user['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $user['IDLE']: false;
				$user['MOBILE_LAST_DATE'] = $user['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $user['MOBILE_LAST_DATE']: false;
				$user['LAST_ACTIVITY_DATE'] = $user['LAST_ACTIVITY_DATE'] instanceof \Bitrix\Main\Type\DateTime? $user['LAST_ACTIVITY_DATE']: false;

				$users[$user["ID"]] = Array(
					'id' => $user["ID"],
					'status' => in_array($user['STATUS'], self::$AVAILABLE_STATUSES)? $user['STATUS']: 'online',
					'color' => $color,
					'idle' => $user['IDLE'],
					'last_activity_date' => $user['LAST_ACTIVITY_DATE'],
					'mobile_last_date' => $user['MOBILE_LAST_DATE'],
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

	public static function GetStatus($userId)
	{
		$userStatus = null;
		$userId = intval($userId);
		if (!$userId)
			return $userStatus;

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(self::CACHE_TTL, 'list_v1', self::CACHE_PATH.$userId.'/'))
		{
			$userStatus = $cache->getVars();
		}
		else
		{
			$res = IM\Model\StatusTable::getList(Array(
				'select' => Array('STATUS', 'IDLE', 'MOBILE_LAST_DATE', 'EXTERNAL_AUTH_ID' => 'USER.EXTERNAL_AUTH_ID'),
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

		if (in_array($status['EXTERNAL_AUTH_ID'], array('bot', 'email', 'network', 'replica', 'controller', 'imconnector')))
		{
			$result['STATUS'] = 'online';
			$result['STATUS_TEXT'] = GetMessage('IM_STATUS_EAID_'.strtoupper($status['EXTERNAL_AUTH_ID']));
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
				$result['LAST_SEEN'] = $mobileLastDate->getTimestamp();
				$result['LAST_SEEN_TEXT'] = CUser::FormatLastActivityDate($mobileLastDate->getTimestamp(), $now);
			}
		}

		if ($mode == CUser::STATUS_OFFLINE)
		{
			return $result;
		}

		if (in_array($status['STATUS'], Array('dnd', 'away', 'mobile')))
		{
			$result['STATUS'] = $status['STATUS'];
			$result['STATUS_TEXT'] = GetMessage('IM_STATUS_'.strtoupper($status['STATUS']));
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

	public static function Enable()
	{
		return CModule::IncludeModule('pull') && CPullOptions::GetNginxStatus()? true: false;
	}
}
?>