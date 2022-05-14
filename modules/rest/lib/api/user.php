<?php
namespace Bitrix\Rest\Api;

use Bitrix\Intranet\Invitation;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UserTable;
use Bitrix\Rest\AppTable;
use Bitrix\Rest\RestException;
use Bitrix\Rest\Controller\File;

class User extends \IRestService
{
	public const SCOPE_USER = 'user';
	public const SCOPE_USER_BASIC = 'user_basic';
	public const SCOPE_USER_BRIEF = 'user_brief';

	private const ALLOWED_USER_NAME_FIELDS = [
		'ID',
		'XML_ID',
		'ACTIVE',
		'NAME',
		'LAST_NAME',
		'SECOND_NAME',
		'TITLE',
		'IS_ONLINE',
		'TIME_ZONE',
		'TIME_ZONE_OFFSET',
		'TIME_ZONE_OFFSET',
		'TIMESTAMP_X',
		'DATE_REGISTER',
		'PERSONAL_PROFESSION',
		'PERSONAL_GENDER',
		'PERSONAL_BIRTHDAY',
		'PERSONAL_PHOTO',
		'PERSONAL_CITY',
		'PERSONAL_STATE',
		'PERSONAL_COUNTRY',
		'WORK_POSITION',
		'WORK_CITY',
		'WORK_STATE',
		'WORK_COUNTRY',
		'LAST_ACTIVITY_DATE',
		'UF_EMPLOYMENT_DATE',
		'UF_TIMEMAN',
		'UF_SKILLS',
		'UF_INTERESTS',
		'UF_DEPARTMENT',
		'UF_PHONE_INNER',
	];

	private const ALLOWED_USER_BASIC_FIELDS = [
		'ID',
		'XML_ID',
		'ACTIVE',
		'NAME',
		'LAST_NAME',
		'SECOND_NAME',
		'TITLE',
		'EMAIL',
		'PERSONAL_PHONE',
		'WORK_PHONE',
		'WORK_POSITION',
		'WORK_COMPANY',
		'IS_ONLINE',
		'TIME_ZONE',
		'TIMESTAMP_X',
		'TIME_ZONE_OFFSET',
		'DATE_REGISTER',
		'LAST_ACTIVITY_DATE',
		'PERSONAL_PROFESSION',
		'PERSONAL_GENDER',
		'PERSONAL_BIRTHDAY',
		'PERSONAL_PHOTO',
		'PERSONAL_PHOTO',
		'PERSONAL_PHONE',
		'PERSONAL_FAX',
		'PERSONAL_MOBILE',
		'PERSONAL_PAGER',
		'PERSONAL_STREET',
		'PERSONAL_MAILBOX',
		'PERSONAL_CITY',
		'PERSONAL_STATE',
		'PERSONAL_ZIP',
		'PERSONAL_COUNTRY',
		'PERSONAL_NOTES',
		'WORK_COMPANY',
		'WORK_DEPARTMENT',
		'WORK_POSITION',
		'WORK_WWW',
		'WORK_PHONE',
		'WORK_FAX',
		'WORK_PAGER',
		'WORK_STREET',
		'WORK_MAILBOX',
		'WORK_CITY',
		'WORK_STATE',
		'WORK_ZIP',
		'WORK_COUNTRY',
		'WORK_PROFILE',
		'WORK_LOGO',
		'WORK_NOTES',
		'UF_DEPARTMENT',
		'UF_DISTRICT',
		'UF_SKYPE',
		'UF_SKYPE_LINK',
		'UF_ZOOM',
		'UF_TWITTER',
		'UF_FACEBOOK',
		'UF_LINKEDIN',
		'UF_XING',
		'UF_WEB_SITES',
		'UF_PHONE_INNER',
		'UF_EMPLOYMENT_DATE',
		'UF_TIMEMAN',
		'UF_SKILLS',
		'UF_INTERESTS',
	];

	private static $entityUser = 'USER';
	private static $nameFieldFullPrefix = 'UF_USR_';
	private static $userUserFieldList;

