<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @var CMain $APPLICATION */
/** @var CUser $USER */
/** @var CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

//*************************************
//show current authorization section
//*************************************
?>
<form action="<?=$arResult['FORM_ACTION']?>" method="post">
<?php echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?php echo GetMessage('subscr_title_auth')?></td></tr></thead>
<tr>
	<td width="40%"><?php echo GetMessage('adm_auth_user')?>
		<?php echo htmlspecialcharsbx($USER->GetFormattedName(false));?> [<?php echo htmlspecialcharsbx($USER->GetLogin())?>].
	</td>
	<td width="60%">
		<?php if ($arResult['ID'] == 0):?>
			<?php echo GetMessage('subscr_auth_logout1')?> <a href="<?php echo $arResult['FORM_ACTION']?>?logout=yes&amp;<?=bitrix_sessid_get()?>&amp;sf_EMAIL=<?php echo $arResult['REQUEST']['EMAIL']?><?php echo $arResult['REQUEST']['RUBRICS_PARAM']?>"><?php echo GetMessage('adm_auth_logout')?></a><?php echo GetMessage('subscr_auth_logout2')?><br />
		<?php else:?>
			<?php echo GetMessage('subscr_auth_logout3')?> <a href="<?php echo $arResult['FORM_ACTION']?>?logout=yes&amp;<?=bitrix_sessid_get()?>&amp;sf_EMAIL=<?php echo $arResult['REQUEST']['EMAIL']?><?php echo $arResult['REQUEST']['RUBRICS_PARAM']?>"><?php echo GetMessage('adm_auth_logout')?></a><?php echo GetMessage('subscr_auth_logout4')?><br />
		<?php endif;?>
	</td>
</tr>
</table>
</form>
<br />
