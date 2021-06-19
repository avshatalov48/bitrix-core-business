<?
if(!check_bitrix_sessid())
	return;
IncludeModuleLangFile(__FILE__);

$ex = $APPLICATION->GetException();
if ($ex)
{
	$msg = new CAdminMessage(array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
}
else
{
	$msg = new CAdminMessage(array(
		"TYPE" => "OK",
		"MESSAGE" => GetMessage("MOD_INST_OK"),
	));
}
$msg->Show();


if ($_REQUEST["public_dir"] <> ''):
?>
<p><?=GetMessage("SEARCH_DEMO_DIR")?></p>
<table border="0" cellspacing="0" cellpadding="0" class="internal">
	<tr class="heading">
		<td align="center"><p><b><?=GetMessage("SEARCH_SITE")?></b></p></td>
		<td align="center"><p><b><?=GetMessage("SEARCH_LINK")?></b></p></td>
	</tr>
	<?
	$sites = CSite::GetList('', '', Array("ACTIVE"=>"Y"));
	while($site = $sites->Fetch())
	{
		?>
		<tr>
			<td width="0%"><p><?echo htmlspecialcharsEx('['.$site["ID"].'] '.$site["NAME"])?></p></td>
			<td width="0%"><p><a href="<? echo htmlspecialcharsbx(
				($site["SERVER_NAME"] <> ''? "http://".$site["SERVER_NAME"] : "").
				$site["DIR"].$_REQUEST["public_dir"].
				"/"
			)?>"><?echo htmlspecialcharsEx($site["DIR"].$_REQUEST["public_dir"])?>/</a></p></td>
		</tr>
		<?
	}
	?>
</table>
<br>
<?
endif;
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>
