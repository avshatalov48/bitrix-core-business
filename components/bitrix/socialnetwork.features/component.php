<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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

use Bitrix\Main\Loader;
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

$arParams["GROUP_ID"] = intval($arParams["GROUP_ID"]);
$arParams["USER_ID"] = intval($arParams["USER_ID"]);
if ($arParams["USER_ID"] <= 0)
	$arParams["USER_ID"] = $USER->GetID();
$arParams["PAGE_ID"] = Trim($arParams["PAGE_ID"]);
if ($arParams["PAGE_ID"] == '')
	$arParams["PAGE_ID"] = "user_features";

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["GROUP_VAR"] == '')
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] == '')
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

if ($arParams["NAME_TEMPLATE"] == '')
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

	if ($arResult["FatalError"] == '')
	{
		$arResult['tasksLimitExceeded'] = (
			Loader::includeModule('tasks')
			&& Bitrix\Tasks\Util\Restriction\Bitrix24Restriction\Limit\TaskLimit::isLimitExceeded()
		);

		$tasksLimited = (
			$arResult['tasksLimitExceeded']
			|| (
				ModuleManager::isModuleInstalled('tasks')
				&& Loader::includeModule('bitrix24')
				&& !\Bitrix\Bitrix24\Feature::isFeatureEnabled('socialnetwork_project_tasks_perms')
			)
		);

		if ($arParams["PAGE_ID"] === 'group_features')
		{
			$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

			if (!\Bitrix\Socialnetwork\Helper\Workgroup::getEditFeaturesAvailability())
			{
				$arResult["FatalError"] = GetMessage("SONET_C3_PERMS").".";
			}
			elseif ($arGroup)
			{
				$group = \Bitrix\Socialnetwork\Item\Workgroup::getById($arParams['GROUP_ID']);
				$arGroup['isScrumProject'] = ($group && $group->isScrumProject());

				$arResult["CurrentUserPerms"] = \Bitrix\Socialnetwork\Helper\Workgroup::getPermissions([
					'groupId' => $arParams['GROUP_ID'],
				]);
				$arResult['InitiatePermsList'] = \Bitrix\Socialnetwork\Item\Workgroup::getInitiatePermOptionsList([
					'project' => ($arGroup['PROJECT'] === 'Y'),
					'scrum' => $arGroup['isScrumProject'],
				]);

				$arResult['SpamPermsList'] = \Bitrix\Socialnetwork\Item\Workgroup::getSpamPermOptionsList();

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
							$arResult["Features"][$feature]['note'] = GetMessage("SONET_WEBDAV_RIGHS_NOTE2");
							continue;
						}

						if ($feature === 'tasks')
						{
							if ($arGroup['isScrumProject'])
							{
								$arResult["Features"][$feature]['note'] = Loc::getMessage('SONET_TASKS_SCRUM_ACCESS_NOTE');
								continue;
							}

							if ($tasksLimited)
							{
								$arResult["Features"][$feature]['limit'] = 'limit_tasks_access_permissions';
							}

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
							$arResult["Features"][$feature]['note'] = GetMessage("SONET_WEBDAV_RIGHS_NOTE2");
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

	if ($arResult["FatalError"] == '')
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

		if ($arParams["PAGE_ID"] === "group_features")
		{
			if ($arResult['Group']['isScrumProject'])
			{
				$pageTitle = Loc::getMessage('SONET_C3_GROUP_SETTINGS_SCRUM');
			}
			elseif ($arResult['Group']['PROJECT'] === 'Y')
			{
				$pageTitle = Loc::getMessage('SONET_C3_GROUP_SETTINGS_PROJECT');
			}
			else
			{
				$pageTitle = Loc::getMessage('SONET_C3_GROUP_SETTINGS');
			}
		}
		else
		{
			$pageTitle = Loc::getMessage('SONET_C3_USER_SETTINGS');
		}

		if ($arParams["SET_TITLE"] === "Y")
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
			&& !empty($_POST["save"])
			&& check_bitrix_sessid()
		)
		{
			if (isset($_POST["ajax_request"]) && $_POST["ajax_request"] == "Y")
			{
				CUtil::JSPostUnescape();
			}

			$errorMessage = "";

			if ($arParams['PAGE_ID'] === "group_features")
			{
				$updateFields = [];

				if (
					(string)$_POST['GROUP_SPAM_PERMS'] !== ''
					&& in_array((string)$_POST['GROUP_SPAM_PERMS'], array_merge(UserToGroupTable::getRolesMember(), [ SONET_ROLES_ALL ]), true)
				)
				{
					$updateFields['SPAM_PERMS'] = (string)$_POST['GROUP_SPAM_PERMS'];
				}

				if (
					(string)$_POST['GROUP_INITIATE_PERMS'] !== ''
					&& in_array($_POST['GROUP_INITIATE_PERMS'], UserToGroupTable::getRolesMember())
				)
				{
					$updateFields['INITIATE_PERMS'] = $_POST['GROUP_INITIATE_PERMS'];
				}

				if (!empty($updateFields))
				{
					$updateFields['=DATE_UPDATE'] = \CDatabase::CurrentTimeFunction();
					CSocNetGroup::update($arResult['Group']['ID'], $updateFields);
				}
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

				if (
					$feature === 'tasks'
					&& (
						$tasksLimited
						|| (
							isset($arGroup)
							&& $arGroup['isScrumProject']
						)
					)
				)
				{
					continue;
				}

				$idTmp = CSocNetFeatures::setFeature(
					($arParams["PAGE_ID"] == "group_features" ? SONET_ENTITY_GROUP : SONET_ENTITY_USER),
					($arParams["PAGE_ID"] == "group_features" ? $arResult["Group"]["ID"] : $arResult["User"]["ID"]),
					$feature,
					($_REQUEST[$feature."_active"] == "Y"),
					($_REQUEST[$feature."_name"] <> '' ? $_REQUEST[$feature."_name"] : false)
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

			if (isset($_REQUEST["ajax_request"]) && $_REQUEST["ajax_request"] == "Y")
			{
				$APPLICATION->RestartBuffer();
				echo CUtil::PhpToJsObject(array(
					'MESSAGE' => ($errorMessage <> '' ? 'ERROR' : 'SUCCESS'),
					'ERROR_MESSAGE' => ($errorMessage <> '' ? $errorMessage : ''),
					'URL' => (
						$errorMessage <> ''
							? ''
							: (
								$arParams["PAGE_ID"] == "group_features"
									? $arResult["Urls"]["Group"]
									: $arResult["Urls"]["User"]
							)
					)
				));
				require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
				die();
			}
			else
			{
				if ($errorMessage <> '')
				{
					$arResult["ErrorMessage"] = $errorMessage;
				}
				else
				{
					if (!empty($_REQUEST['backurl']))
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

		if ($arResult["ShowForm"] === "Input")
		{
			if ($arParams['PAGE_ID'] === "group_features")
			{
				$arResult['ENTITY_TYPE'] = SONET_ENTITY_GROUP;

				$ownerValue = Loc::getMessage('SONET_C3_PVG_OWNER');
				$moderatorsValue = Loc::getMessage('SONET_C3_PVG_MOD');
				$userValue = Loc::getMessage('SONET_C3_PVG_USER');

				$suffix = '';

				if ($arResult['Group']['isScrumProject'])
				{
					$ownerValue = Loc::getMessage('SONET_C3_PVG_OWNER_SCRUM2');
					$moderatorsValue = Loc::getMessage('SONET_C3_PVG_MOD_SCRUM2');
					$userValue = Loc::getMessage('SONET_C3_PVG_USER_SCRUM');
				}
				elseif ($arResult['Group']['PROJECT'] === 'Y')
				{
					$ownerValue = Loc::getMessage('SONET_C3_PVG_OWNER_PROJECT');
					$moderatorsValue = Loc::getMessage('SONET_C3_PVG_MOD_PROJECT');
					$userValue = Loc::getMessage('SONET_C3_PVG_USER_PROJECT');
				}

				$arResult['PermsVar'] = [
					UserToGroupTable::ROLE_OWNER => $ownerValue,
					UserToGroupTable::ROLE_MODERATOR => $moderatorsValue,
					UserToGroupTable::ROLE_USER => $userValue,
					SONET_ROLES_AUTHORIZED => Loc::getMessage('SONET_C3_PVG_AUTHORIZED')
				];

				if (!ModuleManager::isModuleInstalled('bitrix24'))
				{
					$arResult['PermsVar'][SONET_ROLES_ALL] = Loc::getMessage('SONET_C3_PVG_ALL');
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
