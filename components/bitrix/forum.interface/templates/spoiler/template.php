<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// ************************* Input params***************************************************************
// ************************* BASE **********************************************************************
$arParams["TEXT"] = trim($arParams["~TEXT"]);
if (empty($arParams["TEXT"]))
	return ""; 
// *************************/BASE **********************************************************************
// ************************* ADDITIONAL ****************************************************************
$arParams["TITLE"] = trim($arParams["~TITLE"]);
$arParams["TITLE"] = (empty($arParams["TITLE"]) ? GetMessage("F_HIDDEN_TEXT") : $arParams["TITLE"]); 
$arParams["RETURN"] = ($arParams["RETURN"] == "Y" ? "Y" : "N");
// *************************/ADDITIONAL ****************************************************************
// *************************/Input params***************************************************************
ob_start();
?><table class='forum-spoiler'><?
?><thead onclick='<?
	?>if(this.nextSibling.style.display=="none"){this.nextSibling.style.display="";BX.addClass(this,"forum-spoiler-head-open")}<?
	?>else{this.nextSibling.style.display="none";BX.removeClass(this,"forum-spoiler-head-open")}'><tr><th><div><?=htmlspecialcharsbx($arParams["TITLE"])?></div></th></tr></thead><?
?><tbody class='forum-spoiler' style='display:none;'><tr><td><?=$arParams["TEXT"]?></td></tr></tbody></table><?
$str = ob_get_clean();;
if ($arParams["RETURN"] == "Y")
	$this->__component->arParams["RETURN_DATA"] = $str; 
else
	echo $str; 
?>