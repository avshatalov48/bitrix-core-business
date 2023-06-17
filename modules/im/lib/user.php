<?php
namespace Bitrix\Im;

use Bitrix\Im\Model\StatusTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class User
{
	private static $instance = Array();
	private $userId = 0;
	private $userData = null;

	static $formatNameTemplate = null;

	const FILTER_LIMIT = 50;

	const PHONE_ANY = 'phone_any';
	const PHONE_WORK = 'work_phone';
	const PHONE_PERSONAL = 'personal_phone';
	const PHONE_MOBILE = 'personal_mobile';
	const PHONE_INNER = 'uf_phone_inner';

	const SERVICE_ANY = 'service_any';
	const SERVICE_ZOOM = 'zoom';
	const SERVICE_SKYPE = 'skype';

	protected function __construct($userId = null)
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
	 * @return self
	 */
	public static function getInstance($userId = null): self
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
	public function getAvatarHr()
	{
		$fields = $this->getFields();
		if (!$fields)
		{
			return '';
		}

		return $fields['avatar'];
	}

	/**
	 * @return string
	 */
	public function getProfile()
	{
		$fields = $this->getFields();
		if (!$fields)
		{
			return '';
		}

		return $fields['profile'];
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		$fields = $this->getFields();
		if (!$fields)
		{
			return 'offline';
		}

		return $fields['status']?: 'online';
	}

	/**
	 * @return string
	 */
	public function getIdle()
	{
		return $this->getOnlineFields()['idle'];
	}

	/**
	 * @return string
	 */
	public function getLastActivityDate()
	{
		return $this->getOnlineFields()['last_activity_date'];
	}

	/**
	 * @return string
	 */
	public function getMobileLastDate()
	{
		return $this->getOnlineFields()['mobile_last_date'];
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
	 * @param string $type
	 * @return string
	 */
	public function getService($type = self::PHONE_ANY)
	{
		$fields = $this->getServices();

		$result = '';
		if ($type == self::SERVICE_ANY)
		{
			if (isset($fields[self::SERVICE_SKYPE]))
			{
				$result = $fields[self::SERVICE_SKYPE];
			}
			else if (isset($fields[self::SERVICE_ZOOM]))
			{
				$result = $fields[self::SERVICE_ZOOM];
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
		return \CIMContactList::formatAbsentResult($this->getId());
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
	public function getServices()
	{
		$params = $this->getFields();

		return $params? $params['services']: null;
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
	 * Returns an array describing the user.
	 *
	 * @param array $options
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getArray($options = [])
	{
		if (!$this->isExists())
		{
			return null;
		}

		$hrPhotoOption = $options['HR_PHOTO'] ?? null;
		$livechatOption = $options['LIVECHAT'] ?? null;
		$jsonOption = $options['JSON'] ?? null;
		$skipOnlineOption = $options['SKIP_ONLINE'] ?? null;

		$result = [
			'ID' => $this->getId(),
			'ACTIVE' => $this->isActive(),
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
			'IDLE' => $skipOnlineOption === 'Y' ? false: $this->getIdle(),
			'LAST_ACTIVITY_DATE' => $skipOnlineOption === 'Y' ? false: $this->getLastActivityDate(),
			'MOBILE_LAST_DATE' => $skipOnlineOption === 'Y' ? false: $this->getMobileLastDate(),
			'ABSENT' => $this->isAbsent(),
			'DEPARTMENTS' => $this->getDepartments(),
			'PHONES' => $this->getPhones(),
		];
		if ($hrPhotoOption)
		{
			$result['AVATAR_HR'] = $this->getAvatarHr();
		}

		//TODO: Live chat, open lines
		//Just one call, here: \Bitrix\ImOpenLines\Connector::onStartWriting and \Bitrix\Im\Chat::getMessages
		if ($livechatOption && !$this->isConnector())
		{
			$lineId = \Bitrix\ImOpenLines\Queue::getActualLineId(['LINE_ID' => $livechatOption, 'USER_CODE' => $options['USER_CODE']]);

			$imolUserData = \Bitrix\ImOpenLines\Queue::getUserData($lineId, $this->getId());
			if ($imolUserData)
			{
				$result = array_merge($result, $imolUserData);
				$result['AVATAR_HR'] = $result['AVATAR'];
			}
		}
		//TODO: END: Live chat, open lines

		if ($jsonOption)
		{
			foreach ($result as $key => $value)
			{
				if ($value instanceof \Bitrix\Main\Type\DateTime)
				{
					$result[$key] = date('c', $value->getTimestamp());
				}
				else if (is_string($value) && is_string($key) && in_array($key, ['AVATAR', 'AVATAR_HR']) && is_string($value) && $value && mb_strpos($value, 'http') !== 0)
				{
					$result[$key] = \Bitrix\Im\Common::getPublicDomain().$value;
				}
			}
			$result = array_change_key_case($result, CASE_LOWER);
		}

		return $result;
	}

	/**
	 * @param static[] $users
	 * @return array
	 */
	public static function getArrayWithOnline(array $users, array $options = ['JSON' => 'Y', 'SKIP_ONLINE' => 'Y']): array
	{
		$result = [];

		foreach ($users as $user)
		{
			$result[$user->getId()] = $user->getArray($options);
		}

		$ids = array_keys($result);

		if (empty($ids))
		{
			return [];
		}

		$statuses = StatusTable::query()
			->setSelect(['USER_ID', 'IDLE', 'MOBILE_LAST_DATE', 'DESKTOP_LAST_DATE', 'LAST_ACTIVITY_DATE' => 'USER.LAST_ACTIVITY_DATE'])
			->whereIn('USER_ID', $ids)
			->fetchAll()
		;

		foreach ($statuses as $status)
		{
			$id = (int)$status['USER_ID'];
			$result[$id]['last_activity_date'] = $status['LAST_ACTIVITY_DATE'] ? $status['LAST_ACTIVITY_DATE']->format('c') : false;
			$result[$id]['desktop_last_date'] = $status['DESKTOP_LAST_DATE'] ? $status['DESKTOP_LAST_DATE']->format('c') : false;
			$result[$id]['mobile_last_date'] = $status['MOBILE_LAST_DATE'] ? $status['MOBILE_LAST_DATE']->format('c') : false;
			$result[$id]['idle'] = $status['IDLE'] ? $status['IDLE']->format('c') : false;
		}

		return array_values($result);
	}

	/**
	 * @return array|null
	 */
	private function getParams()
	{
		if (is_null($this->userData))
		{
			$userData = \CIMContactList::GetUserData(Array(
				'ID' => $this->getId(),
				'PHONES' => 'Y',
				'EXTRA_FIELDS' => 'Y',
				'DATE_ATOM' => 'N',
				'SHOW_ONLINE' => 'N',
			));
			if (isset($userData['users'][$this->getId()]))
			{
				$this->userData['user'] = $userData['users'][$this->getId()];
			}
		}
		return $this->userData;
	}

	/**
	 * @param string $avatarUrl
	 * @param string $hash
	 *
	 * @return int
	 */
	public static function uploadAvatar($avatarUrl = '', $hash = '')
	{
		if (!$ar = parse_url($avatarUrl))
		{
			return 0;
		}

		if (!preg_match('#\.(png|jpg|jpeg|gif|webp)$#i', $ar['path'], $matches))
		{
			return 0;
		}

		$hash = md5($hash. $avatarUrl);

		$orm = \Bitrix\Im\Model\ExternalAvatarTable::getList([
			'select' => ['*', 'FILE_EXISTS' => 'FILE.ID'],
			'filter' => ['=LINK_MD5' => $hash]
		]);
		if ($cache = $orm->fetch())
		{
			if ((int)$cache['FILE_EXISTS'] > 0)
			{
				return (int)$cache['AVATAR_ID'];
			}
			else
			{
				\Bitrix\Im\Model\ExternalAvatarTable::delete((int)$cache['ID']);
			}
		}

		try
		{
			$tempPath =  \CFile::GetTempName('', $hash.'.'.$matches[1]);

			$http = new \Bitrix\Main\Web\HttpClient();
			if (!defined('BOT_CLIENT_URL'))
			{
				$http->setPrivateIp(false);
			}
			if ($http->download($avatarUrl, $tempPath))
			{
				$recordFile = \CFile::MakeFileArray($tempPath);
			}
			else
			{
				return 0;
			}
		}
		catch (\Bitrix\Main\IO\IoException $exception)
		{
			return 0;
		}

		if (!\CFile::IsImage($recordFile['name'], $recordFile['type']))
		{
			return 0;
		}

		if (is_array($recordFile) && $recordFile['size'] && $recordFile['size'] > 0 && $recordFile['size'] < 1000000)
		{
			$recordFile = array_merge($recordFile, ['MODULE_ID' => 'imbot']);
		}
		else
		{
			$recordFile = 0;
		}

		if ($recordFile)
		{
			$recordFile = \CFile::SaveFile($recordFile, 'botcontroller', true);
		}

		if ((int)$recordFile > 0)
		{
			\Bitrix\Im\Model\ExternalAvatarTable::add([
				'LINK_MD5' => $hash,
				'AVATAR_ID' => (int)$recordFile
			]);
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

		return \Bitrix\ImOpenLines\Config::isOperator($userId);
	}

	private function getOnlineFields()
	{
		$online = \CIMStatus::GetList(Array('ID' => $this->getId()));
		if (!$online || !isset($online['users'][$this->getId()]))
		{
			return null;
		}

		$online = $online['users'][$this->getId()];

		return [
			'id' => $this->getId(),
			'color' => $online['color']?: '',
			'idle' => $online['idle']?: false,
			'last_activity_date' => $online['last_activity_date']?: false,
			'mobile_last_date' => $online['mobile_last_date']?: false,
		];
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

		$ormParams = self::getListParams($params);
		if (is_null($ormParams))
		{
			return false;
		}

		$filter = $ormParams['filter'];
		$filter['ACTIVE'] = 'Y';

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

		$users = array();
		while ($user = $orm->fetch())
		{
			if (\CIMContactList::IsExtranet($user))
			{
				continue;
			}

			$color = false;
			if (isset($user['COLOR']) && $user['COLOR'] <> '')
			{
				$color = \Bitrix\Im\Color::getColor($user['COLOR']);
			}
			if (!$color)
			{
				$color = \CIMContactList::GetUserColor($user["ID"], $user['PERSONAL_GENDER'] == 'M'? 'M': 'F');
			}

			$users[$user["ID"]] = Array(
				'ID' => (int)$user["ID"],
				'NAME' => \Bitrix\Im\User::formatFullNameFromDatabase($user),
				'FIRST_NAME' => \Bitrix\Im\User::formatNameFromDatabase($user),
				'LAST_NAME' => $user['LAST_NAME'],
				'WORK_POSITION' => $user['WORK_POSITION'],
				'COLOR' => $color,
				'AVATAR' => \CIMChat::GetAvatarImage($user["PERSONAL_PHOTO"], 200, false),
				'GENDER' => $user['PERSONAL_GENDER'] == 'F'? 'F': 'M',
				'BIRTHDAY' => $user['PERSONAL_BIRTHDAY'] instanceof \Bitrix\Main\Type\Date? $user['PERSONAL_BIRTHDAY']->format('d-m'): false,
				'EXTRANET' => \CIMContactList::IsExtranet($user),
				'NETWORK' => $user['EXTERNAL_AUTH_ID'] == \CIMContactList::NETWORK_AUTH_ID || $user['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID && $bots[$user["ID"]]['TYPE'] == \Bitrix\Im\Bot::TYPE_NETWORK,
				'BOT' => $user['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID,
				'CONNECTOR' => $user['EXTERNAL_AUTH_ID'] == "imconnector",
				'EXTERNAL_AUTH_ID' => $user['EXTERNAL_AUTH_ID']? $user['EXTERNAL_AUTH_ID']: 'default',
				'STATUS' => $user['STATUS'],
				'IDLE' => $user['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $user['IDLE']: false,
				'LAST_ACTIVITY_DATE' => $user['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $user['MOBILE_LAST_DATE']: false,
				'MOBILE_LAST_DATE' => $user['LAST_ACTIVITY_DATE'] instanceof \Bitrix\Main\Type\DateTime? $user['LAST_ACTIVITY_DATE']: false,
				'DEPARTMENTS' => is_array($user['UF_DEPARTMENT']) && !empty($user['UF_DEPARTMENT'])? $user['UF_DEPARTMENT']: [],
				'ABSENT' => \CIMContactList::formatAbsentResult($user["ID"]),
			);
			if ($params['HR_PHOTO'])
			{
				$users[$user["ID"]]['AVATAR_HR'] = $users[$user["ID"]]['avatar'];
			}

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
			foreach ($users as $key => $userData)
			{
				foreach ($userData as $field => $value)
				{
					if ($value instanceof \Bitrix\Main\Type\DateTime)
					{
						$users[$key][$field] = date('c', $value->getTimestamp());
					}
					else if (is_string($value) && $value && is_string($field) &&  in_array($field, Array('AVATAR', 'AVATAR_HR')) && mb_strpos($value, 'http') !== 0)
					{
						$users[$key][$field] = \Bitrix\Im\Common::getPublicDomain().$value;
					}
					else if (is_array($value))
					{
						$users[$key][$field] = array_change_key_case($value, CASE_LOWER);
					}
				}
				$users[$key] = array_change_key_case($users[$key], CASE_LOWER);;
			}
		}

		return $users;
	}

	public static function getListParams($params)
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
		$filter['!=EXTERNAL_AUTH_ID'] = \Bitrix\Im\Model\UserTable::filterExternalUserTypes([\Bitrix\Im\Bot::EXTERNAL_AUTH_ID]);

		$filterByUsers = [];

		if (User::getInstance($params['CURRENT_USER'])->isExtranet())
		{
			$groups = \Bitrix\Im\Integration\Socialnetwork\Extranet::getGroup(Array(), $params['CURRENT_USER']);
			if (is_array($groups))
			{
				foreach ($groups as $group)
				{
					foreach ($group['USERS'] as $userId)
					{
						$filterByUsers[$userId] = $userId;
					}
				}
				$filterByUsers[$params['CURRENT_USER']] = $params['CURRENT_USER'];
			}
		}

		if (
			$params['FILTER']['BUSINESS'] == 'Y'
			&& \Bitrix\Main\Loader::includeModule('bitrix24')
			&& !\CBitrix24BusinessTools::isLicenseUnlimited()
		)
		{
			$businessUsers = \CBitrix24BusinessTools::getUnlimUsers();

			if (User::getInstance($params['CURRENT_USER'])->isExtranet())
			{
				$extranetBusinessResult = [];
				foreach ($filterByUsers as $userId)
				{
					if (in_array($userId, $businessUsers))
					{
						$extranetBusinessResult[$userId] = $userId;
					}
				}
				$filterByUsers = $extranetBusinessResult;
			}
			else
			{
				foreach ($businessUsers as $userId)
				{
					$filterByUsers[$userId] = $userId;
				}
			}
		}

		if ($filterByUsers)
		{
			$filter['=ID'] = array_keys($filterByUsers);
		}

		return ['filter' => $filter];
	}

	public static function getBusiness($userId = null, $options = array())
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$pagination = isset($options['LIST'])? true: false;

		$limit = isset($options['LIST']['LIMIT'])? intval($options['LIST']['LIMIT']): 50;
		$offset = isset($options['LIST']['OFFSET'])? intval($options['LIST']['OFFSET']): 0;

		$list = Array();

		$businessUsersAvailable = false;
		if (\Bitrix\Main\Loader::includeModule('bitrix24') && !\CBitrix24BusinessTools::isLicenseUnlimited())
		{
			$businessUsers = \CBitrix24BusinessTools::getUnlimUsers();

			if (User::getInstance($userId)->isExtranet())
			{
				$extranetBusinessResult = [];
				$groups = \Bitrix\Im\Integration\Socialnetwork\Extranet::getGroup(Array(), $userId);
				if (is_array($groups))
				{
					foreach ($groups as $group)
					{
						foreach ($group['USERS'] as $uid)
						{
							if (in_array($uid, $businessUsers))
							{
								$extranetUserList[$uid] = $uid;
							}
						}
					}
				}
				$list = $extranetBusinessResult;
			}
			else
			{
				foreach ($businessUsers as $userId)
				{
					$list[$userId] = $userId;
				}
			}

			$businessUsersAvailable = true;
		}

		$count = count($list);

		$list = array_slice($list, $offset, $limit);

		if ($options['USER_DATA'] == 'Y')
		{
			$result = Array();

			$getOptions = Array();
			if ($options['JSON'] == 'Y')
			{
				$getOptions['JSON'] = 'Y';
			}

			foreach ($list as $userId)
			{
				$result[] = \Bitrix\Im\User::getInstance($userId)->getArray($getOptions);
			}
		}
		else
		{
			$result = array_values($list);
		}

		if ($pagination)
		{
			$result = Array('TOTAL' => $count, 'RESULT' => $result, 'AVAILABLE' => $businessUsersAvailable);

			if ($options['JSON'] == 'Y')
			{
				$result = array_change_key_case($result, CASE_LOWER);
			}
		}
		else
		{
			if (!$businessUsersAvailable)
			{
				$result = false;
			}
		}

		return $result;
	}

	public static function getMessages($userId = null, $options = Array())
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}


		$filter = Array(
			'=AUTHOR_ID' => $userId
		);

		if (isset($options['FIRST_ID']))
		{
			$order = array();

			if (intval($options['FIRST_ID']) > 0)
			{
				$filter['>ID'] = $options['FIRST_ID'];
			}
		}
		else
		{
			$order = Array('ID' => 'DESC');

			if (isset($options['LAST_ID']) && intval($options['LAST_ID']) > 0)
			{
				$filter['<ID'] = intval($options['LAST_ID']);
			}
		}

		if (isset($options['LIMIT']))
		{
			$options['LIMIT'] = intval($options['LIMIT']);
			$limit = $options['LIMIT'] >= 500? 500: $options['LIMIT'];
		}
		else
		{
			$limit = 50;
		}

		$skipMessage = isset($options['SKIP_MESSAGE']) && $options['SKIP_MESSAGE'] == 'Y';

		$select = Array(
			'ID', 'CHAT_ID', 'DATE_CREATE',
			'CHAT_TITLE' => 'CHAT.TITLE'
		);
		if (!$skipMessage)
		{
			$select[] = 'MESSAGE';
		}

		$orm = \Bitrix\Im\Model\MessageTable::getList(array(
			'filter' => $filter,
			'select' => $select,
			'order' => $order,
			'limit' => $limit
		));

		$messages = Array();
		$messagesChat = Array();
		while($message = $orm->fetch())
		{
			$messages[$message['ID']] = Array(
				'ID' => (int)$message['ID'],
				'DATE' => $message['DATE_CREATE'],
				'TEXT' => (string)$message['MESSAGE'],
			);

			if ($skipMessage)
			{
				unset($messages[$message['ID']]['TEXT']);
			}

			$messagesChat[$message['ID']] = Array(
				'ID' => (int)$message['ID'],
				'CHAT_ID' => (int)$message['CHAT_ID']
			);
		}

		$params = \CIMMessageParam::Get(array_keys($messages));

		$fileIds = Array();
		foreach ($params as $messageId => $param)
		{
			$messages[$messageId]['params'] = empty($param)? null: $param;

			if (isset($param['FILE_ID']))
			{
				foreach ($param['FILE_ID'] as $fileId)
				{
					$fileIds[$messagesChat[$messageId]['CHAT_ID']][$fileId] = $fileId;
				}
			}
		}

		$messages = \CIMMessageLink::prepareShow($messages, $params);

		$files = array();
		foreach ($fileIds as $chatId => $fileId)
		{
			if ($result = \CIMDisk::GetFiles($chatId, $fileId))
			{
				$files = array_merge($files, $result);
			}
		}

		$result = Array(
			'MESSAGES' => $messages,
			'FILES' => $files,
		);

		if ($options['JSON'])
		{
			foreach ($result['MESSAGES'] as $key => $value)
			{
				if ($value['DATE'] instanceof \Bitrix\Main\Type\DateTime)
				{
					$result['MESSAGES'][$key]['DATE'] = date('c', $value['DATE']->getTimestamp());
				}

				$result['MESSAGES'][$key] = array_change_key_case($result['MESSAGES'][$key], CASE_LOWER);
			}
			$result['MESSAGES'] = array_values($result['MESSAGES']);

			foreach ($result['FILES'] as $key => $value)
			{
				if ($value['date'] instanceof \Bitrix\Main\Type\DateTime)
				{
					$result['FILES'][$key]['date'] = date('c', $value['date']->getTimestamp());
				}

				foreach (['urlPreview', 'urlShow', 'urlDownload'] as $field)
				{
					$url = $result['FILES'][$key][$field];
					if (is_string($url) && $url && mb_strpos($url, 'http') !== 0)
					{
						$result['FILES'][$key][$field] = \Bitrix\Im\Common::getPublicDomain().$url;
					}
				}
			}

			$result = array_change_key_case($result, CASE_LOWER);
		}

		return $result;
	}

	public static function formatNameFromDatabase($fields)
	{
		if (empty($fields['NAME']) && empty($fields['LAST_NAME']))
		{
			if (in_array($fields['EXTERNAL_AUTH_ID'], \Bitrix\Main\UserTable::getExternalUserTypes()))
			{
				return Loc::getMessage('IM_USER_GUEST_NAME');
			}
			else if (!empty($fields['LOGIN']))
			{
				return $fields['LOGIN'];
			}
			else
			{
				return Loc::getMessage('IM_USER_ANONYM_NAME');
			}
		}

		return $fields['NAME'];
	}

	public static function formatFullNameFromDatabase($fields)
	{
		if (is_null(self::$formatNameTemplate))
		{
			self::$formatNameTemplate = \CSite::GetNameFormat(false);
		}

		if (empty($fields['NAME']) && empty($fields['LAST_NAME']))
		{
			if (in_array($fields['EXTERNAL_AUTH_ID'], \Bitrix\Main\UserTable::getExternalUserTypes()))
			{
				return Loc::getMessage('IM_USER_GUEST_NAME');
			}
			else if (!empty($fields['LOGIN']))
			{
				return $fields['LOGIN'];
			}
			else
			{
				return Loc::getMessage('IM_USER_ANONYM_NAME');
			}
		}

		return \CUser::FormatName(self::$formatNameTemplate, $fields, true, false);
	}
}
