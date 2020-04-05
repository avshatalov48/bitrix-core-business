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
<p><?=GetMessage("SEARCH_DEMO_DIR")?></p>
<table border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td align="center"><p><b><?=GetMessage("SEARCH_SITE")?></b></p></td>
		<td align="center"><p><b><?=GetMessage("SEARCH_LINK")?></b></p></td>
	</tr>
	<?
	$sites = CSite::GetList($by, $order, Array("ACTIVE"=>"Y"));
	while($site = $sites->Fetch())
	{
		?>
		<tr>
			<td width="0%"><p>[<?=$site["ID"]?>] <?=$site["NAME"]?></p></td>
			<td width="0%"><p><a href="<? echo htmlspecialcharsbx(
				(strlen($site["SERVER_NAME"])? "http://".$site["SERVER_NAME"]: "").
				$site["DIR"].$_REQUEST["public_dir"].
				"/"
			)?>"><?echo htmlspecialcharsbx($site["DIR"].$_REQUEST["public_dir"])?>/</a></p></td>
		</tr>
		<?
	}
	?>
</table>
<?
endif;
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>