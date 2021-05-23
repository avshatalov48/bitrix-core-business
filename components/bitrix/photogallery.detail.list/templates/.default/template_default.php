<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
if (!function_exists("__photo_template_default"))
{
	function __photo_template_default($arItem, $arParams = array())
	{
		if (!function_exists("__photo_template_default_comments_ending"))
		{
			function __photo_template_default_comments_ending($count)
			{
				$text = GetMessage("P_COMMENTS");
				$count = intval($count);
				$iCount = intval($count%100);

				if (!(10 < $iCount && $iCount < 20))
				{
					$count = intval($count % 10);
					if ($count == 1)
						$text = GetMessage("P_COMMENT");
					elseif ($count > 1 && $count < 5)
						$text = GetMessage("P_COMMENTS_2");
				}

				return $text;
			}
		}

		if (!function_exists("__photo_template_default_shows_ending"))
		{
			function __photo_template_default_shows_ending($count)
			{
				$text = GetMessage("P_SHOWS");
				$count = intval($count);
				$iCount = intval($count%100);

				if (!(10 < $iCount && $iCount < 20))
				{
					$count = intval($count % 10);
					if ($count == 1)
						$text = GetMessage("P_SHOW");
					elseif ($count > 1 && $count < 5)
						$text = GetMessage("P_SHOWS_2");
				}

				return $text;
			}
		}
		static $bFirstOnPage = true;
		$arParams = (is_array($arParams) ? $arParams : array());
		$arParams["mode"] = ($arParams["mode"] == "edit" ? "edit" : "read");

//		$arParams["view"] = ($arParams["view"] == "square" ? "square" : "default");
//		$arParams["percent"] = intval(intval($arParams["percent"]) > 0 ? $arParams["percent"] : 70);

		$arParams["MAX_HEIGHT"] = intval($arParams["MAX_HEIGHT"]);
		$arParams["MAX_WIDTH"] = intval($arParams["MAX_WIDTH"]);
		$bActiveElement = ($arItem["ACTIVE"] != "Y" ? false : true);

		if (is_array($arItem["PICTURE"]))
		{
			$coeff = max($arItem["PICTURE"]["WIDTH"]/$arParams["MAX_WIDTH"], $arItem["PICTURE"]["HEIGHT"]/$arParams["MAX_HEIGHT"]);
			if ($coeff > 1):
				$arItem["PICTURE"]["WIDTH"] = intval(roundEx($arItem["PICTURE"]["WIDTH"]/$coeff));
				$arItem["PICTURE"]["HEIGHT"] = intval(roundEx($arItem["PICTURE"]["HEIGHT"]/$coeff));
			endif;

			$sImage = "<img src=\"".$arItem["PICTURE"]["SRC"]."\" ".
				"border=\"0\" vspace=\"0\" hspace=\"0\" alt=\"".$arItem["TITLE"]."\" title=\"".$arItem["TITLE"]."\" ".
				"width=\"".$arItem["PICTURE"]["WIDTH"]."\" height=\"".$arItem["PICTURE"]["HEIGHT"]."\" />";
		}
		else
		{
			$sImage = "<div style='width:".$arParams["MAX_WIDTH"]."px; height:".$arParams["MAX_HEIGHT"]."px;' title='".$arItem["TITLE"]."'></div>";
			$arItem["PICTURE"] = array("WIDTH" => $arParams["MAX_WIDTH"], "HEIGHT" => $arParams["MAX_HEIGHT"]);
		}

?>
<table border="0" cellpadding="0" class="photo-photo-item photo-photo-item-default <?=($arParams["mode"] == "edit" ? " photo-photo-item-edit" : "")?><?
	?><?=(!$bActiveElement ? " photo-photo-item-notapproved" : "")?><?
	?><?=(in_array($arItem["ID"], $_REQUEST["items"]) ? " photo-photo-item-checked" : "")?>" id="table_<?=$arItem["ID"]?>">
	<tr>
		<td class="photo-photo-item" style="height:<?=$arParams["MAX_HEIGHT"]?>px;">
		<div style="padding-top:<?=intval($arParams["MAX_HEIGHT"] - $arItem["PICTURE"]["HEIGHT"])?>px;">
			<div class="photo-photo-item-inner"><?
				?><div style="position:relative;"><?
		if ($arParams["mode"] == "edit")
		{
					?><input type="checkbox" value="<?=$arItem["ID"]?>" <?
						?>name="items[]" <?
						?>id="items_<?=$arItem["ID"]?>" <?
						?><?=(in_array($arItem["ID"], $_REQUEST["items"]) ? " checked='checked'" : "")?> style="position:absolute;" <?
						?>onclick="var res=document.getElementById('table_<?=$arItem["ID"]?>'); <?
							?>if (this.checked){res.className+=' photo-photo-item-checked'} <?
							?>else {res.className=res.className.replace(/photo\-photo\-item\-checked/g, ' ').replace(/\s\s/g, ' ');}" /><?
		}
		if ($arParams["SHOW_ANCHOR"] != "Y")
		{
					?><?=$sImage?><?
		}
		else
		{
					?><a href="<?=$arItem["URL"]?>" id="photo_<?=$arItem["ID"]?>" style="display:block; width: <?=$arItem["PICTURE"]["WIDTH"]?>px; height:  <?=$arItem["PICTURE"]["HEIGHT"]?>px;"<?
					if (!empty($arItem["EVENTS"])):
						foreach ($arItem["EVENTS"] as $key => $val):
							?> on<?=$key?>="<?=$val?>" <?
						endforeach;
					endif;
					?>><?=$sImage?></a><?
		}
				?></div><?
?>
			</div>
		</div>
		</td>
	</tr>
<?
	if ($arParams["SHOW_COMMENTS"] == "Y"):
?>
	<tr>
		<td class="photo-photo-info">
			<div class="photo-photo-comments">
<?
		if ($arItem["COMMENTS"] > 0):
			$sText = $arItem["COMMENTS"]." ".__photo_template_default_comments_ending($arItem["COMMENTS"]);
			if ($arParams["SHOW_ANCHOR"] != "Y"):
				?><?=$sText?><?
			else:
				?><a href="<?=$arItem["URL"]?>"><?=$sText?></a><?
			endif;
		endif;
?>
			</div>
		</td>
	</tr>
<?
	elseif ($arParams["SHOW_SHOWS"] == "Y"):
?>
	<tr>
		<td class="photo-photo-info">
			<div class="photo-photo-comments">
<?
		if ($arItem["SHOW_COUNTER"] > 0):
			$sText = $arItem["SHOW_COUNTER"]." ".__photo_template_default_shows_ending($arItem["SHOW_COUNTER"]);
			if ($arParams["SHOW_ANCHOR"] != "Y"):
				?><?=$sText?><?
			else:
				?><a href="<?=$arItem["URL"]?>"><?=$sText?></a><?
			endif;
		endif;
?>
			</div>
		</td>
	</tr>
<?
	elseif ($arParams["SHOW_RATING"] == "Y"):
?>
	<tr>
		<td class="photo-photo-info">
			<div class="photo-photo-comments">
<?
			$DISPLAY_VALUE = doubleval($arItem["PROPERTIES"]["rating"]["VALUE"]);
			if($arParams["DISPLAY_AS_RATING"] == "vote_avg")
			{
				if($arItem["PROPERTIES"]["vote_count"]["VALUE"])
					$DISPLAY_VALUE = round($arItem["PROPERTIES"]["vote_sum"]["VALUE"]/$arItem["PROPERTIES"]["vote_count"]["VALUE"], 2);
				else
					$DISPLAY_VALUE = 0;
			}
			if ($DISPLAY_VALUE > 0)
			{
				$sText = GetMessage("P_RATING").": ".$DISPLAY_VALUE;
				$sTitle = "";
				if ($arParams["DISPLAY_AS_RATING"] == "vote_avg")
					$sTitle = $sText . ", ".GetMessage("P_VOTES").": ".$arItem["PROPERTIES"]["vote_count"]["VALUE"];
				if ($arParams["SHOW_ANCHOR"] != "Y"):
					?><span <?=(!empty($sTitle) ? ' title="'.$sTitle.'"' : '')?>><?=$sText?></span><?
				else:
					?><a href="<?=$arItem["URL"]?>"<?=(!empty($sTitle) ? ' title="'.$sTitle.'"' : '')?>><?=$sText?></a><?
				endif;
			}
?>
			</div>
		</td>
	</tr>
<?
	endif;
?>
</table>
<?
	}
}
?>