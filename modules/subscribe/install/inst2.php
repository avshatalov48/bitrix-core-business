<?
if(!check_bitrix_sessid()) return;
IncludeModuleLangFile(__FILE__);

if($ex = $APPLICATION->GetException())
	echo CAdminMessage::ShowMessage(Array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
else
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));

if(strlen($_REQUEST["public_dir"])>0) :
?>
<p><?=GetMessage("MOD_DEMO_DIR")?></p>
<table border="0" cellspacing="0" cellpadding="0" class="internal">
	<tr class="heading">
		<td align="center"><b><?=GetMessage("MOD_DEMO_SITE")?></b></td>
		<td align="center"><b><?=GetMessage("MOD_DEMO_LINK")?></b></td>
	</tr>
	<?
	$sites = CSite::GetList($by, $order, Array("ACTIVE"=>"Y"));
	while($site = $sites->Fetch())
	{
		?>
		<tr>
			<td>[<?=htmlspecialcharsEx($site["ID"])?>] <?=htmlspecialcharsEx($site["NAME"])?></td>
			<td><a href="<?=htmlspecialcharsBx((strlen($site["SERVER_NAME"])>0? "http://".$site["SERVER_NAME"]: "").$site["DIR"].$public_dir."/index.php")?>"><?=htmlspecialcharsEx($site["DIR"].$public_dir."/index.php")?></a></td>
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