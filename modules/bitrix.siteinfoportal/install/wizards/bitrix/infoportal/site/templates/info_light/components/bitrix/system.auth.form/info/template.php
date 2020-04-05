<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<p class="cart">
<?
if ($arResult["FORM_TYPE"] == "login"):
?>
	<a href="<?=$arResult["AUTH_URL"]?>"><?=GetMessage("AUTH_LOGIN")?></a>
<?
	if($arResult["NEW_USER_REGISTRATION"] == "Y")
	{
?>
	<a href="<?=$arResult["AUTH_REGISTER_URL"]?>"><?=GetMessage("AUTH_REGISTER")?></a>
<?
	}
?>
<?
else:
?>
	<a href="<?=$arResult['PROFILE_URL']?>"><?
	$name = trim($USER->GetFullName());
	if (strlen($name) <= 0)
		$name = $USER->GetLogin();
		
	echo htmlspecialcharsEx($name);
?></a>
	<a href="<?=$APPLICATION->GetCurPageParam("logout=yes", Array("logout"))?>"><?=GetMessage("AUTH_LOGOUT")?></a>
<?
endif;
?>
</p>