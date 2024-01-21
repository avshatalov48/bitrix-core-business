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

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Helper\UI\Discussions\DiscussionsFilterOld;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arResult = $arParams["arResult"];
$arParams = $arParams["arParams"];

if (
	!ComponentHelper::checkLivefeedTasksAllowed()
	|| !ModuleManager::isModuleInstalled('tasks')
	|| !$USER->IsAuthorized()
)
{
	$arParams["SHOW_EXPERT_MODE"] = 'N';
}

$bizprocAvailable = (
	CModule::IncludeModule("lists") && CLists::isFeatureEnabled()
	&& ModuleManager::isModuleInstalled('intranet')
	&& (
		!Loader::includeModule('extranet')
		|| !CExtranet::isExtranetSite()
	)
);

$arResult["PostFormUrl"] = isset($arParams["POST_FORM_URI"]) ? $arParams["POST_FORM_URI"] : '';
$arResult["ActionUrl"] = isset($arParams["ACTION_URI"]) ? $arParams["ACTION_URI"] : '';
if($arResult["ActionUrl"] === "")
{
	$arResult["AjaxURL"] = $APPLICATION->GetCurPageParam("SONET_FILTER_MODE=AJAX", array("SONET_FILTER_MODE"));
}
else
{
	//For custom schemes
	$ajaxUrlParams = array("SONET_FILTER_MODE" => "AJAX");
	if(isset($_REQUEST["flt_created_by_id"]))
	{
		$ajaxUrlParams["flt_created_by_id"] = $_REQUEST["flt_created_by_id"];
	}
	if(isset($_REQUEST["CREATED_BY_CODE"]))
	{
		$ajaxUrlParams["CREATED_BY_CODE"] = $_REQUEST["CREATED_BY_CODE"];
	}
	if(isset($_REQUEST["TO_CODE"]))
	{
		$ajaxUrlParams["TO_CODE"] = $_REQUEST["TO_CODE"];
	}
	if(isset($_REQUEST["flt_comments"]))
	{
		$ajaxUrlParams["flt_comments"] = $_REQUEST["flt_comments"];
	}
	if(isset($_REQUEST["flt_date_datesel"]))
	{
		$ajaxUrlParams["flt_date_datesel"] = $_REQUEST["flt_date_datesel"];
	}
	if(isset($_REQUEST["flt_date_days"]))
	{
		$ajaxUrlParams["flt_date_days"] = $_REQUEST["flt_date_days"];
	}
	if(isset($_REQUEST["flt_date_from"]))
	{
		$ajaxUrlParams["flt_date_from"] = $_REQUEST["flt_date_from"];
	}
	if(isset($_REQUEST["flt_date_to"]))
	{
		$ajaxUrlParams["flt_date_to"] = $_REQUEST["flt_date_to"];
	}

	$arResult["AjaxURL"] = CHTTP::urlAddParams(
		CHTTP::urlDeleteParams(
			$arResult["ActionUrl"],
			array("SONET_FILTER_MODE", "flt_created_by_id", "flt_comments", "flt-date-datesel", "flt_date_days", "flt_date_from", "flt_date_to", "CREATED_BY_CODE", "TO_CODE")
		),
		$ajaxUrlParams
	);
}

$arResult["MODE"] = (isset($_REQUEST["SONET_FILTER_MODE"]) && $_REQUEST["SONET_FILTER_MODE"] == "AJAX" ? "AJAX" : false);

if ($arResult["MODE"] != "AJAX") // old filter
{
	if (intval($arParams["CREATED_BY_ID"] ?? null) > 0)
	{
		\Bitrix\Main\FinderDestTable::merge(array(
			"CONTEXT" => "FEED_FILTER_CREATED_BY",
			"CODE" => 'U'.intval($arParams["CREATED_BY_ID"])
		));
	}

	if (!empty($arParams["DESTINATION"]))
	{
		\Bitrix\Main\FinderDestTable::merge(array(
			"CONTEXT" => "FEED_FILTER_TO",
			"CODE" => $arParams["DESTINATION"]
		));
	}
}

