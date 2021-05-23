<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<ul>
	<li class="blog-search">
		<h3 class="blog-sidebar-title"><?=GetMessage("BLOG_MAIN_SEARCH_SEARCH")?></h3>
		<div class="blog-search-form">
		<form method="get" action="<?=$arParams["SEARCH_PAGE"]?>">
		<input type="hidden" name="<?=$arParams["PAGE_VAR"]?>" value="search">
			<div class="blog-search-text"><input type="text" name="q" size="15" value="<?=$arResult["q"]?>"></div>
			<div class="blog-search-select">
				<select name="where">
				<?foreach($arResult["WHERE"] as $k => $v)
				{
					?><option value="<?=$k?>"<?=$k==$arResult["where"]?" selected":""?>><?=$v?></option><?
				}
				?>
				</select>
			</div>
			<div class="blog-search-submit"><input type="submit" value="<?=GetMessage("BLOG_SEARCH_BUTTON")?>"></div>

		<?if($arResult["how"]=="d"):?>
			<input type="hidden" name="how" value="d">
		<?endif;?>
		</form>
		</div>
	</li>
</ul>