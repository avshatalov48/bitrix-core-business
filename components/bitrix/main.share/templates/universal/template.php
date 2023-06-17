<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["PAGE_URL"])
{
	?>
	<ul class="main-share">
		<?
		if (is_array($arResult["BOOKMARKS"]) && !empty($arResult["BOOKMARKS"]))
		{
			foreach(array_reverse($arResult["BOOKMARKS"]) as $name => $arBookmark)
			{
				?><li class="main-share-icon"><?=$arBookmark["ICON"]?></li><?
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