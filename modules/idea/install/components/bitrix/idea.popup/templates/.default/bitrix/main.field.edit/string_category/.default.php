<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arCategoryList = array();
if(CModule::IncludeModule('iblock') && CModule::IncludeModule('idea') && CIdeaManagment::getInstance()->Idea()->GetCategoryListID()>0)
	$arCategoryList = CIdeaManagment::getInstance()->Idea()->GetCategoryList();
?>
<?
if($arParams["arUserField"]["VALUE"] == '' && array_key_exists("idea", $_REQUEST) && $_REQUEST["idea"] <> '')
	$arParams["arUserField"]["VALUE"] = htmlspecialcharsbx($_REQUEST["idea"]);
?>
<div class="field-<?=$arParams["arUserField"]["FIELD_NAME"]?>">
	<select name="<?=$arParams["arUserField"]["FIELD_NAME"]?>">
		<?foreach($arCategoryList as $opt):?>
		<option value="<?=ToUpper($opt["CODE"])?>"<?if(ToUpper($arParams["arUserField"]["VALUE"]) == ToUpper($opt["CODE"])):?> selected<?endif;?>><?=str_repeat("&bull; ", $opt["DEPTH_LEVEL"]-1)?><?=$opt["NAME"]?></option>
		<?endforeach;?>
	</select>
</div>