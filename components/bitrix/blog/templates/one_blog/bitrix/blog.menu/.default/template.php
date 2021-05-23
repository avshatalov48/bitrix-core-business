<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if($arResult["urlToOwnBlog"] <> '')
{
?>
<div class="blog-menu-box">
	<?
	if ($arResult["urlToOwnNewPost"] <> '')
	{
		?>
		<span class="blog-menu-post"><a href="<?=$arResult["urlToOwnNewPost"]?>"  title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>"><?=GetMessage("BLOG_MENU_ADD_MESSAGE")?></a></span>
		<span class="blog-vert-separator"></span>
		<?
	}
	if($arResult["urlToOwnBlog"] <> '')
	{
		?>
		<span class="blog-menu-blog"><a href="<?=$arResult["urlToOwnBlog"]?>" title="<?=str_replace("#NAME#", $arResult["OwnBlog"]["NAME"], GetMessage("BLOG_MENU_MY_BLOG_TITLE")) ?>"><?=GetMessage("BLOG_MENU_MY_BLOG")?></a></span>
		<span class="blog-vert-separator"></span>
		<?
	}
	if($arResult["urlToUser"] <> '')
	{
		?>
		<span class="blog-menu-profile"><a href="<?=$arResult["urlToUser"]?>" title="<?=GetMessage("BLOG_MENU_PROFILE_TITLE")?>"><?=GetMessage("BLOG_MENU_PROFILE")?></a></span>
		<span class="blog-vert-separator"></span>
		<?
	}
	if($arResult["urlToOwnBlogEdit"] <> '')
	{
		?>
		<span class="blog-menu-settings"><a href="<?=$arResult["urlToOwnBlogEdit"]?>" title="<?=GetMessage("BLOG_MENU_SETTINGS_TITLE")?>"><?=GetMessage("BLOG_MENU_SETTINGS")?></a></span>
		<?
	}
	?>
</div>
<?
}
?>