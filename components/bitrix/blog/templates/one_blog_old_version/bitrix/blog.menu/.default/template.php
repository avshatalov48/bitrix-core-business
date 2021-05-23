<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="blogtoolblock">
	<?
	if ($arResult["SecondLine"] == "Y")
	{
		?>
		<tr>
			<td width="100%" class="blogtoolbar">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td><div class="blogtoolsection"></div></td>
						<td><div class="blogtoolsection"></div></td>
						<?
						if ($arResult["urlToNewPost"] <> '')
						{
							?>
							<td><a href="<?=$arResult["urlToNewPost"]?>" title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>"><img src="<?=$templateFolder?>/images/icon_new_message.gif" class="blogmenuicon" border="0" title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>" hspace="4" alt=""></a></td>
							<td><a href="<?=$arResult["urlToNewPost"]?>" title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_ADD_MESSAGE")?></a></td>
							<?
						}
						
						if($arResult["urlToDraft"] <> '')
						{
							?>
							<td><div class="blogtoolseparator"></div></td>
							<td><a href="<?=$arResult["urlToDraft"]?>" title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>"><img src="<?=$templateFolder?>/images/icon_draft_messages.gif" class="blogmenuicon" border="0" title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>" hspace="4" alt=""></a></td>
							<td><a href="<?=$arResult["urlToDraft"]?>" title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_DRAFT_MESSAGES")?></a></td>
							<?
						}
					if($arResult["urlToUser"] <> '')
					{
						?>
							<td><div class="blogtoolseparator"></div></td>
						<td><a href="<?=$arResult["urlToUser"]?>" title="<?=GetMessage("BLOG_MENU_PROFILE_TITLE")?>"><img src="<?=$templateFolder?>/images/icon_my_profile.gif" class="blogmenuicon" border="0" title="<?=GetMessage("BLOG_MENU_PROFILE_TITLE")?>" hspace="4" alt=""></a></td>
						<td><a href="<?=$arResult["urlToUser"]?>" title="<?=GetMessage("BLOG_MENU_PROFILE_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_PROFILE")?></a></td>
						<?
					}
						if($arResult["urlToBlogEdit"] <> '')
						{
							?>
							<td><div class="blogtoolseparator"></div></td>
							<td><a href="<?=$arResult["urlToBlogEdit"]?>" title="<?=GetMessage("BLOG_MENU_BLOG_EDIT_TITLE")?>"><img src="<?=$templateFolder?>/images/icon_blog_settings.gif" class="blogmenuicon" border="0" title="<?=GetMessage("BLOG_MENU_BLOG_EDIT_TITLE")?>" hspace="4" alt=""></a></td>
							<td><a href="<?=$arResult["urlToBlogEdit"]?>" title="<?=GetMessage("BLOG_MENU_BLOG_EDIT_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_BLOG_EDIT")?></a></td>
							<?
						}

					?>	
					</tr>
				</table>
			</td>
		</tr>
		<?
	}
	?>
</table>
<br />