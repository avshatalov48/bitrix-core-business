<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(is_array($arResult["SEARCH"]) && !empty($arResult["SEARCH"])):
?>
<noindex>
	<div class="search-tags-cloud" <?=$arParams["WIDTH"]?>><?
		foreach ($arResult["SEARCH"] as $key => $res)
		{
		?><a href="<?=$res["URL"]?>" style="font-size: <?=$res["FONT_SIZE"]?>px; color: #<?=$res["COLOR"]?>;px" rel="nofollow"><?=$res["NAME"]?></a> <?
		}
	?></div>
</noindex>
<?
endif;
?>