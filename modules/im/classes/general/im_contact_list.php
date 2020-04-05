<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Im as IM;

class CAllIMContactList
{
	private $user_id = 0;

	const NETWORK_AUTH_ID = 'replica';

	function __construct($user_id = false)
	{
		global $USER;
		$user_id = intval($user_id);
		if ($user_id == 0)
			$user_id = intval($USER->GetID());

		$this->user_id = $user_id;
	}

	function GetList($arParams = Array())
	{
		global $USER, $CACHE_MANAGER;

		$bLoadUsers = isset($arParams['LOAD_USERS']) && $arParams['LOAD_USERS'] == 'N'? false: true;
		$bLoadChats = isset($arParams['LOAD_CHATS']) && $arParams['LOAD_CHATS'] == 'N'? false: true;

		$arGroups = array();
		if(defined("BX_COMP_MANAGED_CACHE"))
			$ttl = 2592000;
		else
			$ttl = 600;

		$bBusShowAll = !IsModuleInstalled('intranet') && COption::GetOptionInt('im', 'contact_list_show_all_bus');

		$bIntranetEnable = false;
		if(CModule::IncludeModule('intranet') && CModule::IncludeModule('iblock'))
		{
			$bIntranetEnable = true;
			if (!(CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser()))
			{
				if(($iblock_id = COption::GetOptionInt('intranet', 'iblock_structure', 0)) > 0)
				{
					$cache_id = 'im_structure_'.$iblock_id;
					$obIMCache = new CPHPCache;
					$cache_dir = '/bx/imc/structure';

					if($obIMCache->InitCache($ttl, $cache_id, $cache_dir))
					{
						$tmpVal = $obIMCache->GetVars();
						$arStructureName = $tmpVal['STRUCTURE_NAME'];
						unset($tmpVal);
					}
					else
					{
						if(defined("BX_COMP_MANAGED_CACHE"))
							$CACHE_MANAGER->StartTagCache($cache_dir);

						$arResult["Structure"] = array();
						$sec = CIBlockSection::GetList(
							Array("left_margin"=>"asc","SORT"=>"ASC"),
							Array("ACTIVE"=>"Y","IBLOCK_ID"=>$iblock_id),
							false,
							Array('ID', 'NAME', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID')
						);
						$arStructureName = Array();
						while($ar = $sec->GetNext(true, false))
						{
							if ($ar['DEPTH_LEVEL'] > 1)
								$ar['NAME'] .= ' / '.$arStructureName[$ar['IBLOCK_SECTION_ID']];
							$arStructureName[$ar['ID']] = $ar['NAME'];
						}

						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->RegisterTag('iblock_id_'.$iblock_id);
							$CACHE_MANAGER->EndTagCache();
						}

						if($obIMCache->StartDataCache())
						{
							$obIMCache->EndDataCache(array(
								'STRUCTURE_NAME' => $arStructureName
							));
						}
					}

					unset($obIMCache);

					foreach ($arStructureName as $key => $value)
					{
						if (strlen($value) > 0)
						{
							$arGroups[$key] = Array('id' => $key, 'name' => $value);
						}
					}
				}
			}
		}

		$arUserSG = array();
		$arUsers = array();
		$arUserInGroup = array();
		$arExtranetUsers = array();

		$groups = \Bitrix\Im\Integration\Socialnetwork\Extranet::getGroup(Array(), $this->user_id);
		if (is_array($groups))
		{
			foreach ($groups as $groupId => $group)
			{
				$arUserInGroup[$groupId] = Array('id' => $groupId, 'users' => $group['USERS']);
				foreach ($group['USERS'] as $groupUserId)
				{
					$arExtranetUsers[$groupUserId] = $groupUserId;
				}
				$arUserSG[$groupId] = array(
					'id' => $groupId,
					'status' => 'open',
					'name' => $group['NAME']
				);
				$arGroups[$groupId] = $arUserSG[$groupId];
			}
		}

		$bFriendEnable = false;
		if ((!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()) && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed())
		{
			$bFriendEnable = true;
			$dbFriends = CSocNetUserRelations::GetList(array(),array("USER_ID" => $USER->GetID(), "RELATION" => SONET_RELATIONS_FRIEND), false, false, array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "DATE_CREATE", "DATE_UPDATE", "INITIATED_BY"));
			if ($dbFriends)
			{
				while ($arFriends = $dbFriends->GetNext(true, false))
				{
					$friendId = $pref = (IntVal($USER->GetID()) == $arFriends["FIRST_USER_ID"]) ? $arFriends["SECOND_USER_ID"] : $arFriends["FIRST_USER_ID"];
					$arFriendUsers[$friendId] = $friendId;

					/*
					if (isset($arUserInGroup["friends"]))
						$arUserInGroup["friends"]['users'][] = $friendId;
					else
						$arUserInGroup["friends"] = Array('id' => "friends", 'users' => Array($friendId));
					*/
				}
			}
			/*
			$arGroups['friends'] = array(
				'id' => 'friends',
				'status' => (isset($arGroupStatus['friends']) && $arGroupStatus['friends'] == 'close'? 'close': 'open'),
				'name' => GetMessage('IM_CL_GROUP_FRIENDS')
			);
			*/
		}

		$blockedFakeUsers = Array();
		foreach (\Bitrix\Main\UserTable::getExternalUserTypes() as $authId)
		{
			if ($authId == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID)
				continue;

			$blockedFakeUsers[] = $authId;
		}

		$filter = array(
			'=ACTIVE' => 'Y',
			'=CONFIRM_CODE' => false,
			'!=EXTERNAL_AUTH_ID' => $blockedFakeUsers
		);
		if (CModule::IncludeModule('extranet'))
		{
			if(!CExtranet::IsIntranetUser())
			{
				$filter['=ID'] = array_merge(Array($USER->GetId()), $arExtranetUsers);
			}
		}

