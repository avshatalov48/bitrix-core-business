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
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */

use Bitrix\Main\ModuleManager;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Main\Localization\Loc;

global $CACHE_MANAGER, $USER_FIELD_MANAGER;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arResult["IS_IFRAME"] = ($_REQUEST["IFRAME"] == "Y");

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);
$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = $USER->GetID();
$arParams["PAGE_ID"] = Trim($arParams["PAGE_ID"]);
if (StrLen($arParams["PAGE_ID"]) <= 0)
	$arParams["PAGE_ID"] = "user_features";

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

$arResult["FatalError"] = "";

$arResult["arSocNetFeaturesSettings"] = CSocNetAllowed::GetAllowedFeatures();

if (!$USER->IsAuthorized())
{
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	if (
		$arParams["PAGE_ID"] == "user_features"
		&& $arParams["USER_ID"] <= 0
	)
	{
		$arResult["FatalError"] = GetMessage("SONET_C3_NO_USER_ID").".";
	}
	elseif (
		$arParams["PAGE_ID"] == "group_features"
		&& $arParams["GROUP_ID"] <= 0
	)
	{
		$arResult["FatalError"] = GetMessage("SONET_C3_NO_GROUP_ID").".";
	}

	if (StrLen($arResult["FatalError"]) <= 0)
	{
		if ($arParams["PAGE_ID"] == "group_features")
		{
			$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

			if (!\Bitrix\Socialnetwork\Item\Workgroup::getEditFeaturesAvailability())
			{
				$arResult["FatalError"] = GetMessage("SONET_C3_PERMS").".";
			}
			elseif (
				$arGroup
				&& (
					$arGroup["OWNER_ID"] == $USER->GetID()
					|| CSocNetUser::IsCurrentUserModuleAdmin()
				)
			)
			{
				$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms($USER->GetID(), $arGroup, CSocNetUser::IsCurrentUserModuleAdmin());
				$arResult["InitiatePermsList"] = \Bitrix\Socialnetwork\Item\Workgroup::getInitiatePermOptionsList(array(
					'project' => ($arGroup["PROJECT"] == 'Y')
				));

				if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"])
				{
					$arResult["Group"] = $arGroup;
					$arResult["Features"] = array();

					$arFeaturesTmp = array();
					$dbResultTmp = CSocNetFeatures::GetList(
						array(),
						array("ENTITY_ID" => $arResult["Group"]["ID"], "ENTITY_TYPE" => SONET_ENTITY_GROUP)
					);
					while ($arResultTmp = $dbResultTmp->GetNext())
					{
						$arFeaturesTmp[$arResultTmp["FEATURE"]] = $arResultTmp;
					}

					foreach ($arResult["arSocNetFeaturesSettings"] as $feature => $arFeature)
					{
						if (
							!is_array($arFeature["allowed"])
							|| !in_array(SONET_ENTITY_GROUP, $arFeature["allowed"])
						)
						{
							continue;
						}

						$arResult["Features"][$feature] = array(
							"FeatureName" => $arFeaturesTmp[$feature]["FEATURE_NAME"],
							"Active" => (array_key_exists($feature, $arFeaturesTmp) ? ($arFeaturesTmp[$feature]["ACTIVE"] == "Y") : true),
							"Operations" => array(),
						);

						if (
							$feature == 'calendar'
							&& (
								!IsModuleInstalled("intranet")
								|| COption::GetOptionString("intranet", "calendar_2", "N") == "Y"
							)
							&& CModule::IncludeModule("calendar")
						)
						{
							$arResult["Features"][$feature]['note'] = GetMessage('SONET_CALENDAR_ACCESS_NOTE');
							continue;
						}

						if ($feature == 'files')
						{
							$arResult["Features"][$feature]['note'] = GetMessage("SONET_WEBDAV_RIGHS_NOTE");
							continue;
						}

						if (
							$feature == "blog"
							&& $arParams["PAGE_ID"] != "group_features"
						)
						{
							$arResult["Features"][$feature]["Active"] = true;
						}

						foreach ($arFeature["operations"] as $op => $arOp)
						{
							$arResult["Features"][$feature]["Operations"][$op] = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $arResult["Group"]["ID"], $feature, $op);
						}
					}
				}
				else
				{
					$arResult["FatalError"] = GetMessage("SONET_C3_PERMS").".";
				}
			}
			else
			{
				$arResult["FatalError"] = GetMessage("SONET_C3_NO_GROUP").".";
			}
		}
		else
		{
			$dbUser = CUser::GetByID($arParams["USER_ID"]);
			$arResult["User"] = $dbUser->GetNext();

			if (is_array($arResult["User"]))
			{
				$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult['User'], $bUseLogin);

				CSocNetUserPerms::InitUserPerms($USER->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

				$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($USER->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());
				if ($arResult["CurrentUserPerms"]["Operations"]["modifyuser"])
				{
					$arResult["Features"] = array();

					$arFeaturesTmp = array();
					$dbResultTmp = CSocNetFeatures::GetList(
						array(),
						array("ENTITY_ID" => $arResult["User"]["ID"], "ENTITY_TYPE" => SONET_ENTITY_USER)
					);
					while ($arResultTmp = $dbResultTmp->GetNext())
						$arFeaturesTmp[$arResultTmp["FEATURE"]] = $arResultTmp;

					foreach ($arResult["arSocNetFeaturesSettings"] as $feature => $arFeature)
					{
						if (!is_array($arFeature["allowed"]) || !in_array(SONET_ENTITY_USER, $arFeature["allowed"]))
							continue;

						$arResult["Features"][$feature] = array(
							"FeatureName" => $arFeaturesTmp[$feature]["FEATURE_NAME"],
							"Active" => (array_key_exists($feature, $arFeaturesTmp) ? ($arFeaturesTmp[$feature]["ACTIVE"] == "Y") : true),
							"Operations" => array(),
						);

						if ($feature == 'files')
						{
							$arResult["Features"][$feature]['note'] = GetMessage("SONET_WEBDAV_RIGHS_NOTE");
							continue;
						}

						if (
							$feature == 'calendar' 
							&& (
								!IsModuleInstalled("intranet")
								|| COption::GetOptionString("intranet", "calendar_2", "N") == "Y"
							)
							&& CModule::IncludeModule("calendar"))
						{
							$arResult["Features"][$feature]['note'] = GetMessage('SONET_CALENDAR_ACCESS_NOTE');
							continue;
						}

						if($feature == "blog" && $arParams["PAGE_ID"] != "group_features")
							$arResult["Features"][$feature]["Active"] = true;

						if (is_array($arFeature["operations"]))
							foreach ($arFeature["operations"] as $op => $arOp)
							{
								if(!($feature == "blog" && !array_key_exists(SONET_ENTITY_USER, $arOp)))
									$arResult["Features"][$feature]["Operations"][$op] = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $arResult["User"]["ID"], $feature, $op);
							}
					}
				}
				else
					$arResult["FatalError"] = GetMessage("SONET_C3_PERMS").".";
			}
			else
				$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").".";
		}
	}

	if (StrLen($arResult["FatalError"]) <= 0)
	{
		$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["USER_ID"]));
		$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"]));

		$strTitleFormatted = "";

		if (
			$arParams["PAGE_ID"] != "group_features"
			&& (
				$arParams["SET_TITLE"] == "Y"
				|| $arParams["SET_NAV_CHAIN"] != "N")
		)
		{
			$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
				array("#NOBR#", "#/NOBR#"),
				array("", ""),
				$arParams["NAME_TEMPLATE"]
			);

			$arTmpUser = array(
				'NAME' => $arResult["User"]["~NAME"],
				'LAST_NAME' => $arResult["User"]["~LAST_NAME"],
				'SECOND_NAME' => $arResult["User"]["~SECOND_NAME"],
				'LOGIN' => $arResult["User"]["~LOGIN"],
			);
			$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
		}
		elseif($arParams["PAGE_ID"] == "group_features")
		{
			$strTitleFormatted = $arResult["Group"]["NAME"];
		}

		$pageTitle = (
			$arParams["PAGE_ID"] == "group_features"
				? Loc::getMessage($arResult["Group"]["PROJECT"] == 'Y' ? "SONET_C3_GROUP_SETTINGS_PROJECT" : "SONET_C3_GROUP_SETTINGS")
				: Loc::getMessage("SONET_C3_USER_SETTINGS")
		);

		if ($arParams["SET_TITLE"] == "Y")
		{
			if ($arResult['IS_IFRAME'])
			{
				$APPLICATION->SetTitle($pageTitle);
				$APPLICATION->SetPageProperty("PageSubtitle", $strTitleFormatted);
			}
			else
			{
				$APPLICATION->SetTitle($strTitleFormatted.": ".$pageTitle);
			}
		}

		if ($arParams["SET_NAV_CHAIN"] != "N")
		{
			$APPLICATION->AddChainItem($strTitleFormatted, ($arParams["PAGE_ID"] == "group_features" ? $arResult["Urls"]["Group"] : $arResult["Urls"]["User"]));
			$APPLICATION->AddChainItem($pageTitle);
		}

		$arResult["ShowForm"] = "Input";

		if (
			$_SERVER["REQUEST_METHOD"] == "POST"
			&& strlen($_POST["save"]) > 0
			&& check_bitrix_sessid()
		)
		{
			if ($_POST["ajax_request"] == "Y")
			{
				CUtil::JSPostUnescape();
			}

			$errorMessage = "";

			if (
				$arParams["PAGE_ID"] == "group_features"
				&& strlen($_POST["GROUP_INITIATE_PERMS"]) > 0
				&& in_array($_POST["GROUP_INITIATE_PERMS"], UserToGroupTable::getRolesMember())
			)
			{
				CSocNetGroup::update($arResult["Group"]["ID"], array(
					'INITIATE_PERMS' => $_POST["GROUP_INITIATE_PERMS"],
					'=DATE_UPDATE' => $DB->currentTimeFunction()
				));
			}

			foreach ($arResult["Features"] as $feature => $arFeature)
			{
				if (
					$feature == "blog"
					&& $arParams["PAGE_ID"] != "group_features"
				)
				{
					$_REQUEST["blog_active"] = "Y";
				}

				$idTmp = CSocNetFeatures::setFeature(
					($arParams["PAGE_ID"] == "group_features" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
					($arParams["PAGE_ID"] == "group_features" ? $arResult["Group"]["ID"] : $arResult["User"]["ID"]),
					$feature,
					($_REQUEST[$feature."_active"] == "Y"),
					(strlen($_REQUEST[$feature."_name"]) > 0 ? $_REQUEST[$feature."_name"] : false)
				);

				if (
					$idTmp
					&& $_REQUEST[$feature."_active"] == "Y"
					&& (
						!array_key_exists("hide_operations_settings", $arResult["arSocNetFeaturesSettings"][$feature])
						|| !$arResult["arSocNetFeaturesSettings"][$feature]["hide_operations_settings"]
					)
				)
				{
					foreach ($arFeature["Operations"] as $operation => $perm)
					{
						if (
							!array_key_exists("restricted", $arResult["arSocNetFeaturesSettings"][$feature]["operations"][$operation])
							|| !in_array($key, $arResult["arSocNetFeaturesSettings"][$feature]["operations"][$operation]["restricted"][($arParams["PAGE_ID"] == "group_features" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER)])
						)
						{
							$id1Tmp = CSocNetFeaturesPerms::SetPerm(
								$idTmp,
								$operation,
								$_REQUEST[$feature."_".$operation."_perm"]
							);
							if (!$id1Tmp && $e = $APPLICATION->GetException())
								$errorMessage .= $e->GetString();
						}
					}
				}
				elseif ($e = $APPLICATION->GetException())
				{
					$errorMessage .= $e->GetString();
				}
			}

			if ($_REQUEST["ajax_request"] == "Y")
			{
				$APPLICATION->RestartBuffer();
				echo CUtil::PhpToJsObject(array(
					'MESSAGE' => (strlen($errorMessage) > 0 ? 'ERROR' : 'SUCCESS'),
					'ERROR_MESSAGE' => (strlen($errorMessage) > 0 ? $errorMessage : ''),
					'URL' => (strlen($errorMessage) > 0 ? '' : $arResult["Urls"]["Group"])
				));
				require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
				die();
			}
			else
			{
				if (strlen($errorMessage) > 0)
				{
					$arResult["ErrorMessage"] = $errorMessage;
				}
				else
				{
					if ($_REQUEST['backurl'])
					{
						LocalRedirect($_REQUEST['backurl']);
					}
					else
					{
						if ($arParams["PAGE_ID"] == "group_features")
						{
							LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"])));
						}
						else
						{
							LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["USER_ID"])));
						}
					}
				}
			}
		}

		if ($arResult["ShowForm"] == "Input")
		{
			if ($arParams["PAGE_ID"] == "group_features")
			{
				$arResult["ENTITY_TYPE"] = SONET_ENTITY_GROUP;
				$arResult["PermsVar"] = array(
					UserToGroupTable::ROLE_OWNER => GetMessage("SONET_C3_PVG_OWNER"),
					UserToGroupTable::ROLE_MODERATOR => GetMessage("SONET_C3_PVG_MOD"),
					UserToGroupTable::ROLE_USER => GetMessage("SONET_C3_PVG_USER"),
					SONET_ROLES_AUTHORIZED => GetMessage("SONET_C3_PVG_AUTHORIZED")
				);
				if (!ModuleManager::isModuleInstalled('bitrix24'))
				{
					$arResult["PermsVar"][SONET_ROLES_ALL] = GetMessage("SONET_C3_PVG_ALL");
				}
			}
			else
			{
				$arResult["ENTITY_TYPE"] = SONET_ENTITY_USER;
				$arResult["PermsVar"] = array(
					SONET_RELATIONS_TYPE_NONE => GetMessage("SONET_C3_PVU_NONE")
				);
				if (CSocNetUser::IsFriendsAllowed())
				{
					$arResult["PermsVar"][SONET_RELATIONS_TYPE_FRIENDS] = GetMessage("SONET_C3_PVU_FR");
				}
				$arResult["PermsVar"][SONET_RELATIONS_TYPE_AUTHORIZED] = GetMessage("SONET_C3_PVU_AUTHORIZED");
				if (!ModuleManager::isModuleInstalled('bitrix24'))
				{
					$arResult["PermsVar"][SONET_RELATIONS_TYPE_ALL] = GetMessage("SONET_C3_PVU_ALL");
				}
			}
		}
	}
}

$this->IncludeComponentTemplate();
?>