if (
	(
		$USER->IsAuthorized()
		|| $arParams["AUTH"] == "Y"
		|| $arParams["SUBSCRIBE_ONLY"] != "Y"
	)
)
{
	$arResult["DATE_FILTER"] = array(
		"" => GetMessage("SONET_C30_DATE_FILTER_NO_NO_NO_1"),
		"today" => GetMessage("SONET_C30_DATE_FILTER_TODAY"),
		"yesterday" => GetMessage("SONET_C30_DATE_FILTER_YESTERDAY"),
		"week" => GetMessage("SONET_C30_DATE_FILTER_WEEK"),
		"week_ago" => GetMessage("SONET_C30_DATE_FILTER_WEEK_AGO"),
		"month" => GetMessage("SONET_C30_DATE_FILTER_MONTH"),
		"month_ago" => GetMessage("SONET_C30_DATE_FILTER_MONTH_AGO"),
		"days" => GetMessage("SONET_C30_DATE_FILTER_LAST"),
		"exact" => GetMessage("SONET_C30_DATE_FILTER_EXACT"),
		"after" => GetMessage("SONET_C30_DATE_FILTER_LATER"),
		"before" => GetMessage("SONET_C30_DATE_FILTER_EARLIER"),
		"interval" => GetMessage("SONET_C30_DATE_FILTER_INTERVAL"),
	);
}

$arResult["FOLLOW_TYPE"] = "";
$arResult["EXPERT_MODE"] = "";

if ($USER->IsAuthorized())
{
	$arParams["SHOW_SMART_FILTER_MYGROUPS"] = $arParams["USE_SMART_FILTER"];

	if (array_key_exists("set_follow_type", $_GET))
	{
		CSocNetLogFollow::Set($USER->GetID(), "**", $_GET["set_follow_type"] == "Y" ? "Y" : "N", false);
		if ($_GET["set_follow_type"] != "Y")
		{
			$_SESSION["SL_SHOW_FOLLOW_HINT"] = "Y";
		}
		LocalRedirect("");
	}
	elseif (
		$arParams["USE_SMART_FILTER"] == "Y"
		&& array_key_exists("set_smart_filter_mygroups", $_GET)
	)
	{
		CSocNetLogSmartFilter::Set($USER->GetID(), ($_GET["set_smart_filter_mygroups"] == "Y" ? "Y" : "N"));
		CSocNetLogPages::DeleteEx($USER->GetID(), SITE_ID);
		LocalRedirect("");
	}
	elseif (array_key_exists("set_expert_mode", $_GET))
	{
		$value = ($_GET['set_expert_mode'] === 'Y' ? 'N' : 'Y');

		\Bitrix\Socialnetwork\LogViewTable::set($USER->GetID(), 'tasks', $value);
		\Bitrix\Socialnetwork\LogViewTable::set($USER->GetID(), 'crm_activity_add', $value);
		\Bitrix\Socialnetwork\LogViewTable::set($USER->GetID(), 'crm_activity_add_comment', $value);

		if (isset($_GET['set_expert_mode']) && $_GET['set_expert_mode'] === 'Y')
		{
			$_SESSION["SL_EXPERT_MODE_HINT"] = "Y";
		}
		LocalRedirect("");
	}

	$arResult["FOLLOW_TYPE"] = CSocNetLogFollow::GetDefaultValue($USER->GetID());

	if (($arParams["SHOW_EXPERT_MODE"] ?? null) == 'Y')
	{
		if (isset($arParams["EXPERT_MODE"]))
		{
			$arResult["EXPERT_MODE"] = ($arParams["EXPERT_MODE"] == "Y" ? "Y" : "N");
		}
		else
		{
			$arResult["EXPERT_MODE"] = "N";

			$rs = \Bitrix\Socialnetwork\LogViewTable::getList(array(
				'order' => array(),
				'filter' => array(
					"USER_ID" => $USER->GetID(),
					"EVENT_ID" => 'tasks'
				),
				'select' => array('TYPE')
			));
			if ($ar = $rs->Fetch())
			{
				$arResult["EXPERT_MODE"] = ($ar['TYPE'] == "N" ? "Y" : "N");
			}
		}
	}
}

