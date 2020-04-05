<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if (strlen($arResult["urlToNewPost"]) > 0 || strlen($arResult["urlToBecomeFriend"]) > 0 || strlen($arResult["urlToAddFriend"]) > 0)
{
?>
	<ul>
	<li class="blog-settings">
	<h3 class="blog-sidebar-title"><?=GetMessage("BLOG_MENU_TITLE")?></h3>
	<ul>
		<?
		if (strlen($arResult["urlToNewPost"])>0)
		{
			?>
			<li><a href="<?=$arResult["urlToNewPost"]?>"  title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>"><?=GetMessage("BLOG_MENU_ADD_MESSAGE")?></a></li>
			<?
		}
		
		if(strlen($arResult["urlToDraft"])>0)
		{
			?>
			<li><a href="<?=$arResult["urlToDraft"]?>"  title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>"><?=GetMessage("BLOG_MENU_DRAFT_MESSAGES")?><?if(IntVal($arResult["CntToDraft"]) > 0 ) echo " (".$arResult["CntToDraft"].")"?></a></li>

			<?
		}
		if(strlen($arResult["urlToModeration"])>0 && IntVal($arResult["CntToModerate"]) > 0)
		{
			?>
			<li><a href="<?=$arResult["urlToModeration"]?>"  title="<?=GetMessage("BLOG_MENU_MODERATION_MESSAGES_TITLE")?>"><?=GetMessage("BLOG_MENU_MODERATION_MESSAGES")?><?if(IntVal($arResult["CntToModerate"]) > 0 ) echo " (".$arResult["CntToModerate"].")"?></a></li>

			<?
		}

		if(strlen($arResult["urlToBecomeFriend"])>0)
		{
			?>
			<li><a href="<?=$arResult["urlToBecomeFriend"]?>" title="<?=GetMessage("BLOG_MENU_FR_B_F")?>"><?=GetMessage("BLOG_MENU_FR_B_F")?></a></li>
			<?
		}
		if(strlen($arResult["urlToAddFriend"])>0)
		{
			?>
			<li><a href="<?=$arResult["urlToAddFriend"]?>" title="<?=GetMessage("BLOG_MENU_FR_A_F")?>"><?=GetMessage("BLOG_MENU_FR_A_F")?></a></li>
			<?
		}
		if(strlen($arResult["urlToBlogEdit"])>0)
		{
			?>
			<li><a href="<?=$arResult["urlToBlogEdit"]?>" title="<?=GetMessage("BLOG_MENU_SETTINGS_TITLE")?>"><?=GetMessage("BLOG_MENU_SETTINGS")?></a></li>
			<?
		}
		?>
	</ul>
	</li>
	</ul>
<?
}