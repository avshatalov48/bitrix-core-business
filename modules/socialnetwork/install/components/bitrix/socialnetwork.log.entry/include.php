<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Socialnetwork\Component\LogList;

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!function_exists('__SLTransportSort'))
{
	function __SLTransportSort($a, $b)
	{
		$arPattern = array("M", "X", "D", "E");
		$a_key = array_search($a, $arPattern);
		$b_key = array_search($b, $arPattern);

		if ($a_key == $b_key)
			return 0;

		return ($a_key < $b_key) ? -1 : 1;
	}
}

if (!function_exists('__SLEGetTransport'))
{
	function __SLEGetTransport($arFields, $arCurrentUserSubscribe)
	{
		$arTransport = array();

		$entityType = ($arFields["ENTITY_TYPE"] ?? '');
		$entityId = ($arFields["ENTITY_ID"] ?? '');
		$eventId = ($arFields["EVENT_ID"] ?? '');

		if (array_key_exists($entityType."_".$entityId."_".$eventId."_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
			$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_".$entityId."_".$eventId."_N_N"];

		if (array_key_exists($entityType."_".$entityId."_all_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
			$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_".$entityId."_all_N_N"];

		$bHasLogEventCreatedBy = CSocNetLogTools::HasLogEventCreatedBy($eventId);
		if ($bHasLogEventCreatedBy)
		{
			if ($eventId)
			{
				if (array_key_exists("U_".$arFields["USER_ID"]."_all_N_Y", $arCurrentUserSubscribe["TRANSPORT"]))
					$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"]["U_".$arFields["USER_ID"]."_all_N_Y"];
				elseif (array_key_exists("U_".$arFields["USER_ID"]."_all_Y_Y", $arCurrentUserSubscribe["TRANSPORT"]))
					$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"]["U_".$arFields["USER_ID"]."_all_Y_Y"];
			}
		}

		if (
			!array_key_exists($entityType."_".$entityId."_".$eventId."_N_N", $arCurrentUserSubscribe["TRANSPORT"])
			&& !array_key_exists($entityType."_".$entityId."_all_N_N", $arCurrentUserSubscribe["TRANSPORT"])
			)
		{
			if (array_key_exists($entityType."_0_".$eventId."_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_0_".$eventId."_N_N"];
			elseif (array_key_exists($entityType."_0_all_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_0_all_N_N"];
			else
				$arTransport[] = "N";
		}

		$arTransport = array_unique($arTransport);
		usort($arTransport, "__SLTransportSort");

		return $arTransport;
	}
}

if (!function_exists('__SLEGetLogRecord'))
{
	function __SLEGetLogRecord($logId, $arParams, $arCurrentUserSubscribe)
	{
		global $APPLICATION, $CACHE_MANAGER, $USER_FIELD_MANAGER, $DB, $USER;

		static
			$isExtranetInstalled,
			$isExtranetUser,
			$arUserIdVisible,
			$arAvailableExtranetUserID,

			$bCurrentUserIsAdmin,
			$arSocNetFeaturesSettings;

		if (!$isExtranetInstalled)
		{
			$isExtranetInstalled = (CModule::IncludeModule("extranet") ? "Y" : "N");
			$isExtranetUser = ($isExtranetInstalled == "Y" && !CExtranet::IsIntranetUser() ? "Y" : "N");
			$isExtranetAdmin = ($isExtranetInstalled == "Y" && CExtranet::IsExtranetAdmin() ? "Y" : "N");
			$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();
			$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

			if ($isExtranetUser == "Y")
			{
				$arUserIdVisible = CExtranet::GetMyGroupsUsersSimple(SITE_ID);
			}
			elseif (
				$isExtranetInstalled == "Y"
				&& $isExtranetUser != "Y"
			)
			{
				if (
					$isExtranetAdmin == "Y"
					&& $bCurrentUserIsAdmin
				)
				{
					$arAvailableExtranetUserID = CExtranet::GetMyGroupsUsers(SITE_ID);
				}
				else
				{
					$arAvailableExtranetUserID = CExtranet::GetMyGroupsUsersSimple(CExtranet::GetExtranetSiteID());
				}
			}
		}

		$cacheTime = 31536000;
		$arEvent = array();

		$cache = new \CPHPCache;

		$cachedFields = [];
		$arKeys = array(
			"AVATAR_SIZE",
			"DESTINATION_LIMIT",
			"CHECK_PERMISSIONS_DEST",
			"NAME_TEMPLATE",
			"NAME_TEMPLATE_WO_NOBR",
			"SHOW_LOGIN",
			"DATE_TIME_FORMAT",
			"PATH_TO_USER",
			"PATH_TO_GROUP",
			"PATH_TO_CONPANY_DEPARTMENT"
		);
		foreach($arKeys as $param_key)
		{
			$cachedFields[$param_key] = (array_key_exists($param_key, $arParams) ? $arParams[$param_key] : false);
		}

		$cacheId = implode('_', [
			'log_post',
			$logId,
			md5(serialize($cachedFields)),
			SITE_TEMPLATE_ID,
			SITE_ID,
			LANGUAGE_ID,
			FORMAT_DATETIME,
			\CTimeZone::getOffset()
		]);
		$cachePath = '/sonet/log/' . (int)((int)$logId / 1000) . '/' . $logId . '/entry/';

		if ($cache->InitCache($cacheTime, $cacheId, $cachePath))
		{
			$arCacheVars = $cache->GetVars();
			$arEvent["FIELDS_FORMATTED"] = $arCacheVars["FIELDS_FORMATTED"];

			if (
				is_array($arEvent["FIELDS_FORMATTED"])
				&& array_key_exists("CACHED_CSS_PATH", $arEvent["FIELDS_FORMATTED"])
			)
			{
				if (
					!is_array($arEvent["FIELDS_FORMATTED"]["CACHED_CSS_PATH"])
					&& $arEvent["FIELDS_FORMATTED"]["CACHED_CSS_PATH"] <> ''
				)
				{
					$APPLICATION->SetAdditionalCSS($arEvent["FIELDS_FORMATTED"]["CACHED_CSS_PATH"]);
				}
				elseif(is_array($arEvent["FIELDS_FORMATTED"]["CACHED_CSS_PATH"]))
				{
					foreach($arEvent["FIELDS_FORMATTED"]["CACHED_CSS_PATH"] as $css_path)
					{
						$APPLICATION->SetAdditionalCSS($css_path);
					}
				}
			}

			if (
				is_array($arEvent["FIELDS_FORMATTED"])
				&& array_key_exists("CACHED_JS_PATH", $arEvent["FIELDS_FORMATTED"])
			)
			{
				if (
					!is_array($arEvent["FIELDS_FORMATTED"]["CACHED_JS_PATH"])
					&& $arEvent["FIELDS_FORMATTED"]["CACHED_JS_PATH"] !== ''
				)
				{
					$APPLICATION->AddHeadScript($arEvent["FIELDS_FORMATTED"]["CACHED_JS_PATH"]);
				}
				elseif(is_array($arEvent["FIELDS_FORMATTED"]["CACHED_JS_PATH"]))
				{
					foreach($arEvent["FIELDS_FORMATTED"]["CACHED_JS_PATH"] as $js_path)
					{
						$APPLICATION->AddHeadScript($js_path);
					}
				}
			}
		}
		else
		{
			$cache->StartDataCache($cacheTime, $cacheId, $cachePath);

			$arFilter = array(
				"ID" => $logId
			);

			$arListParams = array(
				"CHECK_RIGHTS" => "N",
				"USE_FOLLOW" => "N",
				"USE_SUBSCRIBE" => "N"
			);

			$arSelect = array(
				"ID", "TMP_ID", "ENTITY_TYPE", "ENTITY_ID", "USER_ID", "EVENT_ID", "LOG_DATE", "LOG_UPDATE", "TITLE_TEMPLATE", "TITLE", "MESSAGE", "TEXT_MESSAGE", "URL", "MODULE_ID", "CALLBACK_FUNC", "EXTERNAL_ID", "SITE_ID", "PARAMS",
				"COMMENTS_COUNT", "ENABLE_COMMENTS", "SOURCE_ID",
				"GROUP_NAME", "GROUP_OWNER_ID", "GROUP_INITIATE_PERMS", "GROUP_VISIBLE", "GROUP_OPENED", "GROUP_IMAGE_ID",
				"USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER",
				"CREATED_BY_NAME", "CREATED_BY_LAST_NAME", "CREATED_BY_SECOND_NAME", "CREATED_BY_LOGIN", "CREATED_BY_PERSONAL_PHOTO", "CREATED_BY_PERSONAL_GENDER",
				"RATING_TYPE_ID", "RATING_ENTITY_ID",
				"SOURCE_TYPE"
			);

			$dbEvent = CSocNetLog::GetList(
				array(),
				$arFilter,
				false,
				false,
				$arSelect,
				$arListParams
			);

			if ($arEvent = $dbEvent->GetNext())
			{
				$stub = (
					\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')
					&& (
						(
							in_array($arEvent["EVENT_ID"], array("timeman_entry", "report"))
							&& !IsModuleInstalled("timeman")
						)
						|| (
							in_array($arEvent["EVENT_ID"], array("tasks"))
							&& !IsModuleInstalled("tasks")
						)
						|| (
							in_array($arEvent["EVENT_ID"], array("lists_new_element"))
							&& !IsModuleInstalled("lists")
						)
					)
				);

				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->StartTagCache($cachePath);
					$CACHE_MANAGER->RegisterTag("USER_NAME_".intval($arEvent["USER_ID"]));
					$CACHE_MANAGER->RegisterTag("SONET_LOG_".intval($arEvent["ID"]));

					if ($arEvent["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
					{
						$CACHE_MANAGER->RegisterTag("sonet_group_".$arEvent["ENTITY_ID"]);
					}
				}

				$arEvent["EVENT_ID_FULLSET"] = CSocNetLogTools::FindFullSetEventIDByEventID($arEvent["EVENT_ID"]);

				if ($arEvent["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
				{
					static $arSiteWorkgroupsPage;

					if (
						!$arSiteWorkgroupsPage
						&& (
							IsModuleInstalled("extranet")
							|| (
								is_set($arEvent["URL"])
								&& (mb_strpos($arEvent["URL"], "#GROUPS_PATH#") !== false)
							)
						)
					)
					{
						$rsSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
						while($arSite = $rsSite->Fetch())
						{
							$arSiteWorkgroupsPage[$arSite["ID"]] = COption::GetOptionString("socialnetwork", "workgroups_page", $arSite["DIR"]."workgroups/", $arSite["ID"]);
						}
					}

					if (
						is_set($arEvent["URL"])
						&& isset($arSiteWorkgroupsPage[SITE_ID])
					)
					{
						$arEvent["URL"] = str_replace("#GROUPS_PATH#", $arSiteWorkgroupsPage[SITE_ID], $arEvent["URL"]);
					}
				}

				if ($stub)
				{
					$arEvent["FIELDS_FORMATTED"] = SocialnetworkLogEntry::formatStubEvent($arEvent, $arParams);
				}
				else
				{
					$arEventTmp = CSocNetLogTools::FindLogEventByID($arEvent["EVENT_ID"], false);

					if (
						$arEventTmp
						&& isset($arEventTmp["CLASS_FORMAT"])
						&& isset($arEventTmp["METHOD_FORMAT"])
					)
					{
						$contentId = \Bitrix\Socialnetwork\Livefeed\Provider::getContentId($arEvent);
						if (!empty($contentId['ENTITY_TYPE']))
						{
							if ($postProvider = \Bitrix\Socialnetwork\Livefeed\Provider::getProvider($contentId['ENTITY_TYPE']))
							{
								$sourceAdditonalData = $postProvider->getAdditionalData(array(
									'id' => array($arEvent["SOURCE_ID"])
								));

								if (
									!empty($sourceAdditonalData)
									&& isset($sourceAdditonalData[$arEvent["SOURCE_ID"]])
								)
								{
									$arEvent['ADDITIONAL_DATA'] = $sourceAdditonalData[$arEvent["SOURCE_ID"]];
								}
							}
						}

						$arEvent["UF"] = $USER_FIELD_MANAGER->GetUserFields("SONET_LOG", $arEvent["ID"], LANGUAGE_ID);
						$arEvent["FIELDS_FORMATTED"] = call_user_func(array($arEventTmp["CLASS_FORMAT"], $arEventTmp["METHOD_FORMAT"]), $arEvent, $arParams);

						if (is_array($arEvent["FIELDS_FORMATTED"]))
						{
							if (
								isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
								&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
								&& isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"])
							)
							{
								if (in_array($arEvent["EVENT_ID"], array('calendar')))
								{
									$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"] = htmlspecialcharsback($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"]);
								}
								else
								{
									$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"] = CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"]));
								}
							}

							if (
								isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
								&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
							)
							{
								$arFields2Cache = array(
									"URL",
									"STYLE",
									"DESTINATION",
									"DESTINATION_MORE",
									"TITLE_24",
									"TITLE_24_2",
									"TITLE_24_2_STYLE",
									"IS_IMPORTANT",
									"MESSAGE",
									"FOOTER_MESSAGE",
									"MESSAGE_TITLE_24",
									"DATETIME_FORMATTED",
									"LOG_DATE_FORMAT",
									"MENU",
									"COMMENT_URL"
								);
								foreach ($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"] as $field => $value)
								{
									if (!in_array($field, $arFields2Cache))
									{
										unset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"][$field]);
									}
								}
							}

							if (
								isset($arEvent["FIELDS_FORMATTED"]["EVENT"])
								&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT"])
							)
							{
								$arFields2Cache = array(
									"ID",
									"URL",
									"USER_ID",
									"ENTITY_TYPE",
									"ENTITY_ID",
									"EVENT_ID",
									"EVENT_ID_FULLSET",
									"TITLE",
									"MESSAGE",
									"SOURCE_ID",
									"PARAMS",
									"RATING_TYPE_ID",
									"RATING_ENTITY_ID"
								);
								foreach ($arEvent["FIELDS_FORMATTED"]["EVENT"] as $field => $value)
								{
									if (!in_array($field, $arFields2Cache))
									{
										unset($arEvent["FIELDS_FORMATTED"]["EVENT"][$field]);
									}
								}
							}
							if (
								isset($arEvent["FIELDS_FORMATTED"]["CREATED_BY"])
								&& is_array($arEvent["FIELDS_FORMATTED"]["CREATED_BY"])
							)
							{
								$arFields2Cache = array(
									"TOOLTIP_FIELDS",
									"FORMATTED",
									"URL",
									"IS_EXTRANET"
								);
								foreach ($arEvent["FIELDS_FORMATTED"]["CREATED_BY"] as $field => $value)
								{
									if (!in_array($field, $arFields2Cache))
									{
										unset($arEvent["FIELDS_FORMATTED"]["CREATED_BY"][$field]);
									}
								}

								if (
									isset($arEvent["FIELDS_FORMATTED"]["CREATED_BY"]["TOOLTIP_FIELDS"])
									&& is_array($arEvent["FIELDS_FORMATTED"]["CREATED_BY"]["TOOLTIP_FIELDS"])
								)
								{
									$arFields2Cache = array(
										"ID",
										"PATH_TO_SONET_USER_PROFILE",
										"NAME",
										"LAST_NAME",
										"SECOND_NAME",
										"LOGIN",
										"EMAIL",
										"PERSONAL_GENDER"
									);
									foreach ($arEvent["FIELDS_FORMATTED"]["CREATED_BY"]["TOOLTIP_FIELDS"] as $field => $value)
									{
										if (!in_array($field, $arFields2Cache))
										{
											unset($arEvent["FIELDS_FORMATTED"]["CREATED_BY"]["TOOLTIP_FIELDS"][$field]);
										}
									}
								}
							}

							if (
								isset($arEvent["FIELDS_FORMATTED"]["ENTITY"])
								&& is_array($arEvent["FIELDS_FORMATTED"]["ENTITY"])
							)
							{
								$arFields2Cache = array(
									"TOOLTIP_FIELDS",
									"FORMATTED",
									"URL"
								);
								foreach ($arEvent["FIELDS_FORMATTED"]["ENTITY"] as $field => $value)
								{
									if (!in_array($field, $arFields2Cache))
									{
										unset($arEvent["FIELDS_FORMATTED"]["ENTITY"][$field]);
									}
								}

								if (
									isset($arEvent["FIELDS_FORMATTED"]["ENTITY"]["TOOLTIP_FIELDS"])
									&& is_array($arEvent["FIELDS_FORMATTED"]["ENTITY"]["TOOLTIP_FIELDS"])
								)
								{
									$arFields2Cache = array(
										"ID",
										"PATH_TO_SONET_USER_PROFILE",
										"NAME",
										"LAST_NAME",
										"SECOND_NAME",
										"LOGIN",
										"EMAIL",
										"PERSONAL_GENDER"
									);
									foreach ($arEvent["FIELDS_FORMATTED"]["ENTITY"]["TOOLTIP_FIELDS"] as $field => $value)
									{
										if (!in_array($field, $arFields2Cache))
										{
											unset($arEvent["FIELDS_FORMATTED"]["ENTITY"]["TOOLTIP_FIELDS"][$field]);
										}
									}
								}
							}

							$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["UF"] = $arEvent["UF"];

							$arEvent["FIELDS_FORMATTED"]["TAGS"] = array();
							$res = \Bitrix\Socialnetwork\LogTagTable::getList(array(
								'filter' => array(
									'LOG_ID' => $logId,
								),
								'select' => array('NAME')
							));

							while($logTagFields = $res->fetch())
							{
								$arEvent["FIELDS_FORMATTED"]["TAGS"][] = array(
									'NAME' => $logTagFields['NAME'],
									'URL' => \CComponentEngine::makePathFromTemplate($arParams["PATH_TO_LOG_TAG"], array("tag" => urlencode($logTagFields["NAME"])))
								);
							}
						}
					}
				}

				if (is_array($arEvent["FIELDS_FORMATTED"]))
				{
					$dateFormated = FormatDate(
						$DB->DateFormatToPHP(FORMAT_DATE),
						MakeTimeStamp
						(
							isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
							&& isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"])
								? $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"]
								: (
							array_key_exists("LOG_DATE_FORMAT", $arEvent)
								? $arEvent["LOG_DATE_FORMAT"]
								: $arEvent["LOG_DATE"]
							)
						)
					);

					$timeFormated = FormatDateFromDB(
						(
						isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
						&& isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"])
							? $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"]
							: (
						array_key_exists("LOG_DATE_FORMAT", $arEvent)
							? $arEvent["LOG_DATE_FORMAT"]
							: $arEvent["LOG_DATE"]
						)
						),
						(
						mb_stripos($arParams["DATE_TIME_FORMAT"], 'a')
						|| (
							$arParams["DATE_TIME_FORMAT"] == 'FULL'
							&& IsAmPmMode()
						) !== false
							? (mb_strpos(FORMAT_DATETIME, 'TT') !== false ? 'H:MI TT' : 'H:MI T')
							: 'HH:MI'
						)
					);
					$dateTimeFormated = FormatDate(
						(
						!empty($arParams["DATE_TIME_FORMAT"])
							? ($arParams["DATE_TIME_FORMAT"] == "FULL"
							? $DB->DateFormatToPHP(str_replace(":SS", "", FORMAT_DATETIME))
							: $arParams["DATE_TIME_FORMAT"]
						)
							: $DB->DateFormatToPHP(FORMAT_DATETIME)
						),
						MakeTimeStamp(
							isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
							&& isset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"])
								? $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["LOG_DATE_FORMAT"]
								: (
							array_key_exists("LOG_DATE_FORMAT", $arEvent)
								? $arEvent["LOG_DATE_FORMAT"]
								: $arEvent["LOG_DATE"]
							)
						)
					);

					if (strcasecmp(LANGUAGE_ID, 'EN') !== 0 && strcasecmp(LANGUAGE_ID, 'DE') !== 0)
					{
						$dateTimeFormated = ToLower($dateTimeFormated);
						$dateFormated = ToLower($dateFormated);
						$timeFormated =  ToLower($timeFormated);
					}
					// strip current year
					if (!empty($arParams['DATE_TIME_FORMAT']) && ($arParams['DATE_TIME_FORMAT'] == 'j F Y G:i' || $arParams['DATE_TIME_FORMAT'] == 'j F Y g:i a'))
					{
						$dateTimeFormated = ltrim($dateTimeFormated, '0');
						$curYear = date('Y');
						$dateTimeFormated = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $dateTimeFormated);
					}

					$arEvent["MESSAGE_FORMAT"] = htmlspecialcharsback($arEvent["MESSAGE"]);
					if ($arEvent["CALLBACK_FUNC"] <> '')
					{
						if ($arEvent["MODULE_ID"] <> '')
							CModule::IncludeModule($arEvent["MODULE_ID"]);

						$arEvent["MESSAGE_FORMAT"] = call_user_func($arEvent["CALLBACK_FUNC"], $arEvent);
					}

					$arEvent["FIELDS_FORMATTED"]["LOG_TIME_FORMAT"] = $timeFormated;
					$arEvent["FIELDS_FORMATTED"]["LOG_UPDATE_TS"] = MakeTimeStamp($arEvent["LOG_UPDATE"]);

					$arEvent["FIELDS_FORMATTED"]["LOG_DATE_TS"] = MakeTimeStamp($arEvent["LOG_DATE"]);
					$arEvent["FIELDS_FORMATTED"]["LOG_DATE_DAY"] = ConvertTimeStamp(MakeTimeStamp($arEvent["LOG_DATE"]), "SHORT");
					$arEvent["FIELDS_FORMATTED"]["LOG_UPDATE_DAY"] = ConvertTimeStamp(MakeTimeStamp($arEvent["LOG_UPDATE"]), "SHORT");
					$arEvent["FIELDS_FORMATTED"]["COMMENTS_COUNT"] = $arEvent["COMMENTS_COUNT"];
					$arEvent["FIELDS_FORMATTED"]["TMP_ID"] = $arEvent["TMP_ID"];

					$arEvent["FIELDS_FORMATTED"]["DATETIME_FORMATTED"] = $dateTimeFormated;

					$arCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arEvent["EVENT_ID"]);
					if (
						!array_key_exists("HAS_COMMENTS", $arEvent["FIELDS_FORMATTED"])
						|| $arEvent["FIELDS_FORMATTED"]["HAS_COMMENTS"] != "N"
					)
					{
						$arEvent["FIELDS_FORMATTED"]["HAS_COMMENTS"] = (
							$arCommentEvent
							&& (
								!array_key_exists('ENABLE_COMMENTS', $arEvent)
								|| $arEvent['ENABLE_COMMENTS'] !== 'N'
							)
								? 'Y'
								: 'N'
						);
					}
				}

				$arEvent['FIELDS_FORMATTED']['closedWorkgroupsOnly'] = false;

				if (\Bitrix\Main\Config\Option::get('socialnetwork', 'work_with_closed_groups', 'N') !== 'Y')
				{
					$groupIdList = [];
					$res = \Bitrix\Socialnetwork\LogRightTable::getList([
						'filter' => [
							'=LOG_ID' => $logId,
						],
						'select' => [ 'GROUP_CODE' ],
					]);
					while ($logRightsFields = $res->fetch())
					{
						if (!preg_match('/^SG(\d+)/', $logRightsFields['GROUP_CODE'], $matches))
						{
							continue;
						}
						$groupIdList[] = (int)$matches[1];
					}
					if (!empty($groupIdList))
					{
						$arEvent['FIELDS_FORMATTED']['closedWorkgroupsOnly'] = true;

						$res = \Bitrix\Socialnetwork\WorkgroupTable::getList([
							'filter' => [
								'ID' => $groupIdList,
								'=CLOSED' => 'N',
							],
							'select' => [ 'ID' ]
						]);
						if ($res->fetch())
						{
							$arEvent['FIELDS_FORMATTED']['closedWorkgroupsOnly'] = false;
						}
					}
				}
			}

			$arCacheData = [
				'FIELDS_FORMATTED' => $arEvent['FIELDS_FORMATTED'],
			];
			$cache->EndDataCache($arCacheData);
			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->EndTagCache();
			}
		}

		if (!is_array($arEvent["FIELDS_FORMATTED"]))
		{
			return false;
		}

		$feature = CSocNetLogTools::FindFeatureByEventID($arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"]);

		if (
			$feature
			&& (
				!array_key_exists($feature, $arSocNetFeaturesSettings)
				|| (
					array_key_exists("allowed", $arSocNetFeaturesSettings[$feature])
					&& is_array($arSocNetFeaturesSettings[$feature]["allowed"])
					&& !in_array($arEvent["FIELDS_FORMATTED"]["EVENT"]["ENTITY_TYPE"], $arSocNetFeaturesSettings[$feature]["allowed"])
				)
			)
		)
		{
			return false;
		}

		if (!array_key_exists("COMMENTS_PARAMS", $arEvent["FIELDS_FORMATTED"]))
		{
			$arEvent["FIELDS_FORMATTED"]["COMMENTS_PARAMS"] = \Bitrix\Socialnetwork\ComponentHelper::getLFCommentsParams([
				"ID" => $arEvent["FIELDS_FORMATTED"]["EVENT"]["ID"],
				"EVENT_ID" => $arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"],
				"ENTITY_TYPE" => $arEvent["FIELDS_FORMATTED"]["EVENT"]["ENTITY_TYPE"],
				"ENTITY_ID" => $arEvent["FIELDS_FORMATTED"]["EVENT"]["ENTITY_ID"],
				"SOURCE_ID" => $arEvent["FIELDS_FORMATTED"]["EVENT"]["SOURCE_ID"],
				"PARAMS" => $arEvent["FIELDS_FORMATTED"]["EVENT"]["PARAMS"]
			]);
		}

		foreach (GetModuleEvents("socialnetwork", "OnSonetLogEntryMenuCreate", true) as $arModuleEvent) // add menu items
		{
			if (!array_key_exists("FIELDS_FORMATTED", $arEvent))
			{
				$arEvent["FIELDS_FORMATTED"] = array();
			}

			if (!array_key_exists("MENU", $arEvent["FIELDS_FORMATTED"]))
			{
				$arEvent["FIELDS_FORMATTED"]["MENU"] = array();
			}

			$arMenuItems = ExecuteModuleEventEx($arModuleEvent, array($arEvent));
			if (!empty($arMenuItems))
			{
				$arEvent["FIELDS_FORMATTED"]["MENU"] = array_merge($arEvent["FIELDS_FORMATTED"]["MENU"], $arMenuItems);
			}
		}

		if (is_array($arCurrentUserSubscribe))
		{
			$arEvent["FIELDS_FORMATTED"]["TRANSPORT"] = __SLEGetTransport($arEvent, $arCurrentUserSubscribe);
		}

		$arCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"]);

		if (!$USER->IsAuthorized())
		{
			$arEvent["FIELDS_FORMATTED"]["CAN_ADD_COMMENTS"] = "N";
		}
		elseif (
			$arCommentEvent
			&& array_key_exists("OPERATION_ADD", $arCommentEvent)
			&& $arCommentEvent["OPERATION_ADD"] == "log_rights"
		)
		{
			$arEvent["FIELDS_FORMATTED"]["CAN_ADD_COMMENTS"] = (CSocNetLogRights::CheckForUser($arEvent["FIELDS_FORMATTED"]["EVENT"]["ID"], $USER->GetID()) ? "Y" : "N");
		}
		else
		{
			$array_key = $arEvent["FIELDS_FORMATTED"]["EVENT"]["ENTITY_TYPE"]."_".$arEvent["FIELDS_FORMATTED"]["EVENT"]["ENTITY_ID"]."_".$arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"];
			if (array_key_exists($array_key, LogList::$canCurrentUserAddComments))
			{
				$arEvent["FIELDS_FORMATTED"]["CAN_ADD_COMMENTS"] = (LogList::$canCurrentUserAddComments[$array_key] == "Y" && $arEvent["FIELDS_FORMATTED"]["HAS_COMMENTS"] == "Y" ? "Y" : "N");
			}
			else
			{
				if (
					$feature
					&& $arCommentEvent
					&& array_key_exists("OPERATION_ADD", $arCommentEvent)
					&& $arCommentEvent["OPERATION_ADD"] <> ''
				)
				{
					LogList::$canCurrentUserAddComments[$array_key] = (
						CSocNetFeaturesPerms::CanPerformOperation(
								$USER->GetID(),
								$arEvent["FIELDS_FORMATTED"]["EVENT"]["ENTITY_TYPE"],
								$arEvent["FIELDS_FORMATTED"]["EVENT"]["ENTITY_ID"],
								($feature == "microblog" ? "blog" : $feature),
								$arCommentEvent["OPERATION_ADD"],
								$bCurrentUserIsAdmin
						)
							? "Y"
							: "N"
					);
				}
				else
				{
					LogList::$canCurrentUserAddComments[$array_key] = "Y";
				}

				$arEvent["FIELDS_FORMATTED"]["CAN_ADD_COMMENTS"] = (
					LogList::$canCurrentUserAddComments[$array_key] == "Y"
					&& $arEvent["FIELDS_FORMATTED"]["HAS_COMMENTS"] == "Y"
						? "Y"
						: "N"
				);
			}
		}

		if (
			$arEvent['FIELDS_FORMATTED']['CAN_ADD_COMMENTS'] === 'Y'
			&& (
				isset($arEvent['FIELDS_FORMATTED']['closedWorkgroupsOnly'])
				&& $arEvent['FIELDS_FORMATTED']['closedWorkgroupsOnly']
			)
		)
		{
			$arEvent['FIELDS_FORMATTED']['CAN_ADD_COMMENTS'] = 'N';
		}

		$arEvent["FIELDS_FORMATTED"]["FAVORITES"] = $arParams["EVENT"]["FAVORITES"] ?? null;
		$arEvent["FIELDS_FORMATTED"]["PINNED"] = $arParams["EVENT"]["PINNED"];

		if ($arParams['USE_FOLLOW'] === 'Y')
		{
			$arEvent["FIELDS_FORMATTED"]["EVENT"]["FOLLOW"] = $arParams["EVENT"]["FOLLOW"];
			$arEvent["FIELDS_FORMATTED"]["EVENT"]["DATE_FOLLOW_X1"] = ($arParams["EVENT"]["DATE_FOLLOW_X1"] ?? '');
			$arEvent["FIELDS_FORMATTED"]["EVENT"]["DATE_FOLLOW"] = $arParams["EVENT"]["DATE_FOLLOW"];
		}

		if (
			($arParams['CHECK_PERMISSIONS_DEST'] ?? null) === 'N'
			&& !$bCurrentUserIsAdmin
			&& is_object($GLOBALS["USER"])
			&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
			&& (
				(
					array_key_exists("DESTINATION", $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
					&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"])
				)
				|| (
					array_key_exists("DESTINATION_CODE", $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
					&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_CODE"])
				)
			)
		)
		{
			$arEvent['FIELDS_FORMATTED']['EVENT_FORMATTED']['DESTINATION_HIDDEN'] = 0;

			$arGroupID = CSocNetLogTools::GetAvailableGroups();

			if (
				array_key_exists("DESTINATION", $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
				&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"])
			)
			{
				foreach($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"] as $key => $arDestination)
				{
					if (
						isset($arDestination['TYPE'], $arDestination['ID'])
						&& (
							(
								$arDestination['TYPE'] === "SG"
								&& !in_array((int)$arDestination['ID'], $arGroupID)
							)
							|| (
								in_array($arDestination["TYPE"], array("CRMCOMPANY", "CRMLEAD", "CRMCONTACT", "CRMDEAL"))
								&& (
									!isset($arDestination["CRM_USER_ID"])
									|| $arDestination["CRM_USER_ID"] != $USER->GetId()
								)
								&& (
									!CModule::IncludeModule("crm")
									|| (
										in_array($arDestination["TYPE"], array("CRMCOMPANY", "CRMLEAD", "CRMCONTACT"))
										&& !\Bitrix\Crm\Security\EntityAuthorization::checkReadPermission(
											CCrmLiveFeedEntity::ResolveEntityTypeID($arDestination["TYPE"]),
											$arDestination["ID"]
										)
									)
									|| (
										$arDestination['TYPE'] === 'CRMDEAL'
										&& !CCrmDeal::CheckReadPermission($arDestination["ID"])
									)
								)
							)
							|| (
								in_array($arDestination["TYPE"], array("DR", "D"))
								&& $isExtranetUser === 'Y'
							)
							|| (
								$arDestination['TYPE'] === 'U'
								&& $arDestination["ID"] != $USER->GetId()
								&& isset($arUserIdVisible)
								&& is_array($arUserIdVisible)
								&& !in_array((int)$arDestination['ID'], $arUserIdVisible)
							)
							|| (
								$arDestination['TYPE'] === 'U'
								&& $arDestination["ID"] != $USER->GetId()
								&& isset($arDestination["IS_EXTRANET"])
								&& $arDestination['IS_EXTRANET'] === 'Y'
								&& isset($arAvailableExtranetUserID)
								&& is_array($arAvailableExtranetUserID)
								&& !in_array((int)$arDestination['ID'], $arAvailableExtranetUserID)
							)
						)
					)
					{
						unset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"][$key]);
						$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_HIDDEN"]++;
					}
					elseif (
						isset($arParams["PUBLIC_MODE"])
						&& $arParams['PUBLIC_MODE'] === 'Y'
						&& !empty($arDestination["URL"])
					)
					{
						$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"][$key]["URL"] = "";
					}
				}

				if (
					intval($arParams["DESTINATION_LIMIT_SHOW"]) > 0
					&& count($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"]) > $arParams["DESTINATION_LIMIT_SHOW"]
				)
				{
					$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_MORE"] = count($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"]) - $arParams["DESTINATION_LIMIT_SHOW"];
					$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"] = array_slice($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"], 0, $arParams["DESTINATION_LIMIT_SHOW"]);
				}
			}
			elseif (
				array_key_exists("DESTINATION_CODE", $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
				&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_CODE"])
			)
			{
				foreach($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_CODE"] as $key => $right_tmp)
				{
					if (
						preg_match('/^SG(\d+)$/', $right_tmp, $matches)
						&& !in_array(intval($matches[1]), $arGroupID)
					)
					{
						unset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_CODE"][$key]);
						$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_HIDDEN"]++;
					}
				}
			}
		}

		if (
			$arParams["SHOW_RATING"] == "Y"
			&& $arEvent["FIELDS_FORMATTED"]["EVENT"]["RATING_TYPE_ID"] <> ''
			&& intval($arEvent["FIELDS_FORMATTED"]["EVENT"]["RATING_ENTITY_ID"]) > 0
		)
		{
			$arEvent["FIELDS_FORMATTED"]["RATING"] = CRatings::GetRatingVoteResult($arEvent["FIELDS_FORMATTED"]["EVENT"]["RATING_TYPE_ID"], $arEvent["FIELDS_FORMATTED"]["EVENT"]["RATING_ENTITY_ID"]);
		}

		if (
			in_array($arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"], array("tasks", "crm_activity_add"))
			&& CModule::IncludeModule('tasks')
		)
		{
			$url = CTaskNotifications::getNotificationPath(
				array('ID' => $USER->GetId()),
				$arEvent["FIELDS_FORMATTED"]["EVENT"]['SOURCE_ID'],
				false
			);
			$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"] = preg_replace('/(<a href=")#USER_PERSONAL_TASK_URL#(">)/', '$1'.$url.'$2', $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["MESSAGE"]);
		}

		return $arEvent["FIELDS_FORMATTED"];
	}
}

?>