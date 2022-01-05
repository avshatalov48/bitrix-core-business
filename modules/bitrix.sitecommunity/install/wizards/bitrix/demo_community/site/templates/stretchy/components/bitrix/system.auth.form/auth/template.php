<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

?><ul id="user-menu"><?
if ($arResult["FORM_TYPE"] != "login")
{
	?><li><?=GetMessage("AUTH_HELLO_1")?><a href="<?= $arResult["urlToOwnProfile"] ?>"><?= $arResult["USER_LOGIN"]?></a><?=GetMessage("AUTH_HELLO_2")?></li><?
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
	?><li><a href="<?= $GLOBALS["APPLICATION"]->GetCurPageParam("logout=yes&".bitrix_sessid_get(), array("logout", "sessid")) ?>" title="<?=GetMessage("AUTH_LOGOUT")?>"><?=GetMessage("AUTH_LOGOUT")?></a></li><?
}
else
{
	?><li><?=GetMessage("AUTH_HELLO_1")?><b><?=GetMessage("AUTH_GUEST")?><?=GetMessage("AUTH_HELLO_2")?></b></li>
	<li><a href="<?= SITE_DIR."auth/?backurl=".$GLOBALS["APPLICATION"]->GetCurPageParam("", array("login", "logout")) ?>" title="<?=GetMessage("AUTH_LOGIN_DESC")?>"><?=GetMessage("AUTH_LOGIN")?></a></li><?
	if($arResult["NEW_USER_REGISTRATION"] == "Y")
	{
		?><li><a href="<?=$arResult["AUTH_REGISTER_URL"]?>" title="<?=GetMessage("AUTH_REGISTER_DESC")?>"><?=GetMessage("AUTH_REGISTER")?></a></li><?
	}
}
?></ul>