$arResult["flt_created_by_string"] = "";

$requestFltCreatedById = $_REQUEST["flt_created_by_id"] ?? null;
$requestFltCreatedByString = $_REQUEST["flt_created_by_string"] ?? '';

if ($requestFltCreatedByString <> '')
{
	$arResult["flt_created_by_string"] = $requestFltCreatedByString;
}
else
{
	$user_id_tmp = 0;
	if (
		!empty($_REQUEST["CREATED_BY_CODE"])
		&& !empty($_REQUEST["CREATED_BY_CODE"]["U"])
		&& is_array($_REQUEST["CREATED_BY_CODE"]["U"])
	)
	{
		preg_match('/^U(\d+)$/', $_REQUEST["CREATED_BY_CODE"]["U"][0], $matches);
		if (!empty($matches))
		{
			$user_id_tmp = $matches[1];
		}
	}
	elseif (is_array($requestFltCreatedById) && intval($requestFltCreatedById[0]) > 0)
	{
		$user_id_tmp = $requestFltCreatedById[0];
	}
	elseif(intval($requestFltCreatedById) > 0)
	{
		$user_id_tmp = $requestFltCreatedById;
	}

	if (intval($user_id_tmp) > 0)
	{
		$rsUser = CUser::GetByID($user_id_tmp);
		if ($arUser = $rsUser->GetNext())
		{
			$arResult["flt_created_by_string"] = CUser::FormatName($arParams["NAME_TEMPLATE"]." <#EMAIL#> [#ID#]", $arUser, ($arParams["SHOW_LOGIN"] != "N"), false);
		}
	}
}

$discussionsFilter = new DiscussionsFilterOld($arParams['GROUP_ID']);
$presetsParams = $discussionsFilter->getParamsForPresets(
	$arResult["PresetFiltersTop"] ?? [],
	$arResult["PresetFilters"] ?? []
);

$arResult["PageParamsToClear"] = $presetsParams["pageParamsToClear"];
$arResult["PresetFiltersTop"] = $presetsParams["presetFiltersTop"];
$arResult["PresetFilters"] = $presetsParams["presetFilters"];
$arResult["ALL_ITEM_TITLE"] = $presetsParams["allItemTitle"];

$arResult["PresetFiltersNew"] = $discussionsFilter->getPresets($arResult["PresetFilters"] ?? []);

$arResult["Filter"] = $discussionsFilter->getFilter();

$preset_filter_top_id = '';
if (
	isset($_REQUEST["preset_filter_top_id"])
	&& $_REQUEST["preset_filter_top_id"] === "clearall"
)
{
	$preset_filter_top_id = false;
}
elseif(array_key_exists("preset_filter_top_id", $_REQUEST) && $_REQUEST["preset_filter_top_id"] <> '')
{
	$preset_filter_top_id = $_REQUEST["preset_filter_top_id"];
}

if (
	$preset_filter_top_id <> ''
	&& array_key_exists($preset_filter_top_id, $arResult["PresetFiltersTop"])
	&& is_array($arResult["PresetFiltersTop"][$preset_filter_top_id])
)
{
	$arResult["PresetFilterTopActive"] = $preset_filter_top_id;
}
else
{
	$arResult["PresetFilterTopActive"] = false;
}

$preset_filter_id = '';
if (
	isset($_REQUEST["preset_filter_id"])
	&& $_REQUEST["preset_filter_id"] === "clearall"
)
{
	$preset_filter_id = false;
}
elseif(array_key_exists("preset_filter_id", $_REQUEST) && $_REQUEST["preset_filter_id"] <> '')
{
	$preset_filter_id = $_REQUEST["preset_filter_id"];
}

if (
	$preset_filter_id <> ''
	&& array_key_exists($preset_filter_id, $arResult["PresetFilters"])
	&& isset($arResult["PresetFilters"][$preset_filter_id]["FILTER"])
	&& is_array($arResult["PresetFilters"][$preset_filter_id]["FILTER"])
)
{
	$arResult["PresetFilterActive"] = $preset_filter_id;
}
else
{
	$arResult["PresetFilterActive"] = false;
}

$this->IncludeComponentTemplate();
?>