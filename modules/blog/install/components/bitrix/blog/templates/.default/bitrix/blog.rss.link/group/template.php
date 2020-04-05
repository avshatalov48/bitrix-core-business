<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult))
{
	?>
	<div class="blog-group-rss">
		<a href="<?=$arResult[0]["url"]?>" title="<?=$arResult[0]["name"]?>" class="blog-rss-icon"></a>
	</div>
	<?
}
?>