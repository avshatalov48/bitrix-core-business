<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!isset($GLOBALS["CurUserCanAddComments"]))
	$GLOBALS["CurUserCanAddComments"] = array();

if (!function_exists('__SLGetUFMeta'))
{
	function __SLGetUFMeta()
	{
		global $USER_FIELD_MANAGER;
		static $arUFMeta;
		if (!$arUFMeta)
		{
			$arUFMeta = $USER_FIELD_MANAGER->GetUserFields("SONET_COMMENT", 0, LANGUAGE_ID);
		}
		return $arUFMeta;
	}
}

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

		if (array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_".$arFields["EVENT_ID"]."_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
			$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_".$arFields["EVENT_ID"]."_N_N"];

		if (array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_all_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
			$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_all_N_N"];

		$bHasLogEventCreatedBy = CSocNetLogTools::HasLogEventCreatedBy($arFields["EVENT_ID"]);
		if ($bHasLogEventCreatedBy)
		{
			if ($arFields["EVENT_ID"])
			{
				if (array_key_exists("U_".$arFields["USER_ID"]."_all_N_Y", $arCurrentUserSubscribe["TRANSPORT"]))
					$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"]["U_".$arFields["USER_ID"]."_all_N_Y"];
				elseif (array_key_exists("U_".$arFields["USER_ID"]."_all_Y_Y", $arCurrentUserSubscribe["TRANSPORT"]))
					$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"]["U_".$arFields["USER_ID"]."_all_Y_Y"];
			}
		}

		if (
			!array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_".$arFields["EVENT_ID"]."_N_N", $arCurrentUserSubscribe["TRANSPORT"])
			&& !array_key_exists($arFields["ENTITY_TYPE"]."_".$arFields["ENTITY_ID"]."_all_N_N", $arCurrentUserSubscribe["TRANSPORT"])
			)
		{
			if (array_key_exists($arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_0_".$arFields["EVENT_ID"]."_N_N"];
			elseif (array_key_exists($arFields["ENTITY_TYPE"]."_0_all_N_N", $arCurrentUserSubscribe["TRANSPORT"]))
				$arTransport[] = $arCurrentUserSubscribe["TRANSPORT"][$arFields["ENTITY_TYPE"]."_0_all_N_N"];
			else
				$arTransport[] = "N";
		}

		$arTransport = array_unique($arTransport);
		usort($arTransport, "__SLTransportSort");

		return $arTransport;
	}
}

if (!function_exists('__SLGetLogRecord'))
{
	function __SLEGetLogRecord($logID, $arParams, $arCurrentUserSubscribe, $current_page_date)
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

		$cache_time = 31536000;
		$arEvent = array();
		$bEmpty = false;

		$cache = new CPHPCache;

		$arCacheID = array();
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
			if (array_key_exists($param_key, $arParams))
				$arCacheID[$param_key] = $arParams[$param_key];
			else
				$arCacheID[$param_key] = false;
		}
		$cache_id = "log_post_".$logID."_".md5(serialize($arCacheID))."_".SITE_TEMPLATE_ID."_".SITE_ID."_".LANGUAGE_ID."_".FORMAT_DATETIME."_".CTimeZone::GetOffset();
		$cache_path = "/sonet/log/".intval(intval($logID) / 1000)."/".$logID."/entry/";

		if (
			is_object($cache)
			&& $cache->InitCache($cache_time, $cache_id, $cache_path)
		)
		{
			$arCacheVars = $cache->GetVars();
			$arEvent["FIELDS_FORMATTED"] = $arCacheVars["FIELDS_FORMATTED"];

			if (array_key_exists("CACHED_CSS_PATH", $arEvent["FIELDS_FORMATTED"]))
			{
				if (
					!is_array($arEvent["FIELDS_FORMATTED"]["CACHED_CSS_PATH"])
					&& strlen($arEvent["FIELDS_FORMATTED"]["CACHED_CSS_PATH"]) > 0
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

			if (array_key_exists("CACHED_JS_PATH", $arEvent["FIELDS_FORMATTED"]))
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
			if (is_object($cache))
			{
				$cache->StartDataCache($cache_time, $cache_id, $cache_path);
			}

			$arFilter = array(
				"ID" => $logID
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
				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->StartTagCache($cache_path);
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
								&& (strpos($arEvent["URL"], "#GROUPS_PATH#") !== false)
							)
						)
					)
					{
						$rsSite = CSite::GetList($by="sort", $order="desc", Array("ACTIVE" => "Y"));
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

				$arEventTmp = CSocNetLogTools::FindLogEventByID($arEvent["EVENT_ID"]);

				if (
					$arEventTmp
					&& isset($arEventTmp["CLASS_FORMAT"])
					&& isset($arEventTmp["METHOD_FORMAT"])
				)
				{
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
					}
					else
					{
						$bEmpty = true;
					}
				}

				if (!$bEmpty)
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
						stripos($arParams["DATE_TIME_FORMAT"], 'a')
						|| (
							$arParams["DATE_TIME_FORMAT"] == 'FULL'
							&& IsAmPmMode()
						) !== false
							? (strpos(FORMAT_DATETIME, 'TT')!==false ? 'H:MI TT' : 'H:MI T')
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
					if (StrLen($arEvent["CALLBACK_FUNC"]) > 0)
					{
						if (StrLen($arEvent["MODULE_ID"]) > 0)
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
							!array_key_exists("ENABLE_COMMENTS", $arEvent)
							|| $arEvent["ENABLE_COMMENTS"] != "N"
						)
							? "Y"
							: "N"
						);
					}
				}
			}

			if (is_object($cache))
			{
				$arCacheData = Array(
					"FIELDS_FORMATTED" => $arEvent["FIELDS_FORMATTED"]
				);
				$cache->EndDataCache($arCacheData);
				if(defined("BX_COMP_MANAGED_CACHE"))
				{
					$CACHE_MANAGER->EndTagCache();
				}
			}
		}

		if ($bEmpty)
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
			$arForumMetaData = CSocNetLogTools::GetForumCommentMetaData($arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"]);

			if (
				$arForumMetaData
				&& $arEvent["FIELDS_FORMATTED"]["EVENT"]["SOURCE_ID"] > 0
			)
			{
				$arEvent["FIELDS_FORMATTED"]["COMMENTS_PARAMS"] = array(
					"ENTITY_TYPE" => $arForumMetaData[1],
					"ENTITY_XML_ID" => $arForumMetaData[0]."_".$arEvent["FIELDS_FORMATTED"]["EVENT"]["SOURCE_ID"],
					"NOTIFY_TAGS" => $arForumMetaData[2]
				);

				// Calendar events could generate different livefeed entries with same SOURCE_ID
				// That's why we should add entry ID to make comment interface work
				if ($arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"] == 'calendar')
				{
					$arEvent["FIELDS_FORMATTED"]["COMMENTS_PARAMS"]["ENTITY_XML_ID"] .= '_'.$arEvent["FIELDS_FORMATTED"]["EVENT"]["ID"];
				}
			}
			else
			{
				$arEvent["FIELDS_FORMATTED"]["COMMENTS_PARAMS"] = array(
					"ENTITY_TYPE" => substr(strtoupper($arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"])."_".$arEvent["FIELDS_FORMATTED"]["EVENT"]["ID"], 0, 2),
					"ENTITY_XML_ID" => strtoupper($arEvent["FIELDS_FORMATTED"]["EVENT"]["EVENT_ID"])."_".$arEvent["FIELDS_FORMATTED"]["EVENT"]["ID"],
					"NOTIFY_TAGS" => ""
				);
			}
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
			if (array_key_exists($array_key, $GLOBALS["CurUserCanAddComments"]))
			{
				$arEvent["FIELDS_FORMATTED"]["CAN_ADD_COMMENTS"] = ($GLOBALS["CurUserCanAddComments"][$array_key] == "Y" && $arEvent["FIELDS_FORMATTED"]["HAS_COMMENTS"] == "Y" ? "Y" : "N");
			}
			else
			{
				if (
					$feature
					&& $arCommentEvent
					&& array_key_exists("OPERATION_ADD", $arCommentEvent)
					&& strlen($arCommentEvent["OPERATION_ADD"]) > 0
				)
				{
					$GLOBALS["CurUserCanAddComments"][$array_key] = (
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
					$GLOBALS["CurUserCanAddComments"][$array_key] = "Y";
				}

				$arEvent["FIELDS_FORMATTED"]["CAN_ADD_COMMENTS"] = (
					$GLOBALS["CurUserCanAddComments"][$array_key] == "Y"
					&& $arEvent["FIELDS_FORMATTED"]["HAS_COMMENTS"] == "Y"
						? "Y"
						: "N"
				);
			}
		}

		$arEvent["FIELDS_FORMATTED"]["FAVORITES"] = $arParams["EVENT"]["FAVORITES"];

		if ($arParams["USE_FOLLOW"] == "Y")
		{
			$arEvent["FIELDS_FORMATTED"]["EVENT"]["FOLLOW"] = $arParams["EVENT"]["FOLLOW"];
			$arEvent["FIELDS_FORMATTED"]["EVENT"]["DATE_FOLLOW_X1"] = $arParams["EVENT"]["DATE_FOLLOW_X1"];
			$arEvent["FIELDS_FORMATTED"]["EVENT"]["DATE_FOLLOW"] = $arParams["EVENT"]["DATE_FOLLOW"];
		}

		if (
			$arParams["CHECK_PERMISSIONS_DEST"] == "N"
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
			$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_HIDDEN"] = 0;

			$arGroupID = CSocNetLogTools::GetAvailableGroups();

			if (
				array_key_exists("DESTINATION", $arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"])
				&& is_array($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"])
			)
			{
				foreach($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"] as $key => $arDestination)
				{
					if (
						array_key_exists("TYPE", $arDestination)
						&& array_key_exists("ID", $arDestination)
						&& (
							(
								$arDestination["TYPE"] == "SG"
								&& !in_array(intval($arDestination["ID"]), $arGroupID)
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
										$arDestination["TYPE"] == "CRMDEAL"
										&& !CCrmDeal::CheckReadPermission($arDestination["ID"])
									)
								)
							)
							|| (
								in_array($arDestination["TYPE"], array("DR", "D"))
								&& $isExtranetUser == "Y"
							)
							|| (
								$arDestination["TYPE"] == "U"
								&& $arDestination["ID"] != $USER->GetId()
								&& isset($arUserIdVisible)
								&& is_array($arUserIdVisible)
								&& !in_array(intval($arDestination["ID"]), $arUserIdVisible)
							)
							|| (
								$arDestination["TYPE"] == "U"
								&& $arDestination["ID"] != $USER->GetId()
								&& isset($arDestination["IS_EXTRANET"])
								&& $arDestination["IS_EXTRANET"] == "Y"
								&& isset($arAvailableExtranetUserID)
								&& is_array($arAvailableExtranetUserID)
								&& !in_array(intval($arDestination["ID"]), $arAvailableExtranetUserID)
							)
						)
					)
					{
						unset($arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION"][$key]);
						$arEvent["FIELDS_FORMATTED"]["EVENT_FORMATTED"]["DESTINATION_HIDDEN"]++;
					}
					elseif (
						isset($arParams["PUBLIC_MODE"])
						&& $arParams["PUBLIC_MODE"] == "Y"
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
			&& strlen($arEvent["FIELDS_FORMATTED"]["EVENT"]["RATING_TYPE_ID"]) > 0
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

if (!function_exists('__SLEGetLogCommentRecord'))
{
	function __SLEGetLogCommentRecord($arComments, $arParams, &$arAssets)
	{
		global $DB, $APPLICATION;

		static $arUserCache = array();

		// for the same post log_update - time only, if not - date and time
		$timestamp = MakeTimeStamp(array_key_exists("LOG_DATE_FORMAT", $arComments)
			? $arComments["LOG_DATE_FORMAT"]
			: $arComments["LOG_DATE"]
		);

		$timeFormated = FormatDateFromDB($arComments["LOG_DATE"],
			(
				stripos($arParams["DATE_TIME_FORMAT"], 'a')
				|| (
					$arParams["DATE_TIME_FORMAT"] == 'FULL'
					&& IsAmPmMode()
				) !== false
					? (strpos(FORMAT_DATETIME, 'TT')!==false ? 'G:MI TT' : 'G:MI T')
					: 'HH:MI'
			)
		);

		$dateTimeFormated = FormatDate(
			(!empty($arParams['DATE_TIME_FORMAT'])
				? ($arParams['DATE_TIME_FORMAT'] == 'FULL'
					? $DB->DateFormatToPHP(str_replace(':SS', '', FORMAT_DATETIME))
					: $arParams['DATE_TIME_FORMAT']
				)
				: $DB->DateFormatToPHP(FORMAT_DATETIME)
			),
			$timestamp
		);
		if (
			strcasecmp(LANGUAGE_ID, 'EN') !== 0
			&& strcasecmp(LANGUAGE_ID, 'DE') !== 0
		)
		{
			$dateTimeFormated = ToLower($dateTimeFormated);
		}
		// strip current year
		if (
			!empty($arParams['DATE_TIME_FORMAT'])
			&& (
				$arParams['DATE_TIME_FORMAT'] == 'j F Y G:i'
				|| $arParams['DATE_TIME_FORMAT'] == 'j F Y g:i a'
			)
		)
		{
			$dateTimeFormated = ltrim($dateTimeFormated, '0');
			$curYear = date('Y');
			$dateTimeFormated = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $dateTimeFormated);
		}

		$path2Entity = (
			$arComments["ENTITY_TYPE"] == SONET_ENTITY_GROUP
				? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arComments["ENTITY_ID"]))
				: CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComments["ENTITY_ID"]))
		);

		if (intval($arComments["USER_ID"]) > 0)
		{
			$suffix = (
				is_array($GLOBALS["arExtranetUserID"])
				&& in_array($arComments["USER_ID"], $GLOBALS["arExtranetUserID"])
					? GetMessage("SONET_LOG_EXTRANET_SUFFIX")
					: ""
			);

			$arTmpUser = array(
				"NAME" => $arComments["~CREATED_BY_NAME"],
				"LAST_NAME" => $arComments["~CREATED_BY_LAST_NAME"],
				"SECOND_NAME" => $arComments["~CREATED_BY_SECOND_NAME"],
				"LOGIN" => $arComments["~CREATED_BY_LOGIN"]
			);
			$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;
			$arCreatedBy = array(
				"FORMATTED" => CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, $bUseLogin).$suffix,
				"URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComments["USER_ID"], "id" => $arComments["USER_ID"]))
			);

			$arCreatedBy["TOOLTIP_FIELDS"] = array(
				"ID" => $arComments["USER_ID"],
				"NAME" => $arComments["~CREATED_BY_NAME"],
				"LAST_NAME" => $arComments["~CREATED_BY_LAST_NAME"],
				"SECOND_NAME" => $arComments["~CREATED_BY_SECOND_NAME"],
				"LOGIN" => $arComments["~CREATED_BY_LOGIN"],
				"PERSONAL_GENDER" => $arComments["~CREATED_BY_PERSONAL_GENDER"],
				"USE_THUMBNAIL_LIST" => "N",
				"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
				"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_USER"],
				"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
				"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
				"SHOW_YEAR" => $arParams["SHOW_YEAR"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"].$suffix,
				"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
				"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
				"INLINE" => "Y",
				"EXTERNAL_AUTH_ID" => $arComments["~CREATED_BY_EXTERNAL_AUTH_ID"]
			);
			if (
				isset($arParams["ENTRY_HAS_CRM_USER"])
				&& $arParams["ENTRY_HAS_CRM_USER"]
				&& IsModuleInstalled('crm')
			)
			{
				$ar = array();

				if (isset($arUserCache[$arComments["USER_ID"]]))
				{
					$ar = $arUserCache[$arComments["USER_ID"]];
				}
				else
				{
					$arSelectParams = array(
						"FIELDS" => array("ID"),
						"SELECT" => array("UF_USER_CRM_ENTITY")
					);

					$res = CUser::getList(
						($by="id"),
						($order="asc"),
						array("ID_EQUAL_EXACT" => intval($arComments["USER_ID"])),
						$arSelectParams
					);
					if ($ar = $res->fetch())
					{
						$arUserCache[$ar["ID"]] = $ar;
					}
				}

				if (!empty($ar))
				{
					$arCreatedBy["TOOLTIP_FIELDS"] = array_merge($arCreatedBy["TOOLTIP_FIELDS"], $ar);
				}
			}
		}
		else
		{
			$arCreatedBy = array("FORMATTED" => GetMessage("SONET_C73_CREATED_BY_ANONYMOUS"));
		}

		$arTmpUser = array(
			"NAME" => $arComments["~USER_NAME"],
			"LAST_NAME" => $arComments["~USER_LAST_NAME"],
			"SECOND_NAME" => $arComments["~USER_SECOND_NAME"],
			"LOGIN" => $arComments["~USER_LOGIN"]
		);

		$arParamsTmp = $arParams;
		$arParamsTmp["AVATAR_SIZE"] = (isset($arParams["AVATAR_SIZE_COMMON"]) ? $arParams["AVATAR_SIZE_COMMON"] : $arParams["AVATAR_SIZE"]);

		$arTmpCommentEvent = array(
			"EVENT"	=> $arComments,
			"LOG_DATE" => $arComments["LOG_DATE"],
			"LOG_DATE_TS" => MakeTimeStamp($arComments["LOG_DATE"]),
			"LOG_DATE_DAY"	=> ConvertTimeStamp(MakeTimeStamp($arComments["LOG_DATE"]), "SHORT"),
			"LOG_TIME_FORMAT" => $timeFormated,
			"LOG_DATETIME_FORMAT" => $dateTimeFormated,
			"TITLE_TEMPLATE" => "",
			"TITLE" => "",
			"TITLE_FORMAT" => "", // need to use url here
			"ENTITY_NAME" => (($arComments["ENTITY_TYPE"] == SONET_ENTITY_GROUP) ? $arComments["GROUP_NAME"] : CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin)),
			"ENTITY_PATH" => $path2Entity,
			"CREATED_BY" => $arCreatedBy,
			"AVATAR_SRC" => CSocNetLogTools::FormatEvent_CreateAvatar($arComments, $arParamsTmp)
		);

		$arEvent = CSocNetLogTools::FindLogCommentEventByID($arComments["EVENT_ID"]);
		$arFIELDS_FORMATTED = array();

		if (
			is_array($arEvent)
			&& array_key_exists("CLASS_FORMAT", $arEvent)
			&& array_key_exists("METHOD_FORMAT", $arEvent)
		)
		{
			$arLog = (
				$arParams["USER_COMMENTS"] == "Y"
					? array()
					: array(
						"TITLE" => $arComments["~LOG_TITLE"],
						"URL" => $arComments["~LOG_URL"],
						"PARAMS" => $arComments["~LOG_PARAMS"]
					)
			);

			$arFIELDS_FORMATTED = call_user_func(array($arEvent["CLASS_FORMAT"], $arEvent["METHOD_FORMAT"]), $arComments, $arParams, false, $arLog);

			if ($arParams["USE_COMMENTS"] != "Y")
			{
				if (
					array_key_exists("CREATED_BY", $arFIELDS_FORMATTED)
					&& isset($arFIELDS_FORMATTED["CREATED_BY"]["TOOLTIP_FIELDS"])
				)
				{
					$arTmpCommentEvent["CREATED_BY"]["TOOLTIP_FIELDS"] = $arFIELDS_FORMATTED["CREATED_BY"]["TOOLTIP_FIELDS"];
				}
			}
		}

		$message = (
			is_array($arFIELDS_FORMATTED)
			&& array_key_exists("EVENT_FORMATTED", $arFIELDS_FORMATTED)
			&& array_key_exists("MESSAGE", $arFIELDS_FORMATTED["EVENT_FORMATTED"])
				? $arFIELDS_FORMATTED["EVENT_FORMATTED"]["MESSAGE"]
				: $arTmpCommentEvent["EVENT"]["MESSAGE"]
		);

		if (strlen($message) > 0)
		{
			$arFIELDS_FORMATTED["EVENT_FORMATTED"]["FULL_MESSAGE_CUT"] = CSocNetTextParser::closetags(htmlspecialcharsback($message));
		}

		if (is_array($arTmpCommentEvent))
		{
			$arFIELDS_FORMATTED["EVENT_FORMATTED"]["DATETIME"] = (
				$arTmpCommentEvent["LOG_DATE_DAY"] == ConvertTimeStamp()
					? $timeFormated
					: $dateTimeFormated
			);
			$arTmpCommentEvent["EVENT_FORMATTED"] = $arFIELDS_FORMATTED["EVENT_FORMATTED"];
			$arTmpCommentEvent["EVENT_FORMATTED"]["URLPREVIEW"] = false;

			if (
				isset($arComments["UF"]["UF_SONET_COM_URL_PRV"])
				&& !empty($arComments["UF"]["UF_SONET_COM_URL_PRV"]["VALUE"])
			)
			{
				$arCss = $APPLICATION->sPath2css;
				$arJs = $APPLICATION->arHeadScripts;

				$urlPreviewText = \Bitrix\Socialnetwork\ComponentHelper::getUrlPreviewContent($arComments["UF"]["UF_SONET_COM_URL_PRV"], array(
					"MOBILE" => "N",
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"PATH_TO_USER" => $arParams["~PATH_TO_USER"]
				));

				if (!empty($urlPreviewText))
				{
					$arTmpCommentEvent["EVENT_FORMATTED"]["URLPREVIEW"] = true;
					$arTmpCommentEvent["EVENT_FORMATTED"]["FULL_MESSAGE_CUT"] .= $urlPreviewText;
				}

				$arAssets["CSS"] = array_merge($arAssets["CSS"], array_diff($APPLICATION->sPath2css, $arCss));
				$arAssets["JS"] = array_merge($arAssets["JS"], array_diff($APPLICATION->arHeadScripts, $arJs));

				unset($arComments["UF"]["UF_SONET_COM_URL_PRV"]);
			}

			$arTmpCommentEvent["UF"] = $arComments["UF"];

			if (
				isset($arTmpCommentEvent["EVENT_FORMATTED"])
				&& is_array($arTmpCommentEvent["EVENT_FORMATTED"])
			)
			{
				$arFields2Cache = array(
					"DATETIME",
					"MESSAGE",
					"FULL_MESSAGE_CUT",
					"ERROR_MSG",
					"URLPREVIEW",
				);
				foreach ($arTmpCommentEvent["EVENT_FORMATTED"] as $field => $value)
				{
					if (!in_array($field, $arFields2Cache))
					{
						unset($arTmpCommentEvent["EVENT_FORMATTED"][$field]);
					}
				}
			}

			if (
				isset($arTmpCommentEvent["EVENT"])
				&& is_array($arTmpCommentEvent["EVENT"])
			)
			{
				if (!empty($arTmpCommentEvent["EVENT"]["URL"]))
				{
					$arTmpCommentEvent["EVENT"]["URL"] = str_replace(
						"#GROUPS_PATH#",
						COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID),
						$arTmpCommentEvent["EVENT"]["URL"]
					);
				}

				$arFields2Cache = array(
					"ID",
					"SOURCE_ID",
					"EVENT_ID",
					"USER_ID",
					"LOG_DATE",
					"RATING_TYPE_ID",
					"RATING_ENTITY_ID",
					"URL"
				);

				if (
					isset($arParams["MAIL"])
					&& $arParams["MAIL"] == "Y"
				)
				{
					$arFields2Cache[] = "MESSAGE";
				}

				foreach ($arTmpCommentEvent["EVENT"] as $field => $value)
				{
					if (!in_array($field, $arFields2Cache))
					{
						unset($arTmpCommentEvent["EVENT"][$field]);
					}
				}
			}

			if (
				isset($arTmpCommentEvent["CREATED_BY"])
				&& is_array($arTmpCommentEvent["CREATED_BY"])
			)
			{
				$arFields2Cache = array(
					"TOOLTIP_FIELDS",
					"FORMATTED",
					"URL"
				);
				foreach ($arTmpCommentEvent["CREATED_BY"] as $field => $value)
				{
					if (!in_array($field, $arFields2Cache))
					{
						unset($arTmpCommentEvent["CREATED_BY"][$field]);
					}
				}

				if (
					isset($arTmpCommentEvent["CREATED_BY"]["TOOLTIP_FIELDS"])
					&& is_array($arTmpCommentEvent["CREATED_BY"]["TOOLTIP_FIELDS"])
				)
				{
					$arFields2Cache = array(
						"ID",
						"PATH_TO_SONET_USER_PROFILE",
						"NAME",
						"LAST_NAME",
						"SECOND_NAME",
						"PERSONAL_GENDER",
						"LOGIN",
						"EMAIL",
						"EXTERNAL_AUTH_ID",
						"UF_USER_CRM_ENTITY",
						"UF_DEPARTMENT"
					);
					foreach ($arTmpCommentEvent["CREATED_BY"]["TOOLTIP_FIELDS"] as $field => $value)
					{
						if (!in_array($field, $arFields2Cache))
						{
							unset($arTmpCommentEvent["CREATED_BY"]["TOOLTIP_FIELDS"][$field]);
						}
					}
				}
			}
		}

		foreach($arTmpCommentEvent["EVENT"] as $key => $value)
		{
			if (strpos($key, "~") === 0)
			{
				unset($arTmpCommentEvent["EVENT"][$key]);
			}
		}

		return $arTmpCommentEvent;
	}
}

?>