	protected static $allowedUserFields = array(
		'ID',
		'XML_ID',
		'ACTIVE',
		'NAME',
		'LAST_NAME',
		'SECOND_NAME',
		'TITLE',
		'EMAIL',
		'LAST_LOGIN',
		'DATE_REGISTER',
		'TIME_ZONE',
		'IS_ONLINE',
		'TIME_ZONE_OFFSET',
		'TIMESTAMP_X',
		'LAST_ACTIVITY_DATE',
		'PERSONAL_GENDER',
		'PERSONAL_PROFESSION',
		'PERSONAL_WWW',
		'PERSONAL_BIRTHDAY',
		'PERSONAL_PHOTO',
		'PERSONAL_ICQ',
		'PERSONAL_PHONE',
		'PERSONAL_FAX',
		'PERSONAL_MOBILE',
		'PERSONAL_PAGER',
		'PERSONAL_STREET',
		'PERSONAL_CITY',
		'PERSONAL_STATE',
		'PERSONAL_ZIP',
		'PERSONAL_COUNTRY',
		'PERSONAL_MAILBOX',
		'PERSONAL_NOTES',
		'PERSONAL_PROFESSION',
		'PERSONAL_GENDER',
		'PERSONAL_BIRTHDAY',

		'WORK_PHONE',
		'WORK_COMPANY',
		'WORK_POSITION',
		'WORK_DEPARTMENT',
		'WORK_WWW',
		'WORK_FAX',
		'WORK_PAGER',
		'WORK_STREET',
		'WORK_MAILBOX',
		'WORK_CITY',
		'WORK_STATE',
		'WORK_ZIP',
		'WORK_COUNTRY',
		'WORK_PROFILE',
		'WORK_LOGO',
		'WORK_NOTES',

		'UF_SKYPE_LINK',
		'UF_ZOOM',
		'UF_EMPLOYMENT_DATE',
		'UF_TIMEMAN',
		'UF_DEPARTMENT',
		'UF_INTERESTS',
		'UF_SKILLS',
		'UF_WEB_SITES',
		'UF_XING',
		'UF_LINKEDIN',
		'UF_FACEBOOK',
		'UF_TWITTER',
		'UF_SKYPE',
		'UF_DISTRICT',
		'UF_PHONE_INNER',
	);

	protected static $holdEditFields = [
		"LAST_LOGIN",
		"DATE_REGISTER",
		"IS_ONLINE",
		"TIME_ZONE_OFFSET",
	];

	public static function getDefaultAllowedUserFields()
	{
		$result = static::$allowedUserFields;

		if (Loader::includeModule('intranet'))
		{
			$result[] = 'USER_TYPE';
		}

		return $result;
	}

	private static function isMainScope(\CRestServer $server)
	{
		return in_array(static::SCOPE_USER, $server->getAuthScope());
	}

	private static function getErrorScope()
	{
		return [
			'error' => 'insufficient_scope',
			'error_description' => 'The request requires higher privileges than provided by the access token',
		];
	}

	private static function getAllowedUserFields($scopeList): array
	{
		$result = [];
		if (in_array(static::SCOPE_USER, $scopeList))
		{
			$result = static::getDefaultAllowedUserFields();
		}
		else
		{
			if (in_array(static::SCOPE_USER_BASIC, $scopeList))
			{
				$result = static::ALLOWED_USER_BASIC_FIELDS;
			}
			elseif (in_array(static::SCOPE_USER_BRIEF, $scopeList))
			{
				$result = static::ALLOWED_USER_NAME_FIELDS;
			}

			if (Loader::includeModule('intranet'))
			{
				$result[] = 'USER_TYPE';
			}

			if (in_array(UserField::SCOPE_USER_USERFIELD, $scopeList))
			{
				$result = array_merge($result, static::getUserFields());
			}
		}

		return $result;
	}

	public static function unsetDefaultAllowedUserField($key)
	{
		unset(static::$allowedUserFields[$key]);
	}

	public static function setDefaultAllowedUserField($field)
	{
		static::$allowedUserFields[] = $field;
	}

	public static function onRestServiceBuildDescription()
	{
		$result = array(
			\CRestUtil::GLOBAL_SCOPE => array(
				'user.admin' => array(__CLASS__, 'isAdmin'),
				'user.access' => array(__CLASS__, 'hasAccess'),
				'access.name' => array(__CLASS__, 'getAccess'),
			)
		);

		if(ModuleManager::isModuleInstalled('intranet'))
		{
			$result[static::SCOPE_USER] = array(
				'user.fields' => array(__CLASS__, 'getFields'),
				'user.current' => array(__CLASS__, 'userCurrent'),
				'user.get' => array(__CLASS__, 'userGet'),
				'user.search' => array(__CLASS__, 'userGet'),
				'user.add' => array(__CLASS__, 'userAdd'),
				'user.update' => array(__CLASS__, 'userUpdate'),
				'user.online' => array(__CLASS__, 'userOnline'),
				'user.counters' => array(__CLASS__, 'userCounters'),
				\CRestUtil::EVENTS => array(
					'OnUserAdd' => array('main', 'OnUserInitialize', array(__CLASS__, 'onUserInitialize')),
				),
			);
			$result[static::SCOPE_USER_BRIEF] = [
				'user.fields' => array(__CLASS__, 'getFields'),
				'user.current' => array(__CLASS__, 'userCurrent'),
				'user.get' => array(__CLASS__, 'userGet'),
				'user.search' => array(__CLASS__, 'userGet'),
				'user.online' => array(__CLASS__, 'userOnline'),
				'user.counters' => array(__CLASS__, 'userCounters'),
				\CRestUtil::EVENTS => array(
					'OnUserAdd' => array('main', 'OnUserInitialize', array(__CLASS__, 'onUserInitialize')),
				),
			];
			$result[static::SCOPE_USER_BASIC] = [
				'user.fields' => array(__CLASS__, 'getFields'),
				'user.current' => array(__CLASS__, 'userCurrent'),
				'user.get' => array(__CLASS__, 'userGet'),
				'user.search' => array(__CLASS__, 'userGet'),
				'user.online' => array(__CLASS__, 'userOnline'),
				'user.counters' => array(__CLASS__, 'userCounters'),
				\CRestUtil::EVENTS => array(
					'OnUserAdd' => array('main', 'OnUserInitialize', array(__CLASS__, 'onUserInitialize')),
				),
			];
			$result[UserField::SCOPE_USER_USERFIELD] = [
				'user.userfield.add' => [UserField::class, 'addRest'],
				'user.userfield.update' => [UserField::class, 'updateRest'],
				'user.userfield.delete' => [UserField::class, 'deleteRest'],
				'user.userfield.list' => [UserField::class, 'getListRest'],
				'user.userfield.file.get' => [__CLASS__, 'getFile'],
			];
		}

		return $result;
	}

