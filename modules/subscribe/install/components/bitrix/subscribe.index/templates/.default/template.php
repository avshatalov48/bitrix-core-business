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
?>
<div class="subscribe-index">

<h4><?php echo GetMessage('SUBSCR_NEW_TITLE')?></h4>
<p><?php echo GetMessage('SUBSCR_NEW_NOTE')?></p>
<form action="<?=$arResult['FORM_ACTION']?>" method="get">
	<table class="data-table" border="0" cellpadding="0" cellspacing="0">
		<thead>
		<tr>
			<td>&nbsp;</td>
			<td><?php echo GetMessage('SUBSCR_NAME')?></td>
			<td><?php echo GetMessage('SUBSCR_DESC')?></td>
			<?php if ($arResult['SHOW_COUNT']):?>
				<td><?php echo GetMessage('SUBSCR_CNT')?></td>
			<?php endif;?>
		</tr>
		</thead>
		<?php foreach ($arResult['RUBRICS'] as $itemID => $itemValue):?>
		<tr>
			<td><input type="checkbox" name="sf_RUB_ID[]" id="sf_RUB_ID_<?=$itemID?>" value="<?=$itemValue['ID']?>" checked /></td>
			<td><label for="sf_RUB_ID_<?=$itemID?>"><?=$itemValue['NAME']?></label></td>
			<td><?=$itemValue['DESCRIPTION']?></td>
			<?php if ($arResult['SHOW_COUNT']):?>
				<td align="right"><?=$itemValue['SUBSCRIBER_COUNT']?></td>
			<?php endif?>
		</tr>
		<?php endforeach;?>
	</table>
	<p><?php echo GetMessage('SUBSCR_ADDR')?>&nbsp;<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult['EMAIL']?>" title="<?php echo GetMessage('SUBSCR_EMAIL_TITLE')?>" /><input type="submit" value="<?php echo GetMessage('SUBSCR_BUTTON')?>" /></p>
</form>
<br />

<form action="<?=$arResult['FORM_ACTION']?>" method="get">
<?php echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?php echo GetMessage('SUBSCR_EDIT_TITLE')?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br />
		<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult['EMAIL']?>" title="<?php echo GetMessage('SUBSCR_EMAIL_TITLE')?>" /></p>
		<?php if ($arResult['SHOW_PASS'] == 'Y'):?>
			<p><?php echo GetMessage('SUBSCR_EDIT_PASS')?><span class="starrequired">*</span><br />
			<input type="password" name="AUTH_PASS" size="20" value="" title="<?php echo GetMessage('SUBSCR_EDIT_PASS_TITLE')?>" /></p>
		<?php else:?>
			<p><span class="green"><?php echo GetMessage('SUBSCR_EDIT_PASS_ENTERED')?></span><span class="starrequired">*</span></p>
		<?php endif;?>
	<td width="60%">
		<p><?php echo GetMessage('SUBSCR_EDIT_NOTE')?></p>
	</td>
</tr>
<tfoot><tr><td colspan="2">
	<input type="submit" value="<?php echo GetMessage('SUBSCR_EDIT_BUTTON')?>" />
</td></tr></tfoot>
</table>
<input type="hidden" name="action" value="authorize" />
</form>
<br />

<form action="<?=$arResult['FORM_ACTION']?>" method="get">
<?php echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?php echo GetMessage('SUBSCR_PASS_TITLE')?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br />
		<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult['EMAIL']?>" title="<?php echo GetMessage('SUBSCR_EMAIL_TITLE')?>" /></p>
	<td width="60%">
		<p><?php echo GetMessage('SUBSCR_PASS_NOTE')?></p>
	</td>
</tr>
<tfoot><tr><td colspan="2">
	<input type="submit" value="<?php echo GetMessage('SUBSCR_PASS_BUTTON')?>" />
</td></tr></tfoot>
</table>
<input type="hidden" name="action" value="sendpassword" />
</form>
<br />

<form action="<?=$arResult['FORM_ACTION']?>" method="get">
<?php echo bitrix_sessid_post();?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="data-table">
<thead><tr><td colspan="2"><?php echo GetMessage('SUBSCR_UNSUBSCRIBE_TITLE')?></td></tr></thead>
<tr valign="top">
	<td width="40%">
		<p>e-mail<br />
		<input type="text" name="sf_EMAIL" size="20" value="<?=$arResult['EMAIL']?>" title="<?php echo GetMessage('SUBSCR_EMAIL_TITLE')?>" /></p>
		<?php if ($arResult['SHOW_PASS'] == 'Y'):?>
			<p><?php echo GetMessage('SUBSCR_EDIT_PASS')?><span class="starrequired">*</span><br />
			<input type="password" name="AUTH_PASS" size="20" value="" title="<?php echo GetMessage('SUBSCR_EDIT_PASS_TITLE')?>" /></p>
		<?php else:?>
			<p><span class="green"><?php echo GetMessage('SUBSCR_EDIT_PASS_ENTERED')?></span><span class="starrequired">*</span></p>
		<?php endif;?>
	<td width="60%">
		<p><?php echo GetMessage('SUBSCR_UNSUBSCRIBE_NOTE')?></p>
	</td>
</tr>
<tfoot><tr><td colspan="2">
	<input type="submit" value="<?php echo GetMessage('SUBSCR_EDIT_BUTTON')?>" />
</td></tr></tfoot>
</table>
<input type="hidden" name="action" value="authorize" />
</form>
<br />

<p><span class="starrequired">*&nbsp;</span><?php echo GetMessage('SUBSCR_NOTE')?></p>

</div>
