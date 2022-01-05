<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if ($arResult["FORM_TYPE"] == "login"):?>


<div id="login-form-window">

<a href="" onclick="return CloseLoginForm()" style="float:right;"><?=GetMessage("AUTH_CLOSE_WINDOW")?></a>

<form method="post" target="_top" action="<?=$arResult["AUTH_URL"]?>">
	<?
	if ($arResult["BACKURL"] <> '')
	{
	?>
		<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
	<?
	}
	?>
	<?
	foreach ($arResult["POST"] as $key => $value)
	{
	?>
	<input type="hidden" name="<?=$key?>" value="<?=$value?>" />
	<?
	}
	?>
	<input type="hidden" name="AUTH_FORM" value="Y" />
	<input type="hidden" name="TYPE" value="AUTH" />

	<table width="95%">
			<tr>
				<td colspan="2">
				<?=GetMessage("AUTH_LOGIN")?>:<br />
				<input type="text" name="USER_LOGIN" maxlength="50" value="<?=$arResult["USER_LOGIN"]?>" size="17" /></td>
			</tr>
			<tr>
				<td colspan="2">
				<?=GetMessage("AUTH_PASSWORD")?>:<br />
				<input type="password" name="USER_PASSWORD" maxlength="255" size="17" /></td>
			</tr>
		<?
		if ($arResult["STORE_PASSWORD"] == "Y") 
		{
		?>
			<tr>
				<td valign="top"><input type="checkbox" id="USER_REMEMBER" name="USER_REMEMBER" value="Y" /></td>
				<td width="100%"><label for="USER_REMEMBER"><?=GetMessage("AUTH_REMEMBER_ME")?></label></td>
			</tr>
		<?
		}
		?>
			<tr>
				<td colspan="2"><input type="submit" name="Login" value="<?=GetMessage("AUTH_LOGIN_BUTTON")?>" /></td>
			</tr>

			<tr>
				<td colspan="2"><a href="<?=$arResult["AUTH_FORGOT_PASSWORD_URL"]?>"><?=GetMessage("AUTH_FORGOT_PASSWORD_2")?></a></td>
			</tr>
		<?
		if($arResult["NEW_USER_REGISTRATION"] == "Y")
		{
		?>
			<tr>
				<td colspan="2"><a href="<?=$arResult["AUTH_REGISTER_URL"]?>"><?=GetMessage("AUTH_REGISTER")?></a><br /></td>
			</tr>
		<?
		}
		?>
	</table>	
</form>
</div>

<a href="<?=$arResult["AUTH_REGISTER_URL"]?>" onclick="return ShowLoginForm();"><?=GetMessage("AUTH_LOGIN_BUTTON")?></a>

<?else:?>
	<a href="<?=$APPLICATION->GetCurPageParam("logout=yes&".bitrix_sessid_get(), Array("logout", "sessid"))?>"><?=GetMessage("AUTH_LOGOUT_BUTTON")?></a>
<?endif?>