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
	
		<div class="blog-tab-container">
		<div class="blog-tab-left"></div>
		<div class="blog-tab-right"></div>
		<div class="blog-tab">
			<span class="blog-tab-items">
				<a href="<?=$arResult[0]["url"]?>" title="<?=$arResult[0]["name"]?>" class="blog-rss-icon"></a>
			</span>
			<span class="blog-tab-title"><a href="<?=$arResult[0]["url"]?>" title="<?=$arResult[0]["name"]?>"><?=GetMessage("BC_RSS")?></a></span>
		</div>	
		</div>

	
	
	<?
}
?>