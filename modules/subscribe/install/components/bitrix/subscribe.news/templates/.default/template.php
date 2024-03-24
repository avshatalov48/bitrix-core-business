<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
?>
<table cellpadding="0" cellspacing="10" border="0">
<?php
foreach ($arResult['IBLOCKS'] as $arIBlock):
	if (count($arIBlock['ITEMS']) > 0):
?>
	<tr><td><h1><?=$arIBlock['NAME']?></h1></td></tr>
<?php
	foreach ($arIBlock['ITEMS'] as $arItem):
		?><tr><td>
		<font class="text">
		<?php
		if ($arItem['PREVIEW_PICTURE']):
			if (COption::GetOptionString('subscribe', 'attach_images') === 'Y')
			{
				$sImagePath = $arItem['PREVIEW_PICTURE']['SRC'];
			}
			elseif (mb_strpos($arItem['PREVIEW_PICTURE']['SRC'], 'http') !== 0)
			{
				$sImagePath = 'http://' . $arResult['SERVER_NAME'] . $arItem['PREVIEW_PICTURE']['SRC'];
			}
			else
			{
				$sImagePath = $arItem['PREVIEW_PICTURE']['SRC'];
			}

			$width = 100;
			$height = 100;

			$width_orig = $arItem['PREVIEW_PICTURE']['WIDTH'];
			$height_orig = $arItem['PREVIEW_PICTURE']['HEIGHT'];

			if (($width_orig > $width) || ($height_orig > $height))
			{
				if ($width_orig > $width)
				{
					$height_new = ($width / $width_orig) * $height_orig;
				}
				else
				{
					$height_new = $height_orig;
				}

				if ($height_new > $height)
				{
					$width = ($height / $height_orig) * $width_orig;
				}
				else
				{
					$height = $height_new;
				}
			}
?>
		<a href="<?php echo $arItem['DETAIL_PAGE_URL']?>"><img hspace='5' vspace='5' align='left' border='0' src="<?php echo $sImagePath?>" width="<?php echo $width?>" height="<?php echo $height?>" alt="<?php echo $arItem['PREVIEW_PICTURE']['ALT']?>"  title="<?php echo $arItem['NAME']?>"></a>
		<?php endif;?>
		<?php if ($arItem['DATE_ACTIVE_FROM'] <> ''):?>
			<font class="newsdata"><?php echo $arItem['DATE_ACTIVE_FROM']?></font><br>
		<?php endif;?>
		<a href="<?php echo $arItem['DETAIL_PAGE_URL']?>"><b><?php echo $arItem['NAME']?></b></a><br>
		<?php echo $arItem['PREVIEW_TEXT'];?>
		</font>
	</td></tr>
<?php
	endforeach;
	endif;
?>
<?php endforeach?>
</table>
