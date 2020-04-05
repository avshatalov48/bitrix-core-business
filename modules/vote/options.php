<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/vote/include.php");
$old_module_version = CVote::IsOldVersion();

IncludeModuleLangFile(__FILE__);
$module_id = "vote";
$VOTE_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($VOTE_RIGHT>="R")
{

	$arAllOptions = $arDisplayOptions = array(
		array("USE_HTML_EDIT", GetMessage("VOTE_USE_HTML_EDIT"), Array("checkbox", "Y")),
		array("VOTE_COMPATIBLE_OLD_TEMPLATE", GetMessage("VOTE_COMPATIBLE"), Array("checkbox", "Y")),
		array("VOTE_DIR", GetMessage("VOTE_PUBLIC_DIR"), array("text", 45)),
		array("VOTE_TEMPLATE_PATH", GetMessage("VOTE_TEMPLATE_VOTES"), array("text", 45)),
		array("VOTE_TEMPLATE_PATH_VOTE", GetMessage("VOTE_TEMPLATE_RESULTS_VOTE"), array("text", 45)),
		array("VOTE_TEMPLATE_PATH_QUESTION", GetMessage("VOTE_TEMPLATE_RESULTS_QUESTION"), array("text", 45)),
		array("VOTE_TEMPLATE_PATH_QUESTION_NEW", GetMessage("VOTE_TEMPLATE_RESULTS_QUESTION_NEW"), array("text", 45)),
		
	);

	if ($REQUEST_METHOD=="GET" && $VOTE_RIGHT=="W" && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
	{
		COption::RemoveOption("vote");
		$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}

	if($REQUEST_METHOD=="POST" && strlen($Update)>0 && $VOTE_RIGHT=="W" && check_bitrix_sessid())
	{
		while(list($key,$name)=each($arAllOptions))
		{
			$val = ${$name[0]};

			if($name[2][0]=="checkbox" && $val != "Y") 
				$val="N";
			elseif(!array_key_exists($name[0], $_POST))
				continue;

			COption::SetOptionString($module_id, $name[0], $val);
		}
	}

	if (COption::GetOptionString("vote", "VOTE_COMPATIBLE_OLD_TEMPLATE", "Y") == "N")
	{
		unset($arDisplayOptions[2]);
		unset($arDisplayOptions[3]);
		unset($arDisplayOptions[4]);
		unset($arDisplayOptions[5]);
		unset($arDisplayOptions[6]);
	}
	elseif ($old_module_version=="Y")
	{
		unset($arDisplayOptions[6]);
	}
	else
	{
		unset($arDisplayOptions[2]);
		unset($arDisplayOptions[3]);
		unset($arDisplayOptions[4]);
		unset($arDisplayOptions[5]);
	}


	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
		array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "vote_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	?>
	<?
	$tabControl->Begin();
	?><form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>">
	<?=bitrix_sessid_post()?>
	<?$tabControl->BeginNextTab();?>
		<?
		if (is_array($arDisplayOptions)):
			foreach($arDisplayOptions as $Option):
			$val = COption::GetOptionString($module_id, $Option[0]);

			$type = $Option[2];
		?>
		<tr>
			<td valign="top" width="50%"><?if($type[0]=="checkbox")
								echo "<label for=\"".htmlspecialcharsbx($Option[0])."\">".$Option[1]."</label>";
							else
								echo $Option[1];?></td>
			<td valign="top" width="50%"><?
			if($type[0]=="checkbox"):
				?><input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo" checked";?>><?
			elseif($type[0]=="text"):
				?><input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?
			elseif($type[0]=="textarea"):
				?><textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea><?
			endif;
			?></td>
		</tr>
		<?
			endforeach;
		endif;
		?>

	<?$tabControl->BeginNextTab();?>
	<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
	<?$tabControl->Buttons();?>
	<script language="JavaScript">
	function RestoreDefaults()
	{
		if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
			window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANGUAGE_ID?>&mid=<?echo urlencode($mid)?>";
	}
	</script>
	<input <?if ($VOTE_RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("VOTE_SAVE")?>">
	<input type="hidden" name="Update" value="Y">
	<input type="reset" name="reset" value="<?=GetMessage("VOTE_RESET")?>">
	<input <?if ($VOTE_RIGHT<"W") echo "disabled" ?> type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?$tabControl->End();?>
	</form>
<?
}
?>
