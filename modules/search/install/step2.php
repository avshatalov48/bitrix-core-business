<?php
if (!check_bitrix_sessid())
{
	return;
}
IncludeModuleLangFile(__FILE__);

$ex = $APPLICATION->GetException();
if ($ex)
{
	$msg = new CAdminMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => GetMessage('MOD_INST_ERR'),
		'DETAILS' => $ex->GetString(),
		'HTML' => true,
	]);
}
else
{
	$msg = new CAdminMessage([
		'TYPE' => 'OK',
		'MESSAGE' => GetMessage('MOD_INST_OK'),
	]);
}
$msg->Show();


if ($_REQUEST['public_dir'] <> ''):
?>
<p><?=GetMessage('SEARCH_DEMO_DIR')?></p>
<table border="0" cellspacing="0" cellpadding="0" class="internal">
	<tr class="heading">
		<td align="center"><p><b><?=GetMessage('SEARCH_SITE')?></b></p></td>
		<td align="center"><p><b><?=GetMessage('SEARCH_LINK')?></b></p></td>
	</tr>
	<?php
	$sites = CSite::GetList('', '', ['ACTIVE' => 'Y']);
	while ($site = $sites->Fetch())
	{
		?>
		<tr>
			<td width="0%"><p><?php echo htmlspecialcharsEx('[' . $site['ID'] . '] ' . $site['NAME'])?></p></td>
			<td width="0%"><p><a href="<?php echo htmlspecialcharsbx(
				($site['SERVER_NAME'] <> '' ? 'http://' . $site['SERVER_NAME'] : '')
				. $site['DIR'] . $_REQUEST['public_dir']
				. '/'
			)?>"><?php echo htmlspecialcharsEx($site['DIR'] . $_REQUEST['public_dir'])?>/</a></p></td>
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
	<input type="hidden" name="lang" value="<?php echo LANG?>">
	<input type="submit" name="" value="<?php echo GetMessage('MOD_BACK')?>">
<form>
