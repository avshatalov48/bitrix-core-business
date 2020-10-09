<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @var CBitrixComponent $component
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$index = 0;
$fIndex = $arResult["RANDOM"];
?>
<div id="date_container_<?=$fIndex?>">
<?
foreach ($arResult["VALUE"] as $res):

	if($index == 0 && $arParams["arUserField"]["ENTITY_VALUE_ID"]<1 && $arParams["arUserField"]["SETTINGS"]["DEFAULT_VALUE"]["TYPE"]!="NONE")
	{
		if($arParams["arUserField"]["SETTINGS"]["DEFAULT_VALUE"]["TYPE"]=="NOW")
			$res = ConvertTimeStamp(time(), "SHORT");
		else
			$res = CDatabase::FormatDate($arParams["arUserField"]["SETTINGS"]["DEFAULT_VALUE"]["VALUE"], "YYYY-MM-DD", CLang::GetDateFormat("SHORT"));
	}

	$name = $arParams["arUserField"]["FIELD_NAME"];
	if ($arParams["arUserField"]["MULTIPLE"] == "Y")
		$name = $arParams["arUserField"]["~FIELD_NAME"]."[".$index."]";

?><div class="fields datetime">
<input type="text" name="<?=$name?>" value="<?=$res?>"<?
	if (intval($arParams["arUserField"]["SETTINGS"]["SIZE"]) > 0):
		?> size="<?=$arParams["arUserField"]["SETTINGS"]["SIZE"]?>"<?
	endif;
	if ($arParams["arUserField"]["EDIT_IN_LIST"]!="Y"):
		?> readonly="readonly"<?
	endif;
?> class="fields datetime"><?
if ($arParams["arUserField"]["EDIT_IN_LIST"]=="Y"):?><?
	$APPLICATION->IncludeComponent(
		"bitrix:main.calendar",
		"",
		array(
			"SHOW_INPUT" => "N",
			"FORM_NAME" => $arParams["form_name"],
			"INPUT_NAME" => $name,
			"SHOW_TIME" => 'N',
		),
		$component,
		array("HIDE_ICONS" => "Y"));
?><?endif;?></div><?
$index++;
endforeach;
?></div>

<?if ($arParams["arUserField"]["EDIT_IN_LIST"] == "Y" && $arParams["arUserField"]["MULTIPLE"] == "Y" && $arParams["SHOW_BUTTON"] <> "N"):?>
<script type="text/javascript">
if(!window.bxDateInputs)
{
	var bxDateInputs = {};
}
bxDateInputs['<?=$fIndex?>'] = {
	'fieldName': '<?=$arParams["arUserField"]["~FIELD_NAME"]?>',
	'index': '<?=$index?>'
};
</script>

<input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onclick="addElementDate(bxDateInputs, '<?=$fIndex?>');">

<div id="hidden_<?=$fIndex?>" style="display:none;">
	<div class="fields datetime">
		<input type="text" name="#FIELD_NAME#" value=""<?
	if (intval($arParams["arUserField"]["SETTINGS"]["SIZE"]) > 0):
		?> size="<?=$arParams["arUserField"]["SETTINGS"]["SIZE"]?>"<?
	endif;
?> class="fields datetime"><?
	$APPLICATION->IncludeComponent(
		"bitrix:main.calendar",
		"",
		array(
			"SHOW_INPUT" => "N",
			"FORM_NAME" => $arParams["form_name"],
			"INPUT_NAME" => "#FIELD_NAME#",
			"SHOW_TIME" => 'N',
		),
		$component,
		array("HIDE_ICONS" => "Y"));
?></div>
</div>
<?endif;?>