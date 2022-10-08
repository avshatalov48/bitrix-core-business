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
if (($_REQUEST["action"] == "set_filter") && check_bitrix_sessid() && $GLOBALS["USER"]->IsAuthorized())
{
	$res = CUserOptions::GetOption("forum", "Filter", "");
	$res = (CheckSerializedData($res) ? @unserialize($res, ["allowed_classes" => false]) : array());
	if (!is_array($res))
		$res = array();

	if ($_REQUEST["filter_show"] == "show" && !in_array($_REQUEST["filter_name"], $res))
	{
		$res[] = $_REQUEST["filter_name"];
		CUserOptions::SetOption("forum", "Filter", serialize($res));
	}
	elseif ($_REQUEST["filter_show"] == "hide" && in_array($_REQUEST["filter_name"], $res))
	{
		foreach ($res as $key => $val)
		{
			if ($val == $_REQUEST["filter_name"])
				unset($res[$key]);
		}
		CUserOptions::SetOption("forum", "Filter", serialize($res));
	}
}
elseif ($_REQUEST["action"] == "set_filter")
{
	$res = $_SESSION["FORUM"]["SHOW_FILTER"];
	if (!is_array($res))
		$res = array();

	if ($_REQUEST["filter_show"] == "show" && !in_array($_REQUEST["filter_name"], $res))
	{
		$res[] = $_REQUEST["filter_name"];
	}
	elseif ($_REQUEST["filter_show"] == "hide" && in_array($_REQUEST["filter_name"], $res))
	{
		foreach ($res as $key => $val)
		{
			if ($val == $_REQUEST["filter_name"])
				unset($res[$key]);
		}
	}
	
	$_SESSION["FORUM"]["SHOW_FILTER"] = $res;
}
?>