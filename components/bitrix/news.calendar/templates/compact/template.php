<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="news-calendar-compact">
	<table width='100%' border='0' cellspacing='0' cellpadding='1' class='NewsCalTable'>
	<tr>
	<?foreach($arResult["WEEK_DAYS"] as $WDay):?>
		<td align="center" class='NewsCalHeader'><?=$WDay["SHORT"]?></td>
	<?endforeach?>
	</tr>
	<?foreach($arResult["MONTH"] as $arWeek):?>
	<tr>
		<?foreach($arWeek as $arDay):?>
		<td align="right" valign="top" class='<?=$arDay["td_class"]?>' width="14%">
			<?if(count($arDay["events"])>0):?>
				<a title="<?=$arDay["events"][0]["title"]?>" href="<?=$arDay["events"][0]["url"]?>" class="<?=$arDay["day_class"]?>"><?=$arDay["day"]?></a>
			<?else:?>
				<span class="<?=$arDay["day_class"]?>"><?=$arDay["day"]?></span>
			<?endif;?>
		</td>
		<?endforeach?>
	</tr >
	<?endforeach?>
	</table>
</div>
