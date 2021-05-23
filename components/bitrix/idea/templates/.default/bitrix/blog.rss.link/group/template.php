<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(!empty($arResult))
{
	?>
	<span class="idea-post-comment-rss-icon">
		<a href="<?=$arResult[0]["url"]?>" title="<?=$arResult[0]["name"]?>" class="blog-rss-icon-small"></a>
	</span>
	<?
}
?>