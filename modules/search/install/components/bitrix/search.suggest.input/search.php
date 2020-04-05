<?
define("STOP_STATISTICS", true);
define("PUBLIC_AJAX_MODE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
CUtil::JSPostUnescape();

$arResult = array();

if(CModule::IncludeModule("search"))
{
	if(!empty($_POST["search"]) && is_string($_POST["search"]))
	{
		$search = $_POST["search"];

		$arParams = array();
		$params = explode(",", $_POST["params"]);
		foreach($params as $param)
		{
			list($key, $val) = explode(":", $param);
			$arParams[$key] = $val;
		}

		$obSearchSuggest = new CSearchSuggest($arParams["md5"], $search);

		$db_res = $obSearchSuggest->GetList($arParams["pe"], $arParams["site"]);
		if($db_res)
		{
			while($res = $db_res->Fetch())
			{
				$arResult[] = array(
					"NAME" => $res["PHRASE"],
					"CNT" => intval($res["CNT"]),
				);
			}
		}
	}
}

echo CUtil::PhpToJSObject($arResult);

CMain::FinalActions();
die();
