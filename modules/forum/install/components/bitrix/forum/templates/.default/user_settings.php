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
if (check_bitrix_sessid() && $GLOBALS["USER"]->IsAuthorized())
{
	$arData = CUserOptions::GetOption("forum", "default_template", "");
	$arData = (CheckSerializedData($arData) ? @unserialize($arData, ["allowed_classes" => false]) : array());
	if (!is_array($arData))
		$arData = array();
	if ($_REQUEST["save"] == "smiles_position")
	{
		$arData["smiles"] = ($_REQUEST["value"] == "hide" ? "hide" : "show");
		CUserOptions::SetOption("forum", "default_template", serialize($arData));
	}
	elseif ($_REQUEST["save"] == "first_post")
	{
		$arData["first_post"] = ($_REQUEST["value"] == "hide" ? "hide" : "show");
		CUserOptions::SetOption("forum", "default_template", serialize($arData));
	}
}
?>