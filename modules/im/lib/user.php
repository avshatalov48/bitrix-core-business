<?php
namespace Bitrix\Im;

class User
{
	private static $instance = Array();
	private $userId = 0;
	private $userData = null;

	const FILTER_LIMIT = 50;

	const PHONE_ANY = 'PHONE_ANY';
	const PHONE_WORK = 'work_phone';
	const PHONE_PERSONAL = 'personal_phone';
	const PHONE_MOBILE = 'personal_mobile';
	const PHONE_INNER = 'uf_phone_inner';

	function __construct($userId = null)
	{
		global $USER;

		$this->userId = (int)$userId;
		if ($this->userId <= 0 && is_object($USER) && $USER->GetID() > 0)
		{
			$this->userId = (int)$USER->GetID();
		}
	}

	/**
	 * @param null $userId
	 * @return User
	 */
	public static function getInstance($userId = null)
	{
		global $USER;

		$userId = (int)$userId;
		if ($userId <= 0 && is_object($USER) && $USER->GetID() > 0)
		{
			$userId = (int)$USER->GetID();
		}

		if (!isset(self::$instance[$userId]))
		{
			self::$instance[$userId] = new self($userId);
		}

		return self::$instance[$userId];
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->userId;
	}

	/**
	 * @return string
	 */
	public function getFullName($safe = true)
	{
		$fields = $this->getFields();
		if (!$fields)
			return '';

		return $safe? $fields['name']: htmlspecialcharsback($fields['name']);
	}

	/**
	 * @return string
	 */
	public function getName($safe = true)
	{
		$fields = $this->getFields();
		if (!$fields)
			return '';

		return $safe? $fields['first_name']: htmlspecialcharsback($fields['first_name']);
	}

	/**
	 * @return string
	 */
	public function getLastName($safe = true)
	{
		$fields = $this->getFields();
		if (!$fields)
			return '';

		return $safe? $fields['last_name']: htmlspecialcharsback($fields['last_name']);
	}

	/**
	 * @return string
	 */
	public function getAvatar()
	{
		$fields = $this->getFields();

		return $fields && $fields['avatar'] != '/bitrix/js/im/images/blank.gif'? $fields['avatar']: '';
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		$fields = $this->getFields();

		return $fields? $fields['status']: '';
	}

