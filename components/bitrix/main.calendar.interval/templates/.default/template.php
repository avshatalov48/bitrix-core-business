<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)
	die();

/**
 * @var array $arParams
 * @var array $arResult
 * @global CMain $APPLICATION
 */
if(!empty($arResult["INTERVALS"])):
?>
<select name="<?=$arParams["SELECT_NAME"]?>" onchange="bxCalendarInterval.OnDateChange(this)">
<?
	foreach($arResult["INTERVALS"] as $k=>$v):
?>
	<option value="<?=$k?>"<?if($arParams["~SELECT_VALUE"] == $k) echo ' selected="selected"'?>><?=$v?></option>
<?
	endforeach;
?>
</select>
<?
endif;
?>
<span class="bx-filter-br" style="display:none"></span>
<span class="bx-filter-days" style="display:none"><input type="text" name="<?=$arParams["INPUT_NAME_DAYS"]?>" value="<?=$arParams["INPUT_VALUE_DAYS"]?>" class="filter-date-days" size="5" /> <?echo GetMessage("inerface_grid_days")?></span>
<span class="bx-filter-from" style="display:none"><input type="text" name="<?=$arParams["INPUT_NAME_FROM"]?>" value="<?=$arParams["INPUT_VALUE_FROM"]?>" class="filter-date-interval"<?=$arParams["~INPUT_PARAMS"]?> /><?
$APPLICATION->IncludeComponent(
	"bitrix:main.calendar",
	"",
	array(
		"SHOW_INPUT"=>"N",
		"INPUT_NAME"=>$arParams["~INPUT_NAME_FROM"],
		"INPUT_VALUE"=>$arParams["~INPUT_VALUE_FROM"],
		"FORM_NAME"=>$arParams["~FORM_NAME"],
	),
	$component,
	array("HIDE_ICONS"=>true)
);?></span><span class="bx-filter-hellip" style="display:none">&hellip;</span><span class="bx-filter-to" style="display:none"><input type="text" name="<?=$arParams["INPUT_NAME_TO"]?>" value="<?=$arParams["INPUT_VALUE_TO"]?>" class="filter-date-interval"<?=$arParams["~INPUT_PARAMS"]?> /><?
$APPLICATION->IncludeComponent(
	"bitrix:main.calendar",
	"",
	array(
		"SHOW_INPUT"=>"N",
		"INPUT_NAME"=>$arParams["~INPUT_NAME_TO"],
		"INPUT_VALUE"=>$arParams["~INPUT_VALUE_TO"],
		"FORM_NAME"=>$arParams["~FORM_NAME"],
	),
	$component,
	array("HIDE_ICONS"=>true)
);?></span>
