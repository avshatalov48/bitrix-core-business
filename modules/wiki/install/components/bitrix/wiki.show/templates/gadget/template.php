<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div id="wiki-post">
<?

if(!empty($arResult["FATAL_MESSAGE"]))
{
	?>
	<div class="wiki-errors">
		<div class="wiki-error-text">
			<?=$arResult['FATAL_MESSAGE']?>
		</div>
	</div>
	<?
}
else 
{
	?><div id="wiki-post-content"><?=$arResult['ELEMENT']['DETAIL_TEXT'];?></div><?
	
	if (intval($arResult['ELEMENT']['ID']) > 0)
	{
		?><div class="wiki-post-link"><a href="<?=$arResult['ELEMENT']['URL'];?>"><?=GetMessage("WIKI_SHOW_GADGET_LINK")?></a> <a href="<?=$arResult['ELEMENT']['URL'];?>"><img width="7" height="7" border="0" src="/bitrix/images/socialnetwork/icons/arrows.gif" /></a></div><?
	}
}
?>
</div>