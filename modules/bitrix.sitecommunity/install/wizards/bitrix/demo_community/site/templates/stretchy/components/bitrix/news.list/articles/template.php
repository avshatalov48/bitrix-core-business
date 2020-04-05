<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="news-list">

<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>


<?foreach($arResult["ITEMS"] as $arItem):?>
	<p class="news-item">
		<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
			<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img class="preview_picture" border="0" src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arItem["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arItem["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arItem["PREVIEW_PICTURE"]["ALT"]?>" title="<?=$arItem["NAME"]?>" style="float:left" /></a>
		<?endif?>

		<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]):?>
			<?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
				<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><b><?echo $arItem["NAME"]?></b></a><br />
			<?else:?>
				<b><?echo $arItem["NAME"]?></b><br />
			<?endif;?>
		<?endif;?>
		<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
			<?echo $arItem["PREVIEW_TEXT"];?>
		<?endif;?>
		<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
			<div style="clear:both"></div>
		<?endif?>
		
		<?if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
			<br /><span class="news-date-time"><img src="<?=$templateFolder?>/images/clocks.gif" width="9" height="9" border="0" alt="">&nbsp;<?echo $arItem["DISPLAY_ACTIVE_FROM"]?></span>
		<?endif?>

		<?if (isset($arItem["DISPLAY_PROPERTIES"]["FORUM_MESSAGE_CNT"])):?>
			<span class="news-date-time">|&nbsp;<img src="<?=$templateFolder?>/images/comments.gif" width="10" height="10" border="0" alt="">&nbsp;<?=GetMessage("NEWS_COMMENTS")?>: <?=$arItem["DISPLAY_PROPERTIES"]["FORUM_MESSAGE_CNT"]["VALUE"]?></span>
		<?endif?>

		<?if (isset($arItem["DISPLAY_PROPERTIES"]["rating"])):?>
			<span class="news-date-time">|&nbsp;<img src="<?=$templateFolder?>/images/rating.gif" width="11" height="11" border="0" alt="">&nbsp;<?=GetMessage("NEWS_RATING")?>: <?=$arItem["DISPLAY_PROPERTIES"]["rating"]["VALUE"]?></span>
		<?endif?>
	</p>
<?endforeach;?>

<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
</div>