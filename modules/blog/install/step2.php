<?if(!check_bitrix_sessid()) return;?>
<?
global $errors;

if($errors == ''):
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
else:
	for($i=0; $i<count($errors); $i++)
		$alErrors .= $errors[$i]."<br>";
	echo CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=>$alErrors, "HTML"=>true));
endif;
if ($ex = $APPLICATION->GetException())
{
	echo CAdminMessage::ShowMessage(Array("TYPE" => "ERROR", "MESSAGE" => GetMessage("MOD_INST_ERR"), "HTML" => true, "DETAILS" => $ex->GetString()));
}

global $public_installed;
$dbSites = CSite::GetList('', '', Array("ACTIVE" => "Y"));
while ($site = $dbSites->Fetch())
{ 
	$arSite[] = Array(
		"LANGUAGE_ID" => $site["LANGUAGE_ID"],
		"ABS_DOC_ROOT" => $site["ABS_DOC_ROOT"],
		"DIR" => $site["DIR"],
		"SITE_ID" => $site["LID"],
		"SERVER_NAME" =>$site["SERVER_NAME"],
		"NAME" => $site["NAME"]
	);
}

if ($public_installed) :
?>
<p><?=GetMessage("MOD_DEMO_DIR")?></p>
<table border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td align="center"><p><b><?=GetMessage("MOD_DEMO_SITE")?></b></p></td>
		<td align="center"><p><b><?=GetMessage("MOD_DEMO_LINK")?></b></p></td>
	</tr>
	<?
	foreach($arSite as $fSite)
	{
		global ${"install_public_".$fSite["SITE_ID"]};
		global ${"public_path_".$fSite["SITE_ID"]};
		global ${"public_rewrite_".$fSite["SITE_ID"]};
		global ${"is404_".$fSite["SITE_ID"]};

		if (${"install_public_".$fSite["SITE_ID"]} == "Y" && !empty(${"public_path_".$fSite["SITE_ID"]}))
		{
			?>
			<tr>
				<td width="0%"><p>[<?=$fSite["SITE_ID"]?>] <?=$fSite["NAME"]?></p></td>
				<td width="0%"><p><a href="<?if($fSite["SERVER_NAME"] <> '') echo "http://".$fSite["SERVER_NAME"];?><?=$fSite["DIR"].${"public_path_".$fSite["SITE_ID"]}?>/"><?=$fSite["DIR"].${"public_path_".$fSite["SITE_ID"]}?>/</a></p></td>
			</tr>
			<?
		}
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