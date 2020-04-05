<?define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
// **************************************************************************************
if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		elseif (strpos($item, "%u") !== false)
			$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
		elseif (LANG_CHARSET != "UTF-8" && preg_match("/^.{1}/su", $item) == 1)
			$item = $GLOBALS["APPLICATION"]->ConvertCharset($item, "UTF-8", LANG_CHARSET);
	}
}

array_walk($_REQUEST, '__UnEscape');
if (check_bitrix_sessid() && $GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
	$_REQUEST["picture_sight"] = (empty($_REQUEST["picture_sight"]) && !empty($_REQUEST["PICTURES_SIGHT"]) ? $_REQUEST["PICTURES_SIGHT"] : $_REQUEST["picture_sight"]); 
	$arTemplateParams = CUserOptions::GetOption('photogallery', 'template');
	$arTemplateParams = (!is_array($arTemplateParams) ? array() : $arTemplateParams);
	if ($_REQUEST["picture_sight"] && check_bitrix_sessid() && $arTemplateParams["sight"] != $_REQUEST["picture_sight"]):
		$arTemplateParams['sight'] = $_REQUEST["picture_sight"]; 
		CUserOptions::SetOption('photogallery', 'template', $arTemplateParams);
	endif;
}
?>