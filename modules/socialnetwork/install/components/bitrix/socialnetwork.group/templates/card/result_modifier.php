<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Integration;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

CSocNetLogComponent::processDateTimeFormatParams($arParams);

$APPLICATION->SetTitle(Loc::getMessage($arResult['Group']['PROJECT'] == 'Y' ? 'SONET_C6_CARD_TITLE_PROJECT' : 'SONET_C6_CARD_TITLE'));

if (is_array($arResult["Owner"]))
{
	if (intval($arResult["Owner"]["USER_PERSONAL_PHOTO"]) > 0)
	{
		$arImage = CFile::ResizeImageGet(
			$arResult["Owner"]["USER_PERSONAL_PHOTO"], 
			array("width" => 100, "height" => 100),
			BX_RESIZE_IMAGE_EXACT
		);
	}
	else
	{
		$arImage = array("src" => "");
	}

	$arResult["Owner"]["USER_PERSONAL_PHOTO_FILE"]["SRC"] = $arImage["src"];
	$arResult["Owner"]["NAME_FORMATTED"] = CUser::FormatName(
		$arParams["NAME_TEMPLATE"],
		array(
			"NAME" => htmlspecialcharsBack($arResult["Owner"]["USER_NAME"]),
			"LAST_NAME" => htmlspecialcharsBack($arResult["Owner"]["USER_LAST_NAME"]),
			"SECOND_NAME" => htmlspecialcharsBack($arResult["Owner"]["USER_SECOND_NAME"]),
			"LOGIN" => htmlspecialcharsBack($arResult["Owner"]["USER_LOGIN"])
		),
		true
	);
}

if (is_array($arResult["Moderators"]["List"]))
{
	foreach($arResult["Moderators"]["List"] as $key => $moderator)
	{
		if (is_array($moderator))
		{
			if (intval($moderator["USER_PERSONAL_PHOTO"]) > 0)
			{
				$arImage = CFile::ResizeImageGet(
					$moderator["USER_PERSONAL_PHOTO"],
					array("width" => 100, "height" => 100),
					BX_RESIZE_IMAGE_EXACT
				);
			}
			else
			{
				$arImage = array("src" => "");
			}

			$arResult["Moderators"]["List"][$key]["USER_PERSONAL_PHOTO_FILE"]["SRC"] = $arImage["src"];
			$arResult["Moderators"]["List"][$key]["NAME_FORMATTED"] = CUser::FormatName(
				$arParams["NAME_TEMPLATE"],
				array(
					"NAME" => htmlspecialcharsBack($moderator["USER_NAME"]),
					"LAST_NAME" => htmlspecialcharsBack($moderator["USER_LAST_NAME"]),
					"SECOND_NAME" => htmlspecialcharsBack($moderator["USER_SECOND_NAME"]),
					"LOGIN" => htmlspecialcharsBack($moderator["USER_LOGIN"])
				),
				true
			);
		}
	}
}

if (is_array($arResult["Members"]["List"]))
{
	foreach($arResult["Members"]["List"] as $key => $member)
	{
		if (is_array($member))
		{
			if (intval($member["USER_PERSONAL_PHOTO"]) > 0)
			{
				$arImage = CFile::ResizeImageGet(
					$member["USER_PERSONAL_PHOTO"],
					array("width" => 100, "height" => 100),
					BX_RESIZE_IMAGE_EXACT
				);
			}
			else
			{
				$arImage = array("src" => "");
			}
			
			$arResult["Members"]["List"][$key]["USER_PERSONAL_PHOTO_FILE"]["SRC"] = $arImage["src"];
			$arResult["Members"]["List"][$key]["NAME_FORMATTED"] = CUser::FormatName(
				$arParams["NAME_TEMPLATE"],
				array(
					"NAME" => htmlspecialcharsBack($member["USER_NAME"]),
					"LAST_NAME" => htmlspecialcharsBack($member["USER_LAST_NAME"]),
					"SECOND_NAME" => htmlspecialcharsBack($member["USER_SECOND_NAME"]),
					"LOGIN" => htmlspecialcharsBack($member["USER_LOGIN"])
				),
				true
			);

		}
	}
}

$arResult["Urls"]["Delete"] = CComponentEngine::MakePathFromTemplate(
	$arParams["PATH_TO_GROUP_DELETE"],
	array("group_id" => $arResult["Group"]["ID"])
);

$arResult["FAVORITES"] = false;
if ($USER->IsAuthorized())
{
	$res = \Bitrix\Socialnetwork\WorkgroupFavoritesTable::getList(array(
		'filter' => array(
			'GROUP_ID' => $arResult["Group"]["ID"],
			'USER_ID' => $USER->getId()
		)
	));
	$arResult["FAVORITES"] = ($res->fetch());
}

$arResult["Types"] = \Bitrix\Socialnetwork\Item\Workgroup::getTypes(array(
	'currentExtranetSite' => $arResult["bExtranet"]
));

$arResult["Group"]["IS_EXTRANET_GROUP"] = (
	Loader::includeModule("extranet")
	&& CExtranet::isExtranetSocNetGroup($arResult["Group"]["ID"])
	? "Y"
	: "N"
);

$arResult["Group"]["KEYWORDS_LIST"] = array();
if (
	isset($arResult["Group"]["KEYWORDS"])
	&& strlen($arResult["Group"]["KEYWORDS"]) > 0
)
{
	$arResult["Group"]["KEYWORDS_LIST"] = explode(',', $arResult["Group"]["KEYWORDS"]);
	foreach($arResult["Group"]["KEYWORDS_LIST"] as $key => $val)
	{
		$val = trim($val);
		if ($val !== '')
		{
			$arResult["Group"]["KEYWORDS_LIST"][$key] = $val;
		}
		else
		{
			unset($arResult["Group"]["KEYWORDS_LIST"][$key]);
		}
	}
}

$arParams["PATH_TO_GROUPS_LIST"] = ComponentHelper::getWorkgroupSEFUrl();
$arParams["PATH_TO_GROUP_TAG"] = $arParams["PATH_TO_GROUPS_LIST"].(strpos($arParams["PATH_TO_GROUPS_LIST"], '?') !== false ? '&' : '?')."TAG=#tag#&apply_filter=Y";

if (empty($arResult["Urls"]["GroupsList"]))
{
	$arResult["Urls"]["GroupsList"] = CComponentEngine::MakePathFromTemplate(
		$arParams["PATH_TO_GROUPS_LIST"],
		array("user_id" => $USER->getId())
	);
}

$arParams['USER_LIMIT'] = 17;