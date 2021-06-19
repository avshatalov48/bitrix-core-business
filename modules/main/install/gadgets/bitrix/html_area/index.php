<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/html_area/styles.css');

$bEdit = (($_REQUEST['gdhtml'] ?? '') == $id) && (($_REQUEST['edit'] ?? '') == 'true') && ($arParams["PERMISSION"] > "R");
if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_REQUEST['gdhtmlform'] ?? '') == 'Y' && ($_REQUEST['gdhtml'] ?? '') == $id)
{
	$arGadget["USERDATA"] = Array("content"=>$_POST["html_content"]);
	$arGadget["FORCE_REDIRECT"] = true;
}
$arData = $arGadget["USERDATA"];
$content = $arData["content"];
?>

<?if(!$bEdit):?>

<?
	if($content)
	{
		$parser = new CTextParser();
		$parser->allow = array(
			"HTML"=>($arParams["MODE"] != "AI" ? "N" : "Y"), 
			"ANCHOR"=>"Y", 
			"BIU"=>"Y", 
			"IMG"=>"Y", 
			"QUOTE"=>"Y", 
			"CODE"=>"Y", 
			"FONT"=>"Y", 
			"LIST"=>"Y", 
			"SMILES"=>"N", 
			"NL2BR"=>"N", 
			"VIDEO"=>"N", 
			"TABLE"=>"Y", 
			"CUT_ANCHOR"=>"N", 
			"ALIGN"=>"Y"
		);
		$parser->parser_nofollow = "Y";
		echo $parser->convertText($content);
	}
	else
	{
		if($arParams["PERMISSION"]>"R")
			echo GetMessage("GD_HTML_AREA_NO_CONTENT");
	}
?>

<?if($arParams["PERMISSION"]>"R"):?>
<div class="gdhtmlareach" style="padding-top: 10px;"><a class="gdhtmlareachlink" href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam("gdhtml=".$id."&edit=true", array("gdhtml", "edit"))?>"><?echo GetMessage("GD_HTML_AREA_CHANGE_LINK")?></a></div>
<?endif?>

<?else:?>

<form action="?gdhtml=<?=$id?>" method="post" id="gdf<?=$id?>">
<?
CModule::IncludeModule("fileman");

$LHE = new CLightHTMLEditor;
$LHE->Show(array(
	'jsObjName' => 'oGadgetLHE',
	'inputName' => 'html_content',
	'content' => $content,
	'width' => '100%',
	'height' => '200px',
	'bResizable' => true,
	'bUseFileDialogs' => false,
	'bUseMedialib' => false,
	'toolbarConfig' => array(
		'Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat',
		'CreateLink', 'DeleteLink', 'Image',
		'ForeColor', 'InsertOrderedList', 'InsertUnorderedList',
		'Outdent', 'Indent', 'FontList', 'FontSizeList',
		'Source'
	),
	'bSaveOnBlur' => false,
	'BBCode' => ($arParams["MODE"] != "AI"),
	'bBBParseImageSize' => true,
	'ctrlEnterHandler' => 'gdhtmlsave',
));
?>
	<input type="hidden" name="gdhtmlform" value="Y">
	<?if ($arParams["MULTIPLE"] == "Y"):?>
	<input type="hidden" name="dt_page" value="<?=$arParams["DESKTOP_PAGE"]?>">
	<?endif;?>
	<?=bitrix_sessid_post()?>
</form>
<script type="text/javascript">
function gdhtmlsave()
{
	oGadgetLHE.SaveContent();
	document.getElementById("gdf<?=$id?>").submit();
	return false;
}
</script>
<a href="javascript:void(0);" onclick="return gdhtmlsave();"><?echo GetMessage("GD_HTML_AREA_SAVE_LINK")?></a> | <a href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam(($arParams["MULTIPLE"]=="Y"?"dt_page=".$arParams["DESKTOP_PAGE"]:""), array("dt_page","gdhtml","edit"))?>"><?echo GetMessage("GD_HTML_AREA_CANCEL_LINK")?></a>
<?endif?>