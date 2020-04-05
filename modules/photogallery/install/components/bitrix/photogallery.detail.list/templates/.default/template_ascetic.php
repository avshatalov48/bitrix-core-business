<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
if (!function_exists("__photo_template_ascetic"))
{
	function __photo_template_ascetic($arItem, $arParams = array())
	{
		static $bFirstOnPage = true;
		$arParams = (is_array($arParams) ? $arParams : array());
		$arParams["mode"] = ($arParams["mode"] == "edit" ? "edit" : "read");

		$arParams["percent_width"] = intval(intval($arParams["percent_width"]) > 0 ? $arParams["percent_width"] : 0);
		$arParams["percent_width"] = $arParams["percent_width"] / 100;
		$arParams["percent_height"] = intval(intval($arParams["percent_height"]) > 0 ? $arParams["percent_height"] : 100);
		$arParams["percent_height"] = $arParams["percent_height"] / 100;
		$arParams["SHOW_ANCHOR"] = ($arParams["SHOW_ANCHOR"] == "Y" ? "Y" : "N");
		$arParams["MAX_HEIGHT"] = intval($arParams["MAX_HEIGHT"] > 0 ? $arParams["MAX_HEIGHT"] : 250);
		$arParams["MAX_WIDTH"] = intval($arParams["MAX_WIDTH"] > 0 ? $arParams["MAX_WIDTH"] : 250);

		$bActiveElement = ($arItem["ACTIVE"] != "Y" ? false : true);

		$sImage = "";
		if (is_array($arItem["PICTURE"]))
		{
			$coeff = 1;
			if ($arParams["percent_width"] <= 0 && (($arParams["MAX_HEIGHT"] * $arParams["percent_height"]) < $arItem["PICTURE"]["HEIGHT"])):
				$coeff = $arItem["PICTURE"]["HEIGHT"]/($arParams["MAX_HEIGHT"] * $arParams["percent_height"]);
			elseif($arItem["PICTURE"]["WIDTH"] > $arParams["MAX_WIDTH"] || $arItem["PICTURE"]["HEIGHT"] > $arParams["MAX_HEIGHT"]):
				$coeff = max($arItem["PICTURE"]["WIDTH"]/$arParams["MAX_WIDTH"], $arItem["PICTURE"]["HEIGHT"]/$arParams["MAX_HEIGHT"]);
			endif;
			if ($coeff > 1):
				$arItem["PICTURE"]["WIDTH"] = intval(roundEx($arItem["PICTURE"]["WIDTH"]/$coeff));
				$arItem["PICTURE"]["HEIGHT"] = intval(roundEx($arItem["PICTURE"]["HEIGHT"]/$coeff));
			endif;
			$sImage = "<img src=\"".$arItem["PICTURE"]["SRC"]."\" ".
				"border=\"0\" vspace=\"0\" hspace=\"0\" alt=\"".$arItem["TITLE"]."\" title=\"".$arItem["TITLE"]."\" ".
				"width=\"".$arItem["PICTURE"]["WIDTH"]."\" height=\"".$arItem["PICTURE"]["HEIGHT"]."\" />";
		}
		if ($arParams["percent_width"] > 0)
			$arParams["MAX_WIDTH"] = $arParams["percent_width"] * $arParams["MAX_WIDTH"];
		$arParams["MAX_HEIGHT"] = $arParams["percent_height"] * $arParams["MAX_HEIGHT"];

		if (empty($sImage)):
			$sImage = '<div class="photo-photo-image-empty" style="width:'.$arParams["MAX_WIDTH"].'px;height'.$arParams["MAX_HEIGHT"].':px;"></div>';
			$arItem["PICTURE"]["WIDTH"] = $arParams["MAX_WIDTH"];
			$arItem["PICTURE"]["HEIGHT"] = $arParams["MAX_HEIGHT"];
		elseif ($arParams["percent_width"] <= 0):
			$margin_top = 0 - intVal(($arItem["PICTURE"]["HEIGHT"] - $arParams["MAX_HEIGHT"])/2);
			$arItem["PICTURE"]["HEIGHT"] = $arParams["MAX_HEIGHT"];
			$sImage = '<div style="margin-top:'.$margin_top.'px;text-align:left;position:static;">'.$sImage.'</div>';
		else:
			$margin_left = round((0 - intVal(($arItem["PICTURE"]["WIDTH"] - $arParams["MAX_WIDTH"])/2)) * 100 / $arParams["MAX_WIDTH"], 2);
			$margin_top = round((0 - intVal(($arItem["PICTURE"]["HEIGHT"] - $arParams["MAX_HEIGHT"])/2)) * 100 / $arParams["MAX_HEIGHT"], 2);

			$arItem["PICTURE"]["WIDTH"] = $arParams["MAX_WIDTH"];
			$arItem["PICTURE"]["HEIGHT"] = $arParams["MAX_HEIGHT"];

			$sImage = '<div style="margin-top:'.$margin_top.'%;margin-left:'.$margin_left.'%;text-align:left;position:static;">'.$sImage.'</div>';
		endif;

?>
		<div class="photo-photo-item photo-photo-item-ascetic <?=($arParams["mode"] == "edit" ? " photo-photo-item-edit" : "")?><?
			?><?=(!$bActiveElement ? " photo-photo-item-notapproved" : "")?><?
			?><?=(in_array($arItem["ID"], $_REQUEST["items"]) ? " photo-photo-item-checked" : "")?>"><?
	if ($arParams["SHOW_ANCHOR"] == "N")
	{
?>
			<div class="photo-photo-item-ascetic-inner" <?
				?>style="width:<?=intval($arItem["PICTURE"]["WIDTH"])?>px; height:<?=$arItem["PICTURE"]["HEIGHT"]?>px; overflow:hidden;">
				<?=$sImage?>
			</div>
<?
	}
	else
	{
?>
			<div class="photo-photo-item-ascetic-inner" <?
				?>style="width:<?=intval($arItem["PICTURE"]["WIDTH"])?>px; height:<?=$arItem["PICTURE"]["HEIGHT"]?>px; overflow:hidden;position:relative;"><?
			if ($arParams["mode"] == "edit"):
				?><input type="checkbox" value="<?=$arItem["ID"]?>" name="items[]" <?
					?> <?=(in_array($arItem["ID"], $_REQUEST["items"]) ? " checked='checked' " : "")?> <?
					?>id="items_<?=$arItem["ID"]?>" style="position:absolute;top:0;left:0;z-index:100;" <?
					?>onclick="var res = this.parentNode.parentNode; <?
						?>if (this.checked) {res.className += ' photo-photo-item-checked'} <?
						?>else {res.className = res.className.replace(/photo\-photo\-item\-checked/g, ' ').replace(/\s\s/g, ' ');}" /><?
			endif;
				?><a class="photo-photo-item-ascetic-inner" <?
					?>style="width:<?=intval($arItem["PICTURE"]["WIDTH"])?>px;height:<?=$arItem["PICTURE"]["HEIGHT"]?>px;display:block;" <?
					?>href="<?=$arItem["URL"]?>" id="photo_<?=$arItem["ID"]?>"><?=$sImage?>
				</a>
			</div>
<?
	}
?>
		</div>
<?
	}
}
?>