		if ($bLoadUsers)
		{
			if ($bFriendEnable)
			{
				if (!$bIntranetEnable && !$bBusShowAll)
				{
					$filter['=ID'][] = $USER->GetId();
					if (!empty($arFriendUsers))
					{
						$filter['=ID'] =  array_merge($filter['=ID'], $arFriendUsers);
					}
					if (!empty($arExtranetUsers))
					{
						$filter['=ID'] =  array_merge($filter['=ID'], $arExtranetUsers);
					}
				}
			}

			$bCLCacheEnable = false;
			if ($bIntranetEnable && (!$bFriendEnable || $bBusShowAll))
				$bCLCacheEnable = true;

			if ($bCLCacheEnable && CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
				$bCLCacheEnable = false;

			$bVoximplantEnable = CModule::IncludeModule('voximplant');
			$bColorEnabled = IM\Color::isEnabled();
			$bOpenChatEnabled = CIMMessenger::CheckEnableOpenChat();

			$nameTemplate = self::GetUserNameTemplate(SITE_ID);
			$nameTemplateSite = CSite::GetNameFormat(false);
			$cache_id = 'im_contact_list_v21_'.$nameTemplate.'_'.$nameTemplateSite.(!empty($arExtranetUsers)? '_'.$USER->GetID(): '').$bVoximplantEnable.$bColorEnabled.$bOpenChatEnabled;
			$obCLCache = new CPHPCache;
			$cache_dir = '/bx/imc/contact';

			$arUsersToGroup = array();
			$arUserInGroupStructure = array();
			$arPhones = Array();

			if($bCLCacheEnable && $obCLCache->InitCache($ttl, $cache_id, $cache_dir))
			{
				$tmpVal = $obCLCache->GetVars();
				$arUsers = $tmpVal['USERS'];
				$arPhones = $tmpVal['PHONES'];
				$arUsersToGroup = $tmpVal['USER_TO_GROUP'];
				$arUserInGroupStructure = $tmpVal['USER_IN_GROUP'];
				unset($tmpVal);

				$arOnline = CIMStatus::GetList();
				foreach ($arUsers as $userId => $value)
				{
					$arUsers[$userId]['status'] = isset($arOnline['users'][$userId])? $arOnline['users'][$userId]['status']: 'offline';
					$arUsers[$userId]['idle'] = isset($arOnline['users'][$userId])? $arOnline['users'][$userId]['idle']: false;
					$arUsers[$userId]['mobile_last_date'] = isset($arOnline['users'][$userId])? $arOnline['users'][$userId]['mobile_last_date']: false;
					$arUsers[$userId]['last_activity_date'] = isset($arOnline['users'][$userId])? $arOnline['users'][$userId]['last_activity_date']: false;
					$arUsers[$userId]['absent'] = self::formatAbsentResult($userId);
					if ($arOnline['users'][$userId]['color'])
					{
						$arUsers[$userId]['color'] = $arOnline['users'][$userId]['color'];
					}
				}
			}
			else
			{
				$select = array(
					"ID", "LAST_NAME", "NAME", "LOGIN", "PERSONAL_PHOTO", "SECOND_NAME", "PERSONAL_BIRTHDAY", "WORK_POSITION", "PERSONAL_GENDER", "EXTERNAL_AUTH_ID", "WORK_PHONE", "PERSONAL_PHONE", "PERSONAL_MOBILE", "TIME_ZONE_OFFSET", "ACTIVE", "LAST_ACTIVITY_DATE",
					"COLOR" => "ST.COLOR", "STATUS" =>	"ST.STATUS", "IDLE" => "ST.IDLE", "MOBILE_LAST_DATE" => "ST.MOBILE_LAST_DATE",
				);
				if($bIntranetEnable)
				{
					$select[] = 'UF_PHONE_INNER';
					$select[] = 'UF_DEPARTMENT';
				}
				if ($bVoximplantEnable)
				{
					$select[] = 'UF_VI_PHONE';
				}

				$orm = \Bitrix\Main\UserTable::getList(Array(
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
					'order' => Array('LAST_NAME' => 'ASC')
				));

				$bots = \Bitrix\Im\Bot::getListCache();

				while ($arUser = $orm->fetch())
				{
					$skipUser = false;
					if(is_array($arUser["UF_DEPARTMENT"]) && !empty($arUser["UF_DEPARTMENT"]))
					{
						foreach($arUser["UF_DEPARTMENT"] as $dep_id)
						{
							if (isset($arUserInGroupStructure[$dep_id]))
								$arUserInGroupStructure[$dep_id]['users'][] = $arUser["ID"];
							else
								$arUserInGroupStructure[$dep_id] = Array('id' => $dep_id, 'users' => Array($arUser["ID"]));
						}
					}
					/*
					else if ($bBusShowAll)
					{
						$skipUser = false;

						if (isset($arUserInGroup['all']))
							$arUserInGroup['all']['users'][] = $arUser["ID"];
						else
							$arUserInGroup['all'] = Array('id' => 'all', 'users' => Array($arUser["ID"]));
					}*/
					else
					{
						$skipUser = true;
						if (isset($arExtranetUsers[$arUser["ID"]]))
							$skipUser = false;
						elseif (isset($arFriendUsers[$arUser["ID"]]))
							$skipUser = false;
						elseif ($arUser["ID"] == $USER->GetID())
							$skipUser = false;
					}

					if (!$skipUser)
					{
						foreach ($arUser as $key => $value)
						{
							$arUser[$key] = !is_array($value) && !is_object($value)? htmlspecialcharsEx($value): $value;
						}
						$arFileTmp = CFile::ResizeImageGet(
							$arUser["PERSONAL_PHOTO"],
							array('width' => 100, 'height' => 100),
							BX_RESIZE_IMAGE_EXACT,
							false,
							false,
							true
						);

						$color = self::GetUserColor($arUser["ID"], $arUser['PERSONAL_GENDER'] == 'M'? 'M': 'F');
						if (isset($arUser['COLOR']) && strlen($arUser['COLOR']) > 0)
						{
							$color = IM\Color::getColor($arUser['COLOR']);
						}
						if (!$color)
						{
							$color = self::GetUserColor($arUser["ID"], $arUser['PERSONAL_GENDER'] == 'M'? 'M': 'F');
						}

						$arUsersToGroup[$arUser['ID']] = $arUser["UF_DEPARTMENT"];
						$arUser['PERSONAL_BIRTHDAY'] = $arUser['PERSONAL_BIRTHDAY'] instanceof \Bitrix\Main\Type\Date? $arUser['PERSONAL_BIRTHDAY']->format('d-m'): false;
						$arUser['IDLE'] = $arUser['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['IDLE']: false;
						$arUser['MOBILE_LAST_DATE'] = $arUser['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['MOBILE_LAST_DATE']: false;
						$arUser['LAST_ACTIVITY_DATE'] = $arUser['LAST_ACTIVITY_DATE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['LAST_ACTIVITY_DATE']: false;

						$arUsers[$arUser["ID"]] = Array(
							'id' => $arUser["ID"],
							'name' => CUser::FormatName($nameTemplateSite, $arUser, true, false),
							'active' => $arUser['ACTIVE'] == 'Y',
							'first_name' => $arUser['NAME'],
							'last_name' => $arUser['LAST_NAME'],
							'work_position' => $arUser['WORK_POSITION'],
							'color' => $color,
							'avatar' => empty($arFileTmp['src'])? '/bitrix/js/im/images/blank.gif': $arFileTmp['src'],
							'birthday' => $arUser['PERSONAL_BIRTHDAY'],
							'gender' => $arUser['PERSONAL_GENDER'] == 'F'? 'F': 'M',
							'phone_device' => $bVoximplantEnable && $arUser['UF_VI_PHONE'] == 'Y',
							'extranet' => self::IsExtranet($arUser),
							'tz_offset' => intval($arUser['TIME_ZONE_OFFSET']),
							'network' => $arUser['EXTERNAL_AUTH_ID'] == self::NETWORK_AUTH_ID || $arUser['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID && $bots[$arUser["ID"]]['TYPE'] == \Bitrix\Im\Bot::TYPE_NETWORK,
							'bot' => $arUser['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID,
							'profile' => CIMContactList::GetUserPath($arUser["ID"]),
							'external_auth_id' => $arUser['EXTERNAL_AUTH_ID']? $arUser['EXTERNAL_AUTH_ID']: 'default',
							'status' => $arUser['STATUS'],
							'idle' => $arUser['IDLE'],
							'last_activity_date' => $arUser['LAST_ACTIVITY_DATE'],
							'mobile_last_date' => $arUser['MOBILE_LAST_DATE'],
							'absent' => self::formatAbsentResult($arUser["ID"]),
						);

						if ($bVoximplantEnable)
						{
							$result = CVoxImplantPhone::Normalize($arUser["WORK_PHONE"]);
							if ($result)
							{
								$arPhones[$arUser["ID"]]['WORK_PHONE'] = $arUser['WORK_PHONE'];
							}
							$result = CVoxImplantPhone::Normalize($arUser["PERSONAL_MOBILE"]);
							if ($result)
							{
								$arPhones[$arUser["ID"]]['PERSONAL_MOBILE'] = $arUser['PERSONAL_MOBILE'];
							}
							$result = CVoxImplantPhone::Normalize($arUser["PERSONAL_PHONE"]);
							if ($result)
							{
								$arPhones[$arUser["ID"]]['PERSONAL_PHONE'] = $arUser['PERSONAL_PHONE'];
							}
							$result = preg_replace("/[^0-9\#\*]/i", "", $arUser["UF_PHONE_INNER"]);
							if ($result)
							{
								$arPhones[$arUser["ID"]]['INNER_PHONE'] = $result;
							}
						}
						else
						{
							$arPhones[$arUser["ID"]]['WORK_PHONE'] = $arUser['WORK_PHONE'];
							$arPhones[$arUser["ID"]]['PERSONAL_MOBILE'] = $arUser['PERSONAL_MOBILE'];
							$arPhones[$arUser["ID"]]['PERSONAL_PHONE'] = $arUser['PERSONAL_PHONE'];
						}
					}
				}
				if ($bCLCacheEnable)
				{
					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->StartTagCache($cache_dir);
						$CACHE_MANAGER->RegisterTag("IM_CONTACT_LIST");
						$CACHE_MANAGER->RegisterTag($bVoximplantEnable? "USER_CARD": "USER_NAME");
						$CACHE_MANAGER->EndTagCache();
					}
					if($obCLCache->StartDataCache())
					{
						$obCLCache->EndDataCache(array(
								'USERS' => $arUsers,
								'USER_TO_GROUP' => $arUsersToGroup,
								'USER_IN_GROUP' => $arUserInGroupStructure,
								'PHONES' => $arPhones
							)
						);
					}
				}
			}

			//uasort($ar, create_function('$a, $b', 'if($a["stamp"] < $b["stamp"]) return 1; elseif($a["stamp"] > $b["stamp"]) return -1; else return 0;'));
			if (is_array($arUsersToGroup[$USER->GetID()]))
			{
				foreach($arUsersToGroup[$USER->GetID()] as $dep_id)
				{
					if (isset($arGroups[$dep_id]))
					{
						$arGroups[$dep_id]['status'] = (isset($arGroupStatus[$dep_id]) && $arGroupStatus[$dep_id] == 'close'? 'close': 'open');
					}
				}
			}
			foreach ($arUserInGroupStructure as $key => $val)
			{
				$arUserInGroup[$key] = $val;
			}
			unset($arUsersToGroup, $arUserInGroupStructure);
		}

		$arChats = Array();
		if ($bLoadChats)
		{
			$bColorEnabled = IM\Color::isEnabled();
			$bOpenChatEnabled = CIMMessenger::CheckEnableOpenChat();
			$cache_id = 'im_chats_v9_'.$USER->GetID().'_'.$bColorEnabled.'_'.$bOpenChatEnabled;
			$obCLCache = new CPHPCache;
			$cache_dir = '/bx/imc/chats';

			if($obCLCache->InitCache($ttl, $cache_id, $cache_dir))
			{
				$tmpVal = $obCLCache->GetVars();
				$arChats = $tmpVal['CHATS'];
				unset($tmpVal);
			}
			else
			{
				$arChats = CIMChat::GetChatData(Array(
					'SKIP_PRIVATE' => 'Y',
					'GET_LIST' => 'Y',
					'USER_ID' => $USER->GetID()
				));

				if (CIMMessenger::CheckEnableOpenChat() && !IM\User::getInstance($USER->GetID())->isExtranet())
				{
					$chatsOpen = CIMChat::GetOpenChatData(Array(
						'USER_ID' => $USER->GetID()
					));

					foreach ($chatsOpen['chat'] as $key => $value)
					{
						$arChats['chat'][$key] = $value;
					}
					foreach ($chatsOpen['userInChat'] as $key => $value)
					{
						$arChats['userInChat'][$key] = $value;
					}
					foreach ($chatsOpen['userCallStatus'] as $key => $value)
					{
						$arChats['userCallStatus'][$key] = $value;
					}
					foreach ($chatsOpen['userChatBlockStatus'] as $key => $value)
					{
						$arChats['userChatBlockStatus'][$key] = $value;
					}
				}

				if($obCLCache->StartDataCache())
				{
					$obCLCache->EndDataCache(array(
						'CHATS' => $arChats,
					));
				}
			}
		}

		$arContactList = Array('users' => $arUsers, 'groups' => $arGroups, 'chats' => $arChats['chat'], 'lines' => $arChats['lines'], 'phones' => $arPhones, 'userInGroup' => $arUserInGroup);

		foreach(GetModuleEvents("im", "OnAfterContactListGetList", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arContactList));

		return $arContactList;
	}

	public static function CleanChatCache($userId)
	{
		$bColorEnabled = IM\Color::isEnabled();
		$bOpenChatEnabled = CIMMessenger::CheckEnableOpenChat();
		$cache_id = 'im_chats_v9_'.$userId.'_'.$bColorEnabled.'_'.$bOpenChatEnabled;
		$cache_dir = '/bx/imc/chats';

		$obCLCache = new CPHPCache;
		$obCLCache->Clean($cache_id, $cache_dir);

		IM\LastSearch::clearCache($userId);
	}

	public static function CleanAllChatCache()
	{
		$cache_dir = '/bx/imc/chats';

		$obCache = new CPHPCache();
		$obCache->CleanDir($cache_dir);
	}

	function SearchUsers($searchText)
	{
		$searchText = trim($searchText);
		if (strlen($searchText) < 3)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_CL_SEARCH_EMPTY"), "ERROR_SEARCH_EMPTY");
			return false;
		}

		$nameTemplate = self::GetUserNameTemplate(SITE_ID);
		$nameTemplateSite = CSite::GetNameFormat(false);

		$filter = \Bitrix\Main\UserUtils::getUserSearchFilter(Array('FIND'=> $searchText));
		$filter["=ACTIVE"] = "Y";
		$filter["=CONFIRM_CODE"] = false;
		$filter["=IS_REAL_USER"] = "Y";

		$bIntranetEnable = IsModuleInstalled('intranet');
		$bVoximplantEnable = IsModuleInstalled('voximplant');

		if (!$bIntranetEnable)
		{
			$arSettings = CIMSettings::GetDefaultSettings(CIMSettings::SETTINGS);
			if ($arSettings[CIMSettings::PRIVACY_SEARCH] == CIMSettings::PRIVACY_RESULT_ALL)
				$filter['!=UF_IM_SEARCH'] = CIMSettings::PRIVACY_RESULT_CONTACT;
			else
				$filter['=UF_IM_SEARCH'] = CIMSettings::PRIVACY_RESULT_ALL;
		}

		$select = Array(
			"ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "PERSONAL_BIRTHDAY", "WORK_POSITION", "PERSONAL_GENDER", "EXTERNAL_AUTH_ID", "TIME_ZONE_OFFSET", "ACTIVE", "UF_IM_SEARCH", "LAST_ACTIVITY_DATE",
			"COLOR" => "ST.COLOR", "STATUS" =>	"ST.STATUS", "IDLE" => "ST.IDLE", "MOBILE_LAST_DATE" => "ST.MOBILE_LAST_DATE",
		);
		if($bIntranetEnable)
			$select[] = 'UF_DEPARTMENT';
		if ($bVoximplantEnable)
			$select[] = 'UF_VI_PHONE';

		$bots = \Bitrix\Im\Bot::getListCache();

		$orm = \Bitrix\Main\UserTable::getList(Array(
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
			'order' => Array('LAST_NAME' => 'ASC')
		));
		$arUsers = Array();
		while ($arUser = $orm->fetch())
		{
			$arFileTmp = CFile::ResizeImageGet(
				$arUser["PERSONAL_PHOTO"],
				array('width' => 100, 'height' => 100),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);

			$arUser['PERSONAL_BIRTHDAY'] = $arUser['PERSONAL_BIRTHDAY'] instanceof \Bitrix\Main\Type\Date? $arUser['PERSONAL_BIRTHDAY']->format('d-m'): false;
			$arUser['IDLE'] = $arUser['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['IDLE']: false;
			$arUser['MOBILE_LAST_DATE'] = $arUser['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['MOBILE_LAST_DATE']: false;
			$arUser['LAST_ACTIVITY_DATE'] = $arUser['LAST_ACTIVITY_DATE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['LAST_ACTIVITY_DATE']: false;

			$arUsers[$arUser["ID"]] = Array(
				'id' => $arUser["ID"],
				'name' => CUser::FormatName($nameTemplateSite, $arUser, true, false),
				'active' => $arUser['ACTIVE'] == 'Y',
				'first_name' => $arUser['NAME'],
				'last_name' => $arUser['LAST_NAME'],
				'work_position' => $arUser['WORK_POSITION'],
				'color' => self::GetUserColor($arUser["ID"], $arUser['PERSONAL_GENDER'] == 'M'? 'M': 'F'),
				'avatar' => empty($arFileTmp['src'])? '/bitrix/js/im/images/blank.gif': $arFileTmp['src'],
				'birthday' => $arUser['PERSONAL_BIRTHDAY'],
				'gender' => $arUser['PERSONAL_GENDER'] == 'F'? 'F': 'M',
				'phone_device' => $bVoximplantEnable && $arUser['UF_VI_PHONE'] == 'Y',
				'extranet' => self::IsExtranet($arUser),
				'network' => $arUser['EXTERNAL_AUTH_ID'] == self::NETWORK_AUTH_ID || $arUser['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID && $bots[$arUser["ID"]]['TYPE'] == \Bitrix\Im\Bot::TYPE_NETWORK,
				'bot' => $arUser['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID,
				'tz_offset' => intval($arUser['TIME_ZONE_OFFSET']),
				'profile' => CIMContactList::GetUserPath($arUser["ID"]),
				'search_mark' => $searchText,
				'external_auth_id' => $arUser['EXTERNAL_AUTH_ID']? $arUser['EXTERNAL_AUTH_ID']: 'default',
				'status' => $arUser['STATUS'],
				'idle' => $arUser['IDLE'],
				'last_activity_date' => $arUser['LAST_ACTIVITY_DATE'],
				'mobile_last_date' => $arUser['MOBILE_LAST_DATE'],
				'absent' => self::formatAbsentResult($arUser["ID"]),
			);
		}

		if (CModule::IncludeModule('imopenlines'))
		{
			$network = new \Bitrix\ImOpenLines\Network();
			$result = $network->search($searchText);
			if ($result)
			{
				foreach ($result as $arLine)
				{
					$id = 'networkLines'.$arLine["CODE"];
					$arUsers[$id] = Array(
						'id' => $id,
						'name' => htmlspecialcharsbx($arLine["LINE_NAME"]),
						'work_position' => $arLine["LINE_DESC"]? htmlspecialcharsbx($arLine["LINE_DESC"]): GetMessage('IM_SEARCH_OL'),
						'color' => IM\Color::getColor('GRAY'),
						'avatar' => empty($arLine['LINE_AVATAR'])? '/bitrix/js/im/images/blank.gif': $arLine['LINE_AVATAR'],
						'birthday' => false,
						'gender' => 'M',
						'phone_device' => false,
						'tz_offset' => 0,
						'extranet' => true,
						'network' => true,
						'bot' => true,
						'profile' => '',
						'select' => 'Y',
						'network_id' => $arLine["CODE"],
						'search_mark' => $searchText,
						'external_auth_id' => 'network',
						'status' => 'online',
						'idle' => false,
						'last_activity_date' => false,
						'mobile_last_date' => false,
						'absent' => false,
					);
				}
			}
		}

		if (CModule::IncludeModule('socialservices'))
		{
			$network = new \Bitrix\Socialservices\Network();
			if ($network->isEnabled())
			{
				$result = $network->searchUser($searchText);
				if ($result)
				{
					$arUserIds = array_keys($arUsers);
					$arIntersectUserIds = Array();
					foreach ($result as $arUser)
					{
						$id = 'network'.$arUser["NETWORK_ID"];
						$arUsers[$id] = Array(
							'id' => $id,
							'name' => CUser::FormatName($nameTemplateSite, $arUser, true, false),
							'active' => true,
							'first_name' => $arUser['NAME'],
							'last_name' => $arUser['LAST_NAME'],
							'work_position' => $arUser['CLIENT_DOMAIN'],
							'color' => IM\Color::getColor('GRAY'),
							'avatar' => empty($arUser['PERSONAL_PHOTO'])? '/bitrix/js/im/images/blank.gif': $arUser['PERSONAL_PHOTO'],
							'birthday' => false,
							'gender' => $arUser['PERSONAL_GENDER'] == 'F'? 'F': 'M',
							'phone_device' => false,
							'tz_offset' => 0,
							'extranet' => true,
							'network' => true,
							'bot' => false,
							'profile' => 'https://www.bitrix24.net/id'.$arUser['NETWORK_USER_ID'],
							'select' => 'Y',
							'network_id' => $arUser['NETWORK_ID'],
							'search_mark' => $searchText,
							'external_auth_id' => 'replica',
							'status' => 'online',
							'idle' => false,
							'last_activity_date' => false,
							'mobile_last_date' => false,
							'absent' => false,
						);
						$arIntersectUserIds[$arUser['XML_ID']] = $id;
					}
					if (!empty($arUserIds))
					{
						$result = \Bitrix\Main\UserTable::getList(Array(
							'select' => Array('XML_ID'),
							'filter' => Array(
								'=XML_ID' => array_keys($arIntersectUserIds),
								'=EXTERNAL_AUTH_ID' => \Bitrix\Socialservices\Network::EXTERNAL_AUTH_ID
							),
						));
						while($user = $result->fetch())
						{
							unset($arUsers[$arIntersectUserIds[$user['XML_ID']]]);
						}
					}
				}
			}
		}

		return Array('users' => $arUsers);
	}

	static function AllowToSend($arParams)
	{
		$bResult = false;
		if (isset($arParams['TO_USER_ID']))
		{
			global $USER;
			$toUserId = intval($arParams['TO_USER_ID']);

			$bResult = true;
			if(IsModuleInstalled('intranet') && CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
			{
				$bResult = false;
				if (CModule::IncludeModule("socialnetwork"))
				{
					global $USER, $CACHE_MANAGER;

					if(defined("BX_COMP_MANAGED_CACHE"))
						$ttl = 2592000;
					else
						$ttl = 600;

					$cache_id = 'im_user_sg_'.$USER->GetID();
					$obSGCache = new CPHPCache;
					$cache_dir = '/bx/imc/sonet';

					if($obSGCache->InitCache($ttl, $cache_id, $cache_dir))
					{
						$tmpVal = $obSGCache->GetVars();
						$bResult = in_array($toUserId, $tmpVal['EXTRANET_USERS']);
					}
					else
					{
						if(defined("BX_COMP_MANAGED_CACHE"))
							$CACHE_MANAGER->StartTagCache($cache_dir);

						$dbUsersInGroup = CSocNetUserToGroup::GetList(
							array(),
							array(
								"USER_ID" => $USER->GetID(),
								"<=ROLE" => SONET_ROLES_USER,
								"GROUP_SITE_ID" => CExtranet::GetExtranetSiteID(),
								"GROUP_ACTIVE" => "Y",
								"GROUP_CLOSED" => "N"
							),
							false,
							false,
							array("ID", "GROUP_ID", "GROUP_NAME")
						);

						$arUserSocNetGroups = Array();
						$arUserSG = Array();
						while ($ar = $dbUsersInGroup->GetNext(true, false))
						{
							$arUserSocNetGroups[] = $ar["GROUP_ID"];
							$arUserSG['SG'.$ar['GROUP_ID']] = array(
								'id' => 'SG'.$ar['GROUP_ID'],
								'status' => 'close',
								'name' => GetMessage('IM_CL_GROUP_SG').$ar['GROUP_NAME']
							);
							if(defined("BX_COMP_MANAGED_CACHE"))
							{
								$CACHE_MANAGER->RegisterTag('sonet_group_'.$ar['GROUP_ID']);
								$CACHE_MANAGER->RegisterTag('sonet_user2group_G'.$ar['GROUP_ID']);
							}
						}

						$arExtranetUsers = Array();
						$arUserInGroup = Array();
						if (count($arUserSocNetGroups) > 0)
						{
							$dbUsersInGroup = CSocNetUserToGroup::GetList(
								array(),
								array(
									"GROUP_ID" => $arUserSocNetGroups,
									"<=ROLE" => SONET_ROLES_USER,
									"USER_ACTIVE" => "Y",
									"USER_CONFIRM_CODE" => false
								),
								false,
								false,
								array("ID", "USER_ID", "GROUP_ID")
							);

							while ($ar = $dbUsersInGroup->GetNext(true, false))
							{
								if($USER->GetID() != $ar["USER_ID"])
								{
									$arExtranetUsers[$ar["USER_ID"]] = $ar["USER_ID"];

									if (isset($arUserInGroup["SG".$ar["GROUP_ID"]]))
										$arUserInGroup["SG".$ar["GROUP_ID"]]['users'][] = $ar["USER_ID"];
									else
										$arUserInGroup["SG".$ar["GROUP_ID"]] = Array('id' => "SG".$ar["GROUP_ID"], 'users' => Array($ar["USER_ID"]));
								}
							}
						}
						if(defined("BX_COMP_MANAGED_CACHE"))
							$CACHE_MANAGER->EndTagCache();
						if($obSGCache->StartDataCache())
						{
							$obSGCache->EndDataCache(array(
								'USER_SG' => $arUserSG,
								'EXTRANET_USERS' => $arExtranetUsers,
								'USER_IN_GROUP' => $arUserInGroup
							));
						}
						$bResult = in_array($toUserId, $arExtranetUsers);
					}
					unset($obSGCache);
				}
			}
			else if (!IsModuleInstalled('intranet'))
			{
				if (CIMSettings::GetPrivacy(CIMSettings::PRIVACY_MESSAGE) == CIMSettings::PRIVACY_RESULT_CONTACT && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed() && !CSocNetUserRelations::IsFriends($USER->GetID(), $arParams['TO_USER_ID']))
				{
					$bResult = false;
				}
				else if (CIMSettings::GetPrivacy(CIMSettings::PRIVACY_MESSAGE, $arParams['TO_USER_ID']) == CIMSettings::PRIVACY_RESULT_CONTACT && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed() && !CSocNetUserRelations::IsFriends($USER->GetID(), $arParams['TO_USER_ID']))
				{
					$bResult = false;
				}
			}
		}
		else if (isset($arParams['TO_CHAT_ID']))
		{
			global $DB, $USER;
			$toChatId = intval($arParams['TO_CHAT_ID']);
			$fromUserId = intval($USER->GetID());

			$strSql = "
				SELECT R.CHAT_ID
				FROM b_im_relation R
				WHERE R.USER_ID = ".$fromUserId."
					AND R.MESSAGE_TYPE IN ('".IM_MESSAGE_CHAT."', '".IM_MESSAGE_OPEN."', '".IM_MESSAGE_OPEN_LINE."')
					AND R.CHAT_ID = ".$toChatId."";
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				$bResult = true;
			else
				$bResult = false;
		}
		return $bResult;
	}

	static function GetUserData($arParams = Array())
	{
		$getDepartment = $arParams['DEPARTMENT'] == 'N' ? false : true;
		$getHrPhoto = $arParams['HR_PHOTO'] == 'Y' ? true : false;
		$getPhones = $arParams['PHONES'] == 'Y' ? true : false;
		$useCache = $arParams['USE_CACHE'] == 'N' ? false : true;
		$showOnline = $arParams['SHOW_ONLINE'] == 'N' ? false : true;
		$extraFields = $arParams['EXTRA_FIELDS'] == 'Y' ? true : false;

		$arFilter = Array();
		if (isset($arParams['ID']) && is_array($arParams['ID']) && !empty($arParams['ID']))
		{
			foreach ($arParams['ID'] as $key => $value)
			{
				if (intval($value) > 0)
				{
					$arParams['ID'][$key] = intval($value);
				}
			}
			$arFilter['=ID'] = $arParams['ID'];
		}
		else if (isset($arParams['ID']) && intval($arParams['ID']) > 0)
		{
			$arFilter['=ID'] = Array(intval($arParams['ID']));
		}

		if (empty($arFilter))
			return false;

		$nameTemplate = self::GetUserNameTemplate(SITE_ID);
		$nameTemplateSite = CSite::GetNameFormat(false);

		$bIntranetEnable = false;
		if(IsModuleInstalled('intranet') && CModule::IncludeModule('intranet'))
			$bIntranetEnable = true;

		$bVoximplantEnable = IsModuleInstalled('voximplant');
		$bColorEnabled = IM\Color::isEnabled();

		if($useCache)
		{
			global $USER;
			$obCache = new CPHPCache;
			$cache_ttl = intval($arParams['CACHE_TTL']);
			if ($cache_ttl <= 0)
				$cache_ttl = defined("BX_COMP_MANAGED_CACHE") ? 18144000 : 1800;

			$uid = (is_object($USER)? $USER->GetID(): 'AGENT');
            $cache_id = 'user_data_v29_'.$uid.'_'.implode('|', $arFilter['=ID']).'_'.$nameTemplate.'_'.$nameTemplateSite.'_'.$extraFields.'_'.$getPhones.'_'.$getDepartment.'_'.$bIntranetEnable.'_'.$bVoximplantEnable.'_'.LANGUAGE_ID.'_'.$bColorEnabled;

     		$userHash = md5($uid);
            $cache_dir = '/bx/imc/userdata/'.substr($userHash, 0, 2).'/'.substr($userHash, 2, 2);

			if($obCache->InitCache($cache_ttl, $cache_id, $cache_dir))
			{
				$arCacheResult = $obCache->GetVars();
				if ($showOnline)
				{
					$arOnline = CIMStatus::GetList(Array('ID' => array_keys($arCacheResult['users'])));
				}

				foreach ($arCacheResult['users'] as $userId => $value)
				{
					if ($showOnline)
					{
						$arCacheResult['users'][$userId]['status'] = isset($arOnline['users'][$userId])? $arOnline['users'][$userId]['status']: 'offline';
						$arCacheResult['users'][$userId]['idle'] = isset($arOnline['users'][$userId])? $arOnline['users'][$userId]['idle']: false;
						$arCacheResult['users'][$userId]['mobile_last_date'] = isset($arOnline['users'][$userId])? $arOnline['users'][$userId]['mobile_last_date']: false;
						$arCacheResult['users'][$userId]['last_activity_date'] = isset($arOnline['users'][$userId])? $arOnline['users'][$userId]['last_activity_date']: false;
						$arCacheResult['users'][$userId]['absent'] = self::formatAbsentResult($userId);
					}

					if ($getHrPhoto && !isset($arCacheResult['hrphoto']))
					{
						$arPhotoHrTmp = CFile::ResizeImageGet(
							$arCacheResult['source'][$userId]["PERSONAL_PHOTO"],
							array('width' => 200, 'height' => 200),
							BX_RESIZE_IMAGE_EXACT,
							false,
							false,
							true
						);
						$arCacheResult['hrphoto'][$userId] = empty($arPhotoHrTmp['src'])? '/bitrix/js/im/images/hidef-avatar-v3.png': $arPhotoHrTmp['src']; // TODO REMOVE DEFAULT
					}
				}
				return $arCacheResult;
			}
		}

		$arSelect = array("ID", "LAST_NAME", "NAME", "EMAIL", "LOGIN", "PERSONAL_PHOTO", "SECOND_NAME", "PERSONAL_BIRTHDAY", "WORK_POSITION", "PERSONAL_GENDER", "EXTERNAL_AUTH_ID", "TIME_ZONE_OFFSET", "PERSONAL_WWW", "ACTIVE", "LAST_ACTIVITY_DATE"); // TODO , "TIME_ZONE_OFFSET"
		if ($getPhones)
		{
			$arSelect[] = 'WORK_PHONE';
			$arSelect[] = 'PERSONAL_PHONE';
			$arSelect[] = 'PERSONAL_MOBILE';
		}
		if($bIntranetEnable)
		{
			$arSelect[] = 'UF_PHONE_INNER';
			$arSelect[] = 'UF_DEPARTMENT';
		}
		if ($bVoximplantEnable)
		{
			$arSelect[] = 'UF_VI_PHONE';
		}

		$arUsers = array();
		$arUserInGroup = array();
		$arPhones = array();
		$arHrPhoto = array();
		$arSource = array();

		$query = new \Bitrix\Main\Entity\Query(\Bitrix\Main\UserTable::getEntity());

		$query->registerRuntimeField('', new \Bitrix\Main\Entity\ReferenceField('ref', 'Bitrix\Im\Model\StatusTable', array('=this.ID' => 'ref.USER_ID')));
		$query->addSelect('ref.COLOR', 'COLOR')
			->addSelect('ref.STATUS', 'STATUS')
			->addSelect('ref.IDLE', 'IDLE')
			->addSelect('ref.MOBILE_LAST_DATE', 'MOBILE_LAST_DATE');

		foreach ($arSelect as $value)
		{
			$query->addSelect($value);
		}
		foreach ($arFilter as $key => $value)
		{
			$query->addFilter($key, $value);
		}
		$resultQuery = $query->exec();

		$bots = \Bitrix\Im\Bot::getListCache();

		while ($arUser = $resultQuery->fetch())
		{
			foreach ($arUser as $key => $value)
			{
				$arUser[$key] = !is_array($value) && !is_object($value)? htmlspecialcharsEx($value): $value;
			}

			$arSource[$arUser["ID"]]["PERSONAL_PHOTO"] = $arUser["PERSONAL_PHOTO"];

			$arPhotoTmp = CFile::ResizeImageGet(
				$arUser["PERSONAL_PHOTO"],
				array('width' => 100, 'height' => 100),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);

			$color = self::GetUserColor($arUser["ID"], $arUser['PERSONAL_GENDER'] == 'M'? 'M': 'F');
			if (isset($arUser['COLOR']) && strlen($arUser['COLOR']) > 0)
			{
				$color = IM\Color::getColor($arUser['COLOR']);
			}
			if (!$color)
			{
				$color = self::GetUserColor($arUser["ID"], $arUser['PERSONAL_GENDER'] == 'M'? 'M': 'F');
			}

			$arUser['PERSONAL_BIRTHDAY'] = $arUser['PERSONAL_BIRTHDAY'] instanceof \Bitrix\Main\Type\Date? $arUser['PERSONAL_BIRTHDAY']->format('d-m'): false;
			$arUser['IDLE'] = $arUser['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['IDLE']: false;
			$arUser['MOBILE_LAST_DATE'] = $arUser['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['MOBILE_LAST_DATE']: false;
			$arUser['LAST_ACTIVITY_DATE'] = $arUser['LAST_ACTIVITY_DATE'] instanceof \Bitrix\Main\Type\DateTime? $arUser['LAST_ACTIVITY_DATE']: false;

			$arUsers[$arUser["ID"]] = Array(
				'id' => $arUser["ID"],
				'name' => CUser::FormatName($nameTemplateSite, $arUser, true, false),
				'active' => $arUser['ACTIVE'] == 'Y',
				'first_name' => $arUser['NAME'],
				'last_name' => $arUser['LAST_NAME'],
				'work_position' => $arUser['WORK_POSITION'],
				'color' => $color,
				'avatar' => empty($arPhotoTmp['src'])? '/bitrix/js/im/images/blank.gif': $arPhotoTmp['src'],
				'avatar_id' => $arUser["PERSONAL_PHOTO"],
				'birthday' => $arUser['PERSONAL_BIRTHDAY'],
				'gender' => $arUser['PERSONAL_GENDER'] == 'F'? 'F': 'M',
				'phone_device' => $bVoximplantEnable && $arUser['UF_VI_PHONE'] == 'Y',
				'phones' => $bVoximplantEnable && $arUser['UF_VI_PHONE'] == 'Y',
				'extranet' => self::IsExtranet($arUser),
				'tz_offset' => intval($arUser['TIME_ZONE_OFFSET']),
				'network' => $arUser['EXTERNAL_AUTH_ID'] == self::NETWORK_AUTH_ID || $arUser['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID && $bots[$arUser["ID"]]['TYPE'] == \Bitrix\Im\Bot::TYPE_NETWORK,
				'bot' => $arUser['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID,
				'connector' => $arUser['EXTERNAL_AUTH_ID'] == "imconnector",
				'profile' => CIMContactList::GetUserPath($arUser["ID"]),
				'external_auth_id' => $arUser['EXTERNAL_AUTH_ID']? $arUser['EXTERNAL_AUTH_ID']: 'default',
				'status' => $arUser['STATUS'],
				'idle' => $arUser['IDLE'],
				'last_activity_date' => $arUser['LAST_ACTIVITY_DATE'],
				'mobile_last_date' => $arUser['MOBILE_LAST_DATE'],
				'departments' => $getDepartment && is_array($arUser["UF_DEPARTMENT"]) && !empty($arUser["UF_DEPARTMENT"])? array_values($arUser["UF_DEPARTMENT"]): Array(),
				'absent' => self::formatAbsentResult($arUser["ID"]),
			);

			if ($extraFields)
			{
				$arUsers[$arUser["ID"]]['website'] = $arUser['PERSONAL_WWW'];
				$arUsers[$arUser["ID"]]['email'] = $arUser['EMAIL'];
			}

			foreach($arUsers[$arUser["ID"]]["departments"] as $dep_id)
			{
				if (isset($arUserInGroup[$dep_id]))
					$arUserInGroup[$dep_id]['users'][] = $arUser["ID"];
				else
					$arUserInGroup[$dep_id] = Array('id' => $dep_id, 'users' => Array($arUser["ID"]));
			}

			if ($getHrPhoto)
			{
				$arPhotoHrTmp = CFile::ResizeImageGet(
					$arUser["PERSONAL_PHOTO"],
					array('width' => 200, 'height' => 200),
					BX_RESIZE_IMAGE_EXACT,
					false,
					false,
					true
				);
				$arHrPhoto[$arUser["ID"]] = empty($arPhotoHrTmp['src'])? '/bitrix/js/im/images/hidef-avatar-v3.png': $arPhotoHrTmp['src']; // TODO REMOVE DEFAULT
			}

			if ($getPhones)
			{
				if (CModule::IncludeModule('voximplant'))
				{
					$result = CVoxImplantPhone::Normalize($arUser["WORK_PHONE"]);
					if ($result)
					{
						$arPhones[$arUser["ID"]]['WORK_PHONE'] = $arUser['WORK_PHONE'];
					}
					$result = CVoxImplantPhone::Normalize($arUser["PERSONAL_MOBILE"]);
					if ($result)
					{
						$arPhones[$arUser["ID"]]['PERSONAL_MOBILE'] = $arUser['PERSONAL_MOBILE'];
					}
					$result = CVoxImplantPhone::Normalize($arUser["PERSONAL_PHONE"]);
					if ($result)
					{
						$arPhones[$arUser["ID"]]['PERSONAL_PHONE'] = $arUser['PERSONAL_PHONE'];
					}
					$result = preg_replace("/[^0-9\#\*]/i", "", $arUser["UF_PHONE_INNER"]);
					if ($result)
					{
						$arPhones[$arUser["ID"]]['INNER_PHONE'] = $result;
					}
				}
				else
				{
					$arPhones[$arUser["ID"]]['WORK_PHONE'] = $arUser['WORK_PHONE'];
					$arPhones[$arUser["ID"]]['PERSONAL_MOBILE'] = $arUser['PERSONAL_MOBILE'];
					$arPhones[$arUser["ID"]]['PERSONAL_PHONE'] = $arUser['PERSONAL_PHONE'];
				}
				if (isset($arPhones[$arUser["ID"]]))
				{
					$arUsers[$arUser["ID"]]['phones'] = array_change_key_case($arPhones[$arUser["ID"]], CASE_LOWER);
				}
			}
		}

		$result = array('users' => $arUsers, 'hrphoto' => $arHrPhoto, 'userInGroup' => $arUserInGroup, 'phones' => $arPhones, 'source' => $arSource);

		if($useCache)
		{
			$cacheTag = array();
			if($obCache->StartDataCache())
			{
				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					global $CACHE_MANAGER;
					$CACHE_MANAGER->StartTagCache($cache_dir);
					if(is_array($arParams['ID']))
					{
						foreach ($arParams['ID'] as $id)
						{
							$tag = 'USER_NAME_'.intval($id);
							if(!in_array($tag, $cacheTag))
							{
								$cacheTag[] = $tag;
								$CACHE_MANAGER->RegisterTag($tag);
							}
						}
					}
					elseif (isset($arParams['ID']) && intval($arParams['ID']) > 0)
					{
						$tag = 'USER_NAME_'.intval($arParams['ID']);
						$CACHE_MANAGER->RegisterTag($tag);
					}
					$CACHE_MANAGER->EndTagCache();
				}
				$obCache->EndDataCache($result);
				unset($cacheTag);
			}
		}

		unset($result['source']);

		return $result;
	}

	public static function SetOnline($userId = null, $cache = true)
	{
		return CUser::SetLastActivityDate($userId, $cache);
	}

	public static function SetOffline($userId = null)
	{
		global $USER, $DB;

		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$sqlDateFunction = 'NULL';
		$dbType = strtolower($DB->type);
		if ($dbType== "mysql")
			$sqlDateFunction = "DATE_SUB(NOW(), INTERVAL 120 SECOND)";
		else if ($dbType == "mssql")
			$sqlDateFunction = "dateadd(SECOND, -120, getdate())";
		else if ($dbType == "oracle")
			$sqlDateFunction = "SYSDATE-(1/24/60/60*120)";

		$DB->Query("UPDATE b_user SET LAST_ACTIVITY_DATE = ".$sqlDateFunction." WHERE ID = ".$userId);

		if ($userId == $USER->GetId())
		{
			unset($_SESSION['IM_LAST_ONLINE']);
			unset($_SESSION['USER_LAST_ONLINE_'.$userId]);
		}

		$USER->Logout();

		return true;
	}

	public static function SetCurrentTab($userId)
	{
		return true;
	}

	public static function InRecent($userId, $type, $itemId)
	{
		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		$messageId = false;
		$result = \Bitrix\Im\Model\RecentTable::getList(Array(
			'filter' => Array(
				'=USER_ID' => $userId,
				'=ITEM_TYPE' => $type,
				'=ITEM_ID' => $itemId,
			)
		))->fetch();
		if ($result)
		{
			$messageId = $result['ITEM_MID'];
		}

		return $messageId;
	}

	public static function SetRecentForNewUser($userId)
	{
		$userId = intval($userId);
		if ($userId <= 0)
		{
			return false;
		}

		$colleagues = \Bitrix\Im\Department::getColleagues($userId);
		foreach ($colleagues as $uid)
		{
			self::SetRecent(Array(
				'ENTITY_ID' => $uid,
				'USER_ID' => $userId
			));
		}

		return true;
	}

	public static function SetRecent($arParams)
	{
		global $USER;

		$itemId = intval($arParams['ENTITY_ID']);
		$messageId = intval($arParams['MESSAGE_ID']);
		$chatId = intval($arParams['CHAT_ID']);
		$relationId = intval($arParams['RELATION_ID']);
		$sessionId = intval($arParams['SESSION_ID']);

		if (in_array($arParams['CHAT_TYPE'], Array(IM_MESSAGE_OPEN, IM_MESSAGE_CHAT, IM_MESSAGE_OPEN_LINE)))
		{
			$chatType = $arParams['CHAT_TYPE'];
		}
		else
		{
			$chatType = IM_MESSAGE_PRIVATE;
		}

		if ($itemId <= 0)
		{
			return false;
		}

		$userId = intval($arParams['USER_ID']);
		if ($userId <= 0)
			$userId = (int)$USER->GetID();

		$isChat = $chatType != IM_MESSAGE_PRIVATE;

		$connection = \Bitrix\Main\Application::getInstance()->getConnection();

		$isUserAlreadyInRecent = $connection->queryScalar("SELECT 1 FROM b_im_recent WHERE USER_ID = ".$userId);

		$merge = $connection->getSqlHelper()->prepareMerge(
			"b_im_recent",
			array('USER_ID', 'ITEM_TYPE', 'ITEM_ID'),
			array(
				'USER_ID' => $userId,
				'ITEM_TYPE' => $chatType,
				'ITEM_ID' => $itemId,
				'ITEM_MID' => $messageId,
				'ITEM_CID' => $chatId,
				'ITEM_RID' => $relationId,
				'ITEM_OLID' => $sessionId,
				'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime(),
			),
			array(
				'ITEM_MID' => $messageId,
				'ITEM_CID' => $chatId,
				'ITEM_RID' => $relationId,
				'ITEM_OLID' => $sessionId,
				'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime(),
			)
		);
		if ($merge && $merge[0] != "")
		{
			$connection->query($merge[0]);
		}

		$obCache = new CPHPCache();
		$obCache->CleanDir('/bx/imc/recent'.CIMMessenger::GetCachePath($userId));

		if ($isChat)
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_GROUP);
		else
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_MESSAGE);

		if (!$isUserAlreadyInRecent)
		{
			$event = new \Bitrix\Main\Event("im", "OnAfterRecentAdd", array(
				"user_id" => $userId,
			));
			$event->send();
		}

		return true;
	}

	public static function DeleteRecent($entityId, $isChat = false, $userId = false)
	{
		global $DB;

		if (is_array($entityId))
		{
			foreach ($entityId as $key => $value)
				$entityId[$key] = intval($value);

			$entityId = array_slice($entityId, 0, 1000);

			$sqlEntityId = 'ITEM_ID IN ('.implode(',', $entityId).')';
		}
		else if (intval($entityId) > 0)
		{
			$sqlEntityId = 'ITEM_ID = '.intval($entityId);
		}
		else
		{
			return false;
		}

		if (intval($userId) <= 0)
			$userId = $GLOBALS['USER']->GetID();

		if ($isChat)
		{
			$itemType = "ITEM_TYPE IN ( '".IM_MESSAGE_CHAT."', '".IM_MESSAGE_OPEN."', '".IM_MESSAGE_THREAD."', '".IM_MESSAGE_OPEN_LINE."' )";
		}
		else
		{
			$itemType = "ITEM_TYPE = '".IM_MESSAGE_PRIVATE."'";
		}

		$strSQL = "
			UPDATE b_im_relation R
			INNER JOIN b_im_recent RC ON RC.USER_ID = ".$userId." AND RC.".$itemType." AND RC.".$sqlEntityId."
			SET R.STATUS = '".IM_STATUS_READ."', R.MESSAGE_STATUS = '".IM_MESSAGE_STATUS_RECEIVED."', R.COUNTER = 555, R.LAST_READ = NOW()
			WHERE R.ID = RC.ITEM_RID;
		";
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$strSQL = "DELETE FROM b_im_recent WHERE USER_ID = ".$userId." AND ".$itemType." AND ".$sqlEntityId;
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if ($isChat && CModule::IncludeModule('pull'))
		{
			if (is_array($entityId))
			{
				foreach ($entityId as $value)
				{
					CPullWatch::Delete($userId, 'IM_PUBLIC_'.intval($value));
				}
			}
			else
			{
				CPullWatch::Delete($userId, 'IM_PUBLIC_'.intval($entityId));
			}
		}

		\Bitrix\Im\Counter::clearCache($userId);

		$obCache = new CPHPCache();
		$obCache->CleanDir('/bx/imc/recent'.CIMMessenger::GetCachePath($userId));

		$strSQL = $DB->TopSql("SELECT 1 FROM b_im_recent WHERE USER_ID = ".$userId, 1);
		$rs = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if (!$rs->Fetch())
		{
			$event = new \Bitrix\Main\Event("im", "OnAfterRecentDelete", array(
				"user_id" => $userId,
			));
			$event->send();
		}

		return true;
	}

	public static function DialogHide($dialogId, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$pullInclude = \Bitrix\Main\Loader::includeModule("pull");

		if (substr($dialogId, 0, 4) == 'chat')
		{
			$chatId = substr($dialogId, 4);
			CIMContactList::DeleteRecent($chatId, true);
		}
		else
		{
			$dialogId = intval($dialogId);
			CIMContactList::DeleteRecent($dialogId);
		}

		if ($pullInclude)
		{
			\Bitrix\Pull\Event::add($userId, Array(
				'module_id' => 'im',
				'command' => 'chatHide',
				'expiry' => 3600,
				'params' => Array(
					'dialogId' => $dialogId
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
		}

		return true;
	}

	public static function ClearRecentCache($userId = null)
	{
		$cache = new CPHPCache();
		$cache->CleanDir('/bx/imc/recent'.($userId? CIMMessenger::GetCachePath($userId): ''));
	}

	public static function GetRecentList($arParams = Array())
	{
		global $DB, $USER, $CACHE_MANAGER;

		$bLoadUnreadMessage = isset($arParams['LOAD_UNREAD_MESSAGE']) && $arParams['LOAD_UNREAD_MESSAGE'] == 'Y'? true: false;
		$bTimeZone = isset($arParams['USE_TIME_ZONE']) && $arParams['USE_TIME_ZONE'] == 'N'? false: true;
		$bSmiles = isset($arParams['USE_SMILES']) && $arParams['USE_SMILES'] == 'N'? false: true;
		$userId = isset($arParams['USER_ID'])? $arParams['USER_ID']: $USER->GetId();
		if ($userId <= 0)
		{
			return false;
		}

		$nameTemplateSite = CSite::GetNameFormat(false);

		$arRecent = Array();
		$arUsers = Array();

		$bColorEnabled = IM\Color::isEnabled();
		$bOpenChatEnabled = CIMMessenger::CheckEnableOpenChat();

		$generalChatId = CIMChat::GetGeneralChatId();

		$isOperator = \Bitrix\Im\Integration\Imopenlines\User::isOperator();

		$cache_ttl = 2592000;
		$cache_id = 'im_recent_v17_'.$userId.'_'.$bColorEnabled.'_'.$bOpenChatEnabled.'_'.($isOperator? 1: 0);
		$cache_dir = '/bx/imc/recent'.CIMMessenger::GetCachePath($userId);
		$obCache = new CPHPCache();

		if($obCache->InitCache($cache_ttl, $cache_id, $cache_dir))
		{
			$arRecent = $obCache->GetVars();

			$arOnline = CIMStatus::GetList();
			foreach ($arRecent as $key => $value)
			{
				if ($value['TYPE'] != IM_MESSAGE_PRIVATE)
				{
					continue;
				}

				if (isset($arOnline['users'][$value['USER']['id']]))
				{
					$arRecent[$key]['USER']['color'] = $arOnline['users'][$value['USER']['id']]['color'];
					$arRecent[$key]['USER']['status'] = $arOnline['users'][$value['USER']['id']]['status'];
					$arRecent[$key]['USER']['idle'] = $arOnline['users'][$value['USER']['id']]['idle'];
					$arRecent[$key]['USER']['mobile_last_date'] = $arOnline['users'][$value['USER']['id']]['mobile_last_date'];
					$arRecent[$key]['USER']['last_activity_date'] = isset($arOnline['users'][$value['USER']['id']]['last_activity_date'])? $arOnline['users'][$value['USER']['id']]['last_activity_date']: false;
					$arRecent[$key]['USER']['absent'] = self::formatAbsentResult($value['USER']['id']);
				}
			}
		}
		else
		{
			if (!$bTimeZone)
				CTimeZone::Disable();

			$strSql = "
				SELECT
					R.ITEM_TYPE, R.ITEM_ID, R.PINNED, C1.COUNTER,
					R.ITEM_MID M_ID, M.AUTHOR_ID M_AUTHOR_ID, M.ID M_ID, M.CHAT_ID M_CHAT_ID, M.MESSAGE M_MESSAGE, ".$DB->DatetimeToTimestampFunction('R.DATE_UPDATE')." M_DATE_CREATE,
					C.TITLE C_TITLE, C.AUTHOR_ID C_OWNER_ID, C.ENTITY_TYPE CHAT_ENTITY_TYPE, C.ENTITY_ID CHAT_ENTITY_ID, C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1, C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2, C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3, C.AVATAR C_AVATAR, C.CALL_NUMBER C_CALL_NUMBER, C.EXTRANET CHAT_EXTRANET, C.COLOR CHAT_COLOR, C.TYPE CHAT_TYPE,
					U.LOGIN, U.NAME, U.LAST_NAME, U.PERSONAL_PHOTO, U.SECOND_NAME, ".$DB->DatetimeToTimestampFunction('U.PERSONAL_BIRTHDAY')." PERSONAL_BIRTHDAY, ".$DB->DatetimeToTimestampFunction('U.LAST_ACTIVITY_DATE')." LAST_ACTIVITY_DATE, U.PERSONAL_GENDER, U.EXTERNAL_AUTH_ID, U.WORK_POSITION, U.TIME_ZONE_OFFSET, U.ACTIVE,
					ST.COLOR, ST.STATUS, ".$DB->DatetimeToTimestampFunction('ST.IDLE')." IDLE, ".$DB->DatetimeToTimestampFunction('ST.MOBILE_LAST_DATE')." MOBILE_LAST_DATE,
					C1.USER_ID RID, C1.NOTIFY_BLOCK RELATION_NOTIFY_BLOCK, C1.USER_ID RELATION_USER_ID
					".($isOperator? ", S.ID LINES_ID, S.STATUS LINES_STATUS": "")."
				FROM
				b_im_recent R
				LEFT JOIN b_user U ON R.ITEM_TYPE = '".IM_MESSAGE_PRIVATE."' AND R.ITEM_ID = U.ID
				LEFT JOIN b_im_status ST ON R.ITEM_TYPE = '".IM_MESSAGE_PRIVATE."' AND R.ITEM_ID = ST.USER_ID
				LEFT JOIN b_im_chat C ON R.ITEM_TYPE != '".IM_MESSAGE_PRIVATE."' AND R.ITEM_ID = C.ID
				LEFT JOIN b_im_message M ON R.ITEM_MID = M.ID
				LEFT JOIN b_im_relation C1 ON C1.CHAT_ID = C.ID AND C1.USER_ID = ".$userId."
				".($isOperator? "LEFT JOIN b_imopenlines_session S ON R.ITEM_OLID > 0 AND S.ID = R.ITEM_OLID": "")."
				WHERE R.USER_ID = ".$userId;
			if (!$bTimeZone)
				CTimeZone::Enable();

			$enableOpenChat = CIMMessenger::CheckEnableOpenChat();
			$bots = \Bitrix\Im\Bot::getListCache();

			$toDelete = Array();
			$arMessageId = Array();

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while ($arRes = $dbRes->GetNext(true, false))
			{
				$arRes['ITEM_TYPE'] = trim($arRes['ITEM_TYPE']);
				$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);
				$arRes['CHAT_EXTRANET'] = trim($arRes['CHAT_EXTRANET']);

				if ($arRes['ITEM_TYPE'] == IM_MESSAGE_OPEN)
				{
					if (!$enableOpenChat)
					{
						continue;
					}
					else if (intval($arRes['RID']) <= 0 && IM\User::getInstance($userId)->isExtranet())
					{
						continue;
					}
				}
				else if ($arRes['ITEM_TYPE'] == IM_MESSAGE_CHAT || $arRes['ITEM_TYPE'] == IM_MESSAGE_OPEN_LINE)
				{
					if (intval($arRes['RID']) <= 0)
					{
						continue;
					}
				}

				$arMessageId[] = $arRes['M_ID'];

				if ($arRes['M_ID'] > 0 && $arRes['M_DATE_CREATE']+2592000 < time())
				{
					$toDelete[$arRes['ITEM_TYPE']][] = $arRes['ITEM_ID'];
					continue;
				}

				$itemId = $arRes['ITEM_ID'];
				$item = Array(
					'TYPE' => $arRes['ITEM_TYPE'],
					'MESSAGE' => Array(
						'id' => $arRes['M_ID'],
						'chatId' => $arRes['M_CHAT_ID'],
						'senderId' => $arRes['M_AUTHOR_ID'],
						'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['M_DATE_CREATE']),
						'text' => \Bitrix\Im\Text::parse($arRes['M_MESSAGE'], Array('CUT_STRIKE' => 'Y', 'SMILES' => 'N', 'SAFE' => 'N')),
						'pinned' => $arRes['PINNED'] == 'Y',
					),
					'COUNTER' => (int)$arRes['COUNTER'],
				);
				$item['MESSAGE']['text'] = preg_replace('#\-{54}.+?\-{54}#s', " [".GetMessage('IM_QUOTE')."] ", strip_tags(str_replace(array("<br>","<br/>","<br />", "#BR#"), Array(" "," ", " ", " "), $item['MESSAGE']['text']), "<img>"));

				if ($arRes['ITEM_TYPE'] == IM_MESSAGE_PRIVATE)
				{
					$arUsers[] = $arRes['ITEM_ID'];

					$arFileTmp = CFile::ResizeImageGet(
						$arRes["PERSONAL_PHOTO"],
						array('width' => 100, 'height' => 100),
						BX_RESIZE_IMAGE_EXACT,
						false,
						false,
						true
					);

					$arRes['PERSONAL_BIRTHDAY'] = $arRes['PERSONAL_BIRTHDAY']? \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['PERSONAL_BIRTHDAY'])->format('d-m'): false;
					$arRes['IDLE'] = $arRes['IDLE']? \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['IDLE']): false;
					$arRes['MOBILE_LAST_DATE'] = $arRes['MOBILE_LAST_DATE']? \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['MOBILE_LAST_DATE']): false;
					$arRes['LAST_ACTIVITY_DATE'] = $arRes['LAST_ACTIVITY_DATE']? \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['LAST_ACTIVITY_DATE']): false;

					$item['USER'] = Array(
						'id' => $arRes['ITEM_ID'],
						'name' => CUser::FormatName($nameTemplateSite, $arRes, true, false),
						'active' => $arRes['ACTIVE'] == 'Y',
						'first_name' => $arRes['NAME'],
						'last_name' => $arRes['LAST_NAME'],
						'work_position' => $arRes['WORK_POSITION'],
						'color' => self::GetUserColor($arRes["ID"], $arRes['PERSONAL_GENDER'] == 'M'? 'M': 'F'),
						'avatar' => empty($arFileTmp['src'])? '/bitrix/js/im/images/blank.gif': $arFileTmp['src'],
						'birthday' => $arRes['PERSONAL_BIRTHDAY'],
						'gender' => $arRes['PERSONAL_GENDER'] == 'F'? 'F': 'M',
						'extranet' => false,
						'network' => $arRes['EXTERNAL_AUTH_ID'] == self::NETWORK_AUTH_ID || $arRes['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID && $bots[$arRes["ITEM_ID"]]['TYPE'] == \Bitrix\Im\Bot::TYPE_NETWORK,
						'bot' => $arRes['EXTERNAL_AUTH_ID'] == \Bitrix\Im\Bot::EXTERNAL_AUTH_ID,
						'tz_offset' => intval($arRes['TIME_ZONE_OFFSET']),
						'phone_device' => false,
						'profile' => CIMContactList::GetUserPath($arRes["ITEM_ID"]),
						'external_auth_id' => $arRes['EXTERNAL_AUTH_ID']? $arRes['EXTERNAL_AUTH_ID']: 'default',
						'status' => $arRes['STATUS'],
						'idle' => $arRes['IDLE'],
						'last_activity_date' => $arRes['LAST_ACTIVITY_DATE'],
						'mobile_last_date' => $arRes['MOBILE_LAST_DATE'],
						'absent' => self::formatAbsentResult($arRes["ITEM_ID"]),
					);
					if (!$item['MESSAGE']['text'])
					{
						$item['MESSAGE']['text'] = $arRes['WORK_POSITION'];
					}

				}
				else
				{
					if ($arRes["CHAT_ENTITY_TYPE"] == 'CALL')
					{
						$chatType = 'call';
					}
					else if ($arRes["CHAT_ENTITY_TYPE"] == 'LINES')
					{
						$chatType = 'lines';
						if ($isOperator)
						{
							$item['LINES'] = Array(
								'ID' => $arRes['LINES_ID'],
								'STATUS' => $arRes['LINES_STATUS']
							);
						}
					}
					else if ($arRes["CHAT_ENTITY_TYPE"] == 'LIVECHAT')
					{
						$chatType = 'livechat';
					}
					else
					{
						if ($generalChatId == $arRes['M_CHAT_ID'])
						{
							$arRes["CHAT_ENTITY_TYPE"] = 'GENERAL';
						}
						$chatType = $arRes["ITEM_TYPE"] == IM_MESSAGE_OPEN? 'open': 'chat';
					}

					$muteList = Array();
					if ($arRes['RELATION_NOTIFY_BLOCK'] == 'Y')
					{
						$muteList = Array($arRes['RELATION_USER_ID'] => true);
					}

					$itemId = 'chat'.$itemId;
					$item['CHAT'] = Array(
						'id' => $arRes['ITEM_ID'],
						'name' => $arRes["C_TITLE"],
						'color' => $arRes["CHAT_COLOR"] == ""? IM\Color::getColorByNumber($arRes['ITEM_ID']): IM\Color::getColor($arRes['CHAT_COLOR']),
						'avatar' => CIMChat::GetAvatarImage($arRes["C_AVATAR"]),
						'extranet' => $arRes["CHAT_EXTRANET"] == ""? "": ($arRes["CHAT_EXTRANET"] == "Y"? true: false),
						'owner' => $arRes["C_OWNER_ID"],
						'type' => $chatType,
						'entity_type' => $arRes["CHAT_ENTITY_TYPE"],
						'entity_id' => $arRes["CHAT_ENTITY_ID"],
						'entity_data_1' => trim($arRes["CHAT_ENTITY_DATA_1"]),
						'entity_data_2' => trim($arRes["CHAT_ENTITY_DATA_2"]),
						'entity_data_3' => trim($arRes["CHAT_ENTITY_DATA_3"]),
						'mute_list' => $muteList,
						'message_type' => $arRes['CHAT_TYPE'],
						'call_number' => $arRes["C_CALL_NUMBER"]
					);
				}
				$arRecent[$itemId] = $item;
			}
			$params = CIMMessageParam::Get($arMessageId);
			foreach ($arRecent as $key => $value)
			{
				if (isset($params[$value['MESSAGE']['id']]) && is_array($params[$value['MESSAGE']['id']]['FILE_ID']))
				{
					if (count($params[$value['MESSAGE']['id']]['FILE_ID']) > 0 && strlen(trim($arRecent[$key]['MESSAGE']['text'])) <= 0)
					{
						$arRecent[$key]['MESSAGE']['text'] = "[".GetMessage('IM_FILE')."]";
					}
					$arRecent[$key]['MESSAGE']['params'] = $params[$value['MESSAGE']['id']];
				}
			}

			if (!empty($toDelete))
			{
				if (isset($toDelete[IM_MESSAGE_PRIVATE]))
					self::DeleteRecent($toDelete[IM_MESSAGE_PRIVATE]);
				if (isset($toDelete[IM_MESSAGE_CHAT]))
					self::DeleteRecent($toDelete[IM_MESSAGE_CHAT], true);
				if (isset($toDelete[IM_MESSAGE_OPEN_LINE]))
					self::DeleteRecent($toDelete[IM_MESSAGE_OPEN_LINE], true);
				if (isset($toDelete[IM_MESSAGE_OPEN]))
					self::DeleteRecent($toDelete[IM_MESSAGE_OPEN], true);
			}

			$bIntranetEnable = IsModuleInstalled('intranet');
			$bVoximplantEnable = IsModuleInstalled('voximplant');
			if ($bIntranetEnable || $bVoximplantEnable)
			{
				$arUserPhone = Array();
				$arUserDepartment = Array();

				$arSelectParams = Array();
				if ($bIntranetEnable)
					$arSelectParams[] = 'UF_DEPARTMENT';
				if ($bVoximplantEnable)
					$arSelectParams[] = 'UF_VI_PHONE';

				$dbUsers = CUser::GetList(($sort_by = Array('last_name'=>'asc')), ($dummy=''), Array('ID' => $userId."|".implode('|', $arUsers)), Array('FIELDS' => Array("ID"), 'SELECT' => $arSelectParams));
				while ($arUser = $dbUsers->GetNext(true, false))
				{
					$arUserPhone[$arUser['ID']] = $arUser['UF_VI_PHONE'] == 'Y';
					$arUserDepartment[$arUser['ID']] = self::IsExtranet($arUser);
				}

				foreach ($arRecent as $key => $value)
				{
					if (isset($value['USER']))
					{
						$arRecent[$key]['USER']['extranet'] = $arUserDepartment[$value['USER']['id']];
						$arRecent[$key]['USER']['phone_device'] = $arUserPhone[$value['USER']['id']];
					}
				}
			}

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$bVoximplantEnable = IsModuleInstalled('voximplant');

				$CACHE_MANAGER->StartTagCache($cache_dir);
				$CACHE_MANAGER->RegisterTag("IM_RECENT_LIST");
				$CACHE_MANAGER->RegisterTag($bVoximplantEnable? "USER_CARD": "USER_NAME");
				$CACHE_MANAGER->EndTagCache();
			}

			if($obCache->StartDataCache())
				$obCache->EndDataCache($arRecent);
		}

		if ($bLoadUnreadMessage)
		{
			$CIMMessage = new CIMMessage(false, Array(
				'HIDE_LINK' => 'Y'
			));

			$ar = $CIMMessage->GetUnreadMessage(Array(
				'LOAD_DEPARTMENT' => 'N',
				'ORDER' => 'ASC',
				'GROUP_BY_CHAT' => 'Y',
				'USE_TIME_ZONE' => $bTimeZone? 'Y': 'N',
				'USE_SMILES' => $bSmiles? 'Y': 'N'
			));
			foreach ($ar['message'] as $data)
			{
				if (!isset($arRecent[$data['senderId']]))
				{
					$arRecent[$data['senderId']] = Array(
						'TYPE' => IM_MESSAGE_PRIVATE,
						'USER' => $ar['users'][$data['senderId']]
					);
				}
				$arRecent[$data['senderId']]['MESSAGE'] = Array(
					'id' => $data['id'],
					'senderId' => $data['senderId'],
					'date' => $data['date'],
					'text' => preg_replace('#\-{54}.+?\-{54}#s', " [".GetMessage('IM_QUOTE')."] ", strip_tags(str_replace(array("<br>","<br/>","<br />", "#BR#"), Array(" ", " ", " ", " "), $data['text']), "<img>"))
				);

				$arRecent[$data['senderId']]['COUNTER'] = $data['counter'];
			}

			$CIMChat = new CIMChat(false, Array(
				'HIDE_LINK' => 'Y'
			));

			$ar = $CIMChat->GetUnreadMessage(Array(
				'ORDER' => 'ASC',
				'GROUP_BY_CHAT' => 'Y',
				'USER_LOAD' => 'N',
				'FILE_LOAD' => 'N',
				'USE_SMILES' => $bSmiles? 'Y': 'N',
				'USE_TIME_ZONE' => $bTimeZone? 'Y': 'N'
			));
			foreach ($ar['message'] as $data)
			{
				if (!isset($arRecent['chat'.$data['recipientId']]))
				{
					$arRecent['chat'.$data['recipientId']] = Array(
						'TYPE' => $ar['messageType']? $ar['messageType']: IM_MESSAGE_CHAT,
						'CHAT' => $ar['chat']
					);
				}
				$arRecent['chat'.$data['recipientId']]['MESSAGE'] = Array(
					'id' => $data['id'],
					'senderId' => $data['senderId'],
					'date' => $data['date'],
					'text' => $data['text']
				);
				$arRecent['chat'.$data['recipientId']]['COUNTER'] = $data['counter'];
			}
		}

		if (!empty($arRecent))
		{
			sortByColumn(
				$arRecent,
				array(
					'COUNTER' => array(SORT_NUMERIC, SORT_DESC),
					'MESSAGE' => array(SORT_NUMERIC, SORT_DESC)
				),
				array(
					'COUNTER' => array(__CLASS__, 'GetRecentListSortCounter'),
					'MESSAGE' => array(__CLASS__, 'GetRecentListSortMessage'),
				),
				null, true
			);
		}
		return $arRecent;
	}

	public static function GetRecentListSortCounter($counter)
	{
		return !is_null($counter);
	}

	public static function GetRecentListSortMessage($recent)
	{
		return $recent['date'];
	}

	public static function IsExtranet($arUser)
	{
		$result = false;

		if (!IsModuleInstalled('intranet'))
			return false;

		if (array_key_exists('UF_DEPARTMENT', $arUser))
		{
			if ($arUser['UF_DEPARTMENT'] == "")
			{
				$result = true;
			}
			else if (is_array($arUser['UF_DEPARTMENT']) && empty($arUser['UF_DEPARTMENT']))
			{
				$result = true;
			}
			else if (is_array($arUser['UF_DEPARTMENT']) && count($arUser['UF_DEPARTMENT']) == 1 && $arUser['UF_DEPARTMENT'][0] == 0)
			{
				$result = true;
			}
		}
		else
		{
			$result = true;
		}

		return $result;
	}

	public static function GetUserPath($userId = false)
	{
		static $extranetSiteID = false;

		$userId = intval($userId);

		if (
			$extranetSiteID === false
			&& CModule::IncludeModule("extranet")
		)
		{
			$extranetSiteID = CExtranet::GetExtranetSiteID();
		}

		if (IsModuleInstalled('intranet'))
		{
			$strPathTemplate = COption::GetOptionString(
				"socialnetwork",
				"user_page",
				SITE_DIR.'company/personal/',
				(CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser() ? $extranetSiteID : SITE_ID)
			)."user/#user_id#/";
		}
		else
		{
			$strPathTemplate = COption::GetOptionString(
				"im",
				"path_to_user_profile",
				"/club/user/#user_id#/",
				SITE_ID
			);
		}

		if ($userId <= 0)
		{
			return $strPathTemplate;
		}
		else
		{
			return CComponentEngine::MakePathFromTemplate(
				$strPathTemplate,
				array("user_id" => $userId)
			);
		}
	}

	public static function GetUserNameTemplate($siteId = false, $langId = false, $getDefault = false)
	{
		if (!$langId && defined('LANGUAGE_ID'))
		{
			$langId = LANGUAGE_ID;
		}

		if (in_array($langId, Array('ru', 'kz', 'by', 'ua')))
		{
			$template = "#LAST_NAME# #NAME#";
		}
		else
		{
			$template = "#LAST_NAME#, #NAME#";
		}

		return $getDefault? $template: COption::GetOptionString("im", "user_name_template", $template, $siteId);
	}

	public static function GetUserColor($id, $gender)
	{
		$code = IM\Color::getCodeByNumber($id);
		if ($gender == 'M')
		{
			$replaceColor = IM\Color::getReplaceColors();
			if (isset($replaceColor[$code]))
			{
				$code = $replaceColor[$code];
			}
		}

		return IM\Color::getColor($code);
	}

	public static function PrepareUserId($id, $searchMark = '')
	{
		$result = self::PrepareUserIds(Array($id), $searchMark);

		return $result[$id];
	}

	public static function formatAbsentResult($userId)
	{
		if (!CModule::IncludeModule('intranet'))
		{
			return false;
		}

		if (method_exists('\Bitrix\Intranet\UserAbsence', 'isAbsentOnVacation'))
		{
			$result = \Bitrix\Intranet\UserAbsence::isAbsentOnVacation($userId, true);
		}
		else
		{
			$result = \Bitrix\Intranet\UserAbsence::isAbsent($userId, true);
		}

		if ($result)
		{
			$result = \Bitrix\Main\Type\DateTime::createFromTimestamp($result['DATE_TO_TS']+86400);
		}
		else
		{
			$result = false;
		}

		return $result;
	}

	public static function PrepareUserIds($userIds, $searchMark = '')
	{
		$portalId = Array();
		$networkId = Array();
		$structureId = Array();
		foreach ($userIds as $userId)
		{
			if (substr($userId, 0, 7) == 'network')
			{
				$networkId[$userId] = substr($userId, 7);
			}
			elseif (substr($userId, 0, 9) == 'structure')
			{
				$sid = intval(substr($userId, 9));
				if ($sid > 0)
				{
					$structureId[$userId] = $sid;
				}
			}
			else
			{
				$userId = intval($userId);
				if ($userId > 0)
				{
					$portalId[$userId] = $userId;
				}
			}
		}
		if (!empty($networkId) && CModule::IncludeModule('socialservices'))
		{
			$network = new \Bitrix\Socialservices\Network();
			$networkEnabled = $network->isEnabled();
			if ($networkEnabled)
			{
				$users = $network->addUsersById($networkId, $searchMark);
				if ($users)
				{
					foreach ($users as $networkId => $userId)
					{
						$portalId['network'.$networkId] = $userId;
					}
				}
			}
		}
		if (!empty($structureId) && CModule::IncludeModule('intranet'))
		{
			$orm = \Bitrix\Main\UserTable::getList(Array(
				'select' => Array('ID', 'UF_DEPARTMENT'),
				'filter' => Array('=ACTIVE' => 'Y', '=UF_DEPARTMENT' => array_values($structureId))
			));
			while ($row = $orm->fetch())
			{
				$portalId[$row['ID']] = $row['ID'];
			}
		}

		return $portalId;
	}
}
?>