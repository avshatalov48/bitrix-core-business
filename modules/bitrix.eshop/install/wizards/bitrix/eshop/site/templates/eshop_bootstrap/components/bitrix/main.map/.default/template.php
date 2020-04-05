<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!is_array($arResult["arMap"]) || count($arResult["arMap"]) < 1)
	return;

$arRootNode = Array();
foreach($arResult["arMap"] as $index => $arItem)
{
	if ($arItem["LEVEL"] == 0)
		$arRootNode[] = $index;
}

$allNum = count($arRootNode);
$colNum = ceil($allNum / $arParams["COL_NUM"]);

?>
<div class="map-columns row">
	<ul class="map-level-0">
	<?
	$previousLevel = -1;
	$counter = 0;
	$column = 1;
	foreach($arResult["arMap"] as $index => $arItem):?>

		<?if ($arItem["LEVEL"] < $previousLevel):?>
			<?=str_repeat("</ul></li>", ($previousLevel - $arItem["LEVEL"]));?>
		<?endif?>


		<?if ($counter >= $colNum && $arItem["LEVEL"] == 0):
				$allNum = $allNum-$counter;
				$colNum = ceil(($allNum) / ($arParams["COL_NUM"] > 1 ? ($arParams["COL_NUM"]-$column) : 1));
				$counter = 0;
				$column++;
		?>
			</ul>
			<ul class="map-level-0">
		<?endif?>

		<?if (array_key_exists($index+1, $arResult["arMap"]) && $arItem["LEVEL"] < $arResult["arMap"][$index+1]["LEVEL"]):?>

			<li><a href="<?=$arItem["FULL_PATH"]?>"><?=$arItem["NAME"]?></a><?if ($arParams["SHOW_DESCRIPTION"] == "Y" && strlen($arItem["DESCRIPTION"]) > 0) {?><div><?=$arItem["DESCRIPTION"]?></div><?}?>
				<ul class="map-level-<?=$arItem["LEVEL"]+1?>">

		<?else:?>

				<li><a href="<?=$arItem["FULL_PATH"]?>"><?=$arItem["NAME"]?></a><?if ($arParams["SHOW_DESCRIPTION"] == "Y" && strlen($arItem["DESCRIPTION"]) > 0) {?><div><?=$arItem["DESCRIPTION"]?></div><?}?></li>

		<?endif?>


		<?
			$previousLevel = $arItem["LEVEL"];
			if($arItem["LEVEL"] == 0)
				$counter++;
		?>

	<?endforeach?>

	<?if ($previousLevel > 1)://close last item tags?>
		<?=str_repeat("</ul></li>", ($previousLevel-1) );?>
	<?endif?>

	</ul>
</div>