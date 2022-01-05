<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult["FORM_TYPE"] != "login") 
{
?>
<div id="user-menu">
	<div id="user-name"><?=GetMessage("AUTH_HELLO")?> <a href="<?=$arResult["urlToOwnProfile"]?>"><?=$arResult["USER_NAME"]?></a>!</div>
	<ul class="mdash-list">
		<li><a href="<?=$arResult["urlToOwnProfile"]?>"><?=GetMessage("AUTH_PROFILE")?></a></li>
	<? 
	if (!empty($arResult["urlToOwnBlog"])) 
	{
	?>
		<li><a href="<?=$arResult["urlToOwnBlog"]?>"><?=GetMessage("AUTH_BLOG")?></a></li>
		<li><a href="<?=$arResult["urlToCreateMessageInBlog"]?>"><?=GetMessage("AUTH_BLOG_NEW_POST")?></a></li>
	<? 
	} 
	elseif (!empty($arResult["urlToCreateInBlog"])) 
	{
	?>
		<li><a href="<?=$arResult["urlToCreateInBlog"]?>"><?=GetMessage("AUTH_BLOG_CREATE")?></a></li>
	<? 
	}
	?>
	<?
	if (!empty($arParams["PATH_TO_SONET_MESSAGES"])) 
	{
	?>
		<li>
		<?$APPLICATION->IncludeComponent("bitrix:socialnetwork.events_dyn", "popup", Array(
				"PATH_TO_USER"	=>	SITE_DIR."people/user/#user_id#/",
				"PATH_TO_GROUP"	=>	SITE_DIR."groups/group/#group_id#/",
				"PATH_TO_MESSAGES"	=>	$arParams["PATH_TO_SONET_MESSAGES"],
				"PATH_TO_MESSAGE_FORM"	=>	SITE_DIR."people/messages/form/#user_id#/",
				"PATH_TO_MESSAGE_FORM_MESS"	=>	SITE_DIR."people/messages/form/#user_id#/#message_id#/",
				"PATH_TO_MESSAGES_CHAT"	=>	SITE_DIR."people/messages/chat/#user_id#/",
				"PATH_TO_SMILE"	=>	"/bitrix/images/socialnetwork/smile/",
				"MESSAGE_VAR"	=>	"message_id",
				"PAGE_VAR"	=>	"page",
				"USER_VAR"	=>	"user_id",
				"POPUP"	=>	"Y",
				)
			);
		?>
		</li>	
	<? 
	}
	?>
	</ul>
	<a href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam("logout=yes&".bitrix_sessid_get(), array("logout", "sessid"))?>" id="logout" title="<?=GetMessage("AUTH_LOGOUT")?>"><?=GetMessage("AUTH_LOGOUT")?></a>
</div>
<? 
} 
else 
{
?>
<form action="<?=$arResult["AUTH_URL"]?>" METHOD="POST" target="_top">
	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="AUTH" />
	
	<table id="auth-form" cellspacing="0">
		<tr>
			<td colspan="2" align="right"><?
	 if($arResult["NEW_USER_REGISTRATION"] == "Y") 
	 {
?>
		<a href="<?=$arResult["AUTH_REGISTER_URL"]?>" title="<?=GetMessage("AUTH_REGISTER_DESC")?>"><?=GetMessage("AUTH_REGISTER")?></a><?
		?>&nbsp;&nbsp;&nbsp;<a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" title="<?=GetMessage("AUTH_FORGOT_PASSWORD")?>">?</a>
<? 
	 } 
	 else
	 {
?>
		<a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>"><?=GetMessage("AUTH_FORGOT_PASSWORD")?></a>
<? 
	 }
?>
			</td>
		</tr>
		<tr>
			<td class="field-name"><label for="login-textbox"><?=GetMessage("AUTH_LOGIN")?>:</label></td>
			<td><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["USER_LOGIN"]?>" class="textbox" id="login-textbox" /></td>
		</tr>
		<tr>
			<td class="field-name"><label for="password-textbox"><?=GetMessage("AUTH_PASSWORD")?>:</label></td>
			<td><input type="password" name="USER_PASSWORD" maxlength="255" class="textbox" id="password-textbox" /></td>
		</tr>
<?
	if ($arResult["STORE_PASSWORD"] == "Y")
	{
?>
		<tr>
			<td>&nbsp;</td>
			<td><input type="checkbox" id="remember-checkbox" class="checkbox" name="USER_REMEMBER" value="Y" /><?
				?><label for="remember-checkbox" class="remember"><?=GetMessage("AUTH_REMEMBER_ME")?></label></td>
		</tr>
<?
	}
?>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="Login" value="<?=GetMessage("AUTH_LOGIN_BUTTON")?>" /></td>
		</tr>							
	</table>
</form>
<?
}
?>