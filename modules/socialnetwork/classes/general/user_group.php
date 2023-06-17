<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Text\Emoji;
use Bitrix\Socialnetwork\Helper\Path;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Item\UserToGroup;
use Bitrix\Socialnetwork\Integration;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Socialnetwork\Internals\Counter;
use Bitrix\Socialnetwork\Internals\EventService;

Loc::loadMessages(__FILE__);

class CAllSocNetUserToGroup
{
	protected static $roleCache = array();

	/***************************************/
	/********  DATA MODIFICATION  **********/
	/***************************************/
	public static function CheckFields($ACTION, &$relationFields, $id = 0): bool
	{
		global $APPLICATION, $DB, $arSocNetAllowedRolesForUserInGroup, $arSocNetAllowedInitiatedByType;

		if ($ACTION !== "ADD" && (int)$id <= 0)
		{
			$APPLICATION->ThrowException("System error 870164", "ERROR");
			return false;
		}

		if (
			(isset($relationFields['USER_ID']) || $ACTION === "ADD")
			&& (int)$relationFields["USER_ID"] <= 0
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_EMPTY_USER_ID'), 'EMPTY_USER_ID');
			return false;
		}

		if (isset($relationFields['USER_ID']))
		{
			$res = CUser::getById($relationFields["USER_ID"]);
			if (!$res->fetch())
			{
				$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_ERROR_NO_USER_ID'), 'ERROR_NO_USER_ID');
				return false;
			}
		}

		if (
			(isset($relationFields['GROUP_ID']) || $ACTION === "ADD")
			&& (int)$relationFields["GROUP_ID"] <= 0
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_EMPTY_GROUP_ID'), 'EMPTY_GROUP_ID');
			return false;
		}

		if (
			isset($relationFields["GROUP_ID"])
			&& !CSocNetGroup::getById($relationFields["GROUP_ID"])
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_ERROR_NO_GROUP_ID'), 'ERROR_NO_GROUP_ID');
			return false;
		}

		if (
			(isset($relationFields["ROLE"]) || $ACTION === "ADD")
			&& $relationFields["ROLE"] === ''
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_EMPTY_ROLE'), 'EMPTY_ROLE');
			return false;
		}

		if (
			isset($relationFields["ROLE"])
			&& !in_array($relationFields["ROLE"], $arSocNetAllowedRolesForUserInGroup, true)
		)
		{
			$APPLICATION->ThrowException(str_replace(
				"#ID#",
				$relationFields["ROLE"],
				Loc::getMessage('SONET_UG_ERROR_NO_ROLE')
			), 'ERROR_NO_ROLE');
			return false;
		}

		if (
			(isset($relationFields["INITIATED_BY_TYPE"]) || $ACTION === "ADD")
			&& (string)$relationFields["INITIATED_BY_TYPE"] === ''
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_EMPTY_INITIATED_BY_TYPE'), 'EMPTY_INITIATED_BY_TYPE');
			return false;
		}

		if (
			isset($relationFields["INITIATED_BY_TYPE"])
			&& !in_array($relationFields["INITIATED_BY_TYPE"], $arSocNetAllowedInitiatedByType, true)
		)
		{
			$APPLICATION->ThrowException(str_replace(
				'#ID#',
				$relationFields["INITIATED_BY_TYPE"],
				Loc::getMessage('SONET_UG_ERROR_NO_INITIATED_BY_TYPE')
			), 'ERROR_NO_INITIATED_BY_TYPE');
			return false;
		}

		if (
			(isset($relationFields['INITIATED_BY_USER_ID']) || $ACTION === "ADD")
			&& (int)$relationFields['INITIATED_BY_USER_ID'] <= 0
		)
		{
			$APPLICATION->ThrowException(
				Loc::getMessage('SONET_UG_EMPTY_INITIATED_BY_USER_ID'),
				'EMPTY_INITIATED_BY_USER_ID'
			);
			return false;
		}

		if (isset($relationFields["INITIATED_BY_USER_ID"]))
		{
			$res = CUser::GetByID($relationFields["INITIATED_BY_USER_ID"]);
			if (!$res->fetch())
			{
				$APPLICATION->ThrowException(
					Loc::getMessage('SONET_UG_ERROR_NO_INITIATED_BY_USER_ID'),
					'ERROR_NO_INITIATED_BY_USER_ID'
				);
				return false;
			}
		}

		if (
			isset($relationFields['DATE_CREATE'])
			&& !$DB->IsDate($relationFields['DATE_CREATE'], false, LANG, 'FULL')
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_EMPTY_DATE_CREATE'), 'EMPTY_DATE_CREATE');
			return false;
		}

		if (
			isset($relationFields['DATE_UPDATE'])
			&& !$DB->IsDate($relationFields['DATE_UPDATE'], false, LANG, 'FULL')
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_EMPTY_DATE_UPDATE'), 'EMPTY_DATE_UPDATE');
			return false;
		}

		if (
			isset($relationFields['DATE_LAST_VIEW'])
			&& !$DB->IsDate($relationFields['DATE_LAST_VIEW'], false, LANG, 'FULL')
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_EMPTY_DATE_LAST_VIEW'), 'EMPTY_DATE_LAST_VIEW');
			return false;
		}

		if (
			(
				isset($relationFields["SEND_MAIL"])
				&& $relationFields["SEND_MAIL"] !== "N"
			)
			|| !isset($relationFields['SEND_MAIL'])
		)
		{
			$relationFields["SEND_MAIL"] = "Y";
		}

		return true;
	}

	public static function Delete($id, $sendExclude = false)
	{
		global $APPLICATION, $DB, $USER, $CACHE_MANAGER;

		if (!CSocNetGroup::__ValidateID($id))
		{
			return false;
		}

		$id = (int)$id;

		$relationFields = CSocNetUserToGroup::GetByID($id);
		if (!$relationFields)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_NO_USER2GROUP'), 'ERROR_NO_USER2GROUP');
			return false;
		}

		$res = GetModuleEvents("socialnetwork", "OnBeforeSocNetUserToGroupDelete");
		while ($eventFields = $res->Fetch())
		{
			if (ExecuteModuleEventEx($eventFields, array($id)) === false)
			{
				return false;
			}
		}

		$events = GetModuleEvents("socialnetwork", "OnSocNetUserToGroupDelete");
		while ($eventFields = $events->Fetch())
		{
			ExecuteModuleEventEx($eventFields, [ $id, $relationFields ]);
		}

		EventService\Service::addEvent(EventService\EventDictionary::EVENT_WORKGROUP_USER_DELETE, [
			'GROUP_ID' => $relationFields['GROUP_ID'],
			'USER_ID' => $relationFields['USER_ID'],
		]);

		if (Loader::includeModule('im'))
		{
			CIMNotify::DeleteByTag("SOCNET|INVITE_GROUP|" . (int)$relationFields['USER_ID']  . "|" . (int)$id);
		}

		$bSuccess = $DB->Query("DELETE FROM b_sonet_user2group WHERE ID = ".$id."", true);

		CSocNetGroup::SetStat($relationFields["GROUP_ID"]);
		CSocNetSearch::OnUserRelationsChange($relationFields["USER_ID"]);

		$roleCacheKey = $relationFields['USER_ID'] . '_' . $relationFields['GROUP_ID'];
		if (isset(self::$roleCache[$roleCacheKey]))
		{
			unset(self::$roleCache[$roleCacheKey]);
		}

		if ($bSuccess && defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->ClearByTag("sonet_user2group_G".$relationFields["GROUP_ID"]);
			$CACHE_MANAGER->ClearByTag("sonet_user2group_U".$relationFields["USER_ID"]);
			$CACHE_MANAGER->ClearByTag("sonet_user2group");
		}

		if (
			$bSuccess
			&& in_array($relationFields['ROLE'], [
				UserToGroupTable::ROLE_MODERATOR,
				UserToGroupTable::ROLE_USER,
			], true)
		)
		{
			$chatNotificationResult = false;

			if (Loader::includeModule('im'))
			{
				$chatNotificationResult = UserToGroup::addInfoToChat([
					'group_id' => $relationFields["GROUP_ID"],
					'user_id' => $relationFields["USER_ID"],
					'action' => UserToGroup::CHAT_ACTION_OUT,
					'sendMessage' => $sendExclude,
				]);

				if ($sendExclude)
				{
					$arMessageFields = [
						"TO_USER_ID" => $relationFields["USER_ID"],
						"FROM_USER_ID" => 0,
						"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
						"NOTIFY_MODULE" => "socialnetwork",
						"NOTIFY_EVENT" => "invite_group",
						"NOTIFY_TAG" => "SOCNET|INVITE_GROUP|" . (int)$relationFields["USER_ID"] . "|" . (int)$relationFields["ID"],
						"NOTIFY_MESSAGE" => str_replace(
							"#NAME#",
							$relationFields["GROUP_NAME"],
							Loc::getMessage('SONET_UG_EXCLUDE_MESSAGE')
						),
					];

					CIMNotify::Add($arMessageFields);
				}
			}

			if (
				$sendExclude
				&& !$chatNotificationResult
			)
			{
				CSocNetUserToGroup::notifyImToModerators([
					"TYPE" => "exclude",
					"RELATION_ID" => $relationFields["ID"],
					"USER_ID" => $relationFields["USER_ID"],
					"GROUP_ID" => $relationFields["GROUP_ID"],
					"GROUP_NAME" => $relationFields["GROUP_NAME"],
					"EXCLUDE_USERS" => array($USER->getId()),
				]);
			}
		}

		Counter\CounterService::addEvent(Counter\Event\EventDictionary::EVENT_WORKGROUP_USER_DELETE, [
			'GROUP_ID' => (int)$relationFields['GROUP_ID'],
			'USER_ID' => (int)$relationFields['USER_ID'],
			'ROLE' => $relationFields['ROLE'],
			'INITIATED_BY_TYPE' => ($relationFields['INITIATED_BY_TYPE']),
			'RELATION_ID' => (int)$relationFields['ID'],
		]);

