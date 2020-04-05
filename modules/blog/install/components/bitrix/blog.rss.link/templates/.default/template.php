<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if(!empty($arResult))
{
	?>
	<ul>
		<li class="blog-rss">
			<h3 class="blog-sidebar-title"><span style="float:right;"><a href="<?=$arResult[0]["url"]?>" title="<?=$arResult[0]["name"]?>" class="blog-rss-icon"></a></span><a href="<?=$arResult[0]["url"]?>" title="<?=$arResult[0]["name"]?>"><?=GetMessage("BC_RSS")?></a></h3>
			
		</li>
	</ul>
	<?
}
?>