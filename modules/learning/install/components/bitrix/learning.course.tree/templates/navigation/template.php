<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$found = false;
foreach ($arResult["ITEMS"] as $key => $arItem):

	if ($arItem["SELECTED"]):?>

		<?if ($arItem["TYPE"] == "CD"):?>
			<div class="learn-course-start"></div>&nbsp;<a href="<?=$arResult["ITEMS"][1]["URL"]?>"><?=GetMessage("LEARNING_START_COURSE")?></a>
		<?return;endif?>

		<?if (isset($arResult["ITEMS"][$key-1]) && $key > 1):?>
			<div class="learn-course-back"></div>&nbsp;<a href="<?=$arResult["ITEMS"][$key-1]["URL"]?>"><?=$arResult["ITEMS"][$key-1]["NAME"]?></a> |
		<?endif?>

		<a href="<?=$arResult["ITEMS"][0]["URL"]?>"><?=$arResult["ITEMS"][0]["NAME"]?></a>

		<?if (isset($arResult["ITEMS"][$key+1])):?>
			| <a href="<?=$arResult["ITEMS"][$key+1]["URL"];?>"> <?=$arResult["ITEMS"][$key+1]["NAME"]?></a>&nbsp;<div class="learn-course-next">&nbsp;&nbsp;&nbsp;</div>
		<?endif?>

		<?
		$found = true;
		break;

	endif;

endforeach;?>

<?if ($found === false):?>
	<div class="learn-course-start"></div>&nbsp;<a href="<?=$arResult["ITEMS"][1]["URL"]?>"><?=GetMessage("LEARNING_START_COURSE")?></a>
<?endif?>