	private static function getUserFields()
	{
		if (is_null(static::$userUserFieldList))
		{
			static::$userUserFieldList = [];
			global $USER_FIELD_MANAGER;

			$fields = $USER_FIELD_MANAGER->GetUserFields("USER");

			foreach ($fields as $code => $field)
			{
				if (mb_strpos($code, static::$nameFieldFullPrefix) === 0)
				{
					static::$userUserFieldList[] = $code;
				}
			}
		}

		return static::$userUserFieldList;
	}

	protected static function checkAllowedFields()
	{
		global $USER_FIELD_MANAGER;

		$fields = $USER_FIELD_MANAGER->GetUserFields("USER");

		foreach(static::getDefaultAllowedUserFields() as $key => $field)
		{
			if(mb_substr($field, 0, 3) === 'UF_' && !array_key_exists($field, $fields))
			{
				static::unsetDefaultAllowedUserField($key);
			}
		}

		foreach ($fields as $code => $field)
		{
			if (mb_strpos($code, static::$nameFieldFullPrefix) === 0)
			{
				static::setDefaultAllowedUserField($code);
			}
		}
	}

	public static function onUserInitialize($arParams, $arHandler)
	{
		$ID = $arParams[0];

		$dbRes = \CUser::GetByID($ID);
		$arUser = $dbRes->Fetch();

		if(in_array($arUser['EXTERNAL_AUTH_ID'], UserTable::getExternalUserTypes()))
		{
			throw new RestException('Unnecessary event call for this user type');
		}

		$allowedFields = null;
		if ($arHandler['APP_ID'] > 0)
		{
			$app = AppTable::getByClientId($arHandler['APP_CODE']);
			if ($app['SCOPE'])
			{
				$scope = explode(',', $app['SCOPE']);
				$allowedFields = static::getAllowedUserFields($scope);
			}
		}

		$arRes = self::getUserData($arUser, $allowedFields);
		if($arUser['PERSONAL_PHOTO'] > 0)
		{
			$arRes['PERSONAL_PHOTO'] = \CRestUtil::GetFile($arUser["PERSONAL_PHOTO"]);
		}

		return $arRes;
	}

	public static function isAdmin()
	{
		return \CRestUtil::isAdmin();
	}

	public static function hasAccess($params)
	{
		global $USER;

		$params = array_change_key_case($params, CASE_UPPER);

		if(!is_array($params['ACCESS']))
		{
			$params['ACCESS'] = array($params['ACCESS']);
		}

		return self::isAdmin() || $USER->canAccess($params['ACCESS']);
	}

