<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if($arResult["urlToBlogEdit"] <> '')
{?>
<div class="blog-settings-menu-box">
	<?
	if($arResult["urlToUserSettings"] <> '')
	{
		?>
		<span class="blog-menu-user-settings"><a href="<?=$arResult["urlToUserSettings"]?>" title="<?=GetMessage("BLOG_MENU_USER_SETTINGS_TITLE")?>"><?=GetMessage("BLOG_MENU_USER_SETTINGS")?></a></span>
		<span class="blog-vert-separator"></span>
		<?
	}
	if($arResult["urlToBlogEdit"] <> '')
	{
		?>
		<span class="blog-menu-blog-edit"><a href="<?=$arResult["urlToBlogEdit"]?>" title="<?=GetMessage("BLOG_MENU_BLOG_EDIT_TITLE")?>"><?=GetMessage("BLOG_MENU_BLOG_EDIT")?></a></span>
		<?
	}
	?>
</div>
<?
}?>