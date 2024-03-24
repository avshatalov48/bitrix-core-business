<?php
/* @var CMain APPLICATION */
if (!check_bitrix_sessid())
{
	return;
}
IncludeModuleLangFile(__FILE__);

if ($ex = $APPLICATION->GetException())
{
	echo CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => GetMessage('MOD_INST_ERR'),
		'DETAILS' => $ex->GetString(),
		'HTML' => true,
	]);
}
else
{
	echo CAdminMessage::ShowNote(GetMessage('MOD_INST_OK'));
}

if ($_REQUEST['public_dir'] <> '') :
?>
<p><?=GetMessage('MOD_DEMO_DIR')?></p>
<table border="0" cellspacing="0" cellpadding="0" class="internal">
	<tr class="heading">
		<td align="center"><b><?=GetMessage('MOD_DEMO_SITE')?></b></td>
		<td align="center"><b><?=GetMessage('MOD_DEMO_LINK')?></b></td>
	</tr>
	<?php
	$sites = CSite::GetList('', '', ['ACTIVE' => 'Y']);
	while ($site = $sites->Fetch())
	{
		?>
		<tr>
			<td>[<?=htmlspecialcharsEx($site['ID'])?>] <?=htmlspecialcharsEx($site['NAME'])?></td>
			<td><a href="<?=htmlspecialcharsbx(($site['SERVER_NAME'] <> '' ? 'http://' . $site['SERVER_NAME'] : '') . $site['DIR'] . $_REQUEST['public_dir'] . '/index.php')?>"><?=htmlspecialcharsEx($site['DIR'] . $_REQUEST['public_dir'] . '/index.php')?></a></td>
		</tr>
		<?php
	}
	?>
</table>
<br>
<?php
endif;
?>
<form action="<?php echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?php echo LANGUAGE_ID?>">
	<input type="submit" name="" value="<?php echo GetMessage('MOD_BACK')?>">
<form>