	public static function getAccess($params)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if(!is_array($params['ACCESS']) || count($params['ACCESS']) <= 0)
		{
			return false;
		}
		else
		{
			$ob = new \CAccess();
			$res = $ob->getNames($params['ACCESS']);
			foreach($res as $key => $value)
			{
				if(!in_array($key, $params['ACCESS']))
					unset($res[$key]);
			}

			return $res;
		}
	}

	public static function getFields($query = [], $nav = 0, \CRestServer $server = null)
	{
		global $USER_FIELD_MANAGER;

		static::checkAllowedFields();

		$res = array();

		$langMessages = array_merge(
			IncludeModuleLangFile('/bitrix/modules/main/admin/user_edit.php', false, true),
			IncludeModuleLangFile('/bitrix/modules/main/admin/user_admin.php', false, true)
		);
		$fieldsList = $USER_FIELD_MANAGER->getUserFields('USER', 0, LANGUAGE_ID);
		if (!is_null($server))
		{
			$allowedFields = static::getAllowedUserFields($server->getAuthScope());
		}
		else
		{
			$allowedFields = static::getDefaultAllowedUserFields();
		}
		foreach ($allowedFields as $key)
		{
			if(mb_substr($key, 0, 3) != 'UF_')
			{
				$lkey = isset($langMessages[$key]) ? $key : str_replace('PERSONAL_', 'USER_', $key);
				$res[$key] = isset($langMessages[$lkey]) ? $langMessages[$lkey] : $key;
				if(mb_substr($res[$key], -1) == ':')
				{
					$res[$key] = mb_substr($res[$key], 0, -1);
				}
			}
			else
			{
				$res[$key] = $fieldsList[$key]['EDIT_FORM_LABEL'];
			}
		}

		return $res;
	}

	public static function userCurrent($query, $n, \CRestServer $server)
	{
		global $USER;

		static::checkAllowedFields();

		$dbRes = \CUser::getByID($USER->getID());
		$userFields = $dbRes->fetch();

		$allowedFields = static::getAllowedUserFields($server->getAuthScope());
		$result = self::getUserData($userFields, $allowedFields);
		if($userFields['PERSONAL_PHOTO'] > 0)
		{
			$result['PERSONAL_PHOTO'] = \CRestUtil::GetFile($userFields["PERSONAL_PHOTO"]);
		}

		$server->setSecurityState(array(
			"ID" => $result['ID'],
			"EMAIL" => $result['EMAIL'],
			"NAME" => $result['NAME'],
		));

		return $result;
	}

	public static function userGet($query, $nav = 0, \CRestServer $server)
	{
		global $USER;

		static::checkAllowedFields();

		static $moduleAdminList = false;

		$query = array_change_key_case($query, CASE_UPPER);

		$sort = $query['SORT'];
		$order = $query['ORDER'];
		$adminMode = false;

		//getting resize preset before user data preparing
		$resizePresets = [
			"small"=>["width"=>150, "height" => 150],
			"medium"=>["width"=>300, "height" => 300],
			"large"=>["width"=>1000, "height" => 1000],
		];

		$presetName = $query["IMAGE_RESIZE"];
		$resize = ($presetName && $resizePresets[$presetName]
			? $resizePresets[$presetName]
			: false);

		if(isset($query['ADMIN_MODE']) && $query['ADMIN_MODE'])
		{
			if ($moduleAdminList === false && Loader::includeModule('socialnetwork'))
			{
				$moduleAdminList = \Bitrix\Socialnetwork\User::getModuleAdminList(array(SITE_ID, false));
			}

			if(is_array($moduleAdminList))
			{
				$adminMode = (array_key_exists($USER->getID(), $moduleAdminList));
			}
		}

		$allowedUserFields = static::getAllowedUserFields($server->getAuthScope());
		$allowedUserFields[] = 'IS_ONLINE';
		$allowedUserFields[] = 'HAS_DEPARTAMENT';
		$allowedUserFields[] = 'NAME_SEARCH';
		$allowedUserFields[] = 'EXTERNAL_AUTH_ID';
		if ($server->getMethod() == "user.search")
		{
			$allowedUserFields[] = 'FIND';
			$allowedUserFields[] = 'UF_DEPARTMENT_NAME';
			$allowedUserFields[] = 'CONFIRM_CODE';
		}

		if(isset($query['FILTER']) && is_array($query['FILTER']))
		{
			/**
			 * The following code is a mistake
			 * but it must be here to save backward compatibility
			 */
			$query = array_change_key_case($query['FILTER'], CASE_UPPER);
		}

		$filter = self::prepareUserData($query, $allowedUserFields);

		if (isset($filter['NAME_SEARCH']) || isset($filter['FIND']))
		{
			$nameSearch = isset($filter['NAME_SEARCH'])? $filter['NAME_SEARCH']: $filter['FIND'];
			unset($filter['NAME_SEARCH']);
			unset($filter['FIND']);

			$filter = array_merge($filter, \Bitrix\Main\UserUtils::getUserSearchFilter(Array(
				'FIND' => $nameSearch
			)));
		}
		else if ($server->getMethod() == "user.search")
		{
			$previousFilter = $filter;
			unset($filter['NAME']);
			unset($filter['LAST_NAME']);
			unset($filter['SECOND_NAME']);
			unset($filter['WORK_POSITION']);
			unset($filter['UF_DEPARTMENT_NAME']);

			$filter = array_merge($filter, \Bitrix\Main\UserUtils::getUserSearchFilter(Array(
				'NAME' => $previousFilter['NAME'],
				'LAST_NAME' => $previousFilter['LAST_NAME'],
				'SECOND_NAME' => $previousFilter['SECOND_NAME'],
				'WORK_POSITION' => $previousFilter['WORK_POSITION'],
				'UF_DEPARTMENT_NAME' => $previousFilter['UF_DEPARTMENT_NAME'],
			)));
		}

		if (
			!$adminMode
			&& Loader::includeModule("extranet")
		)
		{
			$filteredUserIDs = \CExtranet::getMyGroupsUsersSimple(\CExtranet::getExtranetSiteID());
			$filteredUserIDs[] = $USER->getID();

			if (\CExtranet::isIntranetUser())
			{
				if (
					!isset($filter["ID"])
					|| !Loader::includeModule('socialnetwork')
					|| !\CSocNetUser::IsCurrentUserModuleAdmin(\CSite::getDefSite(), false)
				)
				{
					$filter[] = array(
						'LOGIC' => 'OR',
						'!UF_DEPARTMENT' => false,
						'ID' => $filteredUserIDs
					);
				}
			}
			else
			{
				$filter["ID"] = (isset($filter["ID"]) ? array_intersect((is_array($filter["ID"]) ? $filter["ID"] : array($filter["ID"])), $filteredUserIDs) : $filteredUserIDs);
			}
		}

		if(array_key_exists("HAS_DEPARTAMENT", $filter))
		{
			if ($filter["HAS_DEPARTAMENT"] === "Y")
			{
				$filter[] = [
					'LOGIC' => 'AND',
					'!UF_DEPARTMENT' => false,
				];
			}

			unset($filter["HAS_DEPARTAMENT"]);
		}

		$result = array();

		$filter['=IS_REAL_USER'] = 'Y';

		$getListClassName = '\Bitrix\Main\UserTable';
		if (Loader::includeModule('intranet'))
		{
			$getListClassName = '\Bitrix\Intranet\UserTable';
		}
		$getListMethodName = 'getList';

		$dbResCnt = $getListClassName::$getListMethodName(array(
			'filter' => $filter,
			'select' => array("CNT" => new ExpressionField('CNT', 'COUNT(1)')),
		));

		$resCnt = $dbResCnt->fetch();
		if ($resCnt && $resCnt["CNT"] > 0)
		{
			$navParams = self::getNavData($nav, true);

			$querySort = array();
			if($sort && $order)
			{
				$querySort[$sort] = $order;
			}
			$allowedFields = static::getAllowedUserFields($server->getAuthScope());
			$dbRes = $getListClassName::$getListMethodName(array(
				'order' => $querySort,
				'filter' => $filter,
				'select' => $allowedFields,
				'limit' => $navParams['limit'],
				'offset' => $navParams['offset'],
				'data_doubling' => false,
			));

			$result = array();
			$files = array();

			while($userInfo = $dbRes->fetch())
			{
				$result[] = self::getUserData($userInfo, $allowedFields);

				if($userInfo['PERSONAL_PHOTO'] > 0)
				{
					$files[] = $userInfo['PERSONAL_PHOTO'];
				}
			}

			if(count($files) > 0)
			{
				$files = \CRestUtil::getFile($files, $resize);

				foreach ($result as $key => $userInfo)
				{
					if($userInfo['PERSONAL_PHOTO'] > 0)
					{
						$result[$key]['PERSONAL_PHOTO'] = $files[$userInfo['PERSONAL_PHOTO']];
					}
				}
			}

			return self::setNavData(
				$result,
				array(
					"count" => $resCnt['CNT'],
					"offset" => $navParams['offset']
				)
			);
		}

		return $result;
	}

	public static function userOnline()
	{
		$dbRes = UserTable::getList(array(
			'filter' => array(
				'IS_ONLINE' => 'Y',
			),
			'select' => array('ID')
		));

		$onlineUsers = array();
		while($userData = $dbRes->fetch())
		{
			$onlineUsers[] = $userData['ID'];
		}

		return $onlineUsers;
	}

	public static function userCounters($arParams)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		global $USER;

		$counters = \CUserCounter::GetAllValues($USER->getID());

		if (!isset($arParams['SKIP_LIVEFEED_GROUP']) || $arParams['SKIP_LIVEFEED_GROUP'] != 'Y')
		{
			$counters = \CUserCounter::getGroupedCounters($counters);
		}

		return $counters;
	}

	public static function userAdd($userFields, $nav = 0, \CRestServer $server = null)
	{
		if (!is_null($server) && !static::isMainScope($server))
		{
			return static::getErrorScope();
		}

		global $APPLICATION, $USER;

		static::checkAllowedFields();

		$bB24 = ModuleManager::isModuleInstalled('bitrix24');
		$res = false;

		if(
			(
				$bB24 && $USER->canDoOperation('bitrix24_invite')
				|| $USER->canDoOperation('edit_all_users')
			)
			&& Loader::includeModule('intranet'))
		{
			$userFields = array_change_key_case($userFields, CASE_UPPER);

			$bExtranet = false;

			if (
				isset($userFields["EXTRANET"])
				&& $userFields["EXTRANET"] == "Y"
			)
			{
				if (IsModuleInstalled('extranet'))
				{
					$bExtranet = true;
					$userFields["UF_DEPARTMENT"] = array();

					if (!empty($userFields["SONET_GROUP_ID"]))
					{
						$sonetGroupId = $userFields["SONET_GROUP_ID"];
						if (!is_array($sonetGroupId))
						{
							$sonetGroupId = array($sonetGroupId);
						}

						unset($userFields["SONET_GROUP_ID"]);
					}
					else
					{
						throw new \Exception('no_sonet_group_for_extranet');
					}
				}

				unset($userFields["EXTRANET"]);
			}

			$inviteFields = self::prepareSaveData($userFields);

			$userFields["EMAIL"] = trim($userFields["EMAIL"]);
			if(check_email($userFields["EMAIL"]))
			{
				$siteId = self::getDefaultSite();

				if(\CIntranetInviteDialog::checkUsersCount(1))
				{
					if (
						IsModuleInstalled('extranet')
						&& empty($inviteFields["UF_DEPARTMENT"])
						&& !$bExtranet
					)
					{
						throw new \Exception('no_extranet_field');
					}

					$inviteFields['EMAIL'] = $userFields["EMAIL"];
					$inviteFields['ACTIVE'] = (isset($inviteFields['ACTIVE'])? $inviteFields['ACTIVE'] : 'Y');
					$inviteFields['GROUP_ID'] = \CIntranetInviteDialog::getUserGroups($siteId, $bExtranet);
					$inviteFields["CONFIRM_CODE"] = randString(8);

					$ID = \CIntranetInviteDialog::RegisterUser($inviteFields);
					if(is_array($ID))
					{
						throw new \Exception(implode("\n", $ID));
					}
					elseif($ID > 0)
					{
						$obUser = new \CUser;
						if(!$obUser->update($ID, $inviteFields))
						{
							throw new \Exception($obUser->LAST_ERROR);
						}

						$inviteFields['ID'] = $ID;

						Invitation::add([
							'USER_ID' => $ID,
							'TYPE' => Invitation::TYPE_EMAIL
						]);

						\CIntranetInviteDialog::InviteUser(
							$inviteFields,
							(isset($userFields["MESSAGE_TEXT"])) ? htmlspecialcharsbx($userFields["MESSAGE_TEXT"]) : GetMessage("BX24_INVITE_DIALOG_INVITE_MESSAGE_TEXT_1")
						);

						if (
							isset($sonetGroupId)
							&& is_array($sonetGroupId)
							&& \CModule::IncludeModule('socialnetwork')
						)
						{
							foreach($sonetGroupId as $groupId)
							{
								if (!\CSocNetUserToGroup::SendRequestToJoinGroup($USER->GetID(), $ID, $groupId, "", false))
								{
									if ($e = $APPLICATION->GetException())
									{
										throw new \Exception($e->GetString());
									}
								}
							}
						}

						$res = $ID;
					}
				}
				else
				{
					throw new \Exception('user_count_exceeded');
				}
			}
			else
			{
				throw new \Exception('wrong_email');
			}
		}
		else
		{
			throw new \Exception('access_denied');
		}

		return $res;
	}

	public static function userUpdate($userFields, $nav = 0, \CRestServer $server = null)
	{
		if (!is_null($server) && !static::isMainScope($server))
		{
			return static::getErrorScope();
		}

		global $USER;

		static::checkAllowedFields();

		$bB24 = ModuleManager::isModuleInstalled('bitrix24');

		$bAdmin = ($bB24 && $USER->canDoOperation('bitrix24_invite'))
			|| $USER->canDoOperation('edit_all_users');

		$userFields = array_change_key_case($userFields, CASE_UPPER);

		if($userFields['ID'] > 0)
		{
			if($bAdmin || ($USER->getID() == $userFields['ID'] && $USER->CanDoOperation('edit_own_profile')))
			{
				$updateFields = self::prepareSaveData($userFields);

				// security
				if(!$bAdmin)
				{
					unset($updateFields['ACTIVE']);
					unset($updateFields['UF_DEPARTMENT']);
				}
				// \security

				$obUser = new \CUser;
				if(!$obUser->update($userFields['ID'], $updateFields))
				{
					throw new \Exception($obUser->LAST_ERROR);
				}
				else
				{
					$res = true;
				}
			}
			else
			{
				throw new \Exception('access_denied');
			}
		}
		else
		{
			throw new \Exception('access_denied');
		}

		return $res;
	}

	private static function prepareUserField($params, $data)
	{
		$result = $data;
		switch ($params['USER_TYPE_ID'])
		{
			case 'datetime':
				$result = \CRestUtil::unConvertDateTime($data);
				break;
			case 'date':
				$result = \CRestUtil::unConvertDate($data);
				break;
			case 'file':
				if (is_array($data))
				{
					if ($params['MULTIPLE'] === 'N')
					{
						if (!empty($data['fileData']))
						{
							$result = \CRestUtil::saveFile($data['fileData']);
							$result['old_id'] = $params['VALUE'];
						}
						$id = isset($data['id']) ? (int)$data['id'] : 0;
						$remove = isset($data['remove']) && is_string($data['remove']) && mb_strtoupper($data['remove']) === 'Y';
						if ($remove && $id > 0)
						{
							$result = [
								'old_id' => $id,
								'del' => 'Y'
							];
						}
					}
					else
					{
						if ($params['VALUE'])
						{
							$result = array_merge($result, $params['VALUE']);
						}

						foreach ($result as $key => $value)
						{
							if ($value['fileData'])
							{
								$result[$key] = \CRestUtil::saveFile($value['fileData']);
							}
							else
							{
								$id = isset($value['id']) ? (int)$value['id'] : 0;
								$remove = isset($value['remove']) && is_string($value['remove']) && mb_strtoupper($value['remove']) === 'Y';
								if ($remove && $id > 0)
								{
									$result[$key] = [
										'old_id' => $id,
										'del' => 'Y'
									];
								}
								elseif ($value > 0)
								{
									$result[$key] = [
										'old_id' => $value,
										'error' => 'Y'
									];
								}
							}
						}
					}
				}
				break;
		}
		return $result;
	}

	protected static function prepareUserData($userData, $allowedUserFields = null)
	{
		$user = array();

		if (!$allowedUserFields)
		{
			$allowedUserFields = static::getDefaultAllowedUserFields();
		}
		foreach($userData as $key => $value)
		{
			if(in_array($key, $allowedUserFields, true))
			{
				$user[$key] = $value;
			}
		}

		if(isset($user['ID']))
		{
			if(is_array($user['ID']))
			{
				$user['ID'] = array_map("intval", $user['ID']);
			}
			else
			{
				$user['ID'] = intval($user['ID']);
			}
		}

		if(isset($user['ACTIVE']))
			$user['ACTIVE'] = ($user['ACTIVE'] && $user['ACTIVE'] != 'N') ? 'Y' : 'N';

		if(isset($user['IS_ONLINE']))
			$user['IS_ONLINE'] = ($user['IS_ONLINE'] && $user['IS_ONLINE'] != 'N') ? 'Y' : 'N';

		if(isset($user['PERSONAL_BIRTHDAY']))
			$user['PERSONAL_BIRTHDAY'] = \CRestUtil::unConvertDate($user['PERSONAL_BIRTHDAY']);

		if(isset($user['UF_DEPARTMENT']) && !is_array($user['UF_DEPARTMENT']) && !empty($user['UF_DEPARTMENT']))
			$user['UF_DEPARTMENT'] = array($user['UF_DEPARTMENT']);

		if(isset($user['AUTO_TIME_ZONE']))
			$user['AUTO_TIME_ZONE'] = ($user['AUTO_TIME_ZONE'] && $user['AUTO_TIME_ZONE'] === 'Y') ? 'Y' : 'N';

		if(isset($user['PERSONAL_PHOTO']))
		{
			$user['PERSONAL_PHOTO'] = \CRestUtil::saveFile($user['PERSONAL_PHOTO']);

			if(!$user['PERSONAL_PHOTO'])
			{
				$user['PERSONAL_PHOTO'] = array('del' => 'Y');
			}
		}

		if(
			isset($user['CONFIRM_CODE'])
			&& $user['CONFIRM_CODE'] === '0'
		)
		{
			$user['CONFIRM_CODE'] = false;
		}

		return $user;
	}

	protected static function prepareSaveData($userData, $allowedUserFields = null)
	{
		global $USER_FIELD_MANAGER;
		$user = array();

		if (!$allowedUserFields)
		{
			$allowedUserFields = static::getDefaultAllowedUserFields();
		}

		$userId = (int) $userData['ID'];

		$fieldsList = $USER_FIELD_MANAGER->getUserFields('USER', $userId, LANGUAGE_ID);

		foreach ($userData as $key => $value)
		{
			if (in_array($key, $allowedUserFields, true))
			{
				if (mb_strpos($key, static::$nameFieldFullPrefix) === 0)
				{
					$user[$key] = static::prepareUserField($fieldsList[$key], $value);
				}
				else
				{
					$user[$key] = $value;
				}
			}
		}


		if (isset($user['ACTIVE']))
			$user['ACTIVE'] = ($user['ACTIVE'] && $user['ACTIVE'] != 'N') ? 'Y' : 'N';

		if (isset($user['PERSONAL_BIRTHDAY']))
			$user['PERSONAL_BIRTHDAY'] = \CRestUtil::unConvertDate($user['PERSONAL_BIRTHDAY']);

		if (isset($user['UF_DEPARTMENT']) && !is_array($user['UF_DEPARTMENT']) && !empty($user['UF_DEPARTMENT']))
			$user['UF_DEPARTMENT'] = array($user['UF_DEPARTMENT']);

		if (isset($user['PERSONAL_PHOTO']))
		{
			$user['PERSONAL_PHOTO'] = \CRestUtil::saveFile($user['PERSONAL_PHOTO']);

			if (!$user['PERSONAL_PHOTO'])
			{
				$user['PERSONAL_PHOTO'] = array('del' => 'Y');
			}
		}

		$user = array_diff_key($user, array_fill_keys(static::$holdEditFields, 'Y'));

		return $user;
	}

	protected static function getUserData($userFields, $allowedFields = null)
	{
		static $extranetModuleInstalled = null;
		if ($extranetModuleInstalled === null)
		{
			$extranetModuleInstalled = ModuleManager::isModuleInstalled('extranet');
		}
		global $USER_FIELD_MANAGER;
		$fieldsList = $USER_FIELD_MANAGER->getUserFields(static::$entityUser, 0, LANGUAGE_ID);

		$urlManager = \Bitrix\Main\Engine\UrlManager::getInstance();

		$res = array();
		if (is_null($allowedFields))
		{
			$allowedFields = static::getDefaultAllowedUserFields();
		}
		foreach ($allowedFields as $key)
		{
			switch ($key)
			{
				case 'ACTIVE':
					$res[$key] = $userFields[$key] == 'Y';
					break;
				case 'PERSONAL_BIRTHDAY':
				case 'LAST_LOGIN':
				case 'DATE_REGISTER':
					$res[$key] = \CRestUtil::convertDate($userFields[$key]);
					break;
				case 'EXTERNAL_AUTH_ID':
					$res['IS_NETWORK'] = $userFields[$key] == 'replica';
					$res['IS_EMAIL'] = $userFields[$key] == 'email';
					unset($userFields[$key]);
					break;
				default:
					if (!empty($fieldsList[$key]))
					{
						if ($fieldsList[$key]['USER_TYPE_ID'] === 'date')
						{
							if ($fieldsList[$key]['MULTIPLE'] === 'Y' && is_array($userFields[$key]))
							{
								foreach ($userFields[$key] as $k => $value)
								{
									$res[$key][$k] = \CRestUtil::convertDate($userFields[$key][$k]);
								}
							}
							else
							{
								$res[$key] = \CRestUtil::convertDate($userFields[$key]);
							}
						}
						elseif ($fieldsList[$key]['USER_TYPE_ID'] === 'datetime')
						{
							if ($fieldsList[$key]['MULTIPLE'] === 'Y' && is_array($userFields[$key]))
							{
								foreach ($userFields[$key] as $k => $value)
								{
									$res[$key][$k] = \CRestUtil::convertDateTime($userFields[$key][$k]);
								}
							}
							else
							{
								$res[$key] = \CRestUtil::convertDateTime($userFields[$key]);
							}
						}
						elseif ($fieldsList[$key]['USER_TYPE_ID'] === 'file')
						{
							if ($fieldsList[$key]['MULTIPLE'] === 'Y' && is_array($userFields[$key]))
							{
								foreach ($userFields[$key] as $k => $value)
								{
									$res[$key][$k] = [
										'id' => $userFields[$key][$k],
										'showUrl' => $urlManager->create(
											'rest.file.get',
											[
												'entity' => static::$entityUser,
												'id' => $userFields['ID'],
												'field' => $key,
												'value' => $userFields[$key]
											]
										),
										'downloadData' => [
											'id' => $userFields['ID'],
											'field' => $key,
											'value' => $userFields[$key][$k],
										],
									];
								}
							}
							else
							{
								$res[$key] = [
									'id' => $userFields[$key],
									'showUrl' => $urlManager->create(
										'rest.file.get',
										[
											'entity' => static::$entityUser,
											'id' => $userFields['ID'],
											'field' => $key,
											'value' => $userFields[$key]
										]
									),
									'downloadData' => [
										'id' => $userFields['ID'],
										'field' => $key,
										'value' => $userFields[$key]
									]
								];
							}
						}
					}

					if (!isset($res[$key]))
					{
						$res[$key] = $userFields[$key];
					}
					break;
			}
		}

		return $res;
	}

	public static function getFile($query, $n, \CRestServer $server)
	{
		$file = new File();
		return $file->getAction(static::$entityUser, $query['id'], $query['field'], $query['value'], $server);
	}

	protected static function getDefaultSite()
	{
		return \CSite::getDefSite();
	}
}
