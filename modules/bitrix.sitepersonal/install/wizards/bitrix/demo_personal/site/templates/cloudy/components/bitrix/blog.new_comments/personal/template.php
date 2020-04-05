<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<dl class="block-list">
<?
foreach($arResult as $arComment)
{
	?>
	<dt><?=$arComment["DATE_CREATE_FORMATED"]?></dt>
	<dd><a href="<?=$arComment["urlToComment"]?>"><?=$arComment["TEXT_FORMATED"]?></a></dd>
	<?
}
?>	
</dl>