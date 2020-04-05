<?
IncludeModuleLangFile(__FILE__); 
$SHOW = array(
	"FILTER" => "Y",
	"SEARCH" => ((IsModuleInstalled("search") && 
		($DB->TableExists("b_forum_message") || $DB->TableExists("B_FORUM_MESSAGE"))) ? "Y" : "N"));

if (($GLOBALS["DB"]->TableExists("b_forum_dictionary") || $GLOBALS["DB"]->TableExists("B_FORUM_DICTIONARY")) &&
	($GLOBALS["DB"]->TableExists("b_forum_filter") || $GLOBALS["DB"]->TableExists("B_FORUM_FILTER")))
{
		$tmp_res_q = $GLOBALS["DB"]->Query(
		"SELECT COUNT(FF.ID) AS COUNT_WORDS
		FROM b_forum_dictionary FD
		LEFT JOIN b_forum_filter FF ON (FD.ID=FF.DICTIONARY_ID)
		WHERE (FD.ID=1 OR FD.ID=2)", True);
		if ($tmp_res_q && ($res = $tmp_res_q->Fetch()))
			$SHOW["FILTER"] = "N";
}

$arSites= array();
$db_res = CSite::GetList(($b = ""), ($o = ""), array("ACTIVE" => "Y"));
if ($db_res && ($res = $db_res->Fetch()))
{
	do 
	{
		$arSites[] = array("SITE_ID" => $res["LID"], "NAME" => $res["NAME"], "DIR" => preg_replace("/[\/\\\]+/is", "/", "/".$res["DIR"]."/forum/"));
	}while ($res = $db_res->Fetch());
}


?><form action="<?=$APPLICATION->GetCurPage()?>" name="form1">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="forum">
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="install_forum" value="Y" />
	<input type="hidden" name="step" value="2">	
	
	<table class="filter-form" cellpadding="3" cellspacing="0" border="0" width="0%">
	<?
	if ($SHOW["FILTER"] == "Y"):?>
		<tr><td><input type="checkbox" name="INSTALL_FILTER" id="INSTALL_FILTER" value="Y"  checked="checked" /></td>
		<td><label for="INSTALL_FILTER"><?=GetMessage("FORUM_INSTALL_FILTER")?></label></td></tr><?
	endif;
	if ($SHOW["SEARCH"] == "Y"):?>
		<tr><td><input type="checkbox" name="REINDEX" id="REINDEX" value="Y" /></td>
		<td><label for="REINDEX"><?=GetMessage("FORUM_REINDEX")?></label></td></tr><?
	endif;
		?><tr><td><input type="checkbox" name="INSTALL_PUBLIC" id="INSTALL_PUBLIC" value="Y"  checked="checked" onclick="document.getElementById('row_install_public').style.display = (this.checked ? '' : 'none');"/></td>
		<td><label for="INSTALL_PUBLIC"><?=GetMessage("FORUM_INSTALL_PUBLIC")?></label></td></tr><?
	
	?><tr id="row_install_public">
		<td><div></div></td>
		<td>
			<table class="filter-form" cellpadding="3" cellspacing="0" border="0" width="0%">
			<tr><td width="0%"><input type="checkbox" id="REWRITE_PUBLIC" name="REWRITE_PUBLIC" value="Y"  checked="checked" /></td>
				<td width="100%"><label for="REWRITE_PUBLIC"><?=GetMessage("FORUM_PUBLIC_REWRITE")?></label></td></tr>
			<tr><td colspan="2">
				<table border="0" cellspacing="0" cellpadding="0" width="100%" class="list-table">
					<tr class="head"><td><?=GetMessage("FORUM_HEADER_INSTALL")?></td><td><?=GetMessage("FORUM_HEADER_PATH")?></td><td><?=GetMessage("FORUM_HEADER_MODE")?></td></tr><?
				$iIndex = 1;
				foreach ($arSites as $res):
					?><tr>
						<td><input type="checkbox" name="PUBLIC_INFO[<?=$iIndex?>][ID]" id="ID_<?=$iIndex?>" value="<?=$iIndex?>" onclick="ChangeInstallPublic(this)" checked="checked" /></td>
						<td><input name="PUBLIC_INFO[<?=$iIndex?>][PATH]" type="text" value="<?=htmlspecialcharsbx($res["DIR"])?>" /></td>
						<td><select name="PUBLIC_INFO[<?=$iIndex?>][MODE]">
							<option value="sef"><?=GetMessage("FORUM_HEADER_MODE_SEF")?></option>
							<option value="nsef"><?=GetMessage("FORUM_HEADER_MODE_NSEF")?></option></select></td></tr><?
					$iIndex++;
				endforeach;?>
				</table>
			</td></tr></table>
	</td></tr>
	
	
	
	
	<tr><td colspan="2"><input type="submit" name="inst" value="<?=GetMessage("MOD_INSTALL")?>" /></td></tr>
	</table>
</form>
<script language="JavaScript">
<!--
function ChangeInstallPublic(oObj)
{
	if (typeof oObj != "object")
		return false;
	var form = oObj.form;
	var bRewriteDisabled = true;
	for (var ii = 0; ii < form.elements.length; ii++)
	{
		var sName = "PUBLIC_INFO[" + oObj.value + "]";
		var element = form.elements[ii];
		
		if (element.name.substr(0, sName.length) == sName && (element.name != (sName + '[ID]')))
			element.disabled = (!oObj.checked);
		if (element.name.substr(0, "PUBLIC_INFO".length) == "PUBLIC_INFO" && element.name.search(/ID/) == -1 && element.disabled != true)
			bRewriteDisabled = false;
	}
	form['REWRITE_PUBLIC'].disabled = bRewriteDisabled;
	form['INSTALL_PUBLIC'].disabled = bRewriteDisabled;
	return false;
}
//-->
</script>