	/**
	 * @return string
	 */
	public function getIdle()
	{
		$fields = $this->getFields();

		if ($fields && $fields['idle'])
		{
			return $fields['idle'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return string
	 */
	public function getLastActivityDate()
	{
		$fields = $this->getFields();

		if ($fields && $fields['last_activity_date'])
		{
			return $fields['last_activity_date'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return string
	 */
	public function getMobileLastDate()
	{
		$fields = $this->getFields();

		if ($fields && $fields['mobile_last_date'])
		{
			return $fields['mobile_last_date'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return string
	 */
	public function getBirthday()
	{
		$fields = $this->getFields();

		return $fields? $fields['birthday']: '';
	}

	/**
	 * @return string
	 */
	public function getAvatarId()
	{
		$fields = $this->getFields();

		return $fields? $fields['avatar_id']: 0;
	}

	/**
	 * @return string
	 */
	public function getWorkPosition($safe = false)
	{
		$fields = $this->getFields();

		if ($fields)
		{
			return $safe? $fields['work_position']: htmlspecialcharsback($fields['work_position']);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return string
	 */
	public function getGender()
	{
		$fields = $this->getFields();

		return $fields? $fields['gender']: '';
	}

	/**
	 * @return string
	 */
	public function getExternalAuthId()
	{
		$fields = $this->getFields();

		return $fields? $fields['external_auth_id']: '';
	}

	/**
	 * @return string
	 */
	public function getWebsite()
	{
		$fields = $this->getFields();

		return $fields? $fields['website']: '';
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		$fields = $this->getFields();

		return $fields? $fields['email']: '';
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public function getPhone($type = self::PHONE_ANY)
	{
		$fields = $this->getPhones();

		$result = '';
		if ($type == self::PHONE_ANY)
		{
			if (isset($fields[self::PHONE_MOBILE]))
			{
				$result = $fields[self::PHONE_MOBILE];
			}
			else if (isset($fields[self::PHONE_PERSONAL]))
			{
				$result = $fields[self::PHONE_PERSONAL];
			}
			else if (isset($fields[self::PHONE_WORK]))
			{
				$result = $fields[self::PHONE_WORK];
			}
		}
		else if (isset($fields[$type]))
		{
			$result = $fields[$type];
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function getColor()
	{
		$fields = $this->getFields();

		return $fields? $fields['color']: '';
	}

	/**
	 * @return string
	 */
	public function getTzOffset()
	{
		$fields = $this->getFields();

		return $fields? $fields['tz_offset']: '';
	}

	/**
	 * @return bool
	 */
	public function isOnline()
	{
		$fields = $this->getFields();

		return $fields? $fields['status'] != 'offline': false;
	}
	/**
	 * @return bool
	 */
	public function isExtranet()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['extranet']: null;
	}

	/**
	 * @return bool
	 */
	public function isActive()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['active']: null;
	}

	/**
	 * @return bool
	 */
	public function isAbsent()
	{
		$fields = $this->getFields();

		if ($fields && $fields['absent'])
		{
			return $fields['absent'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function isNetwork()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['network']: null;
	}

	/**
	 * @return bool
	 */
	public function isBot()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['bot']: null;
	}

	/**
	 * @return bool
	 */
	public function isConnector()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['connector']: null;
	}

	/**
	 * @return bool
	 */
	public function isExists()
	{
		$fields = $this->getFields();

		return $fields? true: false;
	}

	/**
	 * @return array|null
	 */
	public function getFields()
	{
		$params = $this->getParams();

		return $params? $params['user']: null;
	}

	/**
	 * @return array|null
	 */
	public function getPhones()
	{
		$params = $this->getFields();

		return $params? $params['phones']: null;
	}

	/**
	 * @return array|null
	 */
	public function getDepartments()
	{
		$params = $this->getFields();

		return $params? $params['departments']: Array();
	}

	/**
	 * @return array|null
	 */
	public function getArray($options = array())
	{
		if (!$this->isExists())
		{
			return null;
		}

		$result = Array(
			'ID' => $this->getId(),
			'NAME' => $this->getFullName(false),
			'FIRST_NAME' => $this->getName(false),
			'LAST_NAME' => $this->getLastName(false),
			'WORK_POSITION' => $this->getWorkPosition(false),
			'COLOR' => $this->getColor(),
			'AVATAR' => $this->getAvatar(),
			'GENDER' => $this->getGender(),
			'BIRTHDAY' => (string)$this->getBirthday(),
			'EXTRANET' => $this->isExtranet(),
			'NETWORK' => $this->isNetwork(),
			'BOT' => $this->isBot(),
			'CONNECTOR' => $this->isConnector(),
			'EXTERNAL_AUTH_ID' => $this->getExternalAuthId(),
			'STATUS' => $this->getStatus(),
			'IDLE' => $this->getIdle(),
			'LAST_ACTIVITY_DATE' => $this->getLastActivityDate(),
			'MOBILE_LAST_DATE' => $this->getMobileLastDate(),
			'DEPARTMENTS' => $this->getDepartments(),
			'ABSENT' => $this->isAbsent(),
			'PHONES' => $this->getPhones(),
		);

		if ($options['JSON'])
		{
			foreach ($result as $key => $value)
			{
				if ($value instanceof \Bitrix\Main\Type\DateTime)
				{
					$result[$key] = date('c', $value->getTimestamp());
				}
				else if ($key == 'AVATAR' && is_string($value) && $value && strpos($value, 'http') !== 0)
				{
					$result[$key] = \Bitrix\Im\Common::getPublicDomain().$value;
				}
			}
			$result = array_change_key_case($result, CASE_LOWER);
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	private function getParams()
	{
		if (is_null($this->userData))
		{
			$userData = \CIMContactList::GetUserData(Array(
				'ID' => self::getId(),
				'PHONES' => 'Y',
				'EXTRA_FIELDS' => 'Y',
				'DATE_ATOM' => 'N'
			));
			if (isset($userData['users'][self::getId()]))
			{
				$this->userData['user'] = $userData['users'][self::getId()];
			}
		}
		return $this->userData;
	}

	public static function uploadAvatar($avatarUrl = '')
	{
		if (strlen($avatarUrl) <= 4)
			return '';

		if (!in_array(\GetFileExtension($avatarUrl), Array('png', 'jpg', 'gif')))
			return '';

		$orm = \Bitrix\Im\Model\ExternalAvatarTable::getList(Array(
			'filter' => Array('=LINK_MD5' => md5($avatarUrl))
		));
		if ($cache = $orm->fetch())
		{
			return $cache['AVATAR_ID'];
		}

		$recordFile = \CFile::MakeFileArray($avatarUrl);
		if (!\CFile::IsImage($recordFile['name'], $recordFile['type']))
			return '';

		if (is_array($recordFile) && $recordFile['size'] && $recordFile['size'] > 0 && $recordFile['size'] < 1000000)
		{
			$recordFile = array_merge($recordFile, array('MODULE_ID' => 'imbot'));
		}
		else
		{
			$recordFile = 0;
		}

		if ($recordFile)
		{
			$recordFile = \CFile::SaveFile($recordFile, 'botcontroller');
		}

		if ($recordFile > 0)
		{
			\Bitrix\Im\Model\ExternalAvatarTable::add(Array(
				'LINK_MD5' => md5($avatarUrl),
				'AVATAR_ID' => intval($recordFile)
			));
		}

		return $recordFile;
	}

	/**
	 * @return bool
	 */
	public static function clearStaticCache()
	{
		self::$instance = Array();
		return true;
	}

	public static function isOpenlinesOperator($userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$list = \Bitrix\ImOpenLines\Config::getQueueList($userId);

		return empty($list);
	}

	public static function getList($params)
	{
		$params = is_array($params)? $params: Array();

		if (!isset($params['CURRENT_USER']) && is_object($GLOBALS['USER']))
		{
			$params['CURRENT_USER'] = $GLOBALS['USER']->GetID();
		}

		$params['CURRENT_USER'] = intval($params['CURRENT_USER']);

		$userId = $params['CURRENT_USER'];
		if ($userId <= 0)
		{
			return false;
		}

		$enableLimit = false;
		if (isset($params['OFFSET']))
		{
			$filterLimit = intval($params['LIMIT']);
			$filterLimit = $filterLimit <= 0? self::FILTER_LIMIT: $filterLimit;

			$filterOffset = intval($params['OFFSET']);

			$enableLimit = true;
		}
		else
		{
			$filterLimit = false;
			$filterOffset = false;
		}

		$filter = self::getListFilter($params);
		if (is_null($filter))
		{
			return false;
		}

		$intranetInstalled = \Bitrix\Main\Loader::includeModule('intranet');
		$voximplantInstalled = \Bitrix\Main\Loader::includeModule('voximplant');

		$select = array(
			"ID", "LAST_NAME", "NAME", "LOGIN", "PERSONAL_PHOTO", "SECOND_NAME", "PERSONAL_BIRTHDAY", "WORK_POSITION", "PERSONAL_GENDER", "EXTERNAL_AUTH_ID", "WORK_PHONE", "PERSONAL_PHONE", "PERSONAL_MOBILE", "TIME_ZONE_OFFSET", "ACTIVE", "LAST_ACTIVITY_DATE",
			"COLOR" => "ST.COLOR", "STATUS" =>	"ST.STATUS", "IDLE" => "ST.IDLE", "MOBILE_LAST_DATE" => "ST.MOBILE_LAST_DATE",
		);
		if($intranetInstalled)
		{
			$select[] = 'UF_PHONE_INNER';
			$select[] = 'UF_DEPARTMENT';
		}
		if ($voximplantInstalled)
		{
			$select[] = 'UF_VI_PHONE';
		}

		$ormParams = Array(
			'select' => $select,
			'filter' => $filter,
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
		);
		if ($enableLimit)
		{
			$ormParams['offset'] = $filterOffset;
			$ormParams['limit'] = $filterLimit;
		}

		$orm = \Bitrix\Main\UserTable::getList($ormParams);

		$bots = \Bitrix\Im\Bot::getListCache();
		$nameTemplate = \CSite::GetNameFormat(false);

		$users = array();
		while ($user = $orm->fetch())
		{
			if (isset($extranetUsers[$user['ID']]))
			{
				continue;
			}

			$tmpFile = \CFile::ResizeImageGet(
				$user["PERSONAL_PHOTO"],
				array('width' => 100, 'height' => 100),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);

			$color = false;
			if (isset($user['COLOR']) && strlen($user['COLOR']) > 0)
			{
				$color = \Bitrix\Im\Color::getColor($user['COLOR']);
			}
			if (!$color)
			{
				$color = \CIMContactList::GetUserColor($user["ID"], $user['PERSONAL_GENDER'] == 'M'? 'M': 'F');
			}

			$users[$user["ID"]] = Array(
				'ID' => $user["ID"],
				'NAME' => \CUser::FormatName($nameTemplate, $user, true, false),
				'ACTIVE' => $user['ACTIVE'] == 'Y',
				'FIRST_NAME' => $user['NAME'],
				'LAST_NAME' => $user['LAST_NAME'],
				'WORK_POSITION' => $user['WORK_POSITION'],
				'COLOR' => $color,
				'AVATAR' => !empty($tmpFile['src'])? $tmpFile['src']: '',
				'BIRTHDAY' => $user['PERSONAL_BIRTHDAY'] instanceof \Bitrix\Main\Type\Date? $user['PERSONAL_BIRTHDAY']->format('d-m'): false,
				'GENDER' => $user['PERSONAL_GENDER'] == 'F'? 'F': 'M',
				'PHONE_DEVICE' => $voximplantInstalled && $user['UF_VI_PHONE'] == 'Y',
				'EXTRANET' => \CIMContactList::IsExtranet($user),
				'TZ_OFFSET' => intval($user['TIME_ZONE_OFFSET']),
				'NETWORK' => $user['EXTERNAL_AUTH_ID'] == \CIMContactList::NETWORK_AUTH_ID || $user['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID && $bots[$user["ID"]]['TYPE'] == \Bitrix\Im\Bot::TYPE_NETWORK,
				'BOT' => $user['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID,
				'PROFILE' => \CIMContactList::GetUserPath($user["ID"]),
				'EXTERNAL_AUTH_ID' => $user['EXTERNAL_AUTH_ID']? $user['EXTERNAL_AUTH_ID']: 'default',
				'STATUS' => $user['STATUS'],
				'IDLE' => $user['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $user['IDLE']: false,
				'LAST_ACTIVITY_DATE' => $user['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $user['MOBILE_LAST_DATE']: false,
				'MOBILE_LAST_DATE' => $user['LAST_ACTIVITY_DATE'] instanceof \Bitrix\Main\Type\DateTime? $user['LAST_ACTIVITY_DATE']: false,
				'ABSENT' => \CIMContactList::formatAbsentResult($user["ID"]),
			);

			if ($voximplantInstalled)
			{
				$user["WORK_PHONE"] = \CVoxImplantPhone::Normalize($user["WORK_PHONE"]);
				if ($user["WORK_PHONE"])
				{
					$users[$user["ID"]]['PHONES']['WORK_PHONE'] = $user['WORK_PHONE'];
				}
				$user["PERSONAL_MOBILE"] = \CVoxImplantPhone::Normalize($user["PERSONAL_MOBILE"]);
				if ($user["PERSONAL_MOBILE"])
				{
					$users[$user["ID"]]['PHONES']['PERSONAL_MOBILE'] = $user['PERSONAL_MOBILE'];
				}
				$user["PERSONAL_PHONE"] = \CVoxImplantPhone::Normalize($user["PERSONAL_PHONE"]);
				if ($user["PERSONAL_PHONE"])
				{
					$users[$user["ID"]]['PHONES']['PERSONAL_PHONE'] = $user['PERSONAL_PHONE'];
				}
				$user["UF_PHONE_INNER"] = preg_replace("/[^0-9\#\*]/i", "", $user["UF_PHONE_INNER"]);
				if ($user["UF_PHONE_INNER"])
				{
					$users[$user["ID"]]['PHONES']['INNER_PHONE'] = $user["UF_PHONE_INNER"];
				}
			}
			else
			{
				$users[$user["ID"]]['PHONES']['WORK_PHONE'] = $user['WORK_PHONE'];
				$users[$user["ID"]]['PHONES']['PERSONAL_MOBILE'] = $user['PERSONAL_MOBILE'];
				$users[$user["ID"]]['PHONES']['PERSONAL_PHONE'] = $user['PERSONAL_PHONE'];
				$users[$user["ID"]]['PHONES']['INNER_PHONE'] = $user["UF_PHONE_INNER"];
			}
		}

		if ($params['JSON'])
		{

			foreach ($users as $key => $value)
			{
				if ($value instanceof \Bitrix\Main\Type\DateTime)
				{
					$users[$key] = date('c', $value->getTimestamp());
				}
				else if (is_string($value) && $value && in_array($key, Array('AVATAR')) && strpos($value, 'http') !== 0)
				{
					$users[$key] = \Bitrix\Im\Common::getPublicDomain().$value;
				}
				else if (is_array($value))
				{
					$users[$key] = array_change_key_case($value, CASE_LOWER);
				}
			}
		}

		return $users;
	}

	public static function getListFilter($params)
	{
		if (isset($params['FILTER']['SEARCH']))
		{
			$filter = \Bitrix\Main\UserUtils::getUserSearchFilter(Array('FIND' => $params['FILTER']['SEARCH']));
			if (empty($filter))
			{
				return null;
			}
		}
		else
		{
			$filter = Array();
		}

		$filter['=ACTIVE'] = 'Y';
		$filter['=CONFIRM_CODE'] = false;
		foreach (\Bitrix\Main\UserTable::getExternalUserTypes() as $authId)
		{
			if ($authId != \Bitrix\Im\Bot::EXTERNAL_AUTH_ID)
			{
				$filter['!=EXTERNAL_AUTH_ID'][] = $authId;
			}
		}

		$extranetUsers = Array($params['CURRENT_USER'] => $params['CURRENT_USER']);

		$groups = \Bitrix\Im\Integration\Socialnetwork\Extranet::getGroup(Array(), $params['CURRENT_USER']);
		if (is_array($groups))
		{
			foreach ($groups as $group)
			{
				foreach ($group['USERS'] as $userId)
				{
					$extranetUsers[$userId] = $userId;
				}
			}
		}

		if (User::getInstance()->isExtranet())
		{
			$filter['=ID'] = array_keys($extranetUsers);
		}

		return $filter;
	}
}