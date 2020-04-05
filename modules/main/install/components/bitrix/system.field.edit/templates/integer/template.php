<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="fields integer" id="main_<?=$arParams["arUserField"]["FIELD_NAME"]?>"><?
foreach ($arResult["VALUE"] as $res):
?><div class="fields integer">
<input type="text" name="<?=$arParams["arUserField"]["FIELD_NAME"]?>" value="<?=$res?>"<?
	if (intVal($arParams["arUserField"]["SETTINGS"]["SIZE"]) > 0):
		?> size="<?=$arParams["arUserField"]["SETTINGS"]["SIZE"]?>"<?
	endif;
	if ($arParams["arUserField"]["EDIT_IN_LIST"]!="Y"):
		?> disabled="disabled"<?
	endif;
?> class="fields integer"></div><?
endforeach;?>
</div>
<?if ($arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] != "N"):?>
<input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onClick="addElement('<?=$arParams["arUserField"]["FIELD_NAME"]?>', this)">
<?endif;?>
