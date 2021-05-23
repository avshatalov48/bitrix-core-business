<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<table cellpadding="0" cellspacing="10" border="0">
<?
foreach($arResult["IBLOCKS"] as $arIBlock):
	if(count($arIBlock["ITEMS"]) > 0):
?>
	<tr><td><h1><?=$arIBlock['NAME']?></h1></td></tr>
<?
	foreach($arIBlock["ITEMS"] as $arItem):

		if($arItem["PREVIEW_PICTURE"])
		{
			if(COption::GetOptionString("subscribe", "attach_images")==="Y")
			{
				$sImagePath = $arItem["PREVIEW_PICTURE"]["SRC"];
			}
			elseif(mb_strpos($arItem["PREVIEW_PICTURE"]["SRC"], "http") !== 0)
			{
				$sImagePath = "http://".$arResult["SERVER_NAME"].$arItem["PREVIEW_PICTURE"]["SRC"];
			}
			else
			{
				$sImagePath = $arItem["PREVIEW_PICTURE"]["SRC"];
			}

			$width = 100;
			$height = 100;

			$width_orig = $arItem["PREVIEW_PICTURE"]["WIDTH"];
			$height_orig = $arItem["PREVIEW_PICTURE"]["HEIGHT"];

			if(($width_orig > $width) || ($height_orig > $height))
			{
				if($width_orig > $width)
					$height_new = ($width / $width_orig) * $height_orig;
				else
					$height_new = $height_orig;

				if($height_new > $height)
					$width = ($height / $height_orig) * $width_orig;
				else
					$height = $height_new;
			}
		}
?>
	<tr><td>
		<font class="text">
		<?if($arItem["PREVIEW_PICTURE"]):?>
		<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><img hspace='5' vspace='5' align='left' border='0' src="<?echo $sImagePath?>" width="<?echo $width?>" height="<?echo $height?>" alt="<?echo $arItem["PREVIEW_PICTURE"]["ALT"]?>"  title="<?echo $arItem["NAME"]?>"></a>
		<?endif;?>
		<?if($arItem["DATE_ACTIVE_FROM"] <> ''):?>
			<font class="newsdata"><?echo $arItem["DATE_ACTIVE_FROM"]?></font><br>
		<?endif;?>
		<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><b><?echo $arItem["NAME"]?></b></a><br>
		<?echo $arItem["PREVIEW_TEXT"];?>
		</font>
	</td></tr>
<?
	endforeach;
	endif;
?>
<?endforeach?>
</table>
