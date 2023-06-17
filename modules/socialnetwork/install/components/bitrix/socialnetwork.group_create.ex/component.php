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

global $CACHE_MANAGER, $USER_FIELD_MANAGER;

use Bitrix\Main\ArgumentException;
use Bitrix\Intranet\Invitation;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\Component\WorkgroupForm;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupSiteTable;
use Bitrix\Socialnetwork\Item\UserToGroup;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Integration\UI\EntitySelector;
use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;
use Bitrix\Socialnetwork\Helper;

if (!Loader::includeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$bAutoSubscribe = !(isset($arParams["USE_AUTOSUBSCRIBE"]) && $arParams['USE_AUTOSUBSCRIBE'] === 'N');
$createdGroupId = 0;

$errorData = [];
$errorMessage = [];
$warningMessage = [];

if (!$USER->IsAuthorized())
{
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$arResult['currentUserId'] = (int)$USER->getId();
	$arResult["bIntranet"] = $arResult["intranetInstalled"] = ModuleManager::isModuleInstalled('intranet');
	$arResult["landingInstalled"] = ModuleManager::isModuleInstalled('landing');

	$extranetSiteValue = Option::get("extranet", "extranet_site");

	$arResult["bExtranetInstalled"] =  (
		$arResult["intranetInstalled"]
		&& ModuleManager::isModuleInstalled('extranet')
		&& !empty($extranetSiteValue)
	);

	$arResult["bExtranet"] = (
		$arResult["bExtranetInstalled"]
		&& Loader::includeModule('extranet')
		&& CExtranet::IsExtranetSite()
	);
	$arResult["isCurrentUserIntranet"] = (
		!Loader::includeModule('extranet')
		|| CExtranet::IsIntranetUser()
	);
	$arResult['bitrix24Installed'] = ModuleManager::isModuleInstalled('bitrix24');

	$arResult["messageTextDisabled"] = (
		Loader::includeModule('bitrix24')
		&& (
			!CBitrix24::isLicensePaid()
			|| CBitrix24::isDemoLicense()
		)
		&& !CBitrix24::isNfrLicense()
	);

	$inviteMessageTextDefault = $arResult["inviteMessageText"] = Loc::getMessage("SONET_GCE_INVITE_MESSAGE_TEXT");

	if (
		!$arResult["messageTextDisabled"]
		&& (
			($userMessage = CUserOptions::getOption("socialnetwork", "invite_message_text"))
			|| ($userMessage = CUserOptions::getOption((IsModuleInstalled("bitrix24") ? "bitrix24" : "intranet"), "invite_message_text"))
		)
	)
	{
		$inviteMessageTextDefault = $arResult["inviteMessageText"] = $userMessage;
	}

	if (
		!$arResult["messageTextDisabled"]
		&& isset($_POST["MESSAGE_TEXT"])
	)
	{
		$arResult["inviteMessageText"] = htmlspecialcharsbx($_POST["MESSAGE_TEXT"]);
	}

	$arResult["POST"] = array(
		"FEATURES" => array(),
		"USER_IDS" => false,
		"MODERATOR_IDS" => false,
		"USERS_FOR_JS" => array(),
		"USERS_FOR_JS_I" => array(),
		"USERS_FOR_JS_E" => array(),
		"EMAILS" => ""
	);

	if ($arParams["GROUP_ID"] > 0)
	{
		WorkgroupForm::processWorkgroupData($arParams["GROUP_ID"], $arResult["GROUP_PROPERTIES"], $arResult["POST"], $arResult["TAB"]);
	}
	else
	{
		$arParams["GROUP_ID"] = 0;
		$arResult["POST"]["VISIBLE"] = "Y";
		if ($arResult["bExtranet"])
		{
			$arResult["POST"]["INITIATE_PERMS"] = "E";
		}
		else
		{
			$arResult["POST"]["INITIATE_PERMS"] = "K";
		}
		$arResult["POST"]["SPAM_PERMS"] = "K";
		$arResult["POST"]["IMAGE_ID_IMG"] = '<img src="/bitrix/images/1.gif" height="60" class="sonet-group-create-popup-image" id="sonet_group_create_popup_image" border="0">';
		$arResult['POST']['AVATAR_TYPE'] = array_key_first(\Bitrix\Socialnetwork\Helper\Workgroup::getAvatarTypes());
	}
	$arResult["USE_PRESETS"] = ($arResult['intranetInstalled'] ? 'Y' : 'N');

	$arResult['Types'] = (
		$arResult['USE_PRESETS'] === 'Y'
			? Helper\Workgroup::getPresets([
				'currentExtranetSite' => $arResult['bExtranet'],
				'entityOptions' => $arParams['PROJECT_OPTIONS'],
			])
			: []
	);

	$arResult['ProjectTypes'] = (
		$arResult['USE_PRESETS'] === 'Y'
			? Helper\Workgroup::getProjectPresets([
				'currentExtranetSite' => $arResult['bExtranet'],
				'entityOptions' => $arParams['PROJECT_OPTIONS'],
			])
			: []
	);

	$arResult['ConfidentialityTypes'] = (
		$arResult['USE_PRESETS'] === 'Y'
			? Helper\Workgroup::getConfidentialityPresets([
				'currentExtranetSite' => $arResult['bExtranet'],
				'entityOptions' => $arParams['PROJECT_OPTIONS'],
			])
			: []
	);

	$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["currentUserId"]));
	$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"]));


	$arResult["CALLBACK"] = '';

	if (($arResult['TAB'] ?? '') !== 'invite')
	{
		if ($arParams["GROUP_ID"] <= 0)
		{
			if (!\Bitrix\Socialnetwork\Helper\Workgroup\Access::canCreate([
				'siteId' => $this->getSiteId(),
			]))
			{
				$arResult["FatalError"] = GetMessage("SONET_GCE_ERR_CANT_CREATE").". ";
			}
		}
		elseif (
			empty($errorMessage)
			&& !\Bitrix\Socialnetwork\Helper\Workgroup\Access::canUpdate([
				'groupId' => $arParams['GROUP_ID'],
			])
		)
		{
			$arResult["FatalError"] = GetMessage("SONET_GCE_ERR_SECURITY").". ";
		}
	}

	if (StrLen($arResult["FatalError"] ?? '') <= 0)
	{
		if (
			!isset($arResult['TAB'])
			|| $arResult['TAB'] === 'edit'
		)
		{
			WorkgroupForm::processWorkgroupFeatures($arParams['GROUP_ID'], $arResult['POST']['FEATURES']);
		}

		$arResult["ShowForm"] = "Input";
		$arResult["ErrorFields"] = array();

		$avatarType = '';

		if (
			$_SERVER['REQUEST_METHOD'] === 'POST'
			&& strlen($_POST["save"] ?? '') > 0
			&& check_bitrix_sessid()
		)
		{
			if (isset($_POST['ajax_request']) && $_POST['ajax_request'] === 'Y')
			{
				CUtil::JSPostUnescape();
			}

			$moderatorIdList = [];
			$ownerId = (int)(
				isset($arResult['POST']['OWNER_ID'])
				&& (int)$arResult['POST']['OWNER_ID'] > 0
					? $arResult['POST']['OWNER_ID']
					: $arResult['currentUserId']
			);

			if (
				!array_key_exists("TAB", $arResult)
				|| $arResult['TAB'] === 'edit'
			)
			{
				$avatarType = (string) ($_POST['GROUP_AVATAR_TYPE'] ?? null);

				if (
					(int)$_POST['GROUP_IMAGE_ID'] > 0
					&& (
						!isset($_POST["GROUP_IMAGE_ID_del"])
						|| (
							$_POST['GROUP_IMAGE_ID_del'] !== 'Y'
							&& (int)$_POST['GROUP_IMAGE_ID_del'] !== (int)$_POST['GROUP_IMAGE_ID'] // main.file.input
						)
					)
				)
				{
					if (
						(int) ($arResult['POST']['IMAGE_ID'] ?? 0) !== (int) $_POST['GROUP_IMAGE_ID']
						&& (
							in_array($_POST['GROUP_IMAGE_ID'], \Bitrix\Main\UI\FileInputUtility::instance()->checkFiles('GROUP_IMAGE_ID', [ $_POST['GROUP_IMAGE_ID'] ]))
							|| in_array((int)$_POST['GROUP_IMAGE_ID'], $_SESSION['workgroup_avatar_loader'], true)
						)
					)
					{
						$arImageID = CFile::MakeFileArray($_POST["GROUP_IMAGE_ID"]);
						$arImageID["old_file"] = $arResult["POST"]["IMAGE_ID"] ?? 0;
						$arImageID["del"] = "N";
						CFile::ResizeImage($arImageID, array("width" => 300, "height" => 300));
						$avatarType = '';
					}
				}
				else
				{
					$arImageID = [
						'del' => 'Y',
						'old_file' => $arResult['POST']['IMAGE_ID'] ?? 0,
					];
				}

				$arResult["POST"]["NAME"] = htmlspecialcharsbx($_POST["GROUP_NAME"]);
				$arResult["POST"]["DESCRIPTION"] = $_POST["GROUP_DESCRIPTION"];
				$arResult["POST"]["IMAGE_ID_DEL"] = (
					isset($_POST["GROUP_IMAGE_ID_del"])
					&& (
						$_POST['GROUP_IMAGE_ID_del'] === "Y"
						|| (int)$_POST['GROUP_IMAGE_ID_del'] === (int)$_POST['GROUP_IMAGE_ID']
					)
						? "Y"
						: "N"
				);
				$arResult["POST"]["SUBJECT_ID"] = $_POST["GROUP_SUBJECT_ID"] ?? null;
				$arResult['POST']['VISIBLE'] = (($_POST['GROUP_VISIBLE'] ?? null) === 'Y' ? 'Y' : 'N');
				$arResult['POST']['OPENED'] = (($_POST['GROUP_OPENED'] ?? null) === 'Y' ? 'Y' : 'N');
				$arResult['POST']['IS_EXTRANET_GROUP'] = (($_POST['IS_EXTRANET_GROUP'] ?? null) === 'Y' ? 'Y' : 'N');
				$arResult['POST']['EXTRANET_INVITE_ACTION'] = (
					isset($_POST['EXTRANET_INVITE_ACTION'])
					&& $_POST['EXTRANET_INVITE_ACTION'] === 'add' ? 'add' : 'invite'
				);
				$arResult['POST']['CLOSED'] = (($_POST["GROUP_CLOSED"] ?? null) === 'Y' ? 'Y' : 'N');
				$arResult["POST"]["KEYWORDS"] = $_POST["GROUP_KEYWORDS"] ?? null;
				$arResult["POST"]["INITIATE_PERMS"] = $_POST["GROUP_INITIATE_PERMS"] ?? null;
				$arResult["POST"]["SPAM_PERMS"] = $_POST["GROUP_SPAM_PERMS"] ?? null;

				foreach($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
				{
					if (array_key_exists($field, $_POST))
					{
						$arResult["POST"]["PROPERTIES"][$field] = $_POST[$field];
					}
				}

				if (strlen($_POST['GROUP_NAME']) <= 0)
				{
					$errorValue = (
						isset($_POST['GROUP_PROJECT']) && $_POST['GROUP_PROJECT'] === 'Y'
							? Loc::getMessage('SONET_GCE_ERR_NAME_PROJECT')
							: Loc::getMessage('SONET_GCE_ERR_NAME')
					);
					$errorField = 'GROUP_NAME';

					$errorMessage[] = $errorValue;
					$arResult['ErrorFields'][] = $errorField;
					$errorData[] = [
						'message' => $errorValue,
						'field' => $errorField,
					];
				}

				if ((int)($_POST['GROUP_SUBJECT_ID']) <= 0 && empty($_POST['SCRUM_PROJECT']))
				{
					$errorValue = (
						isset($_POST['GROUP_PROJECT']) && $_POST['GROUP_PROJECT'] === 'Y'
							? Loc::getMessage('SONET_GCE_ERR_SUBJECT_PROJECT')
							: Loc::getMessage('SONET_GCE_ERR_SUBJECT')
					);
					$errorField = 'GROUP_SUBJECT_ID';

					$errorMessage[] = $errorValue;
					$arResult['ErrorFields'][] = $errorField;
					$errorData[] = [
						'message' => $errorValue,
						'field' => $errorField,
					];
				}

				if ((string)$_POST['GROUP_INITIATE_PERMS'] === '')
				{
					$errorValue = (
						isset($_POST['GROUP_PROJECT']) && $_POST['GROUP_PROJECT'] === 'Y'
							? Loc::getMessage('SONET_GCE_ERR_PERMS_PROJECT')
							: Loc::getMessage('SONET_GCE_ERR_PERMS')
					);
					$errorField = 'GROUP_INITIATE_PERMS';

					$errorMessage[] = $errorValue;
					$arResult['ErrorFields'][] = $errorField;
					$errorData[] = [
						'message' => $errorValue,
						'field' => $errorField,
					];
				}

				if ((string)$_POST['GROUP_SPAM_PERMS'] === '')
				{
					$errorValue = Loc::getMessage('SONET_GCE_ERR_SPAM_PERMS');
					$errorField = 'GROUP_SPAM_PERMS';

					$errorMessage[] = $errorValue;
					$arResult['ErrorFields'][] = $errorField;
					$errorData[] = [
						'message' => $errorValue,
						'field' => $errorField,
					];
				}

				if (!empty($_POST['SCRUM_PROJECT']))
				{
					if ((string)($_POST['SCRUM_MASTER_CODE']) === '')
					{
						$errorValue = Loc::getMessage('SONET_GCE_ERR_SCRUM_MASTER_ID');
						$errorField = 'SCRUM_MASTER_ID';

						$errorMessage[] = $errorValue;
						$arResult['ErrorFields'][] = $errorField;
						$errorData[] = [
							'message' => $errorValue,
							'field' => $errorField,
						];
					}
				}

				foreach ($arResult["POST"]["FEATURES"] as $feature => $arFeature)
				{
					$arResult['POST']['FEATURES'][$feature]['Active'] = (($_POST[$feature . '_active'] ?? '') === 'Y');
					$arResult["POST"]["FEATURES"][$feature]["FeatureName"] = (strlen(trim($_POST[$feature."_name"])) > 0 ? trim($_POST[$feature."_name"]) : '');
				}

				// owner
				if (
					isset($_POST["OWNER_CODE"])
					&& preg_match('/^U(\d+)$/', $_POST["OWNER_CODE"], $match)
					&& (int)$match[1] > 0
				)
				{
					$ownerId = (int)$match[1];
				}

				// moderators
				$moderatorCodeList = (
					(
						isset($_POST["MODERATOR_CODES"])
						&& is_array($_POST["MODERATOR_CODES"])
					)
						? $_POST["MODERATOR_CODES"]
						: [$_POST["MODERATOR_CODES"] ?? '']
				);

				foreach ($moderatorCodeList as $destinationCode)
				{
					if(
						preg_match('/^U(\d+)$/', $destinationCode, $match)
						&& (int)$match[1] !== $ownerId
					)
					{
						$moderatorCodeList[] = $destinationCode;
						if (!in_array($match[1], $moderatorIdList, true))
						{
							$moderatorIdList[] = (int)$match[1];
						}
					}
				}
			}

			if (
				!array_key_exists("TAB", $arResult)
				|| $arResult['TAB'] === 'invite'
			)
			{
				if (
					isset($_POST['NEW_INVITE_FORM'])
					&& $_POST['NEW_INVITE_FORM'] === 'Y'
				)
				{
					// new form

					// members
					$arUserIDs = [];
					$arDepartmentIDs = [];
					$arUserCodes = [];

					$arUserCodesFromPost = (
						(
							isset($_POST["USER_CODES"])
							&& is_array($_POST["USER_CODES"])
						)
							? $_POST["USER_CODES"]
							: [$_POST["USER_CODES"] ?? '']
					);

					foreach ($arUserCodesFromPost as $destinationCode)
					{
						if (preg_match('/^U(\d+)$/', $destinationCode, $match))
						{
							if (
								(int)$match[1] === $ownerId
								|| in_array((int)$match[1], $arUserIDs, true)
								|| in_array((int)$match[1], $moderatorIdList, true)
							)
							{
								continue;
							}

							$arUserIDs[] = (int)$match[1];
							$arUserCodes['U'.$match[1]] = 'users';
						}
						elseif (preg_match('/^DR(\d+)$/', $destinationCode, $match))
						{
							if (!in_array((int)$match[1], $arDepartmentIDs, true))
							{
								$arDepartmentIDs[] = (int)$match[1];
							}

							if (!array_key_exists('DR'.$match[1], $arUserCodes))
							{
								$arUserCodes['DR'.$match[1]] = 'users';
							}
						}
					}

					$arResult["POST"]["USER_IDS"] = $arUserIDs;
					$arResult["POST"]["USER_CODES"] = $arUserCodes;

					if (
						$arResult["bExtranetInstalled"]
						&& array_key_exists("EMAILS", $_POST)
					)
					{
						$arResult["POST"]["EMAILS"] = $_POST["EMAILS"];
					}

					if (
						array_key_exists("TAB", $arResult)
						&& $arResult['TAB'] === 'invite'
						&& empty($arUserIDs)
						&& empty($arDepartmentIDs)
						&& !$arResult["intranetInstalled"])
					{
						$errorValue = Loc::getMessage('SONET_GCE_NO_USERS');
						$errorField = 'USERS';

						$errorMessage[] = $errorValue;
						$arResult['ErrorFields'][] = $errorField;
						$errorData[] = [
							'message' => $errorValue,
							'field' => $errorField,
						];
					}
				}
				else
				{
					// old form

					if ($arResult["intranetInstalled"]) // user.selector.new
					{
						if (
							is_array($_POST["USER_IDS"])
							&& count($_POST["USER_IDS"]) > 0
						)
						{
							$arResult["POST"]["USER_IDS"] = $_POST["USER_IDS"];
						}

						//adding e-mail from the input field to the list
						if (
							array_key_exists("EMAIL", $_POST)
							&& strlen($_POST["EMAIL"]) > 0
							&& check_email($_POST["EMAIL"])
						)
						{
							$_POST["EMAILS"] .= (empty($_POST["EMAILS"]) ? "" : ", ").trim($_POST["EMAIL"]);
						}

						if (array_key_exists("EMAILS", $_POST))
						{
							$arResult["POST"]["EMAILS"] = $_POST["EMAILS"];
						}
					}
					else // user_search_input
					{
						$arUserIDs = [];

						$arUsersList = array();
						$arUsersListTmp = Explode(",", $_POST["users_list"]);
						foreach ($arUsersListTmp as $userTmp)
						{
							$userTmp = Trim($userTmp);
							if (StrLen($userTmp) > 0)
							{
								$arUsersList[] = $userTmp;
							}
						}

						if (
							$arResult['TAB'] === 'invite'
							&& Count($arUsersList) <= 0
						)
						{
							$errorValue = Loc::getMessage('SONET_GCE_NO_USERS');
							$errorField = 'USERS';

							$errorMessage[] = $errorValue;
							$arResult['ErrorFields'][] = $errorField;
							$errorData[] = [
								'message' => $errorValue,
								'field' => $errorField,
							];
						}

						if (empty($errorMessage))
						{
							foreach ($arUsersList as $user)
							{
								$arFoundUsers = CSocNetUser::SearchUser($user);
								if (
									$arFoundUsers
									&& is_array($arFoundUsers)
									&& count($arFoundUsers) > 0
								)
								{
									foreach ($arFoundUsers as $userID => $userName)
									{
										if ((int)$userID > 0)
										{
											$arUserIDs[] = (int)$userID;
										}
									}
								}
							}
						}

						$arResult["POST"]["USER_IDS"] = $arUserIDs;
					}
				}
			}

			$bFirstStepSuccess = false;
			$bSecondStepSuccess = false;

			if (
				(
					!array_key_exists("TAB", $arResult)
					|| $arResult['TAB'] === 'edit'
				)
				&& empty($errorMessage)
			)
			{
				$arFields = array(
					"NAME" => $_POST["GROUP_NAME"] ?? null,
					"DESCRIPTION" => $_POST["GROUP_DESCRIPTION"] ?? null,
					'VISIBLE' => (($_POST['GROUP_VISIBLE'] ?? null) === 'Y' ? 'Y' : 'N'),
					'OPENED' => (($_POST['GROUP_OPENED'] ?? null) === 'Y' ? 'Y' : 'N'),
					'CLOSED' => (($_POST['GROUP_CLOSED'] ?? null) === 'Y' ? 'Y' : 'N'),
					"SUBJECT_ID" => $_POST["GROUP_SUBJECT_ID"] ?? null,
					"KEYWORDS" => $_POST["GROUP_KEYWORDS"] ?? null,
					"INITIATE_PERMS" => $_POST["GROUP_INITIATE_PERMS"] ?? null,
					"SPAM_PERMS" => $_POST["GROUP_SPAM_PERMS"] ?? null,
					'PROJECT' => (($_POST['GROUP_PROJECT'] ?? null) === 'Y' ? 'Y' : 'N'),
					'LANDING' => (($_POST['GROUP_LANDING'] ?? null) === 'Y' ? 'Y' : 'N'),
					'AVATAR_TYPE' => $avatarType,
				);

				if(\Bitrix\Main\Config\Configuration::getValue("utf_mode") === true)
				{
					$conn = \Bitrix\Main\Application::getConnection();
					$table = \Bitrix\Socialnetwork\WorkgroupTable::getTableName();

					if (
						((string)$arFields["NAME"] !== '')
						&& !$conn->isUtf8mb4($table, 'NAME')
					)
					{
						$arFields["NAME"] = Emoji::encode($arFields["NAME"]);
					}
					if (
						((string)$arFields["DESCRIPTION"] !== '')
						&& !$conn->isUtf8mb4($table, 'DESCRIPTION')
					)
					{
						$arFields["DESCRIPTION"] = Emoji::encode($arFields["DESCRIPTION"]);
					}
				}

				if (!empty($arImageID))
				{
					$arFields["IMAGE_ID"] = $arImageID;
				}

				if ($arFields['PROJECT'] === 'Y')
				{
					if (isset($_POST["PROJECT_DATE_START"]))
					{
						$arFields["PROJECT_DATE_START"] = $_POST["PROJECT_DATE_START"];
					}

					if (isset($_POST["PROJECT_DATE_FINISH"]))
					{
						$arFields["PROJECT_DATE_FINISH"] = $_POST["PROJECT_DATE_FINISH"];
					}
				}

				if (
					!CModule::IncludeModule("extranet")
					|| !CExtranet::IsExtranetSite()
				)
				{
					$arFields["SITE_ID"] = [
						$this->getSiteId()
					];
					if (
						($_POST['IS_EXTRANET_GROUP'] ?? '') === 'Y'
						&& Loader::includeModule('extranet')
						&& !CExtranet::IsExtranetSite()
					)
					{
						$arFields["SITE_ID"][] = CExtranet::GetExtranetSiteID();
						$arFields["VISIBLE"] = "N";
						$arFields["OPENED"] = "N";
					}
				}
				elseif (
					CModule::IncludeModule("extranet")
					&& CExtranet::IsExtranetSite()
				)
				{
					if ($arParams["GROUP_ID"] <= 0)
					{
						$arFields["SITE_ID"] = [
							$this->getSiteId(),
							CSite::GetDefSite()
						];
					}
					else
					{
						$siteIdList = array();
						$res = WorkgroupSiteTable::getList(array(
							'filter' => array(
								'GROUP_ID' => $arParams["GROUP_ID"]
							),
							'select' => array('SITE_ID')
						));
						while ($workGroupSiteFields = $res->fetch())
						{
							$siteIdList[] = $workGroupSiteFields['SITE_ID'];
						}
						$siteIdList[] = $this->getSiteId();

						$siteIdList = array_unique($siteIdList);
						if (!empty($siteIdList))
						{
							$arFields["SITE_ID"] = $siteIdList;
						}
					}
				}

				foreach ($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
				{
					if (array_key_exists($field, $_POST))
					{
						$arFields[$field] = $_POST[$field];
					}
				}

				$USER_FIELD_MANAGER->EditFormAddFields("SONET_GROUP", $arFields);

				if (!empty($_POST["SCRUM_PROJECT"]))
				{
					if (preg_match('/^U(\d+)$/', $_POST["SCRUM_MASTER_CODE"], $match) && (int)$match[1] > 0)
					{
						$arFields['SCRUM_MASTER_ID'] = (int)$match[1];
						$moderatorIdList[] = $arFields['SCRUM_MASTER_ID'];
					}

					$arFields['SCRUM_SPRINT_DURATION'] = (int)$_POST["SCRUM_SPRINT_DURATION"];
					$availableResponsibleTypes = ['A', 'M'];
					$scrumTaskResponsible = (
						is_string($_POST["SCRUM_TASK_RESPONSIBLE"]) ? $_POST["SCRUM_TASK_RESPONSIBLE"] : 'A'
					);
					$scrumTaskResponsible = (
						in_array($scrumTaskResponsible, $availableResponsibleTypes) ? $scrumTaskResponsible : 'A'
					);
					$arFields['SCRUM_TASK_RESPONSIBLE'] = $scrumTaskResponsible;
				}

				\Bitrix\Socialnetwork\Helper\Workgroup::mutateScrumFormFields($arFields);

				if ($arParams["GROUP_ID"] <= 0)
				{
					if (
						CModule::IncludeModule("extranet")
						&& CExtranet::IsExtranetSite()
					)
					{
						$arFields["SITE_ID"][] = CSite::GetDefSite();
					}

					$arResult["GROUP_ID"] = CSocNetGroup::createGroup($ownerId, $arFields, $bAutoSubscribe);
					$createdGroupId = (int)$arResult["GROUP_ID"];
					if (!$arResult["GROUP_ID"])
					{
						if ($e = $APPLICATION->getException())
						{
							$errorValue = $e->getString();
							$errorField = '';

							$errorID = $e->getId();
							if (strlen($errorID) > 0)
							{
								$errorField = $errorID;
								$arResult['ErrorFields'][] = $errorField;
							}

							$errorMessage[] = $errorValue;
							$errorData[] = [
								'message' => $errorValue,
								'field' => $errorField,
							];
						}
					}
					else
					{
						$bFirstStepSuccess = true;
					}
				}
				else
				{
					$arFields['=DATE_UPDATE'] = CDatabase::currentTimeFunction();
					$arFields['=DATE_ACTIVITY'] = CDatabase::currentTimeFunction();

					$arResult["GROUP_ID"] = CSocNetGroup::update($arParams["GROUP_ID"], $arFields, $bAutoSubscribe);

					if (
						!$arResult["GROUP_ID"]
						&& ($e = $APPLICATION->getException())
					)
					{
						$errorValue = $e->getString();
						$errorField = '';

						$errorID = $e->getId();
						if ($errorID === 'ERROR_IMAGE_ID')
						{
							$errorField = 'GROUP_IMAGE_ID';
							$arResult['ErrorFields'][] = $errorField;
						}
						elseif (
							isset($e->messages)
							&& is_array($e->messages)
							&& isset($e->messages[0]["id"])
						)
						{
							$errorField = $e->messages[0]['id'];
							$arResult['ErrorFields'][] = $errorField;
						}

						$errorMessage[] = $errorValue;
						$errorData[] = [
							'message' => $errorValue,
							'field' => $errorField,
						];
					}
					else
					{
						if ((int)$arResult['POST']['OWNER_ID'] !== $ownerId)
						{
							CSocNetUserToGroup::setOwner($ownerId, $arParams["GROUP_ID"], $arResult["POST"]);
						}

						$rsSite = CSite::getList("sort", "desc", Array("ACTIVE" => "Y"));
						while($arSite = $rsSite->Fetch())
						{
							BXClearCache(true, "/".$arSite["ID"]."/bitrix/search.tags.cloud/");
						}
					}
				}

				if ($arResult["GROUP_ID"] > 0)
				{
					$plusList = (
						$arParams["GROUP_ID"] > 0
							? array_diff($moderatorIdList, $arResult["POST"]["MODERATOR_IDS"])
							: $moderatorIdList
					);

					$minusList = (
						$arParams["GROUP_ID"] > 0
							? array_diff($arResult["POST"]["MODERATOR_IDS"], $moderatorIdList)
							: []
					);

					if (!empty($minusList))
					{
						$relationIdList = [];

						$resRelation = UserToGroupTable::getList(array(
							'filter' => array(
								'GROUP_ID' => $arResult["GROUP_ID"],
								'@USER_ID' => $minusList
							),
							'select' => array('ID')
						));
						while($relation = $resRelation->fetch())
						{
							$relationIdList[] = $relation['ID'];
						}

						if (!empty($relationIdList))
						{
							CSocNetUserToGroup::TransferModerator2Member($arResult["currentUserId"], $arResult["GROUP_ID"], $relationIdList);
						}
					}

					UserToGroup::addModerators([
						'group_id' => $arResult["GROUP_ID"],
						'user_id' => array_unique($plusList),
						'current_user_id' => $arResult["currentUserId"]
					]);

					if (!empty($arUserIDs))
					{
						$arUserIDs = array_filter($arUserIDs, static function($value) use ($plusList) {
							return !in_array($value, $plusList, true);
						});
					}

					if (!empty($plusList))
					{
						foreach($plusList as $moderatorId)
						{
							UserToGroup::addInfoToChat(array(
								'group_id' => $arResult["GROUP_ID"],
								'user_id' => $moderatorId,
								'action' => UserToGroup::CHAT_ACTION_IN,
								'sendMessage' => false,
								'role' => UserToGroupTable::ROLE_MODERATOR
							));
						}
					}

					if (
						isset($_POST['GROUP_THEME_ID'])
						&& CModule::includeModule('intranet')
					)
					{
						try
						{
							$themePicker = new ThemePicker(SITE_TEMPLATE_ID, SITE_ID, $USER->getId(), ThemePicker::ENTITY_TYPE_SONET_GROUP, (int)$arResult['GROUP_ID']);
							$themePicker->setCurrentThemeId($_POST['GROUP_THEME_ID']);
							unset($themePicker);
						}
						catch (ArgumentException $exception)
						{
							$errorValue = $exception->getMessage();
							$errorField = 'GROUP_THEME_ID';

							$errorMessage[] = $errorValue;
							$arResult['ErrorFields'][] = $errorField;
							$errorData[] = [
								'message' => $errorValue,
								'field' => $errorField,
							];
						}
					}
				}
			}

			if (
				empty($errorMessage)
				&& array_key_exists("TAB", $arResult)
				&& $arResult["TAB"] !== "edit"
			)
			{
				$arResult["GROUP_ID"] = $arParams["GROUP_ID"];
			}

			if (
				!empty($arImageID)
				&& strlen($arImageID["tmp_name"] ?? '') > 0
			)
			{
				CFile::ResizeImageDeleteCache($arImageID);
			}

			$successfullUserIdList = [];

			if (!empty($errorMessage))
			{
				$arResult["ErrorMessage"] = implode('<br />', $errorMessage);
				$arResult["bVarsFromForm"] = true;
			}
			elseif ($arResult["GROUP_ID"] > 0)
			{
				/* features */
				if (!array_key_exists('TAB', $arResult) || $arResult['TAB'] === 'edit')
				{
					foreach ($arResult["POST"]["FEATURES"] as $feature => $arFeature)
					{
						$idTmp = CSocNetFeatures::setFeature(
							SONET_ENTITY_GROUP,
							$arResult["GROUP_ID"],
							$feature,
							(($_POST[$feature . '_active'] ?? '') === 'Y'),
							(
								strlen($_REQUEST[$feature."_name"]) > 0
									? $_REQUEST[$feature."_name"]
									: (strlen($arFeature["FeatureName"]) > 0 ? $arFeature["FeatureName"] : false)
							)
						);

						if (
							$arParams["GROUP_ID"] <= 0
							&& $feature === 'chat'
						)
						{
							CUserOptions::setOption('socialnetwork', 'default_chat_create_default', ($_POST[$feature . '_active'] === 'Y' ? 'Y' : 'N'));
						}

						if (!$idTmp)
						{
							if ($e = $APPLICATION->GetException())
							{
								$errorValue = $e->getString();

								$errorMessage[] = $errorValue;
								$errorData[] = [
									'message' => $errorValue,
									'field' => '',
								];
							}
						}
						else
						{
							$bSecondStepSuccess = true;
						}
					}
				}

				/* invite */
				if (
					empty($errorMessage)
					&& (
						!array_key_exists("TAB", $arResult)
						|| $arResult['TAB'] === 'invite'
					)
				)
				{
					if (
						CModule::IncludeModule('extranet')
						&& CModule::IncludeModule('intranet')
					)
					{
						$externalAuthIdList = ComponentHelper::checkPredefinedAuthIdList(array('bot', 'imconnector', 'replica'));

						if (
							($_POST['EXTRANET_INVITE_ACTION'] ?? '') === 'invite'
							&& strlen($_POST["EMAILS"]) > 0
						)
						{
							if ($_POST["MESSAGE_TEXT"] != $inviteMessageTextDefault)
							{
								CUserOptions::setOption("socialnetwork", "invite_message_text", $_POST["MESSAGE_TEXT"]);
							}

							$arEmail = array();
							$arIntranetUsersEmails = array();
							$arEmailOriginal = preg_split("/[\n\r\t\\,;]+/", $_POST["EMAILS"]);

							$emailCnt = 0;
							foreach($arEmailOriginal as $addr)
							{
								if ($emailCnt >= 100)
								{
									break;
								}

								if(strlen($addr) > 0 && check_email($addr))
								{
									$addrX = "";
									$phraseX = "";
									$white_space = "(?:(?:\\r\\n)?[ \\t])";
									$spec = '()<>@,;:\\\\".\\[\\]';
									$cntl = '\\000-\\037\\177';
									$dtext = "[^\\[\\]\\r\\\\]";
									$domain_literal = "\\[(?:$dtext|\\\\.)*\\]$white_space*";
									$quoted_string = "\"(?:[^\\\"\\r\\\\]|\\\\.|$white_space)*\"$white_space*";
									$atom = "[^$spec $cntl]+(?:$white_space+|\\Z|(?=[\\[\"$spec]))";
									$word = "(?:$atom|$quoted_string)";
									$localpart = "$word(?:\\.$white_space*$word)*";
									$sub_domain = "(?:$atom|$domain_literal)";
									$domain = "$sub_domain(?:\\.$white_space*$sub_domain)*";
									$addr_spec = "$localpart\\@$white_space*$domain";
									$phrase = "$word*";

									if (preg_match("/$addr_spec/", $addr, $arMatches))
									{
										$addrX = $arMatches[0];
									}

									if (preg_match("/$localpart/", $addr, $arMatches))
									{
										$phraseX = trim(trim($arMatches[0]), "\"");
									}

									$arEmail[] = array("EMAIL" => $addrX, "NAME" => $phraseX);
									$emailCnt++;
								}
							}

							if (!empty($arEmail))
							{
								$userData = array(
									"GROUP_ID" => CIntranetInviteDialog::getUserGroups($this->getSiteId(), true)
								);
								$invitedUserIdList = [];

								foreach($arEmail as $email)
								{
									$arUser = array();
									$arFilter = array(
										"ACTIVE" => "Y",
										"=EMAIL" => $email["EMAIL"]
									);

									if (!empty($externalAuthIdList))
									{
										$arFilter['!EXTERNAL_AUTH_ID'] = $externalAuthIdList;
									}

									$userID = 0;

									$rsUser = CUser::GetList(
										"id",
										"asc",
										$arFilter,
										array(
											"FIELDS" => array("ID", "EXTERNAL_AUTH_ID", "CONFIRM_CODE"),
											"SELECT" => array("UF_DEPARTMENT")
										)
									);
									if ($arUser = $rsUser->Fetch())
									{
										if (
											(int)$arUser['ID'] === $ownerId
											|| in_array((int)$arUser['ID'], $moderatorIdList, true)
										)
										{
											continue;
										}

										//if user with this e-mail is registered, but is external user
										if ($arUser['EXTERNAL_AUTH_ID'] === 'email')
										{
											$ID_TRANSFERRED = CIntranetInviteDialog::TransferEmailUser($arUser["ID"], array(
												"SITE_ID" => $this->getSiteId(),
												"GROUP_ID" => $userData["GROUP_ID"]
											));

											if (!$ID_TRANSFERRED)
											{
												if ($e = $APPLICATION->GetException())
												{
													$errorValue = $e->getString();

													$errorMessage[] = $errorValue;
													$errorData[] = [
														'message' => $errorValue,
														'field' => '',
													];
												}
											}
											else
											{
												$arUserIDs[] = $ID_TRANSFERRED;
											}
										}
										elseif (
											empty($arUser["UF_DEPARTMENT"])
											|| (
												is_array($arUser["UF_DEPARTMENT"])
												&& (int)$arUser["UF_DEPARTMENT"][0] <= 0
											)
										)
										{
											if (!empty($arUser["CONFIRM_CODE"]))
											{
												CIntranetInviteDialog::reinviteExtranetUser($this->getSiteId(), $arUser['ID']);
											}

											$arUserIDs[] = $userID = (int)$arUser["ID"];
										}
										else
										{
											$arIntranetUsersEmails[] = $email["EMAIL"];
											continue;
										}
									}
									else
									{
										$userData["EMAIL"] = $email["EMAIL"];
										$userData["LOGIN"] = $email["EMAIL"];
										$userData["CONFIRM_CODE"] = \Bitrix\Main\Security\Random::getString(8, true);

										$name = $last_name = "";
										if ($email["NAME"] <> '')
										{
											[$name, $last_name] = explode(" ", $email["NAME"]);
										}
										$userData["NAME"] = $name;
										$userData["LAST_NAME"] = $last_name;

										$ID = CIntranetInviteDialog::RegisterUser($userData, $this->getSiteId());

										if (is_array($ID))
										{
											foreach ($ID as $strErrorTmp)
											{
												$errorValue = $strErrorTmp;

												$errorMessage[] = $errorValue;
												$errorData[] = [
													'message' => $errorValue,
													'field' => '',
												];
											}
										}
										else
										{
											$invitedUserIdList[] = $ID;
											$arUserIDs[] = (int)$ID;
											$userData['ID'] = $ID;
											CIntranetInviteDialog::InviteUser($userData, htmlspecialcharsbx($_POST["MESSAGE_TEXT"]));
										}
									}
								}

								if (!empty($invitedUserIdList))
								{
									Invitation::add([
										'USER_ID' => $invitedUserIdList,
										'TYPE' => Invitation::TYPE_EMAIL
									]);
								}
							}

							if (!empty($errorMessage))
							{
								$arResult["ErrorFields"][] = "EXTRANET_BLOCK";
							}
						}
						elseif (
							($_POST['EXTRANET_INVITE_ACTION'] ?? '') === 'add'
							&& CModule::IncludeModule("intranet")
						)
						{
							$userData = array(
								"ADD_EMAIL" => $_POST["ADD_EMAIL"],
								"ADD_NAME" => $_POST["ADD_NAME"],
								"ADD_LAST_NAME" => $_POST["ADD_LAST_NAME"],
								"ADD_SEND_PASSWORD" => $_POST["ADD_SEND_PASSWORD"]
							);

							$arFilter = array(
								"EMAIL" => $userData["ADD_EMAIL"]
							);
							if (!empty($externalAuthIdList))
							{
								$arFilter['!EXTERNAL_AUTH_ID'] = $externalAuthIdList;
							}

							$rsUser = CUser::GetList(
								"id",
								"asc",
								$arFilter,
								array(
									"FIELDS" => array("ID", "EXTERNAL_AUTH_ID")
								)
							);
							if (
								($arUser = $rsUser->Fetch())
								&& ($arUser['EXTERNAL_AUTH_ID'] === 'email')
							)
							{
								$ID_ADDED = 0;
								$ID_TRANSFERRED = CIntranetInviteDialog::TransferEmailUser($arUser["ID"], array(
									"SITE_ID" => $this->getSiteId(),
									"NAME" => $userData["ADD_NAME"],
									"LAST_NAME" => $userData["ADD_LAST_NAME"]
								));

								if (!$ID_TRANSFERRED)
								{
									if($e = $APPLICATION->GetException())
									{
										$errorValue = $e->getString();

										$errorMessage[] = $errorValue;
										$errorData[] = [
											'message' => $errorValue,
											'field' => '',
										];
									}
								}
							}
							else
							{
								$ID_ADDED = (int)CIntranetInviteDialog::AddNewUser($this->getSiteId(), $userData, $strError);
							}

							if ($ID_ADDED <= 0)
							{
								$errorValue = $strError;
								$errorField = 'EXTRANET_BLOCK';

								$errorMessage[] = $errorValue;
								$arResult['ErrorFields'][] = $errorField;
								$errorData[] = [
									'message' => $errorValue,
									'field' => $errorField,
								];
							}
							else
							{
								$arUserIDs[] = $ID_ADDED;
							}
						}
					}

					if (
						isset($arUserIDs)
						&& is_array($arUserIDs)
					)
					{
						foreach($arUserIDs as $key => $value)
						{
							if ($value === $arResult["currentUserId"])
							{
								unset($arUserIDs[$key]);
							}
						}
					}

					// send invitations and add users

					if (
						(
							!array_key_exists("TAB", $arResult)
							|| $arResult['TAB'] === 'edit'
						)
						&& isset($ownerId)
						&& $ownerId !== $arResult['currentUserId']
						&& !$arResult['isCurrentUserAdmin'] // not session admin
						&& !in_array($arResult['currentUserId'], $moderatorIdList, true)
					)
					{
						if (CSocNetUserToGroup::add(array(
							"USER_ID" => $arResult["currentUserId"],
							"GROUP_ID" => $arResult["GROUP_ID"],
							"ROLE" => UserToGroupTable::ROLE_USER,
							"=DATE_CREATE" => CDatabase::currentTimeFunction(),
							"=DATE_UPDATE" => CDatabase::currentTimeFunction(),
							"MESSAGE" => "",
							"INITIATED_BY_TYPE" => UserToGroupTable::INITIATED_BY_GROUP,
							"INITIATED_BY_USER_ID" => $arResult["currentUserId"],
							"SEND_MAIL" => "N"
						)))
						{
							UserToGroup::addInfoToChat(array(
								'group_id' => $arResult["GROUP_ID"],
								'user_id' => $arResult["currentUserId"],
								'action' => UserToGroup::CHAT_ACTION_IN,
								'sendMessage' => false,
								'role' => UserToGroupTable::ROLE_USER
							));
						}
					}

					if (
						!empty($arUserIDs)
						&& is_array($arUserIDs)
					)
					{
						foreach ($arUserIDs as $user_id)
						{
							$canInviteGroup = CSocNetUserPerms::CanPerformOperation($arResult["currentUserId"], $user_id, "invitegroup", $arResult['isCurrentUserAdmin']);
							if (!$canInviteGroup)
							{
								continue;
							}

							$res = UserToGroupTable::getList([
								'filter' => [
									'=USER_ID' => $user_id,
									'=GROUP_ID' => $arResult['GROUP_ID'],
								],
								'select' => [ 'ID', 'ROLE', 'INITIATED_BY_TYPE' ]
							]);
							$relationFields = $res->fetch();

							if (!$relationFields)
							{
								if (
									!CSocNetUserToGroup::SendRequestToJoinGroup(
										$arResult["currentUserId"],
										$user_id,
										$arResult["GROUP_ID"],
										$_POST["MESSAGE"] ?? ''
									)
								)
								{
									$rsUser = CUser::GetByID($user_id);
									if ($arUser = $rsUser->Fetch())
									{
										$arErrorUsers[] = array(
											CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, ($arParams['SHOW_LOGIN'] !== 'N')),
											CSocNetUserPerms::CanPerformOperation($arResult["currentUserId"], $arUser["ID"], "viewprofile", $arResult['isCurrentUserAdmin'])
												? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]))
												: ''
										);
										if ($e = $APPLICATION->GetException())
										{
											$warningMessage[] = $e->GetString();
										}
									}
								}
								elseif (
									is_array($arResult["POST"]["USER_IDS"])
									&& in_array($user_id, $arResult["POST"]["USER_IDS"])
								)
								{
									$successfullUserIdList[] = $user_id;
									$bInvited = true;
									// delete from uninvited users list
									$arKeysFound = array_keys($arResult["POST"]["USER_IDS"], $user_id);
									foreach($arKeysFound as $key)
									{
										unset($arResult["POST"]["USER_IDS"][$key]);
									}
								}
							}
							else //user already is related to group, don't invite him again
							{
								$rsUser = CUser::getById($user_id);
								if ($arRes = $rsUser->fetch())
								{
									$nameFormatted = CUser::formatName(CSite::getNameFormat(), $arRes, true);

									switch ($relationFields['ROLE'])
									{
										case SONET_ROLES_BAN:
											$warningMessage[] = str_replace("#NAME#", $nameFormatted, GetMessage("SONET_GCE_USERNAME_BANNED_IN_GROUP"));
											break;
										case SONET_ROLES_REQUEST:

											if ($relationFields['INITIATED_BY_TYPE'] === UserToGroupTable::INITIATED_BY_USER)
											{
												try
												{
													Helper\Workgroup::acceptIncomingRequest([
														'groupId' => $arResult['GROUP_ID'],
														'userId' => $user_id,
													]);
												}
												catch(\Exception $e)
												{
													$warningMessage[] = $e->getMessage();
												}
											}
											else
											{
												$warningMessage[] = str_replace("#NAME#", $nameFormatted, GetMessage("SONET_GCE_USERNAME_REQUEST_SENT"));
											}

											break;
										default:
											$warningMessage[] = str_replace("#NAME#", $nameFormatted, GetMessage(
												!empty($_POST["GROUP_PROJECT"])
												&& $_POST['GROUP_PROJECT'] === 'Y'
													? "SONET_GCE_USERNAME_IN_GROUP_PROJECT"
													: "SONET_GCE_USERNAME_IN_GROUP"
											));
											break;
									}
								}
							}
						}

						if (
							!empty($warningMessage)
							&& !in_array("USERS", $arResult["ErrorFields"])
						)
						{
							$errorMessage = array_merge($errorMessage, $warningMessage);
							if (!$bInvited)
							{
								$errorValue = Loc::getMessage('SONET_GCE_NO_USERS');

								$errorMessage[] = $errorValue;
								$errorData[] = [
									'message' => $errorValue,
									'field' => '',
								];
							}

							$warningMessage = [];
						}
					}

					if (
						isset($arDepartmentIDs)
						&& is_array($arDepartmentIDs)
						&& !empty($arDepartmentIDs)
					)
					{
						if (
							$arParams["GROUP_ID"] > 0
							&& !empty($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"])
							&& !empty($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"]["VALUE"])
							&& is_array($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"]["VALUE"])
						)
						{
							$arDepartmentIDs = array_unique(array_map('intval', array_merge($arDepartmentIDs, $arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"]["VALUE"])));
						}

						CSocNetGroup::Update($arResult["GROUP_ID"], array(
							'UF_SG_DEPT' => $arDepartmentIDs
						));
					}
				}

				if (!empty($arIntranetUsersEmails) && is_array($arIntranetUsersEmails))
				{
					//if some e-mails belong to internal users and can't be used for invitation
					if (count($arIntranetUsersEmails) === 1)
					{
						$warningMessage[] = str_replace("#EMAIL#", HtmlSpecialCharsEx(implode("", $arIntranetUsersEmails)), GetMessage("SONET_GCE_CANNOT_EMAIL_ADD"));
					}
					elseif (count($arIntranetUsersEmails) > 1)
					{
						$warningMessage[] = str_replace("#EMAIL#", HtmlSpecialCharsEx(implode(", ", $arIntranetUsersEmails)), GetMessage("SONET_GCE_CANNOT_EMAILS_ADD"));
					}
				}

				//if no users were invited
				if (
					($arResult['TAB'] ?? '') === 'invite'
					&& empty($arUserIDs)
					&& empty($arDepartmentIDs)
				)
				{
					$errorValue = Loc::getMessage('SONET_GCE_NO_USERS');
					$errorField = 'USERS';

					$errorMessage[] = $errorValue;
					$arResult['ErrorFields'][] = $errorField;
					$errorData[] = [
						'message' => $errorValue,
						'field' => $errorField,
					];
				}
			}

			if (
				empty($errorMessage)
				&& empty($warningMessage)
			)
			{
				if ($arResult["IS_IFRAME"])
				{
					if (
						$arResult["IS_POPUP"]
						|| ($_GET['IFRAME_TYPE'] ?? null) === 'SIDE_SLIDER'
					)
					{
						if (!array_key_exists("TAB", $arResult))
						{
							$groupPathTemplate = (
								isset($arParams['FIRST_ROW'])
								&& $arParams['FIRST_ROW'] === 'project'
									? Helper\Path::get('group_tasks_path_template')
									: $arParams['PATH_TO_GROUP']
							);

							$redirectPath = CComponentEngine::MakePathFromTemplate(
								$arParams['PATH_TO_GROUP'],
								[
									'group_id' => $arResult['GROUP_ID'],
									'user_id' => $arResult['currentUserId'],
								]
							);
						}
						else
						{
							$redirectPath = "";
						}
					}
					else
					{
						if (!array_key_exists("TAB", $arResult))
						{
							$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["GROUP_ID"], "user_id" => $arResult["currentUserId"]));
						}
						elseif (($arResult['TAB'] ?? '') === 'edit')
						{
							$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["GROUP_ID"], "user_id" => $arResult["currentUserId"]));
						}
						elseif (($arResult['TAB'] ?? '') === 'invite')
						{
							$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["GROUP_ID"], "user_id" => $arResult["currentUserId"]));
						}

						$redirectPath .= (mb_strpos($redirectPath, "?") === false ? "?" :  "&")."POPUP=Y&SONET=Y";
						if (($arResult['TAB'] ?? '') === 'invite')
						{
							$redirectPath .= (mb_strpos($redirectPath, "?") === false ? "?" :  "&")."tab=invite";
						}
						elseif (($arResult['TAB'] ?? '') === 'edit')
						{
							$redirectPath .= (mb_strpos($redirectPath, "?") === false ? "?" :  "&")."tab=edit";
						}

						if ($bFirstStepSuccess)
						{
							$redirectPath .= "&CALLBACK=GROUP&GROUP_ID=".$arResult["GROUP_ID"];
						}
						else
						{
							$redirectPath .= "&CALLBACK=REFRESH";
						}
					}
				}
				else
				{
					$redirectPath = CComponentEngine::MakePathFromTemplate(
						(isset($arFields['PROJECT']) && $arFields['PROJECT'] === 'Y' ? $arParams['PATH_TO_GROUP_GENERAL'] : $arParams['PATH_TO_GROUP']),
						array(
							"group_id" => $arResult["GROUP_ID"],
							"user_id" => $arResult["currentUserId"]
						)
					);
				}

				if (isset($_POST['ajax_request']) && $_POST['ajax_request'] === 'Y')
				{
					$groupFieldsList = array(
						'FIELDS' => array(),
						'UF' => array(),
					);
					$selectorGroupId = $arResult['GROUP_ID'];
					WorkgroupForm::processWorkgroupData(
						$arResult['GROUP_ID'], // reference
						$groupFieldsList['UF'],
						$groupFieldsList['FIELDS'],
						'edit'
					);

					$APPLICATION->RestartBuffer();
					echo CUtil::PhpToJsObject([
						'MESSAGE' => 'SUCCESS',
						'URL' => $redirectPath,
						'GROUP' => array_merge($groupFieldsList, [ 'ID' => $arResult['GROUP_ID'] ]),
						'SELECTOR_GROUPS' => Json::encode(
							EntitySelector\ProjectProvider::makeItems(
								EntitySelector\ProjectProvider::getProjects(
									array_merge(
										$arParams['PROJECT_OPTIONS'],
										[ 'projectId' => $selectorGroupId ]
									)
								)
							)
						),
						'ACTION' => (
							!array_key_exists("TAB", $arResult)
								? 'create'
								: $arResult["TAB"]
						)
					]);
					require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
					die();
				}

				$APPLICATION->RestartBuffer();
				LocalRedirect($redirectPath);
			}
			else
			{
				$arResult["WarningMessage"] = implode('<br />', $warningMessage);
				$arResult["ErrorMessage"] = implode('<br />', $errorMessage);

				if (!array_key_exists("TAB", $arResult))
				{
					if ($bFirstStepSuccess)
					{
						WorkgroupForm::processWorkgroupData($arResult["GROUP_ID"], $arResult["GROUP_PROPERTIES"], $arResult["POST"]);
						$arResult["CALLBACK"] = "EDIT";
					}

					if ($bSecondStepSuccess)
					{
						WorkgroupForm::processWorkgroupFeatures($arResult['GROUP_ID'], $arResult['POST']['FEATURES']);
					}
				}

				if (
					is_array($arResult["POST"]["USER_IDS"])
					&& !empty($arResult["POST"]["USER_IDS"])
				)
				{
					$dbUsers = CUser::GetList(
						[ 'last_name'=>'asc', 'IS_ONLINE'=>'desc' ],
						'',
						array(
							"ID" => implode("|", $arResult["POST"]["USER_IDS"]),
						),
						array(
							"FIELDS" => array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION"),
							"SELECT" => array("UF_DEPARTMENT")
						)
					);

					while($arUser = $dbUsers->Fetch())
					{
						$arUserTmp = array(
							"id" => "U".$arUser["ID"],
							"entityId" => $arUser["ID"],
							"name" => trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser)),
							"avatar" => "",
							"desc" => $arUser["WORK_POSITION"] ?: ($arUser["PERSONAL_PROFESSION"] ?: "&nbsp;")
						);
						$arResult["POST"]["USERS_FOR_JS"]["U".$arUser["ID"]] = $arUserTmp;

						if (
							$arResult["bExtranetInstalled"]
							&& $arResult['POST']['IS_EXTRANET_GROUP'] === 'Y'
						)
						{
							$arResult["POST"]["USERS_FOR_JS_".(empty($arUser["UF_DEPARTMENT"]) || (is_array($arUser["UF_DEPARTMENT"]) && (int)$arUser["UF_DEPARTMENT"][0]
								<= 0) ? "E" : "I")]["U".$arUser["ID"]] = $arUserTmp;
						}
					}
				}

				if (isset($_POST['ajax_request']) && $_POST['ajax_request'] === 'Y')
				{
					ob_end_clean();

					$wizardStepProcessed = '';
					if ($bSecondStepSuccess)
					{
						$wizardStepProcessed = 'edit';
					}
					else if ($bFirstStepSuccess)
					{
						$wizardStepProcessed = 'create';
					}

					$arRes = array(
						'ERROR' => implode('<br />', $errorMessage),
						'WARNING' => implode('<br />', $warningMessage),
						'USERS_ID' => $arResult["POST"]["USER_IDS"],
						'SUCCESSFULL_USERS_ID' => $successfullUserIdList,
						'ERROR_DATA' => $errorData,
						'CREATED_GROUP_ID' => $createdGroupId,
						'WIZARD_STEP_PROCESSED' => $wizardStepProcessed,
					);
					CMain::finalActions(Json::encode($arRes));
					die();
				}
			}
		}
		else
		{
			$arResult["GROUP_ID"] = $arParams["GROUP_ID"];
		}

		$arResult['isScrumProject'] = false;
		if ((int)$arResult['GROUP_ID'] > 0)
		{
			$group = Bitrix\Socialnetwork\Item\Workgroup::getById($arResult["GROUP_ID"]);
			$arResult['isScrumProject'] = ($group && $group->isScrumProject());
		}

		if ($arResult['ShowForm'] === 'Input')
		{
			if (!array_key_exists('TAB', $arResult) || $arResult['TAB'] === 'edit')
			{
				$arResult["Subjects"] = array();
				$dbSubjects = CSocNetGroupSubject::GetList(
					array("SORT"=>"ASC", "NAME" => "ASC"),
					array("SITE_ID" => $this->getSiteId()),
					false,
					false,
					array("ID", "NAME")
				);
				while ($arSubject = $dbSubjects->getNext())
				{
					$arResult["Subjects"][$arSubject["ID"]] = $arSubject["NAME"];
				}

				$arResult["InitiatePerms"] = \Bitrix\Socialnetwork\Item\Workgroup::getInitiatePermOptionsList(array(
					'project' => false
				));
				$arResult['InitiatePermsProject'] = \Bitrix\Socialnetwork\Item\Workgroup::getInitiatePermOptionsList(array(
					'project' => true
				));
				$arResult['SpamPerms'] = \Bitrix\Socialnetwork\Item\Workgroup::getSpamPermOptionsList();
			}
		}

		if (
			$arResult['isScrumProject']
			&& $arResult['POST']['MODERATOR_IDS']
		)
		{
			$arResult["POST"]["MODERATOR_IDS"] = array_diff(
				$arResult["POST"]["MODERATOR_IDS"],
				[ $group->getScrumMaster() ]
			);
		}

		$arResult['ScrumSprintDurationValues'] = Helper\Workgroup::getSprintDurationValues();
		$arResult['ScrumSprintDurationDefaultKey'] = Helper\Workgroup::getSprintDurationDefaultKey();
		$arResult['ScrumTaskResponsible'] = Helper\Workgroup::getScrumTaskResponsibleList();

		if (
			!array_key_exists("TAB", $arResult)
			|| $arResult['TAB'] === 'invite'
		)
		{
			$arResult["DEST_SORT"] = CSocNetLogDestination::GetDestinationSort(array(
				"DEST_CONTEXT" => $arResult['destinationContextUsers'],
				"CODE_TYPE" => 'U'
			));
			$arResult["DEST_USERS_LAST"] = array();
			$arResult["LAST_SORT"] = CSocNetLogDestination::fillLastDestination($arResult["DEST_SORT"], $arResult["DEST_USERS_LAST"]);

			if (isset($arResult["DEST_USERS_LAST"]['USERS']))
			{
				$arResult["DEST_USERS_LAST"] = $arResult["DEST_USERS_LAST"]['USERS'];
			}

			$arResult["siteDepartmentID"] = COption::GetOptionString("main", "wizard_departament", false, $this->getSiteId(), true);

			if (
				is_array($arResult["DEST_USERS_LAST"])
				&& !empty($arResult["DEST_USERS_LAST"])
			)
			{
				foreach ($arResult["DEST_USERS_LAST"] as $key => $user_code)
				{
					if ($user_code === 'U'.$arResult["currentUserId"])
					{
						unset($arResult["DEST_USERS_LAST"][$key]);
						break;
					}
				}
			}

			if (
				is_array($arResult["DEST_USERS_LAST"])
				&& !empty($arResult["DEST_USERS_LAST"])
			)
			{
				$arLastUserID = array();

				foreach ($arResult["DEST_USERS_LAST"] as $key => $user_code)
				{
					if ($user_code === 'U'.$arResult["currentUserId"])
					{
						unset($arResult["DEST_USERS_LAST"][$key]);
						continue;
					}

					if (preg_match('/^U(\d+)$/', $key, $match))
					{
						$arLastUserID[] = $match[1];
					}
				}

				if (!empty($arLastUserID))
				{
					$dbUsers = CUser::GetList(
						Array('last_name'=>'asc', 'IS_ONLINE'=>'desc'),
						'',
						array(
							"ACTIVE" => "Y",
							"ID" => implode("|", $arLastUserID),
						),
						array(
							"FIELDS" => array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION"),
							"SELECT" => array("UF_DEPARTMENT")
						)
					);

					while($arUser = $dbUsers->Fetch())
					{
						if ((int)$arResult["siteDepartmentID"] > 0)
						{
							$arUserGroupCode = CAccess::GetUserCodesArray($arUser["ID"]);

							if (!in_array("DR" . (int)$arResult["siteDepartmentID"], $arUserGroupCode, true))
							{
								continue;
							}
						}

						$arFileTmp = CFile::ResizeImageGet(
							$arUser["PERSONAL_PHOTO"],
							array('width' => 32, 'height' => 32),
							BX_RESIZE_IMAGE_EXACT
						);

						$arUserTmp = array(
							"id" => "U".$arUser["ID"],
							"entityId" => $arUser["ID"],
							"name" => trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser)),
							"avatar" => (empty($arFileTmp['src'])? '': $arFileTmp['src']),
							"desc" => $arUser["WORK_POSITION"] ?: ($arUser["PERSONAL_PROFESSION"] ?: "&nbsp;")
						);

						$key = (
							!$arResult["bExtranetInstalled"]
								? "USERS_FOR_JS"
								: (
									empty($arUser["UF_DEPARTMENT"])
									|| (
										is_array($arUser["UF_DEPARTMENT"])
										&& (int)$arUser["UF_DEPARTMENT"][0] <= 0
									)
										? "USERS_FOR_JS_E"
										: "USERS_FOR_JS_I"
							)
						);
						if (!array_key_exists("U".$arUser["ID"], $arResult["POST"][$key]))
						{
							$arResult["POST"][$key]["U".$arUser["ID"]] = $arUserTmp;
						}
					}
				}
			}
		}

		$arResult["arSocNetFeaturesSettings"] = CSocNetAllowed::getAllowedFeatures();
	}

	$arResult["step1Display"] = (
		$arResult['USE_PRESETS'] === 'Y'
		&& $arParams["GROUP_ID"] <= 0
		&& empty($arResult["preset"])
	);

	$arResult["URL_CANCEL"] = (
		$arParams["GROUP_ID"] > 0
			? $arResult["Urls"]["Group"]
			: ComponentHelper::getWorkgroupSEFUrl()
	);
}

Loader::includeModule('intranet');

$arResult['PageTitle'] = Loc::getMessage('SONET_GCE_TITLE_CREATE');

if ($arParams['GROUP_ID'] > 0)
{
	if ($arResult['isScrumProject'])
	{
		$arResult['PageTitle'] = (($arResult['TAB'] ?? '') === 'invite' ? Loc::getMessage('SONET_GCE_TITLE_INVITE_SCRUM') : Loc::getMessage('SONET_GCE_TITLE_EDIT_SCRUM'));
	}
	elseif ($arResult['POST']['PROJECT'] === 'Y')
	{
		$arResult['PageTitle'] = (($arResult['TAB'] ?? '') === 'invite' ? Loc::getMessage('SONET_GCE_TITLE_INVITE_PROJECT') : Loc::getMessage('SONET_GCE_TITLE_EDIT_PROJECT'));
	}
	else
	{
		$arResult['PageTitle'] = (($arResult['TAB'] ?? '') === 'invite' ? Loc::getMessage('SONET_GCE_TITLE_INVITE') : Loc::getMessage('SONET_GCE_TITLE_EDIT'));
	}
}

$APPLICATION->SetTitle($arResult['PageTitle']);

$this->IncludeComponentTemplate();
