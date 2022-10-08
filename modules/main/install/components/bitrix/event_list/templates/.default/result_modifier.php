<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/sonet_log.css");?>

<?
if (
		$GLOBALS["USER"]->IsAuthorized() 
		|| $arParams["AUTH"] == "Y" 
		|| $arParams["SUBSCRIBE_ONLY"] != "Y"
)
{
	__IncludeLang(__DIR__."/lang/".LANGUAGE_ID."/result_modifier.php");

	$arResult["DATE_FILTER"] = array(
		""			=> GetMessage("EVENT_LIST_DATE_FILTER_NO_NO_NO_1"),
		"today"		=> GetMessage("EVENT_LIST_DATE_FILTER_TODAY"),
		"yesterday"	=> GetMessage("EVENT_LIST_DATE_FILTER_YESTERDAY"),
		"week"		=> GetMessage("EVENT_LIST_DATE_FILTER_WEEK"),
		"week_ago"	=> GetMessage("EVENT_LIST_DATE_FILTER_WEEK_AGO"),
		"month"		=> GetMessage("EVENT_LIST_DATE_FILTER_MONTH"),
		"month_ago"	=> GetMessage("EVENT_LIST_DATE_FILTER_MONTH_AGO"),
		"days"		=> GetMessage("EVENT_LIST_DATE_FILTER_LAST"),
		"exact"		=> GetMessage("EVENT_LIST_DATE_FILTER_EXACT"),
		"after"		=> GetMessage("EVENT_LIST_DATE_FILTER_LATER"),
		"before"	=> GetMessage("EVENT_LIST_DATE_FILTER_EARLIER"),
		"interval"	=> GetMessage("EVENT_LIST_DATE_FILTER_INTERVAL"),
	);
}

if ($this->__component->__parent && $this->__component->__parent->arResult && array_key_exists("PATH_TO_SUBSCRIBE", $this->__component->__parent->arResult))
	$arResult["PATH_TO_SUBSCRIBE"] = $this->__component->__parent->arResult["PATH_TO_SUBSCRIBE"];
?>