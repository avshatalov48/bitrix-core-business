<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\Web\Json;

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CCacheManager $CACHE_MANAGER
 * @global CDatabase $DB
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

global $CACHE_MANAGER;

$bSocialNetwork = IsModuleInstalled('socialnetwork');
$bIntranet = IsModuleInstalled('intranet');

if ($bSocialNetwork)
{
	$bUseTooltip = COption::GetOptionString("socialnetwork", "allow_tooltip", "Y") == "Y";

	if(!defined("BXMAINUSERLINK"))
	{
		define("BXMAINUSERLINK", true);
		if ($bUseTooltip)
			CJSCore::Init(array("ajax", "tooltip"));
		else
			CJSCore::Init(array("ajax"));
	}
}
else
{
	$_GET["MUL_MODE"] = "";
}

$arParams['AJAX_CALL'] = $_GET["MUL_MODE"] ?? '';
$arResult["stylePrefix"] = (isset($_REQUEST["MODE"]) && $_REQUEST["MODE"] == 'UI' ? 'bx-ui-tooltip' : 'bx-user');
if ($bSocialNetwork)
{
	if (!array_key_exists("SHOW_FIELDS", $arParams) || !$arParams["SHOW_FIELDS"])
	{
		$arParams["SHOW_FIELDS"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_fields", 's:0:"";'), ['allowed_classes' => false]);
		if (!is_array($arParams["SHOW_FIELDS"]))
		{
			$arParams["SHOW_FIELDS"] = (
				$bIntranet
					? array(
						"EMAIL",
						"WORK_PHONE",
						"PERSONAL_PHOTO",
						"PERSONAL_CITY",
						"WORK_COMPANY",
						"WORK_POSITION",
						"MANAGERS",
					)
					: array(
						"PERSONAL_ICQ",
						"PERSONAL_BIRTHDAY",
						"PERSONAL_PHOTO",
						"PERSONAL_CITY",
						"WORK_COMPANY",
						"WORK_POSITION"
					)
			);
		}
	}

	if (!array_key_exists("USER_PROPERTY", $arParams) || !$arParams["USER_PROPERTY"])
	{
		$arParams["USER_PROPERTY"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_properties", 's:0:"";'), ['allowed_classes' => false]);
		if (!is_array($arParams["USER_PROPERTY"]))
		{
			if ($bIntranet)
				$arParams["USER_PROPERTY"] = array(
					"UF_DEPARTMENT",
					"UF_PHONE_INNER",
					"UF_SKYPE",
				);
			else
				$arParams["USER_PROPERTY"] = array(
				);
		}
	}

	if (COption::GetOptionString("socialnetwork", "tooltip_show_rating", "N") == "Y")
		$arParams["USER_RATING"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_rating_id", serialize(array())), ['allowed_classes' => false]);

}

if (
	$bSocialNetwork
	&& !array_key_exists("IS_ONLINE", $arParams)
)
{
	CJSCore::Init();
	require_once(__DIR__."/include.php");
}

if (isset($_GET["USER_ID"]) && intval($_GET["USER_ID"]) > 0)
{
	$arParams["ID"] = $_GET["USER_ID"];
}

$arParams["ID"] = intval($arParams["ID"] ?? 0);

$arContext = array();
if (
	isset($_GET["entityType"])
	&& $_GET["entityType"] <> ''
)
{
	$arContext["ENTITY_TYPE"] = $_GET["entityType"];
}

if (
	isset($_GET["entityId"])
	&& intval($_GET["entityId"]) > 0
)
{
	$arContext["ENTITY_ID"] = intval($_GET["entityId"]);
}

if ($arParams["ID"] <= 0 && $arParams["AJAX_ONLY"] != "Y")
	$arResult["FatalError"] = GetMessage("MAIN_UL_NO_ID").". ";
elseif (empty($arParams["HTML_ID"]) || trim($arParams["HTML_ID"]) === '')
	$arParams["HTML_ID"] = "mul_".RandString(8);

if (!isset($arParams['USE_THUMBNAIL_LIST']) || $arParams['USE_THUMBNAIL_LIST'] != "N")
{
	$arParams['USE_THUMBNAIL_LIST'] = "Y";
	if (!isset($arParams['THUMBNAIL_LIST_SIZE']) || intval($arParams['THUMBNAIL_LIST_SIZE']) <= 0)
		$arParams['THUMBNAIL_LIST_SIZE'] = 30;
}

if (array_key_exists("SHOW_FIELDS", $arParams) && in_array("PERSONAL_PHOTO", $arParams['SHOW_FIELDS']) && intval($arParams['THUMBNAIL_DETAIL_SIZE'] ?? 0) <= 0)
	$arParams['THUMBNAIL_DETAIL_SIZE'] = 100;

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"] ?? 0);
else
	$arParams["CACHE_TIME"] = 0;

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), "", $arParams["NAME_TEMPLATE"]);

$bUseLogin = isset($arParams['SHOW_LOGIN']) && $arParams['SHOW_LOGIN'] === "N" ? false : true;

if (!array_key_exists("DO_RETURN", $arParams))
	$arParams["DO_RETURN"] = "N";

if ($arParams["DO_RETURN"] != "Y")
	$arParams["DO_RETURN"] = "N";

$bNeedGetUser = false;

if (!isset($arResult["FatalError"]) || $arResult["FatalError"] == '')
{
	if (
		$bSocialNetwork
		&& !array_key_exists("IS_ONLINE", $arParams)
		&& (!isset($arParams["AJAX_ONLY"]) || $arParams["AJAX_ONLY"] != "Y")
		&& (!array_key_exists("INLINE", $arParams) || $arParams["INLINE"] != "Y")
	)
	{
		MULChangeOnlineStatus($arParams["ID"], $arParams["HTML_ID"]);
	}

	if (isset($arParams['AJAX_CALL']) && $arParams['AJAX_CALL'] == 'INFO')
	{
		$bNeedGetUser = true;
	}
	elseif(intval($arParams["ID"]) > 0)
	{
		if (
			!array_key_exists("NAME", $arParams)
			|| !array_key_exists("LAST_NAME", $arParams)
			|| !array_key_exists("SECOND_NAME", $arParams)
			|| !array_key_exists("LOGIN", $arParams)
			|| (
				$arParams['USE_THUMBNAIL_LIST'] == "Y"
				&& !array_key_exists("PERSONAL_PHOTO_IMG", $arParams)
			)
		)
		{
			$bNeedGetUser = true;
		}
	}

	if (
		$bSocialNetwork
		&& CModule::IncludeModule('socialnetwork')
	)
	{
		$arResult["CurrentUserPerms"] = (
			$arParams['AJAX_CALL'] == 'INFO'
				? CSocNetUserPerms::InitUserPerms($USER->GetID(), $arParams["ID"], CSocNetUser::IsCurrentUserModuleAdmin())
				: array(
					"Operations" => array(
						"viewprofile" => true,
						"videocall" => true,
						"message" => true
					)
				)
		);

		if (!$bUseTooltip)
		{
			$arResult["USE_TOOLTIP"] = false;
		}

		if ($arParams['AJAX_CALL'] != 'INFO' && isset($arParams["PROFILE_URL_LIST"]) && $arParams["PROFILE_URL_LIST"] <> '') // don't use PROFILE_URL in ajax call because it could be another component inclusion
			$arResult["Urls"]["SonetProfile"] = $arParams["~PROFILE_URL_LIST"];
		elseif ($arParams['AJAX_CALL'] != 'INFO' && isset($arParams["PROFILE_URL"]) && $arParams["PROFILE_URL"] <> '')
			$arResult["Urls"]["SonetProfile"] = $arParams["~PROFILE_URL"];
		elseif(isset($arParams["PATH_TO_SONET_USER_PROFILE"]) && $arParams["PATH_TO_SONET_USER_PROFILE"] <> '')
			$arResult["Urls"]["SonetProfile"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_SONET_USER_PROFILE"], array("user_id" => $arParams["ID"], "USER_ID" => $arParams["ID"], "ID" => $arParams["ID"]));

		if ((!isset($arResult["Urls"]["SonetProfile"]) || $arResult["Urls"]["SonetProfile"] == '') && $bIntranet)
		{
			$arParams['DETAIL_URL'] = COption::GetOptionString('intranet', 'search_user_url', '/user/#ID#/');
			$arParams['DETAIL_URL'] = str_replace(array('#ID#', '#USER_ID#'), array($arParams["ID"], $arParams["ID"]), $arParams['DETAIL_URL']);
		}
		else
		{
			$arParams['DETAIL_URL'] = $arResult["Urls"]["SonetProfile"];
		}
	}
	else
	{
		if (isset($arParams["PROFILE_URL_LIST"]) && $arParams["PROFILE_URL_LIST"] <> '')
		{
			$arParams['DETAIL_URL'] = $arParams["~PROFILE_URL_LIST"];
		}
		elseif (isset($arParams["PROFILE_URL"]) && $arParams["PROFILE_URL"] <> '')
		{
			$arParams['DETAIL_URL'] = $arParams["~PROFILE_URL"];
		}
	}

	$arResult["User"]["DETAIL_URL"] = $tmpUserDetailUrl = $arParams['DETAIL_URL'];

	if ($bNeedGetUser)
	{
		$obCache = new CPHPCache;
		$strCacheID = $arParams["ID"]."_".$arParams["USE_THUMBNAIL_LIST"]."_".$arParams["THUMBNAIL_LIST_SIZE"]."_".$USER->GetID()."_".$bSocialNetwork;

		$path = "/user_card_new_".intval($arParams["ID"] / TAGGED_user_card_size);

		if (
			$arParams['AJAX_CALL'] == 'INFO'
			|| $obCache->StartDataCache($arParams["CACHE_TIME"], $strCacheID, $path)
		)
		{
			if (
				$arParams['AJAX_CALL'] != 'INFO'
				&& defined("BX_COMP_MANAGED_CACHE")
			)
			{
				$CACHE_MANAGER->StartTagCache($path);
				$CACHE_MANAGER->RegisterTag("USER_CARD_".intval($arParams["ID"] / TAGGED_user_card_size));
			}

			$dbUser = CUser::GetByID($arParams["ID"]);
			$arResult["User"] = $dbUser->Fetch();

			if (!$arResult["User"])
			{
				$arResult["FatalError"] = GetMessage("MAIN_UL_NO_ID").". ";
			}
			elseif(
				$arParams['AJAX_CALL'] == 'INFO'
				&& CModule::IncludeModule("socialnetwork")
				&& !CSocNetUser::CanProfileView($USER->GetID(), $arResult["User"], SITE_ID, $arContext)
			)
			{
				$arResult["FatalError"] = GetMessage("MAIN_UL_NO_ID").". ";
			}

			if (
				(!isset($arResult["FatalError"]) || $arResult["FatalError"] == '')
				&& $arParams["USE_THUMBNAIL_LIST"] == "Y"
				&& $arParams['AJAX_CALL'] != 'INFO'
			)
			{
				$iSize = $arParams["THUMBNAIL_LIST_SIZE"];
				$imageFile = false;
				$imageImg = false;

				if (intval($arResult["User"]["PERSONAL_PHOTO"]) <= 0 && $bSocialNetwork)
				{
					switch ($arResult["User"]["PERSONAL_GENDER"])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
							break;
						default:
							$suffix = "unknown";
					}
					$arResult["User"]["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
				}

				if (intval($arResult["User"]["PERSONAL_PHOTO"]) > 0)
				{
					$imageFile = CFile::GetFileArray($arResult["User"]["PERSONAL_PHOTO"]);
					if ($imageFile !== false)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$imageFile,
							array("width" => $iSize, "height" => $iSize),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$imageImg = CFile::ShowImage($arFileTmp["src"], $iSize, $iSize, "border='0'", "");
					}
				}

				if (
					$bSocialNetwork
					&& $arResult["CurrentUserPerms"]["Operations"]["viewprofile"]
				)
				{
					if (isset($arParams["HREF"]) && $arParams["HREF"] <> '')
					{
						$imageUrl = $arParams["HREF"];
					}
					elseif (isset($arResult["User"]["DETAIL_URL"]) && $arResult["User"]["DETAIL_URL"] <> '')
					{
						$imageUrl = $arResult["User"]["DETAIL_URL"];
					}
					else
					{
						$imageUrl = false;
					}
				}
				else
				{
					$imageUrl = false;
				}

				$arResult["User"]["PersonalPhotoImgThumbnail"] = array(
					"Image" => $imageImg,
					"Url" => ($bSocialNetwork && $arResult["CurrentUserPerms"]["Operations"]["viewprofile"] ? : false)
				);
			}
			$arResult["User"]["DETAIL_URL"] = $tmpUserDetailUrl;

			if (CModule::IncludeModule('intranet'))
			{
				$arResult["User"]['MANAGERS'] = CIntranetUtils::GetDepartmentManager($arResult["User"]["UF_DEPARTMENT"], $arResult["User"]["ID"], true);
				foreach($arResult["User"]['MANAGERS'] as $key=>$manager)
				{
					$arResult["User"]['MANAGERS'][$key]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $manager, $bUseLogin, false);
					$arResult["User"]['MANAGERS'][$key]["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SONET_USER_PROFILE"], array("user_id" => $manager["ID"], "USER_ID" => $manager["ID"], "ID" => $manager["ID"]));
				}
			}

			if ($arParams['AJAX_CALL'] != 'INFO')
			{
				$obCache->EndDataCache($arResult);
				if(defined("BX_COMP_MANAGED_CACHE"))
					$CACHE_MANAGER->EndTagCache();
			}
		}
		else
			$arResult = $obCache->GetVars();

		$arResult["ajax_page"] = $APPLICATION->GetCurPageParam("", array("bxajaxid", "logout"));

		if ($bSocialNetwork && CModule::IncludeModule('socialnetwork'))
		{
			$arResult["Urls"]["SonetMessageChat"] = CComponentEngine::MakePathFromTemplate(
				$arParams["~PATH_TO_SONET_MESSAGES_CHAT"] ?? '',
				[
					"user_id" => $arParams["ID"],
					"USER_ID" => $arParams["ID"],
					"ID" => $arParams["ID"]
				]
			);
		}

		if (
			(!isset($arResult["FatalError"]) || $arResult["FatalError"] == '')
			&& $arParams['AJAX_CALL'] == 'INFO'
			&& $bUseTooltip
		)
		{
			$arResult["User"]["PERSONAL_LOCATION"] = GetCountryByID($arResult["User"]["PERSONAL_COUNTRY"]);
			if ($arResult["User"]["PERSONAL_LOCATION"] <> '' && $arResult["User"]["PERSONAL_CITY"] <> '')
				$arResult["User"]["PERSONAL_LOCATION"] .= ", ";
			$arResult["User"]["PERSONAL_LOCATION"] .= $arResult["User"]["PERSONAL_CITY"];

			$arResult["User"]["WORK_LOCATION"] = GetCountryByID($arResult["User"]["WORK_COUNTRY"]);
			if ($arResult["User"]["WORK_LOCATION"] <> '' && $arResult["User"]["WORK_CITY"] <> '')
				$arResult["User"]["WORK_LOCATION"] .= ", ";
			$arResult["User"]["WORK_LOCATION"] .= $arResult["User"]["WORK_CITY"];

			$arResult["Sex"] = array(
				"M" => GetMessage("MAIN_UL_SEX_M"),
				"F" => GetMessage("MAIN_UL_SEX_F"),
			);

			if ($arResult["User"]["PERSONAL_WWW"] <> '')
				$arResult["User"]["PERSONAL_WWW"] = ((!str_contains($arResult["User"]["PERSONAL_WWW"], "http")) ? "http://" : "").$arResult["User"]["PERSONAL_WWW"];

			$arMonths_r = array();
			for ($i = 1; $i <= 12; $i++)
				$arMonths_r[$i] = mb_strtolower(GetMessage('MONTH_'.$i.'_S'));

			$arTmpUser = array(
				"ID" => $arResult["User"]["ID"],
				"NAME" => $arResult["User"]["NAME"],
				"LAST_NAME" => $arResult["User"]["LAST_NAME"],
				"SECOND_NAME" => $arResult["User"]["SECOND_NAME"],
				"LOGIN" => $arResult["User"]["LOGIN"],
				"DETAIL_URL" => $arResult["User"]["DETAIL_URL"],
			);

			if (
				isset($_GET["entityType"])
				&& $_GET["entityType"] <> ''
				&& isset($_GET["entityId"])
				&& intval($_GET["entityId"]) > 0
			)
			{
				$arTmpUser["DETAIL_URL"] .= (!str_contains($arTmpUser["DETAIL_URL"], '?') ? '?' : '&')."entityType=".urlencode($_GET["entityType"])."&entityId=".intval($_GET["entityId"]);
			}

			$rsCurrentUser = CUser::GetById($USER->GetId());
			$arResult["CurrentUser"] = $rsCurrentUser->Fetch();

			if($this->InitComponentTemplate())
			{
				$template = &$this->GetTemplate();
				$arResult["FOLDER_PATH"] = $folderPath = $template->GetFolder();
				$arResult["VERSION"] = (!empty($_GET["version"]) ? intval($_GET["version"]) : 1);
				if ($arResult["VERSION"] >= 2)
				{
					$arParams['THUMBNAIL_DETAIL_SIZE'] = 57;
				}
				include($_SERVER["DOCUMENT_ROOT"].$folderPath."/card.php");
			}

			if (CModule::IncludeModule('intranet'))
			{
				$arResult['IS_HONOURED'] = CIntranetUtils::IsUserHonoured($arResult["User"]["ID"]);
				$arResult['IS_ABSENT'] = CIntranetUtils::IsUserAbsent($arResult["User"]["ID"]);
			}

			if ($arResult["User"]['PERSONAL_BIRTHDAY'] <> '')
			{
				$arBirthDate = ParseDateTime($arResult["User"]['PERSONAL_BIRTHDAY'], CSite::GetDateFormat('SHORT'));
				$arResult['IS_BIRTHDAY'] = (intval($arBirthDate['MM']) == date('n') && intval($arBirthDate['DD']) == date('j'));
			}

			$strToolbar = "";
			$strToolbar2 = "";
			$intToolbarItems = 0;
			$strOnmouseover = "BX.addClass(this, 'bx-icon-underline');";
			$strOnmouseout = "BX.removeClass(this, 'bx-icon-underline');";

			if (
				!IsModuleInstalled('mail')
				|| (
					(
						!isset($arResult["User"]["EXTERNAL_AUTH_ID"])
						|| !in_array($arResult["User"]["EXTERNAL_AUTH_ID"], array('email'))
					)
					&& (
						$USER->IsAuthorized()
						&& $arResult["CurrentUser"]
						&& !in_array($arResult["CurrentUser"]["EXTERNAL_AUTH_ID"], array('email'))
					)
				)
			)
			{
				if(IsModuleInstalled("im"))
				{
					if (
						$USER->IsAuthorized()
						&& $arResult["User"]["ID"] != $USER->GetID()
						&& $arResult["User"]["ACTIVE"] != "N"
						&& $arResult["CurrentUserPerms"]["Operations"]["message"]
					)
					{
						$strOnclick = "return BX.tooltip.openIM(".$arResult["User"]["ID"].");";
						$strToolbar2 .= '<li class="bx-icon bx-icon-message"><span onmouseover="'.$strOnmouseover.'" onmouseout="'.$strOnmouseout.'" onclick="'.$strOnclick.'">'.GetMessage("MAIN_UL_TOOLBAR_MESSAGES_CHAT").'</span></li>';

						$strOnclick = "return BX.tooltip.openCallTo(".$arResult["User"]["ID"].");";
						$strToolbar2 .= '<li id="im-video-call-button'.$arResult["User"]["ID"].'" class="bx-icon bx-icon-video"><span onmouseover="'.$strOnmouseover.'" onmouseout="'.$strOnmouseout.'" onclick="'.$strOnclick.'">'.GetMessage("MAIN_UL_TOOLBAR_VIDEO_CALL").'</span></li>';
						$strToolbar2 .= '<script>BX.ready(function() {BX.tooltip.checkCallTo(\'im-video-call-button'.$arResult["User"]["ID"].'\'); };</script>';
					}
				}
				elseif (
					$USER->IsAuthorized()
					&& $arResult["User"]["ID"] != $USER->GetID()
					&& $arResult["CurrentUserPerms"]["Operations"]["videocall"]
					&& $arResult["Urls"]["VideoCall"] <> ''
				)
				{
					$strOnclick = "return BX.tooltip.openVideoCall(".$arResult["User"]["ID"].");";
					$strToolbar2 .= '<li class="bx-icon bx-icon-video"><span onmouseover="'.$strOnmouseover.'" onmouseout="'.$strOnmouseout.'" onclick="'.$strOnclick.'">'.GetMessage("MAIN_UL_TOOLBAR_VIDEO_CALL").'</span></li>';
				}
			}

			if ($arResult['IS_BIRTHDAY'])
			{
				$strToolbar .= '<li class="bx-icon bx-icon-birth">'.GetMessage("MAIN_UL_TOOLBAR_BIRTHDAY").'</li>';
				$intToolbarItems++;
			}

			if ($arResult['IS_HONOURED'])
			{
				$strToolbar .= '<li class="bx-icon bx-icon-featured">'.GetMessage("MAIN_UL_TOOLBAR_HONORED").'</li>';
				$intToolbarItems++;
			}

			if ($arResult['IS_ABSENT'])
			{
				$strToolbar .= '<li class="bx-icon bx-icon-away">'.GetMessage("MAIN_UL_TOOLBAR_ABSENT").'</li>';
				$intToolbarItems++;
			}

			if ($strToolbar <> '')
			{
				$strToolbar = "<ul>".$strToolbar."</ul>";
			}

			if ($strToolbar2 <> '')
			{
				$strToolbar2 = "<div class='".$arResult["stylePrefix"]."-info-data-separator'></div><ul>".$strToolbar2."</ul>";
			}

			$arResult = array(
				"Toolbar" => $strToolbar,
				"ToolbarItems" => $intToolbarItems,
				"Toolbar2" => $strToolbar2,
				"Name" => $strNameFormatted,
				"Card" => $strCard,
				"Photo" => $strPhoto,
				"Position" => $strPosition,
				"Scripts" => (!empty($arScripts) ? $arScripts : array())
			);

			$APPLICATION->RestartBuffer();

			header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

			echo Json::encode(array('RESULT' => $arResult));

			require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
		}
	}
	else
	{
		$arResult["User"]["ID"] = $arParams["ID"];
		$arResult["User"]["NAME"] = $arParams["~NAME"] ?? '';
		$arResult["User"]["LAST_NAME"] = $arParams["~LAST_NAME"] ?? '';
		$arResult["User"]["SECOND_NAME"] = $arParams["~SECOND_NAME"] ?? '';
		$arResult["User"]["LOGIN"] = $arParams["LOGIN"] ?? '';
		if (
			$arParams["USE_THUMBNAIL_LIST"] == "Y"
			&& (!isset($arParams["HREF"]) || $arParams["HREF"]) == ''
		)
		{
			$arResult["User"]["PersonalPhotoImgThumbnail"] = array(
				"Image" => $arParams["~PERSONAL_PHOTO_IMG"],
				"Url" => false
			);
		}
		elseif (
			$arParams["USE_THUMBNAIL_LIST"] == "Y"
			&& isset($arParams["PERSONAL_PHOTO_FILE"]["ID"])
			&& intval($arParams["PERSONAL_PHOTO_FILE"]["ID"]) > 0
		)
		{
			$arImage = CSocNetTools::InitImage($arParams["PERSONAL_PHOTO_FILE"]["ID"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/1.gif", 1, $arParams["~HREF"], $canViewProfile);
			$arResult["User"]["PersonalPhotoImgThumbnail"] = array(
				"Image" => $arImage["IMG"],
				"Url" => $arParams["~HREF"]
			);
		}
	}

	if (array_key_exists("NAME_LIST_FORMATTED", $arParams) && trim($arParams['NAME_LIST_FORMATTED']) <> '')
		$arResult["User"]["NAME_FORMATTED"] = trim($arParams['NAME_LIST_FORMATTED']);
	else
		$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult["User"], $bUseLogin);

	if ($bSocialNetwork)
		$arResult["User"]["HTML_ID"] = $arParams["HTML_ID"];

	if (isset($arParams["HREF"]) && $arParams["HREF"] <> '')
		$arResult["User"]["HREF"] = $arParams["~HREF"];

	$arResult["bSocialNetwork"] = $bSocialNetwork;

	if (isset($arParams["DESCRIPTION"]) && $arParams["DESCRIPTION"] <> '')
	{
		$arResult["User"]["NAME_DESCRIPTION"] = $arParams["~DESCRIPTION"];
		if (CheckDateTime($arResult["User"]["NAME_DESCRIPTION"]))
		{
			$arResult["User"]["NAME_DESCRIPTION"] = FormatDateFromDB($arResult["User"]["NAME_DESCRIPTION"]);
		}
	}

}
elseif($arParams['AJAX_CALL'] == 'INFO') // fatal error for ajax page
{
	$APPLICATION->RestartBuffer();
	while (@ob_end_clean());

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

	echo Json::encode(array('RESULT' => $arResult));
	CMain::FinalActions();
}

if (!isset($arParams["AJAX_ONLY"]) || $arParams["AJAX_ONLY"] != "Y")
{
	ob_start();
	$this->IncludeComponentTemplate();
	$sReturn = ob_get_contents();

	if ($arParams["DO_RETURN"] == "Y")
		ob_end_clean();
	else
		ob_end_flush();

	return $sReturn;
}
