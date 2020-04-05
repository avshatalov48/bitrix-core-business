<?
IncludeModuleLangFile(__FILE__);

function tags_prepare($sText, $site_id = false)
{
	static $arEvents = false;
	if($arEvents === false)
		$arEvents = GetModuleEvents("search", "OnSearchGetTag", true);

	$arResult = array();
	$arTags = explode(",", $sText);
	foreach($arTags as $tag)
	{
		$tag = trim($tag);
		if(strlen($tag))
		{
			foreach($arEvents as $arEvent)
				$tag = ExecuteModuleEventEx($arEvent, array($tag));

			if(strlen($tag))
				$arResult[$tag] = $tag;
		}
	}
	return $arResult;
}

function TagsShowScript()
{
	global $APPLICATION;
	$APPLICATION->SetAdditionalCSS("/bitrix/admin/htmleditor2/editor.css");
	CJSCore::Init('search_tags');
}

function InputTags($sName="", $sValue="", $arSites=array(), $sHTML="", $sId="")
{
	if(!$sId)
		$sId = GenerateUniqId($sName);
	TagsShowScript();
	$order = class_exists("cuseroptions")? CUserOptions::GetOption("search_tags", "order", "CNT"): "CNT";
	return '<input style="width:90%;margin-right:4px;" name="'.htmlspecialcharsbx($sName).'" id="'.htmlspecialcharsbx($sId).'" type="text" autocomplete="off" value="'.htmlspecialcharsex($sValue).'" onfocus="'.htmlspecialcharsbx('window.oObject[this.id] = new JsTc(this, '.CUtil::PhpToJSObject($arSites).');').'" '.$sHTML.'/><input type="checkbox" id="ck_'.$sId.'" name="ck_'.htmlspecialcharsbx($sName).'" '.($order=="NAME"? "checked": "").' title="'.GetMessage("SEARCH_TAGS_SORTING_TIP").'">';
}
?>