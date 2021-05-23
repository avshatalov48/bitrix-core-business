<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (is_array($arResult["SEARCH"]) && !empty($arResult["SEARCH"]))
{
?>
<div class="photo-info-box photo-info-box-tags-cloud">
	<div class="photo-info-box-inner">
		<div class="photo-header-big">
			<div class="photo-header-inner">
				<?=GetMessage("P_TAGS_CLOUD")?>
			</div>
		</div>
		<div class="tags-cloud">
			<noindex>
				<div class="search-tags-cloud" <?=$arParams["WIDTH"]?>><?
					foreach ($arResult["SEARCH"] as $key => $res)
					{
					?><a href="<?=$res["URL"]?>" style="font-size: <?=$res["FONT_SIZE"]?>px;" rel="nofollow"><?=$res["NAME"]?></a> <?
					}
				?></div>
			</noindex>
		</div>
	</div>
</div>

<?
}
?>