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

						<td><a href="<?= $arParams["PATH_TO_BLOG_INDEX"]?>" title="<?=GetMessage("BLOG_MENU_BLOGS_LIST_TITLE")?>"><img src="<?=$templateFolder?>/images/icon_blog_list.gif" class="blogmenuicon" border="0" title="<?=GetMessage("BLOG_MENU_BLOGS_LIST_TITLE")?>" alt=""></a></td>
						<td><a href="<?=$arParams["PATH_TO_BLOG_INDEX"]?>" title="<?=GetMessage("BLOG_MENU_BLOGS_LIST_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_BLOGS_LIST")?></a></td>
						
						<?
						if(strlen($arResult["urlToCurrentBlog"])>0)
						{
							?>
							<td><div class="blogtoolseparator"></div></td>
							<td><a href="<?=$arResult["urlToCurrentBlog"]?>" title="<?= str_replace("#NAME#", $arResult["Blog"]["NAME"], GetMessage("BLOG_MENU_CURRENT_BLOG_TITLE")) ?>" ><img src="<?=$templateFolder?>/images/icon_current_blog.gif" class="blogmenuicon" border="0" alt=""></a></td>
							<td><a href="<?=$arResult["urlToCurrentBlog"]?>" title="<?= str_replace("#NAME#", $arResult["Blog"]["NAME"], GetMessage("BLOG_MENU_CURRENT_BLOG_TITLE")) ?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_CURRENT_BLOG")?></a></td>
							<?
						}

						if(strlen($arResult["urlToUser"])>0)
						{
							?>
								<td><div class="blogtoolseparator"></div></td>
							<td><a href="<?=$arResult["urlToUser"]?>" title="<?=GetMessage("BLOG_MENU_PROFILE_TITLE")?>"><img src="<?=$templateFolder?>/images/icon_my_profile.gif" class="blogmenuicon" border="0" title="<?=GetMessage("BLOG_MENU_PROFILE_TITLE")?>" hspace="4" alt=""></a></td>
							<td><a href="<?=$arResult["urlToUser"]?>" title="<?=GetMessage("BLOG_MENU_PROFILE_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_PROFILE")?></a></td>
							<?
						}
						?>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td width="100%" class="blogtoolbar">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td><div class="blogtoolsection"></div></td>
						<td><div class="blogtoolsection"></div></td>

						<?
						if (strlen($arResult["urlToNewPost"])>0)
						{
							?>
							<td><a href="<?=$arResult["urlToNewPost"]?>" title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>"><img src="<?=$templateFolder?>/images/icon_new_message.gif" class="blogmenuicon" border="0" title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>" hspace="4" alt=""></a></td>
							<td><a href="<?=$arResult["urlToNewPost"]?>" title="<?=GetMessage("BLOG_MENU_ADD_MESSAGE_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_ADD_MESSAGE")?></a></td>
							<?
						}
						
						if(strlen($arResult["urlToDraft"])>0)
						{
							?>
							<td><div class="blogtoolseparator"></div></td>
							<td><a href="<?=$arResult["urlToDraft"]?>" title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>"><img src="<?=$templateFolder?>/images/icon_draft_messages.gif" class="blogmenuicon" border="0" title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>" hspace="4" alt=""></a></td>
							<td><a href="<?=$arResult["urlToDraft"]?>" title="<?=GetMessage("BLOG_MENU_DRAFT_MESSAGES_TITLE")?>" class="blogtoolbutton"><?=GetMessage("BLOG_MENU_DRAFT_MESSAGES")?></a></td>
							<?
						}
						if(strlen($arResult["urlToBlogEdit"])>0)
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