		return $bSuccess;
	}

	public static function DeleteNoDemand($userId): bool
	{
		global $DB;

		if (!CSocNetGroup::__ValidateID($userId))
		{
			return false;
		}

		$userId = (int)$userId;
		$bSuccess = True;

		$groupIdList = [];

		$res = CSocNetUserToGroup::GetList(array(), array("USER_ID" => $userId), false, false, array("GROUP_ID"));
		while ($relationFields = $res->fetch())
		{
			$groupIdList[] = (int)$relationFields["GROUP_ID"];
		}

		$DB->Query("DELETE FROM b_sonet_user2group WHERE USER_ID = ".$userId."", true);

		foreach ($groupIdList as $groupId)
		{
			CSocNetGroup::SetStat($groupId);
		}

		self::$roleCache = array();

		CSocNetUserToGroup::__SpeedFileDelete($userId);
		CSocNetSearch::OnUserRelationsChange($userId);

		return $bSuccess;
	}

	/***************************************/
	/**********  DATA SELECTION  ***********/
	/***************************************/
	public static function GetById($id)
	{
		if (!CSocNetGroup::__ValidateID($id))
		{
			return false;
		}

		$id = (int)$id;

		$res = CSocNetUserToGroup::GetList(
			[],
			[ 'ID' => $id ],
			false,
			false,
			[
				'ID',
				'ROLE', 'DATE_CREATE', 'DATE_UPDATE', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID', 'MESSAGE',
				'USER_ID',
				'GROUP_ID', 'GROUP_VISIBLE', 'GROUP_NAME',
			]
		);
		if ($relationFields = $res->fetch())
		{
			if (!empty($relationFields['GROUP_NAME']))
			{
				$relationFields['GROUP_NAME'] = Emoji::decode(htmlspecialcharsEx($relationFields['GROUP_NAME']));
			}

			return $relationFields;
		}

		return false;
	}

	/***************************************/
	/**********  COMMON METHODS  ***********/
	/***************************************/
	public static function GetUserRole($userId, $groupId, $extendedReturn = false)
	{
		$userId = (int)$userId;
		if ($userId <= 0)
		{
			return false;
		}

		// compatibility?
		if (isset($_REQUEST['arSocNetUserInRoleCache']))
		{
			self::$roleCache = [];
		}

		if (is_array($groupId))
		{
			$result = false;

			$groupIdListToGet = [];
			foreach ($groupId as $TmpGroupID)
			{
				$cacheKey = $userId . '_' . $TmpGroupID;
				if (!isset(self::$roleCache[$cacheKey]))
				{
					$groupIdListToGet[] = $TmpGroupID;
				}
			}

			if (count($groupIdListToGet) > 0)
			{
				$res = CSocNetUserToGroup::getList(
					[],
					[
						'USER_ID' => $userId,
						'GROUP_ID' => $groupIdListToGet,
					],
					false,
					false,
					[ 'GROUP_ID', 'ROLE', 'AUTO_MEMBER' ]
				);
				$arRolesFromDB = [];
				while ($relationFields = $res->fetch())
				{
					$arRolesFromDB[$relationFields["GROUP_ID"]] = [
						"ROLE" => $relationFields["ROLE"],
						"AUTO_MEMBER" => $relationFields["AUTO_MEMBER"],
					];
				}

				foreach ($groupIdListToGet as $TmpGroupID)
				{
					self::$roleCache[$userId."_".$TmpGroupID] = (
						array_key_exists($TmpGroupID, $arRolesFromDB)
							? [
								"ROLE" => $arRolesFromDB[$TmpGroupID]["ROLE"],
								"AUTO_MEMBER" => $arRolesFromDB[$TmpGroupID]["AUTO_MEMBER"],
							]
							: array(
								"ROLE" => false,
								"AUTO_MEMBER" => "N"
							)
					);
				}
			}

			foreach ($groupId as $currentGroupId)
			{
				if ($result === false)
				{
					$result = [];
				}
				$result[$currentGroupId] = (
					$extendedReturn
						? self::$roleCache[$userId . '_' . $currentGroupId]
						: self::$roleCache[$userId . '_' . $currentGroupId]['ROLE']
				);
			}

			return $result;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			return false;
		}

		if (!array_key_exists($userId."_".$groupId, self::$roleCache))
		{
			$res = CSocNetUserToGroup::GetList(
				array(),
				array("USER_ID" => $userId, "GROUP_ID" => $groupId),
				false,
				false,
				array("ROLE", "AUTO_MEMBER")
			);
			if ($relationFields = $res->Fetch())
			{
				self::$roleCache[$userId."_".$groupId] = array(
					"ROLE" => $relationFields["ROLE"],
					"AUTO_MEMBER" => $relationFields["AUTO_MEMBER"]
				);
			}
			else
			{
				self::$roleCache[$userId."_".$groupId] = array(
					"ROLE" => false,
					"AUTO_MEMBER" => false
				);
			}
		}

		return (
			$extendedReturn
				? self::$roleCache[$userId."_".$groupId]
				: self::$roleCache[$userId."_".$groupId]["ROLE"]
		);
	}

	/***************************************/
	/**********  SEND EVENTS  **************/
	/***************************************/
	public static function SendEvent($userGroupID, $mailTemplate = "SONET_INVITE_GROUP"): bool
	{
		$userGroupID = (int)$userGroupID;
		if ($userGroupID <= 0)
		{
			return false;
		}

		$dbRelation = CSocNetUserToGroup::GetList(
			[],
			[ 'ID' => $userGroupID ],
			false,
			false,
			[
				'ID',
				'ROLE', 'DATE_CREATE', 'MESSAGE', 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID',
				'USER_ID', 'USER_NAME', 'USER_LAST_NAME', 'USER_EMAIL', 'USER_LID',
				'GROUP_ID', 'GROUP_NAME',
			]
		);
		$arRelation = $dbRelation->Fetch();
		if (!$arRelation)
		{
			return false;
		}

		$arUserGroup = array();

		if (Loader::includeModule('extranet'))
		{
			$arUserGroup = CUser::GetUserGroup($arRelation["USER_ID"]);
		}

		$bExtranetInstalled = ModuleManager::isModuleInstalled('extranet');
		$siteId = false;

		$rsGroupSite = CSocNetGroup::GetSite($arRelation["GROUP_ID"]);
		while ($arGroupSite = $rsGroupSite->Fetch())
		{
			if ($bExtranetInstalled)
			{
				if (
					(
						CExtranet::IsExtranetSite($arGroupSite["LID"])
						&& in_array(CExtranet::GetExtranetUserGroupID(), $arUserGroup)
					)
					||
					(
						!CExtranet::IsExtranetSite($arGroupSite["LID"])
						&& !in_array(CExtranet::GetExtranetUserGroupID(), $arUserGroup)
					)
				)
				{
					$siteId = $arGroupSite["LID"];
					break;
				}
			}
			else
			{
				$siteId = $arGroupSite["LID"];
				break;
			}
		}

		if (empty($siteId))
		{
			return false;
		}

		$requestsPagePath = str_replace(
			"#USER_ID#",
			$arRelation["USER_ID"],
			Option::get(
				"socialnetwork",
				"user_request_page",
				(
					ModuleManager::isModuleInstalled('intranet')
						? "/company/personal/user/#USER_ID#/requests/"
						: "/club/user/#USER_ID#/requests/"
				),
				$siteId
			)
		);

		$arUserInitiatedForEmail = array("NAME"=>"", "LAST_NAME"=>"");

		if ((int)$arRelation["INITIATED_BY_USER_ID"] > 0):

			$dbUserInitiated = CUser::GetList(
				"id",
				"desc",
				array("ID" => $arRelation["INITIATED_BY_USER_ID"])
			);

			if ($arUserInitiated = $dbUserInitiated->Fetch())
			{
				$arUserInitiatedForEmail = [
					'NAME' => $arUserInitiated['NAME'],
					'LAST_NAME' => $arUserInitiated['LAST_NAME'],
				];
			}

		endif;

		$arFields = array(
			"RELATION_ID" => $userGroupID,
			"URL" => $requestsPagePath,
			"GROUP_ID" => $arRelation["GROUP_ID"],
			"USER_ID" => $arRelation["USER_ID"],
			"GROUP_NAME" => Emoji::decode($arRelation["GROUP_NAME"]),
			"USER_NAME" => $arRelation["USER_NAME"],
			"USER_LAST_NAME" => $arRelation["USER_LAST_NAME"],
			"USER_EMAIL" => $arRelation["USER_EMAIL"],
			"INITIATED_USER_NAME" => $arUserInitiatedForEmail["NAME"],
			"INITIATED_USER_LAST_NAME" => $arUserInitiatedForEmail["LAST_NAME"],
			"MESSAGE" => $arRelation["MESSAGE"]
		);

		CEvent::Send($mailTemplate, $siteId, $arFields, "N");

		return true;
	}

	/***************************************/
	/************  ACTIONS  ****************/
	/***************************************/
	public static function SendRequestToBeMember($userId, $groupId, $message, $requestConfirmUrl = "", $autoSubscribe = true): bool
	{
		global $APPLICATION;

		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UR_EMPTY_USERID'), 'ERROR_USERID');
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UR_EMPTY_GROUPID'), 'ERROR_GROUPID');
			return false;
		}

		$groupFields = CSocNetGroup::GetByID($groupId);
		if (
			!$groupFields
			|| !is_array($groupFields)
			|| $groupFields["ACTIVE"] !== "Y"
			/* || $arGroup["VISIBLE"] != "Y"*/)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UG_ERROR_NO_GROUP_ID'), 'ERROR_NO_GROUP');
			return false;
		}

		$relationFields = [
			"USER_ID" => $userId,
			"GROUP_ID" => $groupId,
			"ROLE" => UserToGroupTable::ROLE_REQUEST,
			"=DATE_CREATE" => CDatabase::CurrentTimeFunction(),
			"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
			"MESSAGE" => $message,
			"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
			"INITIATED_BY_USER_ID" => $userId
		];
		if ($groupFields["OPENED"] === "Y")
		{
			$relationFields["ROLE"] = UserToGroupTable::ROLE_USER;
		}

		$ID = CSocNetUserToGroup::Add($relationFields);
		if (!$ID)
		{
			$errorMessage = "";
			if ($e = $APPLICATION->GetException())
			{
				$errorMessage = $e->GetString();
			}
			if ($errorMessage === '')
			{
				$errorMessage = Loc::getMessage("SONET_UR_ERROR_CREATE_USER2GROUP");
			}

			$APPLICATION->ThrowException($errorMessage, "ERROR_CREATE_USER2GROUP");
			return false;
		}

		\Bitrix\Socialnetwork\Helper\UserToGroup\RequestPopup::unsetHideRequestPopup([
			'groupId' => $groupId,
			'userId' => $userId,
		]);

		if ($groupFields["OPENED"] === "Y")
		{
			if ($autoSubscribe)
			{
				CSocNetLogEvents::AutoSubscribe($userId, SONET_ENTITY_GROUP, $groupId);
			}

			if (ModuleManager::isModuleInstalled('im'))
			{
				$chatNotificationResult = UserToGroup::addInfoToChat([
					'group_id' => $groupId,
					'user_id' => $userId,
					'action' => UserToGroup::CHAT_ACTION_IN,
					'role' => $relationFields['ROLE'],
				]);

				if (!$chatNotificationResult)
				{
					CSocNetUserToGroup::notifyImToModerators([
						"TYPE" => "join",
						"RELATION_ID" => $ID,
						"USER_ID" => $userId,
						"GROUP_ID" => $groupId,
						"GROUP_NAME" => $groupFields["NAME"],
					]);
				}
			}
		}
		elseif (
			trim($requestConfirmUrl) !== ''
			&& Loader::includeModule('im')
		)
		{
			static $serverName;
			if (!$serverName)
			{
				$dbSite = CSite::GetByID(SITE_ID);
				$arSite = $dbSite->Fetch();
				$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
				if ($serverName === '')
				{
					if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME !== '')
					{
						$serverName = SITE_SERVER_NAME;
					}
					else
					{
						$serverName = Option::get("main", "server_name");
					}

					if ($serverName === '')
					{
						$serverName = $_SERVER["SERVER_NAME"];
					}
				}
				$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".$serverName;
			}

			// send sonet system messages to owner and (may be) moderators to accept or refuse request
			$FilterRole = (
				$groupFields['INITIATE_PERMS'] === UserToGroupTable::ROLE_OWNER
					? UserToGroupTable::ROLE_OWNER
					: UserToGroupTable::ROLE_MODERATOR
			);

			$res = CSocNetUserToGroup::GetList(
				array("USER_ID" => "ASC"),
				array(
					"GROUP_ID" => $groupId,
					"<=ROLE" => $FilterRole,
					"USER_ACTIVE" => "Y"
				),
				false,
				false,
				array("ID", "USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_EMAIL")
			);
			if ($res)
			{
				$groupSiteId = CSocNetGroup::GetDefaultSiteId($groupId, $groupFields["SITE_ID"]);
				$workgroupsPage = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID);
				$groupUrlTemplate = Path::get('group_path_template');
				$groupUrlTemplate = "#GROUPS_PATH#" . mb_substr($groupUrlTemplate, mb_strlen($workgroupsPage));
				$groupUrl = str_replace(array("#group_id#", "#GROUP_ID#"), $groupId, $groupUrlTemplate);

				while ($recipientRelationFields = $res->fetch())
				{
					$arTmp = CSocNetLogTools::ProcessPath(
						[
							"GROUP_URL" => $groupUrl,
						],
						$recipientRelationFields["USER_ID"],
						$groupSiteId
					);
					$groupUrl = $arTmp["URLS"]["GROUP_URL"];
					$domainName = (
						mb_strpos($groupUrl, "http://") === 0
						|| mb_strpos($groupUrl, "https://") === 0
							? ""
							: (
								isset($arTmp["DOMAIN"])
								&& !empty($arTmp["DOMAIN"])
									? "//".$arTmp["DOMAIN"]
									: ""
							)
					);

					$messageFields = array(
						"TO_USER_ID" => $recipientRelationFields["USER_ID"],
						"FROM_USER_ID" => $userId,
						"NOTIFY_TYPE" => IM_NOTIFY_CONFIRM,
						"NOTIFY_MODULE" => "socialnetwork",
						"NOTIFY_EVENT" => "invite_group_btn",
						"NOTIFY_TAG" => "SOCNET|REQUEST_GROUP|" . $userId . "|" . $groupId . "|" . $ID . "|" . $recipientRelationFields["USER_ID"],
						"NOTIFY_SUB_TAG" => "SOCNET|REQUEST_GROUP|" . $userId."|" . $groupId . "|" . $ID,
						"NOTIFY_TITLE" => str_replace(
							"#GROUP_NAME#",
							truncateText($groupFields["NAME"], 150),
							Loc::getMessage('SONET_UG_REQUEST_CONFIRM_TEXT_EMPTY')
						),
						"NOTIFY_MESSAGE" => str_replace(
							[
								"#TEXT#",
								"#GROUP_NAME#",
							],
							[
								$message,
								"<a href=\"".$domainName.$groupUrl."\" class=\"bx-notifier-item-action\">".$groupFields["NAME"]."</a>"
							],
							(empty($message)
								? Loc::getMessage('SONET_UG_REQUEST_CONFIRM_TEXT_EMPTY')
								: Loc::getMessage('SONET_UG_REQUEST_CONFIRM_TEXT')
							)
						),
						"NOTIFY_BUTTONS" => [
							[
								"TITLE" => Loc::getMessage('SONET_UG_REQUEST_CONFIRM'),
								"VALUE" => "Y",
								"TYPE" => "accept",
							],
							[
								"TITLE" => Loc::getMessage('SONET_UG_REQUEST_REJECT'),
								"VALUE" => "N",
								"TYPE" => "cancel",
							],
						],
					);

					$groupUrl = $serverName.str_replace("#group_id#", $groupId, Path::get('group_path_template'));

					$messageFields["NOTIFY_MESSAGE_OUT"] = $messageFields["NOTIFY_MESSAGE"];
					$messageFields["NOTIFY_MESSAGE_OUT"] .= "\n\n".GetMessage("SONET_UG_GROUP_LINK").$groupUrl;
					$messageFields['NOTIFY_MESSAGE_OUT'] .= "\n\n".GetMessage("SONET_UG_REQUEST_CONFIRM_REJECT").": ".$requestConfirmUrl;

					CIMNotify::Add($messageFields);
				}
			}
		}

		return true;
	}

	public static function SendRequestToJoinGroup($senderUserId, $userId, $groupId, $message, $sendMail = true): bool
	{
		global $APPLICATION, $USER;

		$senderUserId = (int)$senderUserId;
		if ($senderUserId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UR_EMPTY_USERID'), "ERROR_SENDERID");
			return false;
		}

		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UR_EMPTY_USERID"), "ERROR_USERID");
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UR_EMPTY_GROUPID"), "ERROR_GROUPID");
			return false;
		}

		$groupFields = CSocNetGroup::getById($groupId);
		if (!$groupFields || !is_array($groupFields))
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UG_ERROR_NO_GROUP_ID"), "ERROR_NO_GROUP");
			return false;
		}

		$arGroupSites = array();
		$rsGroupSite = CSocNetGroup::GetSite($groupId);
		while ($arGroupSite = $rsGroupSite->Fetch())
		{
			$arGroupSites[] = $arGroupSite["LID"];
		}

		$userRole = CSocNetUserToGroup::GetUserRole($senderUserId, $groupId);
		$userIsMember = ($userRole && in_array($userRole, UserToGroupTable::getRolesMember(), true));
		$canInitiate = (
			$USER->IsAdmin()
			|| CSocNetUser::IsCurrentUserModuleAdmin($arGroupSites)
			|| (
				$userRole
				&& (
					(
						$groupFields["INITIATE_PERMS"] === UserToGroupTable::ROLE_OWNER
						&& $senderUserId === (int)$groupFields["OWNER_ID"]
					)
					|| (
						$groupFields["INITIATE_PERMS"] === UserToGroupTable::ROLE_MODERATOR
						&& in_array($userRole, [ UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR ], true)
					)
					|| (
						$groupFields["INITIATE_PERMS"] === UserToGroupTable::ROLE_USER
						&& $userIsMember
					)
				)
			)
		);

		if (!$canInitiate)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UG_ERROR_NO_PERMS"), "ERROR_NO_PERMS");
			return false;
		}

		$relationFields = array(
			"USER_ID" => $userId,
			"GROUP_ID" => $groupId,
			"ROLE" => UserToGroupTable::ROLE_REQUEST,
			"=DATE_CREATE" => CDatabase::CurrentTimeFunction(),
			"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
			"MESSAGE" => str_replace(
				[ "#TEXT#", "#GROUP_NAME#" ],
				[ $message, $groupFields["NAME"] ],
				(
					empty($message)
						? Loc::getMessage("SONET_UG_INVITE_CONFIRM_TEXT_EMPTY")
						: Loc::getMessage("SONET_UG_INVITE_CONFIRM_TEXT")
				)
			),
			"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
			"INITIATED_BY_USER_ID" => $senderUserId,
			"SEND_MAIL" => ($sendMail ? "Y" : "N")
		);
		$relationId = CSocNetUserToGroup::Add($relationFields);
		if (!$relationId)
		{
			$errorMessage = "";
			if ($e = $APPLICATION->GetException())
			{
				$errorMessage = $e->GetString();
			}

			if ($errorMessage === '')
			{
				$errorMessage = Loc::getMessage('SONET_UR_ERROR_CREATE_USER2GROUP');
			}

			$APPLICATION->ThrowException($errorMessage, "ERROR_CREATE_USER2GROUP");
			return false;
		}

		$userIsConfirmed = true;
		$rsInvitedUser = CUser::GetByID($userId);
		$arInvitedUser = $rsInvitedUser->Fetch();

		if (
			(
				!is_array($arInvitedUser["UF_DEPARTMENT"])
				|| (int)$arInvitedUser["UF_DEPARTMENT"][0] <= 0
			) // extranet
			&& ($arInvitedUser["LAST_LOGIN"] <= 0)
			&& $arInvitedUser["LAST_ACTIVITY_DATE"] == ''
		)
		{
			$userIsConfirmed = false;
		}

		if (
			$userIsConfirmed
			&& Loader::includeModule('im')
		)
		{
			$messageFields = [
				"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
				"TO_USER_ID" => (int)$relationFields['USER_ID'],
				"FROM_USER_ID" => (int)$relationFields['INITIATED_BY_USER_ID'],
				"NOTIFY_TYPE" => IM_NOTIFY_CONFIRM,
				"NOTIFY_MODULE" => "socialnetwork",
				"NOTIFY_EVENT" => "invite_group_btn",
				"NOTIFY_TAG" => "SOCNET|INVITE_GROUP|" . $userId . "|" . $relationId,
				"NOTIFY_TITLE" => str_replace(
					"#GROUP_NAME#",
					truncateText($groupFields["NAME"], 150),
					GetMessage("SONET_UG_INVITE_CONFIRM_TEXT_EMPTY")
				),
				"NOTIFY_MESSAGE" => str_replace(
					[ "#TEXT#", "#GROUP_NAME#" ],
					[ $message, $groupFields["NAME"] ],
					(
						empty($message)
							? Loc::getMessage("SONET_UG_INVITE_CONFIRM_TEXT_EMPTY")
							: Loc::getMessage("SONET_UG_INVITE_CONFIRM_TEXT")
					)
				),
				"NOTIFY_BUTTONS" => [
					[
						'TITLE' => Loc::getMessage('SONET_UG_INVITE_CONFIRM'),
						'VALUE' => 'Y',
						'TYPE' => 'accept',
					],
					[
						'TITLE' => Loc::getMessage('SONET_UG_INVITE_REJECT'),
						'VALUE' => 'N',
						'TYPE' => 'cancel',
					],
				],
			];

			$siteId = (
				(
					!is_array($arInvitedUser["UF_DEPARTMENT"])
					|| (int)$arInvitedUser["UF_DEPARTMENT"][0] <= 0
				)
				&& Loader::includeModule('extranet')
					? CExtranet::GetExtranetSiteID()
					: SITE_ID
			);

			$dbSite = CSite::GetByID($siteId);
			$arSite = $dbSite->Fetch();
			$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);

			if ($serverName === '')
			{
				$serverName = (
					defined("SITE_SERVER_NAME")
					&& SITE_SERVER_NAME !== ''
						? SITE_SERVER_NAME
						: Option::get('main', 'server_name')
				);
			}

			if ($serverName === '')
			{
				$serverName = $_SERVER["SERVER_NAME"];
			}

			$serverName = (CMain::IsHTTPS() ? "https" : "http")."://".$serverName;

			$requestUrl = Option::get(
				"socialnetwork",
				"user_request_page",
				(
					ModuleManager::isModuleInstalled('intranet')
						? "/company/personal/user/#USER_ID#/requests/"
						: "/club/user/#USER_ID#/requests/"
				),
				$siteId
			);

			$requestUrl = $serverName.str_replace(array("#USER_ID#", "#user_id#"), $userId, $requestUrl);
			$groupUrl = $serverName.str_replace("#group_id#", $groupId, Path::get('group_path_template', $siteId));

			$messageFields['NOTIFY_MESSAGE_OUT'] = $messageFields['NOTIFY_MESSAGE'];
			$messageFields['NOTIFY_MESSAGE_OUT'] .= "\n\n" . Loc::getMessage('SONET_UG_GROUP_LINK') . $groupUrl;
			$messageFields['NOTIFY_MESSAGE_OUT'] .= "\n\n" . Loc::getMessage('SONET_UG_INVITE_CONFIRM') . ": " . $requestUrl . '?INVITE_GROUP=' . $relationId . '&CONFIRM=Y';
			$messageFields['NOTIFY_MESSAGE_OUT'] .= "\n\n" . Loc::getMessage('SONET_UG_INVITE_REJECT') . ": " . $requestUrl . '?INVITE_GROUP=' . $relationId . '&CONFIRM=N';

			CIMNotify::Add($messageFields);
		}

		$events = GetModuleEvents("socialnetwork", "OnSocNetSendRequestToJoinGroup");
		while ($arEvent = $events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, [ $relationId, $relationFields ]);
		}

		CSocNetUserToGroup::__SpeedFileCreate($userId);

		return true;
	}

	public static function ConfirmRequestToBeMember($userId, $groupId, $relationIdList, $autoSubscribe = true): bool // request from a user confirmed by a moderator
	{
		global $APPLICATION, $USER;

		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UR_EMPTY_USERID"), "ERROR_USERID");
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UR_EMPTY_GROUPID"), "ERROR_GROUPID");
			return false;
		}

		if (!is_array($relationIdList))
		{
			return true;
		}

		$arGroup = CSocNetGroup::getById($groupId);
		if (!$arGroup || !is_array($arGroup))
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UG_ERROR_NO_GROUP_ID"), "ERROR_NO_GROUP");
			return false;
		}

		$groupSiteIdList = array();
		$res = CSocNetGroup::GetSite($groupId);
		while ($groupSiteFields = $res->fetch())
		{
			$groupSiteIdList[] = $groupSiteFields["LID"];
		}

		$userRole = CSocNetUserToGroup::GetUserRole($userId, $groupId);
		$userIsMember = (
			$userRole
			&& in_array($userRole, UserToGroupTable::getRolesMember(), true)
		);
		$canInitiate = (
			$USER->IsAdmin()
			|| CSocNetUser::IsCurrentUserModuleAdmin($groupSiteIdList)
			|| (
				$userRole
				&& (
					(
						$arGroup["INITIATE_PERMS"] === UserToGroupTable::ROLE_OWNER
						&& $userId === (int)$arGroup["OWNER_ID"]
					)
					|| (
						$arGroup["INITIATE_PERMS"] === UserToGroupTable::ROLE_MODERATOR
						&& in_array($userRole, [ UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR ], true)
					)
					|| (
						$arGroup["INITIATE_PERMS"] === UserToGroupTable::ROLE_USER
						&& $userIsMember
					)
				)
			)
		);

		if (!$canInitiate)
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UG_ERROR_NO_PERMS"), "ERROR_NO_PERMS");
			return false;
		}

		$bSuccess = true;
		$arSuccessRelations = array();
		$chatNotificationResult = false;

		foreach ($relationIdList as $relationId)
		{
			$relationId = (int)$relationId;
			if ($relationId <= 0)
			{
				continue;
			}

			$relationFields = CSocNetUserToGroup::GetByID($relationId);
			if (!$relationFields)
			{
				continue;
			}

			if (
				(int)$relationFields["GROUP_ID"] !== $groupId
				|| $relationFields["INITIATED_BY_TYPE"] !== SONET_INITIATED_BY_USER
				|| $relationFields["ROLE"] !== UserToGroupTable::ROLE_REQUEST
			)
			{
				continue;
			}

			$arFields = array(
				"ROLE" => UserToGroupTable::ROLE_USER,
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
			);
			if (CSocNetUserToGroup::Update($relationFields["ID"], $arFields))
			{
				$arSuccessRelations[] = $relationFields;

				if ($autoSubscribe)
				{
					CSocNetLogEvents::AutoSubscribe($relationFields["USER_ID"], SONET_ENTITY_GROUP, $groupId);
				}

				$chatNotificationResult = UserToGroup::addInfoToChat(array(
					'group_id' => $groupId,
					'user_id' => $relationFields["USER_ID"],
					'action' => UserToGroup::CHAT_ACTION_IN,
					'role' => $arFields['ROLE']
				));

				if (
					!$chatNotificationResult
					&& Loader::includeModule('im')
				)
				{
					$groupSiteId = CSocNetGroup::GetDefaultSiteId($groupId, $arGroup["SITE_ID"]);
					$workgroupsPage = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID);
					$groupUrlTemplate = Path::get('group_path_template');
					$groupUrlTemplate = "#GROUPS_PATH#".mb_substr($groupUrlTemplate, mb_strlen($workgroupsPage));
					$arTmp = CSocNetLogTools::ProcessPath(
						array(
							"GROUP_URL" => str_replace(array("#group_id#", "#GROUP_ID#"), $groupId, $groupUrlTemplate)
						),
						$relationFields["USER_ID"],
						$groupSiteId
					);
					$groupUrl = $arTmp["URLS"]["GROUP_URL"];

					$serverName = (
						mb_strpos($groupUrl, "http://") === 0
						|| mb_strpos($groupUrl, "https://") === 0
							? ""
							: $arTmp["SERVER_NAME"]
					);
					$domainName = (
						mb_strpos($groupUrl, "http://") === 0
						|| mb_strpos($groupUrl, "https://") === 0
							? ""
							: (
								isset($arTmp["DOMAIN"])
								&& !empty($arTmp["DOMAIN"])
									? "//".$arTmp["DOMAIN"]
									: ""
							)
					);

					$arMessageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $relationFields["USER_ID"],
						"FROM_USER_ID" => $userId,
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "socialnetwork",
						"NOTIFY_EVENT" => "invite_group",
						"NOTIFY_TAG" => "SOCNET|INVITE_GROUP|" . (int)$relationFields["USER_ID"] . "|" . (int)$relationFields["ID"],
						"NOTIFY_MESSAGE" => str_replace(
							"#NAME#",
							"<a href=\"".$domainName.$groupUrl."\" class=\"bx-notifier-item-action\">".$arGroup["NAME"]."</a>",
							GetMessage("SONET_UG_CONFIRM_MEMBER_MESSAGE_G")
						),
						"NOTIFY_MESSAGE_OUT" => str_replace(
							"#NAME#",
							$arGroup["NAME"],
							GetMessage("SONET_UG_CONFIRM_MEMBER_MESSAGE_G")." (".$serverName.$groupUrl.")"
						)
					);

					CIMNotify::DeleteBySubTag("SOCNET|REQUEST_GROUP|".$relationFields["USER_ID"]."|".$relationFields["GROUP_ID"]."|".$relationFields["ID"]);
					CIMNotify::Add($arMessageFields);
				}

				if (Loader::includeModule('pull'))
				{
					\Bitrix\Pull\Event::add((int)$relationFields['USER_ID'], [
						'module_id' => 'socialnetwork',
						'command' => 'workgroup_request_accepted',
						'params' => [
							'initiatedByType' => $relationFields['INITIATED_BY_TYPE'],
						],
					]);
				}

				\Bitrix\Socialnetwork\Helper\UserToGroup\RequestPopup::unsetHideRequestPopup([
					'groupId' => (int)$relationFields['GROUP_ID'],
					'userId' => (int)$relationFields['USER_ID'],
				]);
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}

				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UR_ERROR_CREATE_USER2GROUP');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_CONFIRM_MEMBER");
				$bSuccess = false;
			}
		}

		if (
			!empty($arSuccessRelations)
			&& !$chatNotificationResult
		)
		{
			foreach ($arSuccessRelations as $arRel)
			{
				CSocNetUserToGroup::notifyImToModerators(array(
					"TYPE" => "join",
					"RELATION_ID" => $arRel["ID"],
					"USER_ID" => $arRel["USER_ID"],
					"GROUP_ID" => $arRel["GROUP_ID"],
					"GROUP_NAME" => $arRel["GROUP_NAME"],
					"EXCLUDE_USERS" => array($USER->GetID())
				));
			}
		}

		return $bSuccess;
	}

	public static function RejectRequestToBeMember($userId, $groupId, $relationIdList): bool
	{
		global $APPLICATION, $USER;

		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UR_EMPTY_USERID'), "ERROR_USERID");
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage('SONET_UR_EMPTY_GROUPID'), "ERROR_GROUPID");
			return false;
		}

		if (!is_array($relationIdList))
		{
			return true;
		}

		$groupFields = CSocNetGroup::GetByID($groupId);
		if (!$groupFields || !is_array($groupFields))
		{
			$APPLICATION->ThrowException(Loc::getMessage("SONET_UG_ERROR_NO_GROUP_ID"), "ERROR_NO_GROUP");
			return false;
		}

		$groupSiteIdList = [];
		$rsGroupSite = CSocNetGroup::GetSite($groupId);
		while ($groupSiteFields = $rsGroupSite->fetch())
		{
			$groupSiteIdList[] = $groupSiteFields["LID"];
		}

		$userRole = CSocNetUserToGroup::GetUserRole($userId, $groupId);
		$userIsMember = ($userRole && in_array($userRole, UserToGroupTable::getRolesMember(), true));
		$bCanInitiate = (
			$USER->IsAdmin()
			|| CSocNetUser::IsCurrentUserModuleAdmin($groupSiteIdList)
			|| (
				$userRole
				&& (
					(
						$groupFields["INITIATE_PERMS"] === UserToGroupTable::ROLE_OWNER
						&& $userId === (int)$groupFields["OWNER_ID"]
					)
					|| (
						$groupFields["INITIATE_PERMS"] === UserToGroupTable::ROLE_MODERATOR
						&& in_array($userRole, [ UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR ], true)
					)
					|| (
						$groupFields["INITIATE_PERMS"] === UserToGroupTable::ROLE_USER
						&& $userIsMember
					)
				)
			)
		);

		if (!$bCanInitiate)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_ERROR_NO_PERMS"), "ERROR_NO_PERMS");
			return false;
		}

		$bSuccess = true;
		foreach ($relationIdList as $relationId)
		{
			$relationId = (int)$relationId;
			if ($relationId <= 0)
			{
				continue;
			}

			$arRelation = CSocNetUserToGroup::GetByID($relationId);
			if (!$arRelation)
			{
				continue;
			}

			if (
				(int)$arRelation["GROUP_ID"] !== $groupId
				|| $arRelation["INITIATED_BY_TYPE"] !== SONET_INITIATED_BY_USER
				|| $arRelation["ROLE"] !== UserToGroupTable::ROLE_REQUEST
			)
			{
				continue;
			}

			if (CSocNetUserToGroup::Delete($arRelation["ID"]))
			{
				$arMessageFields = array(
					"FROM_USER_ID" => $userId,
					"TO_USER_ID" => $arRelation["USER_ID"],
					"MESSAGE" => str_replace(
						'#NAME#',
						$groupFields['NAME'],
						Loc::getMessage('SONET_UG_REJECT_MEMBER_MESSAGE_G')
					),
					"=DATE_CREATE" => CDatabase::CurrentTimeFunction(),
					"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM
				);
				CSocNetMessages::Add($arMessageFields);

				\Bitrix\Socialnetwork\Helper\UserToGroup\RequestPopup::unsetHideRequestPopup([
					'groupId' => (int)$groupId,
					'userId' => (int)$arRelation['USER_ID'],
				]);
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage("SONET_UR_ERROR_CREATE_USER2GROUP");
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_CONFIRM_MEMBER");
				$bSuccess = false;
			}
		}

		return $bSuccess;
	}

	public static function UserConfirmRequestToBeMember($targetUserID, $relationID, $bAutoSubscribe = true): bool // request from group confirmed by a user
	{
		global $APPLICATION;

		$targetUserID = (int)$targetUserID;
		if ($targetUserID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_USERID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$relationID = (int)$relationID;
		if ($relationID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_RELATIONID"), "ERROR_RELATION_ID");
			return false;
		}

		$dbResult = CSocNetUserToGroup::GetList(
			array(),
			array(
				"ID" => $relationID,
				"USER_ID" => $targetUserID,
				"ROLE" => UserToGroupTable::ROLE_REQUEST,
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP
			),
			false,
			false,
			array("ID", "USER_ID", "INITIATED_BY_USER_ID", "GROUP_ID", "GROUP_VISIBLE", "GROUP_SITE_ID", "GROUP_NAME")
		);

		if ($arResult = $dbResult->Fetch())
		{
			if (!empty($arResult['GROUP_NAME']))
			{
				$arResult['GROUP_NAME'] = Emoji::decode($arResult['GROUP_NAME']);
			}

			$arFields = array(
				"ROLE" => UserToGroupTable::ROLE_USER,
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
			);
			if (CSocNetUserToGroup::Update($arResult["ID"], $arFields))
			{
				$events = GetModuleEvents("socialnetwork", "OnSocNetUserConfirmRequestToBeMember");
				while ($arEvent = $events->Fetch())
				{
					ExecuteModuleEventEx($arEvent, array($arResult["ID"], $arResult));
				}

				if ($bAutoSubscribe)
				{
					CSocNetLogEvents::AutoSubscribe($targetUserID, SONET_ENTITY_GROUP, $arResult["GROUP_ID"]);
				}

				if (Loader::includeModule('im'))
				{
					$groupSiteId = CSocNetGroup::GetDefaultSiteId($arResult["GROUP_ID"], $arResult["GROUP_SITE_ID"]);

					CIMNotify::DeleteByTag("SOCNET|INVITE_GROUP|" . (int)$targetUserID . "|" . (int)$relationID);

					$workgroupsPage = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", $groupSiteId);
					$groupUrlTemplate = Path::get('group_path_template', $groupSiteId);
					$groupUrlTemplate = "#GROUPS_PATH#".mb_substr($groupUrlTemplate, mb_strlen($workgroupsPage));
					$groupUrl = str_replace(array("#group_id#", "#GROUP_ID#"), $arResult["GROUP_ID"], $groupUrlTemplate);

					$arTmp = CSocNetLogTools::ProcessPath(
						array(
							"GROUP_URL" => $groupUrl
						),
						$arResult["INITIATED_BY_USER_ID"],
						$groupSiteId
					);
					$url = $arTmp["URLS"]["GROUP_URL"];
					$serverName = (
					mb_strpos($url, "http://") === 0
						|| mb_strpos($url, "https://") === 0
							? ""
							: $arTmp["SERVER_NAME"]
					);
					$domainName = (
					mb_strpos($url, "http://") === 0
						|| mb_strpos($url, "https://") === 0
							? ""
							: (
								isset($arTmp["DOMAIN"])
								&& !empty($arTmp["DOMAIN"])
									? "//".$arTmp["DOMAIN"]
									: ""
							)
					);

					$arMessageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $arResult['USER_ID'],
						"NOTIFY_TYPE" => IM_NOTIFY_SYSTEM,
						"NOTIFY_MODULE" => "socialnetwork",
						"NOTIFY_EVENT" => "invite_group",
						"NOTIFY_TAG" => "SOCNET|INVITE_GROUP|" . (int)$arResult['USER_ID'] . "|". $relationID,
						"NOTIFY_MESSAGE" => str_replace(
							"#NAME#",
							"<a href=\"".$domainName.$url."\" class=\"bx-notifier-item-action\">".$arResult["GROUP_NAME"]."</a>",
							GetMessage("SONET_UG_CONFIRM_MEMBER_MESSAGE_G")
						),
						"NOTIFY_MESSAGE_OUT" => str_replace(
							"#NAME#",
							$arResult["GROUP_NAME"],
							GetMessage("SONET_UG_CONFIRM_MEMBER_MESSAGE_G")." (".$serverName.$url.")"
						)
					);
					CIMNotify::Add($arMessageFields);

					$chatNotificationResult = UserToGroup::addInfoToChat(array(
						'group_id' => $arResult["GROUP_ID"],
						'user_id' => $arResult["USER_ID"],
						'action' => UserToGroup::CHAT_ACTION_IN,
						'role' => $arFields['ROLE']
					));

					if (!$chatNotificationResult)
					{
						$arMessageFields = array(
							"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
							"TO_USER_ID" => $arResult["INITIATED_BY_USER_ID"],
							"FROM_USER_ID" => $arResult['USER_ID'],
							"NOTIFY_TYPE" => IM_NOTIFY_FROM,
							"NOTIFY_MODULE" => "socialnetwork",
							"NOTIFY_EVENT" => "invite_group",
							"NOTIFY_TAG" => "SOCNET|INVITE_GROUP_SUCCESS|" . (int)$arResult["GROUP_ID"],
							"NOTIFY_MESSAGE" => str_replace(
								"#NAME#",
								"<a href=\"".$domainName.$url."\" class=\"bx-notifier-item-action\">".$arResult["GROUP_NAME"]."</a>",
								GetMessage("SONET_UG_CONFIRM_MEMBER_MESSAGE")
							),
							"NOTIFY_MESSAGE_OUT" => str_replace("#NAME#", $arResult["GROUP_NAME"], GetMessage("SONET_UG_CONFIRM_MEMBER_MESSAGE")." (".$serverName.$url.")"),
						);
						CIMNotify::Add($arMessageFields);

						CSocNetUserToGroup::NotifyImToModerators(array(
							"TYPE" => "join",
							"RELATION_ID" => $arResult["ID"],
							"USER_ID" => $arResult["USER_ID"],
							"GROUP_ID" => $arResult["GROUP_ID"],
							"GROUP_NAME" => htmlspecialcharsbx($arResult["GROUP_NAME"]),
							"EXCLUDE_USERS" => array($arResult["INITIATED_BY_USER_ID"])
						));
					}
				}
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UR_ERROR_CREATE_USER2GROUP');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_CREATE_RELATION");
				return false;
			}
		}
		else
		{
			$APPLICATION->ThrowException(GetMessage("SONET_NO_USER2GROUP"), "ERROR_NO_GROUP_REQUEST");
			return false;
		}

		CSocNetUserToGroup::__SpeedFileCheckMessages($targetUserID);

		return true;
	}

	public static function UserRejectRequestToBeMember($targetUserID, $relationID): bool
	{
		global $APPLICATION;

		$targetUserID = (int)$targetUserID;
		if ($targetUserID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_USERID"), "ERROR_SENDER_USER_ID");
			return false;
		}

		$relationID = (int)$relationID;
		if ($relationID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_RELATIONID"), "ERROR_RELATION_ID");
			return false;
		}

		$dbResult = CSocNetUserToGroup::GetList(
			array(),
			array(
				"ID" => $relationID,
				"USER_ID" => $targetUserID,
				"ROLE" => UserToGroupTable::ROLE_REQUEST,
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP
			),
			false,
			false,
			array("ID", "USER_ID", "GROUP_ID", "GROUP_SITE_ID", "INITIATED_BY_USER_ID", "GROUP_NAME")
		);

		if ($arResult = $dbResult->Fetch())
		{
			if (!empty($arResult['GROUP_NAME']))
			{
				$arResult['GROUP_NAME'] = Emoji::decode($arResult['GROUP_NAME']);
			}

			if (CSocNetUserToGroup::Delete($arResult["ID"]))
			{
				$events = GetModuleEvents("socialnetwork", "OnSocNetUserRejectRequestToBeMember");
				while ($arEvent = $events->Fetch())
				{
					ExecuteModuleEventEx($arEvent, array($arResult["ID"], $arResult));
				}

				if (Loader::includeModule('im'))
				{
					$groupSiteId = CSocNetGroup::GetDefaultSiteId($arResult["GROUP_ID"], $arResult["GROUP_SITE_ID"]);
					$groupUrl = str_replace(
						[ "#group_id#", "#GROUP_ID#" ],
						$arResult["GROUP_ID"],
						Path::get('group_path_template', $groupSiteId)
					);
					$arTmp = CSocNetLogTools::ProcessPath(
						array(
							"GROUP_URL" => $groupUrl
						),
						$arResult["INITIATED_BY_USER_ID"],
						$groupSiteId
					);
					$url = $arTmp["URLS"]["GROUP_URL"];
					$serverName = (
					mb_strpos($url, "http://") === 0
						|| mb_strpos($url, "https://") === 0
							? ""
							: $arTmp["SERVER_NAME"]
					);
					$domainName = (
					mb_strpos($url, "http://") === 0
						|| mb_strpos($url, "https://") === 0
							? ""
							: (
								isset($arTmp["DOMAIN"])
								&& !empty($arTmp["DOMAIN"])
									? "//".$arTmp["DOMAIN"]
									: ""
							)
					);

					$arMessageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $arResult["INITIATED_BY_USER_ID"],
						"FROM_USER_ID" => $arResult['USER_ID'],
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "socialnetwork",
						"NOTIFY_EVENT" => "invite_group",
						"NOTIFY_TAG" => "SOCNET|INVITE_GROUP_REJECT|" . (int)$arResult["GROUP_ID"],
						"NOTIFY_MESSAGE" => str_replace(
							"#NAME#",
							"<a href=\"".$domainName.$url."\" class=\"bx-notifier-item-action\">".$arResult["GROUP_NAME"]."</a>",
							GetMessage("SONET_UG_REJECT_MEMBER_MESSAGE")
						),
						"NOTIFY_MESSAGE_OUT" => str_replace(
							"#NAME#",
							$arResult["GROUP_NAME"],
							GetMessage("SONET_UG_REJECT_MEMBER_MESSAGE")." (".$serverName.$url.")"
						)
					);
					CIMNotify::Add($arMessageFields);
				}
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UR_ERROR_CREATE_USER2GROUP');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_DELETE_RELATION");
				return false;
			}
		}
		else
		{
			$APPLICATION->ThrowException(GetMessage("SONET_NO_USER2GROUP"), "ERROR_NO_MEMBER_REQUEST");
			return false;
		}

		CSocNetUserToGroup::__SpeedFileCheckMessages($targetUserID);

		return true;
	}

	public static function TransferModerator2Member($userID, $groupId, $relationIdList): bool
	{
		global $APPLICATION, $USER;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_USERID"), "ERROR_USERID");
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_GROUPID"), "ERROR_GROUPID");
			return false;
		}

		if (!is_array($relationIdList))
		{
			return true;
		}

		$arGroup = CSocNetGroup::GetByID($groupId);
		if (!$arGroup || !is_array($arGroup))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_ERROR_NO_GROUP_ID"), "ERROR_NO_GROUP");
			return false;
		}

		$bSuccess = true;
		$arSuccessRelations = array();
		$bIMIncluded = false;
		$groupSiteId = SITE_ID;

		if (Loader::includeModule('im'))
		{
			$bIMIncluded = true;
			$groupSiteId = CSocNetGroup::GetDefaultSiteId($groupId, $arGroup["SITE_ID"]);
		}

		$workgroupsPage = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", $groupSiteId);
		$groupUrlTemplate = Path::get('group_path_template', $groupSiteId);
		$groupUrlTemplate = "#GROUPS_PATH#".mb_substr($groupUrlTemplate, mb_strlen($workgroupsPage));
		$groupUrl = str_replace(array("#group_id#", "#GROUP_ID#"), $groupId, $groupUrlTemplate);
		$relationsToUpdateCount = 0;

		foreach ($relationIdList as $relationId)
		{
			$relationId = (int)$relationId;
			if ($relationId <= 0)
			{
				continue;
			}

			$arRelation = CSocNetUserToGroup::GetByID($relationId);
			if (
				!$arRelation
				|| (int)$arRelation["GROUP_ID"] !== $groupId
				|| $arRelation["ROLE"] !== UserToGroupTable::ROLE_MODERATOR
			)
			{
				continue;
			}

			$relationsToUpdateCount++;

			$arFields = array(
				"ROLE" => UserToGroupTable::ROLE_USER,
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
			);
			if (CSocNetUserToGroup::Update($arRelation["ID"], $arFields))
			{
				$arSuccessRelations[] = $arRelation;

				if ($bIMIncluded)
				{
					$arTmp = CSocNetLogTools::ProcessPath(
						array(
							"GROUP_URL" => $groupUrl
						),
						$arRelation["USER_ID"],
						$groupSiteId
					);
					$groupUrl = $arTmp["URLS"]["GROUP_URL"];
					$serverName = (
					mb_strpos($groupUrl, "http://") === 0
						|| mb_strpos($groupUrl, "https://") === 0
							? ""
							: $arTmp["SERVER_NAME"]
					);
					$domainName = (
					mb_strpos($groupUrl, "http://") === 0
						|| mb_strpos($groupUrl, "https://") === 0
							? ""
							: (
								isset($arTmp["DOMAIN"])
								&& !empty($arTmp["DOMAIN"])
									? "//".$arTmp["DOMAIN"]
									: ""
							)
					);

					$arMessageFields = array(
						"TO_USER_ID" => $arRelation["USER_ID"],
						"FROM_USER_ID" => $userID,
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "socialnetwork",
						"NOTIFY_EVENT" => "moderators_group",
						"NOTIFY_TAG" => "SOCNET|MOD_GROUP|" . (int)$userID . "|" . $groupId . "|" . $arRelation["ID"] . "|" . $arRelation["USER_ID"],
						"NOTIFY_MESSAGE" => str_replace(
							array("#NAME#"),
							array("<a href=\"".$domainName.$groupUrl."\" class=\"bx-notifier-item-action\">".$arGroup["NAME"]."</a>"),
							GetMessage("SONET_UG_MOD2MEMBER_MESSAGE")
						),
						"NOTIFY_MESSAGE_OUT" => str_replace(
							array("#NAME#"),
							array($arGroup["NAME"]),
							GetMessage("SONET_UG_MOD2MEMBER_MESSAGE")
						)." (".$serverName.$groupUrl.")"
					);

					CIMNotify::Add($arMessageFields);
				}
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage("SONET_UR_ERROR_CREATE_USER2GROUP");
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_MOD2MEMBER");
				$bSuccess = false;
			}
		}

		if ($relationsToUpdateCount <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_ERROR_MEM2MOD_EMPTY_CORRECT_LIST"), "MOD2MEM_EMPTY_CORRECT_LIST");
			return false;
		}

		$successfulUserIdList = array();
		foreach ($arSuccessRelations as $arRel)
		{
			$arNotifyParams = array(
				"TYPE" => "unmoderate",
				"RELATION_ID" => $arRel["ID"],
				"USER_ID" => $arRel["USER_ID"],
				"GROUP_ID" => $arRel["GROUP_ID"],
				"GROUP_NAME" => $arRel["GROUP_NAME"],
				"EXCLUDE_USERS" => array($USER->GetID())
			);
			CSocNetUserToGroup::NotifyImToModerators($arNotifyParams);

			$successfulUserIdList[] = $arRel["USER_ID"];
		}

		$successfulUserIdList = array_unique($successfulUserIdList);

		if (!empty($successfulUserIdList))
		{
			Integration\Im\Chat\Workgroup::setChatManagers(array(
				'group_id' => $groupId,
				'user_id' => $successfulUserIdList,
				'set' => false
			));
		}

		if (
			$bSuccess
			&& count($arSuccessRelations) <= 0
		)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_ERROR_MOD2MEM_INCORRECT_PARAMS"), "MOD2MEM_INCORRECT_PARAMS");
			$bSuccess = false;
		}

		return $bSuccess;
	}

	public static function TransferMember2Moderator($userID, $groupId, $relationIdList): bool
	{
		global $APPLICATION, $USER;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_USERID"), "ERROR_USERID");
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_GROUPID"), "ERROR_GROUPID");
			return false;
		}

		if (!is_array($relationIdList))
		{
			return true;
		}

		$arGroup = CSocNetGroup::GetByID($groupId);
		if (!$arGroup || !is_array($arGroup))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_ERROR_NO_GROUP_ID"), "ERROR_NO_GROUP");
			return false;
		}

		$bSuccess = true;
		$arSuccessRelations = array();

		$relationsToUpdateCount = 0;

		foreach ($relationIdList as $relationId)
		{
			$relationId = (int)$relationId;
			if ($relationId <= 0)
			{
				continue;
			}

			$arRelation = CSocNetUserToGroup::GetByID($relationId);
			if (
				!$arRelation
				|| (int)$arRelation["GROUP_ID"] !== $groupId
				|| $arRelation["ROLE"] !== UserToGroupTable::ROLE_USER
			)
			{
				continue;
			}

			$relationsToUpdateCount++;

			$arFields = array(
				"ROLE" => UserToGroupTable::ROLE_MODERATOR,
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
			);
			if (CSocNetUserToGroup::update($arRelation["ID"], $arFields))
			{
				$arSuccessRelations[] = $arRelation;
				self::notifyModeratorAdded(array(
					'userId' => $userID,
					'groupId' => $groupId,
					'relationFields' => $arRelation,
					'groupFields' => $arGroup
				));
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UR_ERROR_CREATE_USER2GROUP');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_MEMBER2MOD");
				$bSuccess = false;
			}
		}

		if ($relationsToUpdateCount <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_ERROR_MEM2MOD_EMPTY_CORRECT_LIST"), "MOD2MEM_EMPTY_CORRECT_LIST");
			return false;
		}

		$successfulUserIdList = array();
		foreach ($arSuccessRelations as $arRel)
		{
			$arNotifyParams = array(
				"TYPE" => "moderate",
				"RELATION_ID" => $arRel["ID"],
				"USER_ID" => $arRel["USER_ID"],
				"GROUP_ID" => $arRel["GROUP_ID"],
				"GROUP_NAME" => $arRel["GROUP_NAME"],
				"EXCLUDE_USERS" => array($arRel["USER_ID"], $USER->GetID())
			);
			CSocNetUserToGroup::NotifyImToModerators($arNotifyParams);
			CSocNetSubscription::Set($arRel["USER_ID"], "SG".$arRel["GROUP_ID"], "Y");

			$successfulUserIdList[] = $arRel["USER_ID"];
		}

		$successfulUserIdList = array_unique($successfulUserIdList);

		if (!empty($successfulUserIdList))
		{
			Integration\Im\Chat\Workgroup::setChatManagers(array(
				'group_id' => $groupId,
				'user_id' => $successfulUserIdList,
				'set' => true
			));
		}

		if (
			$bSuccess
			&& count($arSuccessRelations) <= 0
		)
		{
			$errorMessage = "";
			if ($e = $APPLICATION->GetException())
			{
				$errorMessage = $e->GetString();
			}
			if ($errorMessage === '')
			{
				$errorMessage = Loc::getMessage('SONET_UR_ERROR_MEM2MOD_INCORRECT_PARAMS');
			}

			$APPLICATION->ThrowException($errorMessage, "MEM2MOD_INCORRECT_PARAMS");
			$bSuccess = false;
		}

		return $bSuccess;
	}

	public static function BanMember($userID, $groupId, $relationIdList, $currentUserIsAdmin): bool
	{
		global $APPLICATION;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_USERID"), "ERROR_USERID");
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_GROUPID"), "ERROR_GROUPID");
			return false;
		}

		if (!is_array($relationIdList))
		{
			return true;
		}

		$arGroup = CSocNetGroup::GetByID($groupId);
		if (!$arGroup || !is_array($arGroup))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_ERROR_NO_GROUP_ID"), "ERROR_NO_GROUP");
			return false;
		}

		$arUserPerms = CSocNetUserToGroup::InitUserPerms($userID, $arGroup, $currentUserIsAdmin);

		if (!$arUserPerms["UserCanModifyGroup"] && !$arUserPerms["UserCanModerateGroup"])
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_ERROR_NO_PERMS"), "ERROR_NO_PERMS");
			return false;
		}

		$bSuccess = true;
		foreach ($relationIdList as $relationId)
		{
			$relationId = (int)$relationId;
			if ($relationId <= 0)
			{
				continue;
			}

			$arRelation = CSocNetUserToGroup::GetByID($relationId);
			if (!$arRelation)
			{
				continue;
			}

			if (
				(int)$arRelation["GROUP_ID"] !== $groupId
				|| $arRelation["ROLE"] !== UserToGroupTable::ROLE_USER
			)
			{
				continue;
			}

			$arFields = array(
				"ROLE" => UserToGroupTable::ROLE_BAN,
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
			);
			if (CSocNetUserToGroup::Update($arRelation["ID"], $arFields))
			{
				$arMessageFields = array(
					"FROM_USER_ID" => $userID,
					"TO_USER_ID" => $arRelation["USER_ID"],
					"MESSAGE" => str_replace("#NAME#", $arGroup["NAME"], GetMessage("SONET_UG_BANMEMBER_MESSAGE")),
					"=DATE_CREATE" => CDatabase::CurrentTimeFunction(),
					"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM
				);
				CSocNetMessages::Add($arMessageFields);
				CSocNetSubscription::DeleteEx($arRelation["USER_ID"], "SG".$arRelation["GROUP_ID"]);

				UserToGroup::addInfoToChat(array(
					'group_id' => $groupId,
					'user_id' => $arRelation["USER_ID"],
					'action' => UserToGroup::CHAT_ACTION_OUT
				));
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UR_ERROR_CREATE_USER2GROUP');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_BANMEMBER");
				$bSuccess = false;
			}
		}

		return $bSuccess;
	}

	public static function UnBanMember($userID, $groupId, $relationIdList, $currentUserIsAdmin): bool
	{
		global $APPLICATION;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_USERID"), "ERROR_USERID");
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_GROUPID"), "ERROR_GROUPID");
			return false;
		}

		if (!is_array($relationIdList))
		{
			return true;
		}

		$arGroup = CSocNetGroup::GetByID($groupId);
		if (!$arGroup || !is_array($arGroup))
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_ERROR_NO_GROUP_ID"), "ERROR_NO_GROUP");
			return false;
		}

		$arUserPerms = CSocNetUserToGroup::InitUserPerms($userID, $arGroup, $currentUserIsAdmin);

		if (!$arUserPerms["UserCanModifyGroup"] && !$arUserPerms["UserCanModerateGroup"])
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UG_ERROR_NO_PERMS"), "ERROR_NO_PERMS");
			return false;
		}

		$bSuccess = true;
		foreach ($relationIdList as $relationId)
		{
			$relationId = (int)$relationId;
			if ($relationId <= 0)
			{
				continue;
			}

			$arRelation = CSocNetUserToGroup::GetByID($relationId);
			if (!$arRelation)
			{
				continue;
			}

			if (
				(int)$arRelation["GROUP_ID"] !== $groupId
				|| $arRelation["ROLE"] !== UserToGroupTable::ROLE_BAN
			)
			{
				continue;
			}

			$arFields = array(
				"ROLE" => UserToGroupTable::ROLE_USER,
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
			);
			if (CSocNetUserToGroup::Update($arRelation["ID"], $arFields))
			{
				CSocNetMessages::Add(array(
					"FROM_USER_ID" => $userID,
					"TO_USER_ID" => $arRelation["USER_ID"],
					"MESSAGE" => str_replace("#NAME#", $arGroup["NAME"], GetMessage("SONET_UG_UNBANMEMBER_MESSAGE")),
					"=DATE_CREATE" => CDatabase::CurrentTimeFunction(),
					"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM
				));

				UserToGroup::addInfoToChat(array(
					'group_id' => $groupId,
					'user_id' => $userID,
					'action' => UserToGroup::CHAT_ACTION_IN,
					'role' => $arFields['ROLE']
				));
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UR_ERROR_CREATE_USER2GROUP');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_UNBANMEMBER");
				$bSuccess = false;
			}
		}

		return $bSuccess;
	}

	public static function SetOwner($userId, $groupId, $groupFields = []): bool
	{
		global $DB, $APPLICATION, $USER;

		if (empty($groupFields))
		{
			$groupFields = CSocNetGroup::GetByID($groupId);
		}

		if (empty($groupFields))
		{
			return false;
		}

		$errorMessage = "";
		$DB->StartTransaction();

		// setting relations for the old owner
		$res = CSocNetUserToGroup::GetList(
			array(),
			array(
				"USER_ID" => $groupFields["OWNER_ID"],
				"GROUP_ID" => $groupId,
			),
			false,
			false,
			array('ID', 'USER_ID')
		);
		if ($existingRelationFields = $res->fetch())
		{
			$role = UserToGroupTable::ROLE_USER;

			$workgroup = WorkgroupTable::getByPrimary($groupId)->fetchObject();
			if (
				$workgroup
				&& $workgroup->getScrumMasterId() === (int)$existingRelationFields['USER_ID']
			)
			{
				$role = UserToGroupTable::ROLE_MODERATOR;
			}

			$relationFields = array(
				"ROLE" => $role,
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
				"INITIATED_BY_USER_ID" => $USER->GetID(),
			);

			if (!CSocNetUserToGroup::Update($existingRelationFields["ID"], $relationFields))
			{
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UG_ERROR_CANNOT_UPDATE_CURRENT_OWNER');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_UPDATE_USER2GROUP");
				$DB->Rollback();
				return false;
			}
		}

		CSocNetUserToGroup::__SpeedFileDelete($groupFields["OWNER_ID"]);

		// setting relations for the new owner
		$res = CSocNetUserToGroup::GetList(
			[],
			[
				'USER_ID' => $userId,
				'GROUP_ID' => $groupId,
			],
			false,
			false,
			[ 'ID', 'ROLE' ]
		);
		if ($existingRelationFields = $res->Fetch())
		{
			$relationFields = array(
				"ROLE" => UserToGroupTable::ROLE_OWNER,
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
				"INITIATED_BY_USER_ID" => $USER->GetID(),
				"AUTO_MEMBER" => "N"
			);

			if (!CSocNetUserToGroup::Update($existingRelationFields["ID"], $relationFields))
			{
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UG_ERROR_CANNOT_UPDATE_NEW_OWNER_RELATION');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_UPDATE_USER2GROUP");
				$DB->Rollback();
				return false;
			}

			if (!in_array($existingRelationFields["ID"], UserToGroupTable::getRolesMember(), true))
			{
				UserToGroup::addInfoToChat([
					'group_id' => $groupId,
					'user_id' => $userId,
					'action' => UserToGroup::CHAT_ACTION_IN,
					'role' => $relationFields['ROLE'],
				]);
			}

			if (Loader::includeModule('im'))
			{
				CIMNotify::deleteByTag('SOCNET|INVITE_GROUP|' . (int)$userId  . '|' . (int)$existingRelationFields['ID']);
			}
		}
		else
		{
			$relationFields = array(
				"USER_ID" => $userId,
				"GROUP_ID" => $groupId,
				"ROLE" => UserToGroupTable::ROLE_OWNER,
				"=DATE_CREATE" => CDatabase::CurrentTimeFunction(),
				"=DATE_UPDATE" => CDatabase::CurrentTimeFunction(),
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
				"INITIATED_BY_USER_ID" => $USER->GetID(),
				"MESSAGE" => false,
			);

			if (!CSocNetUserToGroup::Add($relationFields))
			{
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UG_ERROR_CANNOT_ADD_NEW_OWNER_RELATION');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_ADD_USER2GROUP");
				$DB->Rollback();
				return false;
			}

			UserToGroup::addInfoToChat(array(
				'group_id' => $groupId,
				'user_id' => $userId,
				'action' => UserToGroup::CHAT_ACTION_IN,
				'role' => $relationFields['ROLE']
			));
		}

		$GROUP_ID = CSocNetGroup::Update($groupId, array("OWNER_ID" => $userId));
		if (!$GROUP_ID || $GROUP_ID <= 0)
		{
			if ($e = $APPLICATION->GetException())
			{
				$errorMessage = $e->GetString();
			}
			if ($errorMessage === '')
			{
				$errorMessage = Loc::getMessage('SONET_UG_ERROR_CANNOT_UPDATE_GROUP');
			}

			$APPLICATION->ThrowException($errorMessage, "ERROR_UPDATE_GROUP");
			$DB->Rollback();
			return false;
		}

		$bIMIncluded = false;
		$groupUrl = "";
		$groupSiteId = SITE_ID;

		if (Loader::includeModule('im'))
		{
			$bIMIncluded = true;
			$groupSiteId = CSocNetGroup::GetDefaultSiteId($groupId, $groupFields["SITE_ID"]);
			$workgroupsPage = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", $groupSiteId);
			$groupUrlTemplate = Path::get('group_path_template', $groupSiteId);
			$groupUrlTemplate = "#GROUPS_PATH#".mb_substr($groupUrlTemplate, mb_strlen($workgroupsPage));
			$groupUrl = str_replace(array("#group_id#", "#GROUP_ID#"), $groupId, $groupUrlTemplate);
		}

		// send message to the old owner
		if ($bIMIncluded)
		{
			$arTmp = CSocNetLogTools::ProcessPath(
				array(
					"GROUP_URL" => $groupUrl
				),
				$groupFields["OWNER_ID"],
				$groupSiteId
			);
			$groupUrl = $arTmp["URLS"]["GROUP_URL"];
			$serverName = (
			mb_strpos($groupUrl, "http://") === 0
				|| mb_strpos($groupUrl, "https://") === 0
					? ""
					: $arTmp["SERVER_NAME"]
			);

			$messageFields = array(
				"TO_USER_ID" => $groupFields["OWNER_ID"],
				"FROM_USER_ID" => $USER->GetID(),
				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => "socialnetwork",
				"NOTIFY_EVENT" => "owner_group",
				"NOTIFY_TAG" => "SOCNET|OWNER_GROUP|".$groupId,
				"NOTIFY_MESSAGE" => str_replace(
					"#NAME#",
					"<a href=\"".$groupUrl."\" class=\"bx-notifier-item-action\">".$groupFields["NAME"]."</a>",
					GetMessage("SONET_UG_OWNER2MEMBER_MESSAGE")
				),
				"NOTIFY_MESSAGE_OUT" => str_replace(
					"#NAME#",
					$groupFields["NAME"],
					GetMessage("SONET_UG_OWNER2MEMBER_MESSAGE")." (".$serverName.$groupUrl.")"
				)
			);

			CIMNotify::Add($messageFields);
		}

		// send message to the new owner
		if ($bIMIncluded)
		{
			$arTmp = CSocNetLogTools::ProcessPath(
				array(
					"GROUP_URL" => $groupUrl
				),
				$userId,
				$groupSiteId
			);
			$groupUrl = $arTmp["URLS"]["GROUP_URL"];

			if (
				mb_strpos($groupUrl, "http://") === 0
				|| mb_strpos($groupUrl, "https://") === 0
			)
			{
				$serverName = "";
			}
			else
			{
				$serverName = $arTmp["SERVER_NAME"];
			}

			$messageFields = array(
				"TO_USER_ID" => $userId,
				"FROM_USER_ID" => $USER->GetID(),
				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
				"NOTIFY_MODULE" => "socialnetwork",
				"NOTIFY_EVENT" => "owner_group",
				"NOTIFY_TAG" => "SOCNET|OWNER_GROUP|".$groupId,
				"NOTIFY_MESSAGE" => str_replace(
					"#NAME#",
					"<a href=\"".$groupUrl."\" class=\"bx-notifier-item-action\">".$groupFields["NAME"]."</a>",
					GetMessage("SONET_UG_MEMBER2OWNER_MESSAGE")
				),
				"NOTIFY_MESSAGE_OUT" => str_replace(
					"#NAME#",
					$groupFields["NAME"],
					GetMessage("SONET_UG_MEMBER2OWNER_MESSAGE")." (".$serverName.$groupUrl.")"
				)
			);

			CIMNotify::Add($messageFields);
		}

		$notificationParams = array(
			"TYPE" => "owner",
			"RELATION_ID" => $existingRelationFields["ID"] ?? null,
			"USER_ID" => $userId,
			"GROUP_ID" => $groupId,
			"GROUP_NAME" => htmlspecialcharsbx($groupFields["NAME"]),
			"EXCLUDE_USERS" => array($userId, $groupFields["OWNER_ID"], $USER->GetID())
		);
		CSocNetUserToGroup::NotifyImToModerators($notificationParams);

		CSocNetSubscription::Set($userId, "SG".$groupId, "Y");

		$DB->Commit();
		return true;
	}

	public static function DeleteRelation($userId, $groupId): bool
	{
		global $APPLICATION;

		$userId = (int)$userId;
		if ($userId <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_USERID"), "ERROR_USER_ID");
			return false;
		}

		$groupId = (int)$groupId;
		if ($groupId <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("SONET_UR_EMPTY_GROUPID"), "ERROR_GROUPID");
			return false;
		}

		$res = CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" => $groupId,
				"USER_ID" => $userId,
			),
			false,
			false,
			[ 'ID', 'USER_ID', 'ROLE', 'GROUP_VISIBLE', 'GROUP_NAME', 'GROUP_SCRUM_MASTER_ID' ]
		);

		if ($relationFields = $res->Fetch())
		{
			if (!in_array($relationFields["ROLE"], [
				UserToGroupTable::ROLE_USER,
				UserToGroupTable::ROLE_MODERATOR,
			], true))
			{
				return false;
			}

			if ((int)$relationFields['USER_ID'] === (int)$relationFields['GROUP_SCRUM_MASTER_ID'])
			{
				return false;
			}

			if (!empty($relationFields['GROUP_NAME']))
			{
				$relationFields['GROUP_NAME'] = Emoji::decode($relationFields['GROUP_NAME']);
			}

			if (CSocNetUserToGroup::Delete($relationFields["ID"]))
			{
				CSocNetSubscription::DeleteEx($userId, "SG".$groupId);

				if (ModuleManager::isModuleInstalled('im'))
				{
					$chatNotificationResult = UserToGroup::addInfoToChat(array(
						'group_id' => $groupId,
						'user_id' => $userId,
						'action' => UserToGroup::CHAT_ACTION_OUT
					));

					if (!$chatNotificationResult)
					{
						CSocNetUserToGroup::notifyImToModerators(array(
							"TYPE" => "unjoin",
							"RELATION_ID" => $relationFields["ID"],
							"USER_ID" => $userId,
							"GROUP_ID" => $groupId,
							"GROUP_NAME" => $relationFields["GROUP_NAME"]
						));
					}
				}
			}
			else
			{
				$errorMessage = "";
				if ($e = $APPLICATION->GetException())
				{
					$errorMessage = $e->GetString();
				}
				if ($errorMessage === '')
				{
					$errorMessage = Loc::getMessage('SONET_UR_ERROR_CREATE_USER2GROUP');
				}

				$APPLICATION->ThrowException($errorMessage, "ERROR_DELETE_RELATION");
				return false;
			}
		}
		else
		{
			$APPLICATION->ThrowException(GetMessage("SONET_NO_USER2GROUP"), "ERROR_NO_MEMBER_REQUEST");
			return false;
		}

		CSocNetUserToGroup::__SpeedFileCheckMessages($userId);

		return true;
	}

	public static function InitUserPerms($userId, $groupFields, $isCurrentUserAdmin)
	{
		global $arSocNetAllowedInitiatePerms;
		global $arSocNetAllowedSpamPerms;

		$arReturn = array();

		$userId = (int)$userId;
		$groupId = (int)$groupFields["ID"];
		$groupOwnerId = (int)$groupFields["OWNER_ID"];
		$groupInitiatePerms = Trim($groupFields["INITIATE_PERMS"]);
		$groupVisible = Trim($groupFields["VISIBLE"]);
		$groupOpened = Trim($groupFields["OPENED"]);
		$groupSpamPerms = Trim(($groupFields["SPAM_PERMS"] ?? ''));

		if ($groupId <= 0 || $groupOwnerId <= 0 || !in_array($groupInitiatePerms, $arSocNetAllowedInitiatePerms))
		{
			return false;
		}

		$arReturn["Operations"] = [];

		if (!in_array($groupSpamPerms, $arSocNetAllowedSpamPerms))
		{
			$groupSpamPerms = "K";
		}

		// UserRole - User role in group. False if user is not group member.
		// UserIsMember - True in user is group member.
		// UserIsAuto - True in user is group auto member.
		// UserIsOwner - True if user is group owner.
		// UserCanInitiate - True if user can invite friends to group.
		// UserCanViewGroup - True if user can view group.
		// UserCanAutoJoinGroup - True if user can join group automatically.
		// UserCanModifyGroup - True if user can modify group.
		// UserCanModerateGroup - True if user can moderate group.

		if ($userId <= 0)
		{
			$arReturn["UserRole"] = false;
			$arReturn["UserIsMember"] = false;
			$arReturn["UserIsAutoMember"] = false;
			$arReturn["UserIsOwner"] = false;
			$arReturn['UserIsScrumMaster'] = false;
			$arReturn["UserCanInitiate"] = false;
			$arReturn["UserCanProcessRequestsIn"] = false;
			$arReturn["UserCanViewGroup"] = ($groupVisible === "Y");
			$arReturn["UserCanAutoJoinGroup"] = false;
			$arReturn["UserCanModifyGroup"] = false;
			$arReturn["UserCanModerateGroup"] = false;
			$arReturn["UserCanSpamGroup"] = false;
			$arReturn["InitiatedByType"] = false;
			$arReturn["InitiatedByUserId"] = false;
			$arReturn["Operations"]["viewsystemevents"] = false;
		}
		else
		{
			if (!isset($groupFields['SCRUM']))
			{
				$group = Workgroup::getById($groupFields['ID']);
				$groupFields['SCRUM'] = ($group && $group->isScrumProject() ? 'Y' : 'N');
			}

			if (!isset($groupFields['SCRUM_MASTER_ID']))
			{
				$group = Workgroup::getById($groupFields['ID']);
				$groupFields['SCRUM_MASTER_ID'] = ($group ? $group->getScrumMaster() : 0);
			}

			$arUserRoleExtended = CSocNetUserToGroup::GetUserRole($userId, $groupId, true);
			$arReturn["UserRole"] = $arUserRoleExtended["ROLE"];

			$arReturn["UserIsMember"] = (
				$arReturn["UserRole"]
				&& in_array($arReturn["UserRole"], UserToGroupTable::getRolesMember(), true)
			);
			$arReturn["UserIsAutoMember"] = (
				$arReturn["UserIsMember"]
				&& $arUserRoleExtended["AUTO_MEMBER"] === "Y"
			);

			$arReturn["InitiatedByType"] = false;
			$arReturn["InitiatedByUserId"] = false;
			if ($arReturn["UserRole"] === UserToGroupTable::ROLE_REQUEST)
			{
				$dbRelation = CSocNetUserToGroup::GetList(
					[],
					[ 'USER_ID' => $userId, 'GROUP_ID' => $groupId ],
					false,
					false,
					[ 'INITIATED_BY_TYPE', 'INITIATED_BY_USER_ID' ]
				);
				if ($arRelation = $dbRelation->Fetch())
				{
					$arReturn["InitiatedByType"] = $arRelation["INITIATED_BY_TYPE"];
					$arReturn["InitiatedByUserId"] = (int)$arRelation['INITIATED_BY_USER_ID'];
				}
			}

			$arReturn["UserIsOwner"] = ($userId === $groupOwnerId);
			$arReturn['UserIsScrumMaster'] = (
				$groupFields['SCRUM'] === 'Y'
				&& (int)$groupFields['SCRUM_MASTER_ID'] === $userId
			);

			if ($isCurrentUserAdmin)
			{
				$arReturn["UserCanInitiate"] = true;
				$arReturn["UserCanProcessRequestsIn"] = true;
				$arReturn["UserCanViewGroup"] = true;
				$arReturn["UserCanAutoJoinGroup"] = true;
				$arReturn["UserCanModifyGroup"] = true;
				$arReturn["UserCanModerateGroup"] = true;
				$arReturn["UserCanSpamGroup"] = true;
				$arReturn["Operations"]["viewsystemevents"] = true;
			}
			elseif ($arReturn["UserIsMember"])
			{
				$arReturn["UserCanInitiate"] = (
					(
						$groupInitiatePerms === UserToGroupTable::ROLE_OWNER
						&& $arReturn['UserIsOwner']
					)
					|| (
						$groupInitiatePerms === UserToGroupTable::ROLE_MODERATOR
						&& in_array($arReturn['UserRole'], [
							UserToGroupTable::ROLE_OWNER,
							UserToGroupTable::ROLE_MODERATOR,
						], true)
					)
					|| ($groupInitiatePerms === UserToGroupTable::ROLE_USER)
				);
				$arReturn['UserCanProcessRequestsIn'] = (
					$arReturn['UserCanInitiate']
					&& in_array($arReturn['UserRole'], [
						UserToGroupTable::ROLE_OWNER,
						UserToGroupTable::ROLE_MODERATOR,
					], true)
				);
				$arReturn["UserCanViewGroup"] = true;
				$arReturn["UserCanAutoJoinGroup"] = false;
				$arReturn["UserCanModifyGroup"] = $arReturn["UserIsOwner"];
				if (
					!$arReturn['UserCanModifyGroup'] 
					&& $groupFields['SCRUM'] === 'Y'
				)
				{
					if (!isset($groupFields['SCRUM_MASTER_ID']))
					{
						$group = Workgroup::getById($groupFields['ID']);
						$groupFields['SCRUM_MASTER_ID'] = ($group ? $group->getScrumMaster() : 0);
					}

					$arReturn['UserCanModifyGroup'] = ((int)$groupFields['SCRUM_MASTER_ID'] === $userId);
				}
				$arReturn["UserCanModerateGroup"] = (in_array($arReturn['UserRole'], [
					UserToGroupTable::ROLE_OWNER,
					UserToGroupTable::ROLE_MODERATOR,
				], true));
				$arReturn['UserCanSpamGroup'] = (
					(
						$groupSpamPerms === UserToGroupTable::ROLE_OWNER
						&& $arReturn['UserIsOwner']
					)
					|| (
						$groupSpamPerms === UserToGroupTable::ROLE_MODERATOR
						&& in_array($arReturn["UserRole"], [
							UserToGroupTable::ROLE_OWNER,
							UserToGroupTable::ROLE_MODERATOR,
						], true)
					)
					|| $groupSpamPerms === UserToGroupTable::ROLE_USER
					|| $groupSpamPerms === SONET_ROLES_ALL
				);
				$arReturn["Operations"]["viewsystemevents"] = true;
			}
			else
			{
				$arReturn["UserCanInitiate"] = false;
				$arReturn["UserCanViewGroup"] = ($groupVisible === "Y");
				$arReturn["UserCanAutoJoinGroup"] = ($arReturn["UserCanViewGroup"] && ($groupOpened === "Y"));
				$arReturn["UserCanModifyGroup"] = false;
				$arReturn["UserCanModerateGroup"] = false;
				$arReturn["UserCanSpamGroup"] = ($groupSpamPerms === SONET_ROLES_ALL);
				$arReturn["Operations"]["viewsystemevents"] = false;
			}
		}

		if (Loader::includeModule('extranet') && CExtranet::IsExtranetSite())
		{
			$arReturn["UserCanSpamGroup"] = true;
		}

		if (!CBXFeatures::IsFeatureEnabled("WebMessenger"))
		{
			$arReturn["UserCanSpamGroup"] = false;
		}

		return $arReturn;
	}

	public static function __SpeedFileCheckMessages($userID)
	{
		global $DB;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			return;
		}

		$cnt = 0;
		$dbResult = $DB->Query(
			"SELECT COUNT(ID) as CNT ".
			"FROM b_sonet_user2group ".
			"WHERE USER_ID = ".$userID." ".
			"	AND ROLE = '".$DB->ForSql(UserToGroupTable::ROLE_REQUEST, 1)."' ".
			"	AND INITIATED_BY_TYPE = '".$DB->ForSql(SONET_INITIATED_BY_GROUP, 1)."' "
		);
		if ($arResult = $dbResult->Fetch())
		{
			$cnt = (int)$arResult["CNT"];
		}

		if ($cnt > 0)
		{
			CSocNetUserToGroup::__SpeedFileCreate($userID);
		}
		else
		{
			CSocNetUserToGroup::__SpeedFileDelete($userID);
		}
	}

	public static function __SpeedFileCreate($userID)
	{
		global $CACHE_MANAGER;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			return;
		}

		if ($CACHE_MANAGER->Read(86400*30, "socnet_cg_".$userID))
		{
			$CACHE_MANAGER->Clean("socnet_cg_".$userID);
		}
	}

	public static function __SpeedFileDelete($userID)
	{
		global $CACHE_MANAGER;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			return;
		}

		if (!$CACHE_MANAGER->Read(86400*30, "socnet_cg_".$userID))
		{
			$CACHE_MANAGER->Set("socnet_cg_".$userID, true);
		}
	}

	public static function SpeedFileExists($userID): bool
	{
		global $CACHE_MANAGER;

		$userID = (int)$userID;
		if ($userID <= 0)
		{
			return false;
		}

		return (!$CACHE_MANAGER->Read(86400*30, "socnet_cg_".$userID));
	}

	/* Module IM callback */
	public static function OnBeforeConfirmNotify($module, $tag, $value)
	{
		global $USER;

		if ($module === "socialnetwork")
		{
			$arTag = explode("|", $tag);
			if (
				count($arTag) === 4
				&& $arTag[1] === 'INVITE_GROUP'
			)
			{
				if ($value === 'Y')
				{
					self::UserConfirmRequestToBeMember($arTag[2], $arTag[3]);
				}
				else
				{
					self::UserRejectRequestToBeMember($arTag[2], $arTag[3]);
				}
				return true;
			}

			if (
				count($arTag) === 6
				&& $arTag[1] === "REQUEST_GROUP"
			)
			{
				if ($value === "Y")
				{
					self::ConfirmRequestToBeMember($USER->GetID(), $arTag[3], array($arTag[4]));
				}
				else
				{
					self::RejectRequestToBeMember($USER->GetID(), $arTag[3], array($arTag[4]));
				}

				if (Loader::includeModule('im'))
				{
					CIMNotify::DeleteBySubTag("SOCNET|REQUEST_GROUP|".$arTag[2]."|".$arTag[3]."|".$arTag[4]);
				}

				return true;
			}
		}

		return null;
	}

	public static function NotifyImToModerators($arNotifyParams): void
	{
		if (!Loader::includeModule('im'))
		{
			return;
		}

		if (
			!is_array($arNotifyParams)
			|| !isset(
				$arNotifyParams["TYPE"],
				$arNotifyParams["USER_ID"],
				$arNotifyParams["GROUP_ID"],
				$arNotifyParams["RELATION_ID"],
				$arNotifyParams["GROUP_NAME"]
			)
			|| (int)$arNotifyParams["USER_ID"] <= 0
			|| (int)$arNotifyParams["GROUP_ID"] <= 0
			|| (int)$arNotifyParams["RELATION_ID"] <= 0
			|| (string)$arNotifyParams["GROUP_NAME"] === ''
			|| !in_array($arNotifyParams["TYPE"], [
				"join",
				"unjoin",
				"exclude",
				"moderate",
				"unmoderate",
				"owner"
			], true)
		)
		{
			return;
		}

		$fromUserId = false;
		$messageCode = false;
		$schemaCode = false;
		$notifyTag = false;

		switch ($arNotifyParams["TYPE"])
		{
			case "join":
				$fromUserId = $arNotifyParams["USER_ID"];
				$messageCode = "SONET_UG_IM_JOIN";
				$schemaCode = "inout_group";
				$notifyTag = "INOUT_GROUP";
				break;
			case "unjoin":
				$fromUserId = $arNotifyParams["USER_ID"];
				$messageCode = "SONET_UG_IM_UNJOIN";
				$schemaCode = "inout_group";
				$notifyTag = "INOUT_GROUP";
				break;
			case "exclude":
				$fromUserId = $arNotifyParams["USER_ID"];
				$messageCode = "SONET_UG_IM_EXCLUDE";
				$schemaCode = "inout_group";
				$notifyTag = "INOUT_GROUP";
				break;
			case "moderate":
				$fromUserId = $arNotifyParams["USER_ID"];
				$messageCode = "SONET_UG_IM_MODERATE";
				$schemaCode = "moderators_group";
				$notifyTag = "MOD_GROUP";
				break;
			case "unmoderate":
				$fromUserId = $arNotifyParams["USER_ID"];
				$messageCode = "SONET_UG_IM_UNMODERATE";
				$schemaCode = "moderators_group";
				$notifyTag = "MOD_GROUP";
				break;
			case "owner":
				$fromUserId = $arNotifyParams["USER_ID"];
				$messageCode = "SONET_UG_IM_OWNER";
				$schemaCode = "owner_group";
				$notifyTag = "OWNER_GROUP";
				break;
			default:
		}

		$gender_suffix = "";
		$rsUser = CUser::GetByID($arNotifyParams["USER_ID"]);
		if ($arUser = $rsUser->Fetch())
		{
			switch ($arUser["PERSONAL_GENDER"])
			{
				case "M":
					$gender_suffix = "_M";
					break;
				case "F":
					$gender_suffix = "_F";
					break;
				default:
					$gender_suffix = "";
			}
		}

		$arToUserID = [];

		$rsUserToGroup = CSocNetUserToGroup::GetList(
			array(),
			array(
				"GROUP_ID" => $arNotifyParams["GROUP_ID"],
				"USER_ACTIVE" => "Y",
				"<=ROLE" => UserToGroupTable::ROLE_MODERATOR,
			),
			false,
			false,
			array("USER_ID")
		);
		while ($arUserToGroup = $rsUserToGroup->Fetch())
		{
			$arToUserID[] = (int)$arUserToGroup["USER_ID"];
		}

		$arMessageFields = array(
			"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
			"FROM_USER_ID" => $fromUserId,
			"NOTIFY_TYPE" => IM_NOTIFY_FROM,
			"NOTIFY_MODULE" => "socialnetwork",
			"NOTIFY_EVENT" => $schemaCode,
			"NOTIFY_TAG" => "SOCNET|" . $notifyTag . "|" . (int)$arNotifyParams["USER_ID"]
				. "|" . (int)$arNotifyParams["GROUP_ID"] . "|" . (int)$arNotifyParams["RELATION_ID"],
		);

		$groups_path = COption::GetOptionString("socialnetwork", "workgroups_page", SITE_DIR."workgroups/");
		$group_url_template = str_replace(
			$groups_path,
			"#GROUPS_PATH#",
			Path::get('group_path_template')
		);

		$groupUrl = str_replace(
			"#group_id#",
			$arNotifyParams["GROUP_ID"],
			$group_url_template
		);

		foreach ($arToUserID as $to_user_id)
		{
			if (
				$to_user_id === (int)$fromUserId
				|| (
					is_array($arNotifyParams["EXCLUDE_USERS"] ?? null)
					&& in_array($to_user_id, $arNotifyParams["EXCLUDE_USERS"])
				)
			)
			{
				continue;
			}

			$arMessageFields["TO_USER_ID"] = $to_user_id;
			$arTmp = CSocNetLogTools::ProcessPath(
				array(
					"GROUP_PAGE" => $groupUrl
				),
				$to_user_id,
				SITE_ID
			);

			$arMessageFields["NOTIFY_MESSAGE"] = GetMessage($messageCode.$gender_suffix, Array(
				"#group_name#" => "<a href=\"".$arTmp["URLS"]["GROUP_PAGE"]."\" class=\"bx-notifier-item-action\">".$arNotifyParams["GROUP_NAME"]."</a>",
			));

			$arMessageFields["NOTIFY_MESSAGE_OUT"] = GetMessage($messageCode.$gender_suffix, Array(
				"#group_name#" => $arNotifyParams["GROUP_NAME"],
			))." (".$arTmp["SERVER_NAME"].$arTmp["URLS"]["GROUP_PAGE"].")";

			CIMNotify::Add($arMessageFields);
		}
	}

	public static function getMessage($message)
	{
		return Loc::getMessage($message);
	}

	public static function notifyModeratorAdded($params): void
	{
		static $groupCache = array();

		$userId = (!empty($params['userId']) ? (int)$params['userId'] : 0);
		$relationFields = (!empty($params['relationFields']) && is_array($params['relationFields']) ? $params['relationFields'] : array());
		$groupFields = (!empty($params['groupFields']) && is_array($params['groupFields']) ? $params['groupFields'] : array());
		$groupId = (
			!empty($params['groupId'])
				? (int)$params['groupId']
				: (!empty($groupFields['ID']) ? (int)$groupFields['ID'] : 0)
		);
		$relationId = (
			!empty($params['relationId'])
				? (int)$params['relationId']
				: (!empty($relationFields['ID']) ? (int)$relationFields['ID'] : 0)
		);

		if (
			empty($groupFields)
			&& $groupId > 0
		)
		{
			if (isset($groupCache[$groupId]))
			{
				$groupFields = $groupCache[$groupId];
			}
			else
			{
				$res = WorkgroupTable::getList(array(
					'filter' => array(
						'=ID' => $groupId
					),
					'select' => array('ID', 'NAME', 'SITE_ID')
				));
				$groupFields = $groupCache[$groupId] = $res->fetch();
			}
		}

		if (
			empty($relationFields)
			&& $relationId > 0
		)
		{
			$res = UserToGroupTable::getList(array(
				'filter' => array(
					'=ID' => $relationId
				),
				'select' => array('ID', 'USER_ID')
			));
			$relationFields = $res->fetch();
		}

		if (
			$groupId <= 0
			|| empty($relationFields)
			|| empty($relationFields['ID'])
			|| empty($relationFields['USER_ID'])
			|| empty($groupFields)
			|| !Loader::includeModule('im')
		)
		{
			return;
		}

		$groupSiteId = CSocNetGroup::getDefaultSiteId($groupId, $groupFields["SITE_ID"]);

		$workgroupsPage = COption::getOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID);
		$groupUrlTemplate = Path::get('group_path_template');
		$groupUrlTemplate = "#GROUPS_PATH#".mb_substr($groupUrlTemplate, mb_strlen($workgroupsPage));
		$groupUrl = str_replace(array("#group_id#", "#GROUP_ID#"), $groupId, $groupUrlTemplate);

		$arTmp = CSocNetLogTools::processPath(
			array(
				"GROUP_URL" => $groupUrl
			),
			$relationFields["USER_ID"],
			$groupSiteId
		);
		$groupUrl = $arTmp["URLS"]["GROUP_URL"];

		$serverName = (
			mb_strpos($groupUrl, "http://") === 0
			|| mb_strpos($groupUrl, "https://") === 0
				? ""
				: $arTmp["SERVER_NAME"]
		);
		$domainName = (
			mb_strpos($groupUrl, "http://") === 0
			|| mb_strpos($groupUrl, "https://") === 0
				? ""
				: (
					isset($arTmp["DOMAIN"])
					&& !empty($arTmp["DOMAIN"])
						? "//".$arTmp["DOMAIN"]
						: ""
				)
		);

		$arMessageFields = array(
			"TO_USER_ID" => $relationFields["USER_ID"],
			"FROM_USER_ID" => $userId,
			"NOTIFY_TYPE" => IM_NOTIFY_FROM,
			"NOTIFY_MODULE" => "socialnetwork",
			"NOTIFY_EVENT" => "moderators_group",
			"NOTIFY_TAG" => "SOCNET|MOD_GROUP|" . $userId . "|".$groupId."|".$relationFields["ID"]."|".$relationFields["USER_ID"],
			"NOTIFY_MESSAGE" => str_replace(
				array("#NAME#"),
				array("<a href=\"".$domainName.$groupUrl."\" class=\"bx-notifier-item-action\">".$groupFields["NAME"]."</a>"),
				GetMessage("SONET_UG_MEMBER2MOD_MESSAGE")
			),
			"NOTIFY_MESSAGE_OUT" => str_replace(
					array("#NAME#"),
					array($groupFields["NAME"]),
					Loc::getMessage("SONET_UG_MEMBER2MOD_MESSAGE")
				)." (".$serverName.$groupUrl.")"
		);

		CIMNotify::add($arMessageFields);
	}
}
