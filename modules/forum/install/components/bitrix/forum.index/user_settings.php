<?define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
// **************************************************************************************
if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		else
		{
			if(mb_strpos($item, "%u") !== false)
				$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
		}
	}
}

array_walk($_REQUEST, '__UnEscape');
if ($GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".mb_strtolower($GLOBALS["DB"]->type)."/favorites.php");
	$arGroup = CUserOptions::GetOption("forum", "GroupHidden", "");
	$arGroup = (CheckSerializedData($arGroup) ? @unserialize($arGroup, ["allowed_classes" => false]) : array());

	if (!is_array($arGroup))
		$arGroup = array();
	$_REQUEST["group"] = intval($_REQUEST["group"]);
	if ($_REQUEST["group"] > 0)
	{
		if (!in_array($_REQUEST["group"], $arGroup))
			$arGroup[] = $_REQUEST["group"];
		else 
		{
			foreach ($arGroup as $key => $val):
				if ($val == $_REQUEST["group"])
					unset($arGroup[$key]);
			endforeach;
		}
		CUserOptions::SetOption("forum", "GroupHidden", serialize($arGroup));
	}
}
elseif (COption::GetOptionString("forum", "USE_COOKIE", "N") == "Y")
{
	$sCookie = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_GROUP"];
	$arGroup = explode("/", $sCookie);
	if ($_REQUEST["group"] > 0)
	{
		if (!in_array($_REQUEST["group"], $arGroup))
			$arGroup[] = $_REQUEST["group"];
		else 
		{
			foreach ($arGroup as $key => $val):
				if ($val == $_REQUEST["group"])
					unset($arGroup[$key]);
			endforeach;
		}
		$GLOBALS["APPLICATION"]->set_cookie("FORUM_GUEST", implode("/", $arGroup), false, "/", false, false, "Y", false);
	}
}
?>