<?php
namespace Bitrix\Rest\Api;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UserFieldTable;
use Bitrix\Main\UserTable;
use Bitrix\Rest\RestException;

class User extends \IRestService
{
	const SCOPE_USER = 'user';

	protected static $allowedUserFields = array(
		"ID", /*"LOGIN", */
		"ACTIVE", "EMAIL",
		"NAME", "LAST_NAME", "SECOND_NAME",
		"PERSONAL_GENDER", "PERSONAL_PROFESSION", "PERSONAL_WWW", "PERSONAL_BIRTHDAY", "PERSONAL_PHOTO",
		"PERSONAL_ICQ", "PERSONAL_PHONE", "PERSONAL_FAX", "PERSONAL_MOBILE", "PERSONAL_PAGER", "PERSONAL_STREET", "PERSONAL_CITY", "PERSONAL_STATE", "PERSONAL_ZIP", "PERSONAL_COUNTRY",

		"WORK_COMPANY", "WORK_POSITION", "WORK_PHONE",

		"UF_DEPARTMENT", "UF_INTERESTS", "UF_SKILLS", "UF_WEB_SITES", "UF_XING", "UF_LINKEDIN", "UF_FACEBOOK", "UF_TWITTER", "UF_SKYPE", "UF_DISTRICT", "UF_PHONE_INNER"
	);

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
		}

		return $result;
	}

	protected static function checkAllowedFields()
	{
		global $USER_FIELD_MANAGER;

		$fields = $USER_FIELD_MANAGER->GetUserFields("USER");

		foreach(static::$allowedUserFields as $key => $field)
		{
			if(substr($field, 0, 3) === 'UF_' && !array_key_exists($field, $fields))
			{
				unset(static::$allowedUserFields[$key]);
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

		$arRes = self::getUserData($arUser);
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

	public static function getFields()
	{
		global $USER_FIELD_MANAGER;

		static::checkAllowedFields();

		$res = array();

		$langMessages = array_merge(
			IncludeModuleLangFile('/bitrix/modules/main/admin/user_edit.php', false, true),
			IncludeModuleLangFile('/bitrix/modules/main/admin/user_admin.php', false, true)
		);
		$fieldsList = $USER_FIELD_MANAGER->getUserFields('USER', 0, LANGUAGE_ID);
		foreach (self::$allowedUserFields as $key)
		{
			if(substr($key, 0, 3) != 'UF_')
			{
				$lkey = isset($langMessages[$key]) ? $key : str_replace('PERSONAL_', 'USER_', $key);
				$res[$key] = isset($langMessages[$lkey]) ? $langMessages[$lkey] : $key;
				if(substr($res[$key], -1) == ':')
				{
					$res[$key] = substr($res[$key], 0, -1);
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

		$result = self::getUserData($userFields);
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

		$allowedUserFields = self::$allowedUserFields;
		$allowedUserFields[] = 'IS_ONLINE';
		$allowedUserFields[] = 'NAME_SEARCH';
		$allowedUserFields[] = 'EXTERNAL_AUTH_ID';
		if ($server->getMethod() == "user.search")
		{
			$allowedUserFields[] = 'FIND';
			$allowedUserFields[] = 'UF_DEPARTMENT_NAME';
		}
		if (Loader::includeModule('intranet'))
		{
			$allowedUserFields[] = 'USER_TYPE';
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

		$result = array();

		$filter['=IS_REAL_USER'] = 'Y';

		$dbResCnt = UserTable::getList(array(
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

			$dbRes = UserTable::getList(array(
				'order' => $querySort,
				'filter' => $filter,
				'select' => self::$allowedUserFields,
				'limit' => $navParams['limit'],
				'offset' => $navParams['offset'],
				'data_doubling' => false,
			));

			$result = array();
			$files = array();

			while($userInfo = $dbRes->fetch())
			{
				$result[] = self::getUserData($userInfo);

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

	public static function userAdd($userFields)
	{
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

			$inviteFields = self::prepareUserData($userFields);

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
						throw new \Exception(implode($ID, "\n"));
					}
					elseif($ID > 0)
					{
						$obUser = new \CUser;
						if(!$obUser->update($ID, $inviteFields))
						{
							throw new \Exception($obUser->LAST_ERROR);
						}

						$inviteFields['ID'] = $ID;

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

	public static function userUpdate($userFields)
	{
		global $USER;

		static::checkAllowedFields();

		$bB24 = ModuleManager::isModuleInstalled('bitrix24');

		$bAdmin = $bB24 && $USER->canDoOperation('bitrix24_invite')
			|| $USER->canDoOperation('edit_all_users');

		$userFields = array_change_key_case($userFields, CASE_UPPER);

		if($userFields['ID'] > 0)
		{
			if($bAdmin || ($USER->getID() == $userFields['ID'] && $USER->CanDoOperation('edit_own_profile')))
			{
				$updateFields = self::prepareUserData($userFields);

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

	protected static function prepareUserData($userData, $allowedUserFields = null)
	{
		$user = array();

		if (!$allowedUserFields)
		{
			$allowedUserFields = self::$allowedUserFields;
		}
		foreach($userData as $key => $value)
		{
			if(in_array($key, $allowedUserFields))
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

		if(isset($user['PERSONAL_PHOTO']))
		{
			$user['PERSONAL_PHOTO'] = \CRestUtil::saveFile($user['PERSONAL_PHOTO']);

			if(!$user['PERSONAL_PHOTO'])
			{
				$user['PERSONAL_PHOTO'] = array('del' => 'Y');
			}
		}

		return $user;
	}

	protected static function getUserData($userFields)
	{
		static $extranetModuleInstalled = null;
		if ($extranetModuleInstalled === null)
		{
			$extranetModuleInstalled = ModuleManager::isModuleInstalled('extranet');
		}

		$res = array();
		foreach(self::$allowedUserFields as $key)
		{
			switch($key)
			{
				case 'ACTIVE':
					$res[$key] = $userFields[$key] == 'Y';
				break;
				case 'PERSONAL_BIRTHDAY':
					$res[$key] = \CRestUtil::convertDate($userFields[$key]);
				break;
				case 'EXTERNAL_AUTH_ID':
					$res['IS_NETWORK'] = $userFields[$key] == 'replica';
					$res['IS_EMAIL'] = $userFields[$key] == 'email';
					unset($userFields[$key]);
				break;
				default:
					$res[$key] = $userFields[$key];
			}
		}

		return $res;
	}

	protected static function getDefaultSite()
	{
		return \CSite::getDefSite();
	}
}