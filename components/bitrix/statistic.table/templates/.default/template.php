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
$this->createFrame()->begin();
?>
<div class="statistic-table">
	<br />
	<div class="container" title="<?echo GetMessage("STATT_VIEW_HITS")?>">
		<div class="inner-dots">
			<div class="left"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/hit_list.php?lang=<?=LANGUAGE_ID?>&amp;del_filter=Y"><?echo GetMessage("STATT_HITS")?></a><?
				else :
					?><?echo GetMessage("STATT_HITS")?><?
				endif;
			?></div>
			<div class="right"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/hit_list.php?lang=<?=LANGUAGE_ID?>&amp;del_filter=Y"><?echo $arResult["STATISTIC"]["TOTAL_HITS"]?></a><?
				else :
					?><?echo $arResult["STATISTIC"]["TOTAL_HITS"]?><?
				endif;
			?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="container" title="<?echo GetMessage("STATT_VIEW_TODAY_HITS")." (".$arResult["NOW"].")"?>">
		<div class="inner">
			<div class="right today"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/hit_list.php?lang=<?=LANGUAGE_ID?>&amp;find_date1=<?echo $arResult["TODAY"]?>&amp;find_date2=<?echo $arResult["TODAY"]?>&amp;set_filter=Y"><?echo $arResult["STATISTIC"]["TODAY_HITS"]?></a><?
				else :
					?><?echo $arResult["STATISTIC"]["TODAY_HITS"]?><?
				endif;
			?></div>
			<div class="clear"></div>
		</div>
	</div>
	<br />
	<div class="container" title="<?echo GetMessage("STATT_VIEW_HOSTS")?>">
		<div class="inner-dots">
			<div class="left"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/stat_list.php?lang=<?=LANGUAGE_ID?>&amp;del_filter=Y"><?echo GetMessage("STATT_HOSTS")?></a><?
				else :
					?><?echo GetMessage("STATT_HOSTS")?><?
				endif;
			?></div>
			<div class="right"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/stat_list.php?lang=<?=LANGUAGE_ID?>&amp;del_filter=Y"><?echo $arResult["STATISTIC"]["TOTAL_HOSTS"]?></a><?
				else :
					?><?echo $arResult["STATISTIC"]["TOTAL_HOSTS"]?><?
				endif;
			?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="container" title="<?echo GetMessage("STATT_VIEW_TODAY_HOSTS")." (".$arResult["NOW"].")"?>">
		<div class="inner">
			<div class="right today"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/stat_list.php?lang=<?=LANGUAGE_ID?>&amp;del_filter=Y"><?echo $arResult["STATISTIC"]["TODAY_HOSTS"]?></a><?
				else :
					?><?echo $arResult["STATISTIC"]["TODAY_HOSTS"]?><?
				endif;
			?></div>
			<div class="clear"></div>
		</div>
	</div>
	<br />
	<div class="container" title="<?echo GetMessage("STATT_VIEW_GUESTS")?>">
		<div class="inner-dots">
			<div class="left"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&amp;del_filter=Y"><?echo GetMessage("STATT_VISITORS")?></a><?
				else :
					?><?echo GetMessage("STATT_VISITORS")?><?
				endif;
			?></div>
			<div class="right"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&amp;del_filter=Y"><?echo $arResult["STATISTIC"]["TOTAL_GUESTS"]?></a><?
				else :
					?><?echo $arResult["STATISTIC"]["TOTAL_GUESTS"]?><?
				endif;
			?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="container" title="<?echo GetMessage("STATT_VIEW_TODAY_GUESTS")." (".$arResult["NOW"].")"?>">
		<div class="inner">
			<div class="right today"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/guest_list.php?lang=<?=LANGUAGE_ID?>&amp;find_period_date1=<?echo $arResult["TODAY"]?>&amp;find_period_date2=<?echo $arResult["TODAY"]?>&amp;set_filter=Y"><?echo $arResult["STATISTIC"]["TODAY_GUESTS"]?></a><?
				else :
					?><?echo $arResult["STATISTIC"]["TODAY_GUESTS"]?><?
				endif;
			?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="container" title="<?echo GetMessage("STATT_VIEW_USERS_ONLINE")." (".$arResult["NOW"].")"?>">
		<div class="inner">
			<div class="right today"><?
				if ($arResult["IS_ADMIN"]) :
					?><a href="/bitrix/admin/users_online.php?lang=<?=LANGUAGE_ID?>"><?echo $arResult["STATISTIC"]["ONLINE_GUESTS"]?></a><?
				else :
					?><?echo $arResult["STATISTIC"]["ONLINE_GUESTS"]?><?
				endif;
			?></div>
			<div class="clear"></div>
		</div>
	</div>
</div>
