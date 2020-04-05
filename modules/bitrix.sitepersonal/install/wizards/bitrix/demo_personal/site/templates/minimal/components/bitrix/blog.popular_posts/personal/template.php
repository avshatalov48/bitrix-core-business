<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<dl class="block-list">
<?
foreach($arResult as $arPost)
{
	?>
	<dt><?=$arPost["DATE_PUBLISH_FORMATED"]?></dt>
	<dd><a href="<?=$arPost["urlToPost"]?>"><?echo $arPost["TITLE"]; ?></a></dd>
	<?
}
?>	
</dl>