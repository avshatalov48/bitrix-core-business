<?
$module_id = "advertising";
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/include.php");
IncludeModuleLangFile(__FILE__);

$ADV_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($ADV_RIGHT>="R") :

if ($REQUEST_METHOD=="GET" && $ADV_RIGHT=="W" && $RestoreDefaults <> '' && check_bitrix_sessid())
{
	COption::RemoveOption($module_id);
	$z = CGroup::GetList("id", "asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
	while($zr = $z->Fetch())
		$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
}

$arAllOptions = array(
	array("DONT_USE_CONTRACT", GetMessage("AD_DONT_USE_CONTRACT"), Array("checkbox", "Y")),
	array("DONT_FIX_BANNER_SHOWS", GetMessage("AD_OPT_DONT_FIX_BANNER_SHOWS"), Array("checkbox", "Y")),
	array("USE_HTML_EDIT", GetMessage("AD_USE_HTML_EDIT"), Array("checkbox", "Y")),
	Array("SHOW_COMPONENT_PREVIEW", GetMessage("AD_SHOW_COMPONENT_PREVIEW"), Array("checkbox", "Y")),
	Array("BANNER_DAYS", GetMessage("AD_BANNER_DAYS"), Array("text", 5), "CAdvBanner::CleanUpAllDynamics", "b_adv_banner_2_day"),
	Array("BANNER_GRAPH_WEIGHT", GetMessage("AD_BANNER_GRAPH_WEIGHT"), Array("text", 5)),
	Array("BANNER_GRAPH_HEIGHT", GetMessage("AD_BANNER_GRAPH_HEIGHT"), Array("text", 5)),
	Array("BANNER_DIAGRAM_DIAMETER", GetMessage("AD_BANNER_DIAGRAM_DIAMETER"), Array("text", 5)),
	Array("COOKIE_DAYS", GetMessage("AD_COOKIE_DAYS"), Array("text", 5)),
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ad_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "ad_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && $Update.$Apply <> '' && $ADV_RIGHT>="W" && check_bitrix_sessid())
{
	// смена подкаталога для хранения баннеров
	$old_subdir = COption::GetOptionString($module_id, "UPLOAD_SUBDIR");
	$new_subdir = $_POST["UPLOAD_SUBDIR"];
	
	if($old_subdir != $new_subdir)
	{
		COption::SetOptionString($module_id, "UPLOAD_SUBDIR", $UPLOAD_SUBDIR);
	}

	for ($i = 0, $cnt = count($arAllOptions); $i < $cnt; $i++)
	{
		$name = $arAllOptions[$i][0];
		$val = ${$name};

		if ($arAllOptions[$i][3] <> '' && $_POST[$name.'_clear'] === "Y")
		{
			if (is_callable($arAllOptions[$i][3]))
			{
				call_user_func($arAllOptions[$i][3]);
			}
		}

		if ($arAllOptions[$i][2][0] == "checkbox" && $val != "Y")
		{
			$val = "N";
		}

		COption::SetOptionString($module_id, $name, $val);
	}

	$Update = $Update.$Apply;
	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	ob_end_clean();

	if($Apply == '' && $_REQUEST["back_url_settings"] <> '')
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}
$UPLOAD_SUBDIR = COption::GetOptionString($module_id, "UPLOAD_SUBDIR");
?>
<?
$tabControl->Begin();
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>">
<?
$tabControl->BeginNextTab();
?>
	<?
	for($i=0, $cnt = count($arAllOptions); $i < $cnt; $i++):
		$Option = $arAllOptions[$i];
		$val = COption::GetOptionString($module_id, $Option[0]);
		$type = $Option[2];
	?>
		<tr>
			<td valign="top" width="50%"><?	if($type[0]=="checkbox")
							echo "<label for=\"".htmlspecialcharsbx($Option[0])."\">".$Option[1]."</label>";
						else
							echo $Option[1];?></td>
			<td valign="top" width="50%"><?
					if($type[0]=="checkbox"):?>
						<input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo " checked";?>>
					<?
					elseif($type[0]=="text"):
						if ($Option[4] <> '')
						{
							$arr = explode(",",$Option[4]);
							$count = 0;
							foreach($arr as $table)
							{
								$strSql = "SELECT count(*) as COUNT FROM ".$table;
								$z = $DB->Query($strSql,false,$err_mess.__LINE__);
								$zr = $z->Fetch();
								$count += $zr["COUNT"];
							}
						}
						?>
						<input type="text" size="<?=$type[1]?>" maxlength="255" value="<?=htmlspecialcharsbx($val)?>" name="<?=htmlspecialcharsbx($Option[0])?>">
						<?
						if ($Option[3] <> '')
						{
							?>
							<label for="<?=htmlspecialcharsbx($Option[0])?>_clear">
								<?=GetMessage("AD_DELETE_ALL")?>:
							</label>
							<input type="checkbox" name="<?=htmlspecialcharsbx($Option[0])?>_clear" id="<?=htmlspecialcharsbx($Option[0])?>_clear" value="Y">
							<?
						};

						if ($Option[4] <> '')
						{
							echo '('.GetMessage("AD_RECORDS").' '.$count.')';
						}
						?>
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
					<?endif?>
			</td>
		</tr>
	<?
	endfor;
	?>
		<tr>
			<td valign="top"><?=GetMessage("AD_UPLOAD_SUBDIR")?></td>
			<td valign="middle"><input type="text" size="30" maxlength="255" value="<?=htmlspecialcharsbx($UPLOAD_SUBDIR)?>" name="UPLOAD_SUBDIR"></td>
		</tr>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
<script type="text/javascript">
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANGUAGE_ID?>&mid=<?echo urlencode($mid)?>&<?echo bitrix_sessid_get()?>";
}
</script>
	<?if($_REQUEST["back_url_settings"] <> ''):?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>"<?if ($ADV_RIGHT<"W") echo " disabled" ?>>
	<?endif?>
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>"<?if ($ADV_RIGHT<"W") echo " disabled" ?>>
	<?if($_REQUEST["back_url_settings"] <> ''):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::JSEscape($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="button" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>"<?if ($ADV_RIGHT<"W") echo " disabled" ?>>
	<?=bitrix_sessid_post();?>

<?$tabControl->End();?>
</form>
<?endif;?>
