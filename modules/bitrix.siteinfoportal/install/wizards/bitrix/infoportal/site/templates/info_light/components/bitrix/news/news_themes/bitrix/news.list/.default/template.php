<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>
<? $themeLetter = ''; $themeLetterPrew = '';?>
<table class="theme-list" >
<?foreach($arResult["ITEMS"] as $arItem):?>
<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"));
	
	$themeLetter = strtoupper ( strtr( substr($arItem["NAME"], 0, 1), 'éöóêåíãøùçõúôûâàïðîëäæýÿ÷ñìèòüáþ¸', 'ÉÖÓÊÅÍÃØÙÇÕÚÔÛÂÀÏÐÎËÄÆÝß×ÑÌÈÒÜÁÞ¨') );
	if($themeLetter != $themeLetterPrew ):
	$themeLetterPrew = $themeLetter;?>
	<tr >
		<td class="theme-letter" ><?=$themeLetter?></td>
		<td>
	<?else:?>, <?endif;?>
<span class="news-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
		<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?><a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><b><?echo $arItem["NAME"]?></b></a><?else:?><b><?echo $arItem["NAME"]?></b><?endif;?>
 (<?=$arItem["COUNT_NEWS"]?>)</span><?if($themeLetter != $themeLetterPrew):?>
		</td>
	</tr>
	<?endif;
endforeach;?>
</table>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>