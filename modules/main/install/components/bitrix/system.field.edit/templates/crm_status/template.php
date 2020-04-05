<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bWasSelect = false;

?>
<span class="fields crm_status field-wrap">
<select name="<?=$arParams["arUserField"]["FIELD_NAME"]?>"<?
if ($arParams["arUserField"]["MULTIPLE"]=="Y"):
?> multiple="multiple"<?
endif;
?>><?

foreach ($arParams["arUserField"]["USER_TYPE"]["FIELDS"] as $key => $val)
{
	$bSelected = in_array($key, $arResult["VALUE"]) && (
		(!$bWasSelect) ||
		($arParams["arUserField"]["MULTIPLE"] == "Y")
	);
	$bWasSelect = $bWasSelect || $bSelected;

	?><option value="<?echo $key?>"<?echo ($bSelected? " selected" : "")?>><?echo $val?></option><?
}
?></select>
</span><?