<?
IncludeModuleLangFile(__FILE__);

define("ADMIN_MODULE_NAME", "search");
define("ADMIN_MODULE_ICON", "<img src=\"/bitrix/images/search/search.gif\" width=\"48\" height=\"48\" border=\"0\" alt=\"".GetMessage("SEARCH_PROLOG_ALT")."\" title=\"".GetMessage("SEARCH_PROLOG_ALT")."\">");

$message = null;
if(CModule::IncludeModule('search'))
{
	if(!$message && COption::GetOptionString("search", "full_reindex_required") === "Y")
	{
		$message = new CAdminMessage(array(
			"MESSAGE" => GetMessage("SEARCH_PROLOG_REINDEX"),
			"TYPE" => "ERROR",
		));
	}
}

function search_isShortDate($strDate)
{
	$arDate = ParseDateTime($strDate, FORMAT_DATETIME);
	unset($arDate["DD"]);
	unset($arDate["MMMM"]);
	unset($arDate["MM"]);
	unset($arDate["M"]);
	unset($arDate["YYYY"]);
	return array_sum($arDate) == 0;
}
