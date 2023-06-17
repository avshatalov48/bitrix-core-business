<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["PAGE_URL"])
{
	?>
	<ul class="bx-share-social">
		<?
		if (is_array($arResult["BOOKMARKS"]) && !empty($arResult["BOOKMARKS"]))
		{
			foreach(array_reverse($arResult["BOOKMARKS"]) as $name => $arBookmark)
			{
				?><li class="bx-share-icon"><?=$arBookmark["ICON"]?></li><?
			}
		}
		?>
	</ul>
	<?
}
else
{
	?><?=GetMessage("SHARE_ERROR_EMPTY_SERVER")?><?
}
?>