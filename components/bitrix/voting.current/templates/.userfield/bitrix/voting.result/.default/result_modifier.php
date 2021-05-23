<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
/************** URL ************************************************/
	$URL_NAME_DEFAULT = array(
			"USER" => "/company/personal/user/#USER_ID#/");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams["~PATH_TO_".$URL] = (!!($arParams["~PATH_TO_".$URL]) ? $arParams["PATH_TO_".$URL] : $URL_VALUE);
		$arParams["PATH_TO_".$URL] = htmlspecialcharsbx($arParams["~PATH_TO_".$URL]);
	}
/************** ADDITIONAL *****************************************/
	$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat());
/********************************************************************
				/Input params
********************************************************************/
if ($_REQUEST["VOTE_ID"] == $arParams["VOTE_ID"] && $_REQUEST["AJAX_RESULT"] == "Y" && check_bitrix_sessid())
{
	$res = array("LAST_VOTE" => $arResult["LAST_VOTE"], "QUESTIONS" => array());
	foreach ($arResult["QUESTIONS"] as $arQuestion)
	{
		$res["QUESTIONS"][$arQuestion["ID"]] = array();
		foreach ($arQuestion["ANSWERS"] as $arAnswer)
		{
			$res["QUESTIONS"][$arQuestion["ID"]][$arAnswer["ID"]] = array(
				"USERS" => $arAnswer["USERS"],
				"COUNTER" => $arAnswer["COUNTER"],
				"PERCENT" => $arAnswer["PERCENT"],
				"BAR_PERCENT" => $arAnswer["BAR_PERCENT"]
			);
		}
	}
	while (@ob_end_clean());
	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJsObject($res);
	die();
}
?>