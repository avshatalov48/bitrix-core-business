<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if ($arResult["FORM_TYPE"] != "login") 
{
	?><div id="user-menu">
		<div id="user-name"><?=GetMessage("AUTH_HELLO")?> <a href="<?=$arResult["urlToOwnProfile"]?>"><?=$arResult["USER_LOGIN"]?></a>!</div>
		<ul class="mdash-list">
			<li><a href="<?=$arResult["urlToOwnProfile"]?>"><?=GetMessage("AUTH_PROFILE")?></a></li><?

			if (!empty($arResult["urlToCreateMessageInBlog"]))
			{
				?><li><a href="<?=$arResult["urlToCreateMessageInBlog"]?>"><?=GetMessage("AUTH_BLOG_MESSAGE")?></a></li><?
			}
			if (array_key_exists("PATH_TO_SONET_LOG", $arParams) && $arParams["PATH_TO_SONET_LOG"] <> '')
			{
				?><li>
					<a href="<?=$arParams["PATH_TO_SONET_LOG"]?>"><?=GetMessage("AUTH_SONET_LOG")?></a><?
					if (intval($arResult["LOG_COUNTER"]) > 0)
						echo " (".intval($arResult["LOG_COUNTER"]).")";
				?></li><?
			}
		?></ul>
		<a href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam("logout=yes&".bitrix_sessid_get(), array("logout", "sessid"))?>" id="logout" title="<?=GetMessage("AUTH_LOGOUT")?>"><?=GetMessage("AUTH_LOGOUT")?></a>
	</div><?
}
else 
{
	?><form action="<?=$arResult["AUTH_URL"]?>" METHOD="POST" target="_top">
		<input type="hidden" name="AUTH_FORM" value="Y" />
		<input type="hidden" name="TYPE" value="AUTH" />
		<table id="auth-form" cellspacing="0">
			<tr>
				<td colspan="2" align="right"><?
				if($arResult["NEW_USER_REGISTRATION"] == "Y") 
				{
					?><a href="<?=$arResult["AUTH_REGISTER_URL"]?>" title="<?=GetMessage("AUTH_REGISTER_DESC")?>"><?=GetMessage("AUTH_REGISTER")?></a><?
					?>&nbsp;&nbsp;&nbsp;<a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>" title="<?=GetMessage("AUTH_FORGOT_PASSWORD")?>">?</a><? 
				} 
				else
				{
					?><a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>"><?=GetMessage("AUTH_FORGOT_PASSWORD")?></a><? 
				}
				?></td>
			</tr>
			<tr>
				<td class="field-name"><label for="login-textbox"><?=GetMessage("AUTH_LOGIN")?>:</label></td>
				<td><input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["USER_LOGIN"]?>" class="textbox" id="login-textbox" /></td>
			</tr>
			<tr>
				<td class="field-name"><label for="password-textbox"><?=GetMessage("AUTH_PASSWORD")?>:</label></td>
				<td><input type="password" name="USER_PASSWORD" maxlength="255" class="textbox" id="password-textbox" /></td>
			</tr><?
			if ($arResult["STORE_PASSWORD"] == "Y")
			{
				?><tr>
					<td>&nbsp;</td>
					<td><?
						?><input type="checkbox" id="remember-checkbox" class="checkbox" name="USER_REMEMBER" value="Y" /><?
						?><label for="remember-checkbox" class="remember"><?=GetMessage("AUTH_REMEMBER_ME")?></label><?
					?></td>
				</tr><?
			}
			?><tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="Login" value="<?=GetMessage("AUTH_LOGIN_BUTTON")?>" /></td>
			</tr>							
		</table>
	</form><?
}
?>