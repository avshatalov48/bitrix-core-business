<?define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
// **************************************************************************************
if (CModule::IncludeModule("search")):
{
	$arParams = array();
	$params = explode(",", $_POST["params"]);
	foreach ($params as $param)
	{
		list($key, $val) = explode(":", $param);
		$arParams[$key] = $val;
	}
	if (intval($arParams["pe"]) <= 0)
		$arParams["pe"] = 10;
	$arResult = array();
// **************************************************************************************
	if(!empty($_POST["search"]))
	{
		if(mb_strtolower($arParams["sort"]) == "name")
			$arOrder = array("NAME"=>"ASC", "CNT"=>"DESC");
		else
			$arOrder = array("CNT"=>"DESC", "NAME"=>"ASC");

		$arFilter = array("TAG"=>$_POST["search"]);
		if (empty($arParams["site_id"])):
			$arFilter["SITE_ID"] = SITE_ID;
		else:
			$arFilter["SITE_ID"] = $arParams["site_id"];
		endif;
		if (!empty($arParams["mid"]))
			$arFilter["MODULE_ID"] = $arParams["mid"];
		if (!empty($arParams["pm1"]))
			$arFilter["PARAM1"] = $arParams["pm1"];
		if (!empty($arParams["pm2"]))
			$arFilter["PARAM2"] = $arParams["pm2"];
		if (!empty($arParams["sng"]))
			$arFilter["PARAMS"] = array("socnet_group" => $arParams["sng"]);

		$db_res = CSearchTags::GetList(
			array("NAME", "CNT"),
			$arFilter,
			$arOrder,
			$arParams["pe"]);
		if($db_res)
		{
			while($res = $db_res->Fetch())
			{
				$arResult[] = array(
					"NAME" => $res["NAME"],
					"CNT" => $res["CNT"],
				);
			}
		}
		?><?=CUtil::PhpToJSObject($arResult)?><?
		CMain::FinalActions();
		die();
	}
}
endif;?>