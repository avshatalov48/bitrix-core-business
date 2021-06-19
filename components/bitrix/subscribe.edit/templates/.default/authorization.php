<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//*************************************
//show current authorization section
//*************************************
?>
<form action="<?=$arResult["FORM_ACTION"]?>" method="post">
<?echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?echo GetMessage("subscr_title_auth")?></td></tr></thead>
<tr>
	<td width="40%"><?echo GetMessage("adm_auth_user")?>
		<?echo htmlspecialcharsbx($USER->GetFormattedName(false));?> [<?echo htmlspecialcharsbx($USER->GetLogin())?>].
	</td>
	<td width="60%">
		<?if($arResult["ID"]==0):?>
			<?echo GetMessage("subscr_auth_logout1")?> <a href="<?echo $arResult["FORM_ACTION"]?>?logout=yes&amp;<?=bitrix_sessid_get()?>&amp;sf_EMAIL=<?echo $arResult["REQUEST"]["EMAIL"]?><?echo $arResult["REQUEST"]["RUBRICS_PARAM"]?>"><?echo GetMessage("adm_auth_logout")?></a><?echo GetMessage("subscr_auth_logout2")?><br />
		<?else:?>
			<?echo GetMessage("subscr_auth_logout3")?> <a href="<?echo $arResult["FORM_ACTION"]?>?logout=yes&amp;<?=bitrix_sessid_get()?>&amp;sf_EMAIL=<?echo $arResult["REQUEST"]["EMAIL"]?><?echo $arResult["REQUEST"]["RUBRICS_PARAM"]?>"><?echo GetMessage("adm_auth_logout")?></a><?echo GetMessage("subscr_auth_logout4")?><br />
		<?endif;?>
	</td>
</tr>
</table>
</form>
<br />
