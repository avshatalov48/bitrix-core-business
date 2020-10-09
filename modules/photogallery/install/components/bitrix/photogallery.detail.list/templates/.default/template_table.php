<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
if (!function_exists("__photo_template_table"))
{
	function __photo_template_table($arItem, $arParams = array(), $component = false)
	{
		static $bFirstOnPage = true;
		$arParams = (is_array($arParams) ? $arParams : array());
		$arParams["mode"] = ($arParams["mode"] == "edit" ? "edit" : "read");

		$arParams["percent_width"] = intval(intval($arParams["percent_width"]) > 0 ? $arParams["percent_width"] : 100);
		$arParams["percent_width"] = $arParams["percent_width"] / 100;
		$arParams["percent_height"] = intval(intval($arParams["percent_height"]) > 0 ? $arParams["percent_height"] : 100);
		$arParams["percent_height"] = $arParams["percent_height"] / 100;

		$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
		$arParams["SHOW_SHOWS"] = ($arParams["SHOW_SHOWS"] == "Y" ? "Y" : "N");
		$arParams["SHOW_COMMENTS"] = ($arParams["SHOW_COMMENTS"] == "Y" ? "Y" : "N");
		$arParams["SHOW_ANCHOR"] = ($arParams["SHOW_ANCHOR"] == "Y" ? "Y" : "N");

		$arShows = array(
			"RATING" => $arParams["SHOW_RATING"],
			"SHOWS" => $arParams["SHOW_SHOWS"],
			"SHOW_COMMENTS" => $arParams["SHOW_COMMENTS"]);

		$arParams["MAX_HEIGHT"] = intval($arParams["MAX_HEIGHT"]);
		$arParams["MAX_WIDTH"] = intval($arParams["MAX_WIDTH"]);
		$bActiveElement = ($arItem["ACTIVE"] != "Y" ? false : true);

		$sImage = "";
		if (is_array($arItem["PICTURE"]))
		{
			if($arItem["PICTURE"]["WIDTH"] > $arParams["MAX_WIDTH"] || $arItem["PICTURE"]["HEIGHT"] > $arParams["MAX_HEIGHT"]):
				$coeff = max($arItem["PICTURE"]["WIDTH"]/$arParams["MAX_WIDTH"], $arItem["PICTURE"]["HEIGHT"]/$arParams["MAX_HEIGHT"]);
				$arItem["PICTURE"]["WIDTH"] = intval(roundEx($arItem["PICTURE"]["WIDTH"]/$coeff));
				$arItem["PICTURE"]["HEIGHT"] = intval(roundEx($arItem["PICTURE"]["HEIGHT"]/$coeff));
			endif;
			$sImage = "<img src=\"".$arItem["PICTURE"]["SRC"]."\" ".
				"border=\"0\" vspace=\"0\" hspace=\"0\" alt=\"".$arItem["TITLE"]."\" title=\"".$arItem["TITLE"]."\" ".
				"width=\"".$arItem["PICTURE"]["WIDTH"]."\" height=\"".$arItem["PICTURE"]["HEIGHT"]."\" />";
		}

		$arParams["MAX_WIDTH"] = $arParams["percent_width"] * $arParams["MAX_WIDTH"];
		$arParams["MAX_HEIGHT"] = $arParams["percent_height"] * $arParams["MAX_HEIGHT"];

		if (empty($sImage)):
			$sImage = '<div class="photo-photo-image-empty" style="width:'.$arParams["MAX_WIDTH"].'px;height'.$arParams["MAX_HEIGHT"].':px;"></div>';
			$arItem["PICTURE"]["WIDTH"] = $arParams["MAX_WIDTH"];
			$arItem["PICTURE"]["HEIGHT"] = $arParams["MAX_HEIGHT"];
		endif;

		$margin_left = 0 - intval(($arItem["PICTURE"]["WIDTH"] - $arParams["MAX_WIDTH"])/2);
		$margin_top = 0 - intval(($arItem["PICTURE"]["HEIGHT"] - $arParams["MAX_HEIGHT"])/2);

		$sImage = '<div style="margin-top:'.$margin_top.'px;margin-left:'.$margin_left.'px;text-align:left;position:static;">'.$sImage.'</div>';
?>
<table cellpadding="0" border="0" class="photo-photo-item photo-photo-item-table <?=($arParams["mode"] == "edit" ? " photo-photo-item-edit" : "")?><?
	?><?=(!$bActiveElement ? " photo-photo-item-notapproved" : "")?><?
	?><?=(in_array($arItem["ID"], $_REQUEST["items"]) ? " photo-photo-item-checked" : "")?>" id="table_<?=$arItem["ID"]?>">
	<tbody>
		<tr>
			<th class="photo-photo-image">
				<div class="photo-photo-item-block-container">
					<div class="photo-photo-item-block-outer">
						<div class="photo-photo-item-block-inner"><?
					if ($arParams["SHOW_ANCHOR"] == "N")
					{
						?><div class="photo-photo-item-outline" <?
							?>style="width:<?=$arParams["MAX_WIDTH"]?>px;height:<?=$arParams["MAX_HEIGHT"]?>px;overflow:hidden;">
							<?=$sImage?>
						</div><?
					}
					elseif ($arParams["mode"] == "edit")
					{
						?><div class="photo-photo-item-outline" <?
							?>style="width:<?=$arParams["MAX_WIDTH"]?>px;height:<?=$arParams["MAX_HEIGHT"]?>px;overflow:hidden;position:relative;">
							<input type="checkbox" value="<?=$arItem["ID"]?>" name="items[]" <?
								?><?=(in_array($arItem["ID"], $_REQUEST["items"]) ? " checked='checked' " : "")?> <?
								?>id="items_<?=$arItem["ID"]?>" <?
								?>style="position:absolute;top:0;left:0;" <?
								?>onclick="var res = document.getElementById('table_<?=$arItem["ID"]?>'); <?
									?>if (this.checked) {res.className += ' photo-photo-item-checked'} <?
									?>else {res.className = res.className.replace(/photo\-photo\-item\-checked/g, ' ').replace(/\s\s/g, ' ');}" />
							<a class="photo-photo-item-outline" style="width:<?=$arParams["MAX_WIDTH"]?>px; height:<?=$arParams["MAX_HEIGHT"]?>px;display:block;" <?
							?>href="<?=$arItem["URL"]?>" id="photo_<?=$arItem["ID"]?>">
								<?=$sImage?>
							</a>
						</div><?
					}
					else
					{
						?><div class="photo-photo-item-outline" <?
							?>style="width:<?=$arParams["MAX_WIDTH"]?>px;height:<?=$arParams["MAX_HEIGHT"]?>px;overflow:hidden;">
							<a class="photo-photo-item-outline" style="width:<?=$arParams["MAX_WIDTH"]?>px; height:<?=$arParams["MAX_HEIGHT"]?>px;display:block;" <?
							?>href="<?=$arItem["URL"]?>">
								<?=$sImage?>
							</a>
						</div><?
					}
			?>

							</div>
						</div>
					</div>
				</div>
			</th>
		</tr>
	</tbody>
	<tfoot>
		<tr class="photo-photo-info">
			<td class="photo-photo-info">
				<div class="photo-photo-name" style="width:<?=$arParams["MAX_WIDTH"]?>px;">
<?
				if ($arParams["SHOW_ANCHOR"] == "Y"):
					?><a href="<?=$arItem["URL"]?>"><?=$arItem["NAME"]?></a><?
				else:
					?><?=$arItem["NAME"]?><?
				endif;
?>
				</div>
<?
if (in_array("Y", $arShows))
{
?>
				<div class="photo-photo-info" style="width:<?=$arParams["MAX_WIDTH"]?>px;">
<?
	if ($arParams["SHOW_RATING"] == "Y"):
?>
					<div class="photo-photo-rating">
			<?$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:iblock.vote",
				"ajax",
				array(
					"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ELEMENT_ID" => $arItem["ID"],
					"MAX_VOTE" => $arParams["MAX_VOTE"],
					"VOTE_NAMES" => $arParams["VOTE_NAMES"],
					"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
				),
				(($component && $component->__component && $component->__component->__parent) ? $component->__component->__parent : null),
				array("HIDE_ICONS" => "Y"));?>
					</div>
<?
	endif;
	$str = "";
	if ($arParams["SHOW_COMMENTS"] == "Y"):
		if ($arItem["COMMENTS"] > 0):
?>
				<div class="photo-photo-comments">
					<?=GetMessage("P_COMMENTS")?>: <?=$iComm?>
				</div>
<?
		else:
			$str .= '<div class="photo-photo-comments">&nbsp;</div>';
		endif;
	endif;
	if ($arParams["SHOW_SHOWS"] == "Y"):
		if ($arItem["SHOW_COUNTER"] > 0):
?>
				<div class="photo-photo-shows">
					<?=GetMessage("P_SHOWS")?>: <?=intval($arItem["SHOW_COUNTER"])?>
				</div>
<?
		else:
			$str .= '<div class="photo-photo-shows">&nbsp;</div>';
		endif;
	endif;
?>
				<?=$str?>
			</div>
<?
}
?>
			</td>
		</tr>
	</table>
<?
	}
}
?>