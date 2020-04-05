<?IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/admin/form_result_list.php");?>
<br>
<?echo BeginFilter($sess_filter, $is_filtered);?>
<form name="form1" method="GET" action="<?=$APPLICATION->GetCurPage()?>?">
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
<input type="hidden" name="WEB_FORM_ID" value="<?=$WEB_FORM_ID?>">
<tr>
	<td class="tablebody"><font class="tablefieldtext"><?=GetMessage("FORM_F_ID")?></font></td>
	<td class="tablebody"><?=CForm::GetTextFilter("id")?></td>
</tr>
<?if ($SHOW_STATUS=="Y"):?>
<tr>
	<td class="tablebody" valign="top"><font class="tablefieldtext"><?echo GetMessage("FORM_F_STATUS")?></font></td>
	<td class="tablebody"><?
		echo SelectBox("find_status", CFormStatus::GetDropdown($WEB_FORM_ID, array("VIEW")), GetMessage("FORM_ALL"), htmlspecialcharsbx($find_status));
		?></td>
</tr>
<tr>
	<td class="tablebody" valign="top">
		<font class="tablefieldtext"><?echo GetMessage("FORM_F_STATUS_ID")?></font></td>
	<td class="tablebody"><?
		echo CForm::GetTextFilter("status_id");
		?></td>
</tr>
<?endif;?>
<tr valign="center">
	<td class="tablebody" width="0%" nowrap><font class="tablefieldtext"><?echo GetMessage("FORM_F_DATE_CREATE")." (".CSite::GetDateFormat("SHORT")."):"?></font></td>
	<td class="tablebody" width="0%" nowrap><font class="tablefieldtext"><?=CForm::GetDateFilter("date_create", "form1", "Y", "class=\"typeselect\"", "class=\"inputtype\"")?></font></td>
</tr>
<tr valign="center">
	<td class="tablebody" width="0%" nowrap><font class="tablefieldtext"><?echo GetMessage("FORM_F_TIMESTAMP")." (".CSite::GetDateFormat("SHORT")."):"?></font></td>
	<td class="tablebody" width="0%" nowrap><font class="tablefieldtext"><?=CForm::GetDateFilter("timestamp", "form1", "Y", "class=\"typeselect\"", "class=\"inputtype\"")?></font></td>
</tr>
<?if ($F_RIGHT>=25):?>
<tr>
	<td class="tablebody">
		<font class="tablefieldtext"><?echo GetMessage("FORM_F_REGISTERED")?></font></td>
	<td class="tablebody">
		<?
		$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_registered", $arr, htmlspecialcharsbx($find_registered), GetMessage("FORM_ALL"));
		?></td>
</tr>
<tr>
	<td class="tablebody"><font class="tablefieldtext"><?echo GetMessage("FORM_F_AUTH")?></font></td>
	<td class="tablebody"><?
		$arr = array("reference"=>array(GetMessage("FORM_YES"), GetMessage("FORM_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_user_auth", $arr, htmlspecialcharsbx($find_user_auth), GetMessage("FORM_ALL"));
		?></td>
</tr>
<tr>
	<td class="tablebody"><font class="tablefieldtext"><?echo GetMessage("FORM_F_USER")?></font></td>
	<td class="tablebody"><?=CForm::GetTextFilter("user_id")?></td>
</tr>
<?if (CModule::IncludeModule("statistic")) :?>
<tr>
	<td class="tablebody"><font class="tablefieldtext"><?echo GetMessage("FORM_F_GUEST")?></font></td>
	<td class="tablebody"><?=CForm::GetTextFilter("guest_id")?></td>
</tr>
<tr>
	<td class="tablebody"><font class="tablefieldtext"><?echo GetMessage("FORM_F_SESSION")?></font></td>
	<td class="tablebody"><?=CForm::GetTextFilter("session_id")?></td>
</tr>
<?endif;?>
<?endif;?>
<?
$arrFORM_FILTER = (is_array($arrFORM_FILTER)) ? $arrFORM_FILTER : array();
reset($arrFORM_FILTER);
if (count($arrFORM_FILTER)>0) :
?>
<?if ($F_RIGHT>=25) : ?>
<tr>
	<td valign="center" nowrap colspan="2" class="selectedbody"><img src="/bitrix/images/1.gif" width="1" height="7" border=0 alt=""><br><font class="tableheadtext"><b>&nbsp;<?=GetMessage("FORM_ENTERED_BY_GUEST")?></b></font><br><img src="/bitrix/images/1.gif" width="1" height="7" border=0 alt=""><br></td>
</tr>
<?endif;?>
<?
endif;

while (list($key, $arrFILTER) = each($arrFORM_FILTER)) :
	reset($arrFILTER);
	while (list($key, $arrF) = each($arrFILTER)) :

	$fname = $arrF["SID"];

	if (!is_array($arrNOT_SHOW_FILTER) || !in_array($fname,$arrNOT_SHOW_FILTER)):

	if (($arrF["ADDITIONAL"]=="Y" && $SHOW_ADDITIONAL=="Y") || $arrF["ADDITIONAL"]!="Y"):
	$i++;
	if ($fname!=$prev_fname) :
		if ($i>1) :
		?></font></td></tr><?
		endif;
		?>
<tr>
	<td class="tablebody" valign="top" width="40%"><font class="tablefieldtext"><?
	if (strlen($arrF["FILTER_TITLE"])<=0)
	{
		$title = ($arrF["TITLE_TYPE"]=="html" ? strip_tags($arrF["TITLE"]) : htmlspecialcharsbx($arrF["TITLE"]));
		echo $title;
	}
	else echo htmlspecialcharsbx($arrF["FILTER_TITLE"]);

	if ($arrF["FILTER_TYPE"]=="date") echo " (".CSite::GetDateFormat("SHORT").")";
	?></font></td>
	<td class="tablebody" nowrap valign="top" width="60%"><font class="tablebodytext"><?
	endif;
	switch($arrF["FILTER_TYPE"]):
		case "text":
			echo CForm::GetTextFilter($arrF["FID"], 45, "class=\"typeinput\"", "");
			break;
		case "date":
			echo CForm::GetDateFilter($arrF["FID"], "form1", "Y", "class=\"typeselect\"", "class=\"typeinput\"");
			break;
		case "integer":
			echo CForm::GetNumberFilter($arrF["FID"], 10, "class=\"typeinput\"");
			break;
		case "dropdown":
			echo CForm::GetDropDownFilter($arrF["ID"], $arrF["PARAMETER_NAME"], $arrF["FID"], "class=\"typeselect\"");
			break;
		case "exist":
			echo CForm::GetExistFlagFilter($arrF["FID"], "");
			break;
	endswitch;
	if ($arrF["PARAMETER_NAME"]=="ANSWER_TEXT")
	{
		echo "&nbsp;<sup>[<font class='anstext'>...</font>]</sup>";
		$f_anstext = "Y";
	}
	elseif ($arrF["PARAMETER_NAME"]=="ANSWER_VALUE")
	{
		echo "&nbsp;<sup>(<font class='ansvalue'>...</font>)</sup>";
		$f_ansvalue = "Y";
	}
	echo "<br>";
	$prev_fname = $fname;
	endif;
	endif;

	endwhile;

endwhile;
?></font></td>
</tr>
<tr>
	<td colspan="2" align="right" nowrap class="tablebody">
		<table border="0" cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td width="0%"><font class="tablebodytext"><input type="hidden" name="set_filter" value="Y"><input class="button" type="submit" name="set_filter" value="<?echo GetMessage("FORM_F_SET_FILTER")?>"></font></td>
				<td width="0%"><font class="tablebodytext">&nbsp;</font></td>
				<td width="100%" align="left"><font class="tablebodytext"><input class="button" type="submit" name="del_filter" value="<?echo GetMessage("FORM_F_DEL_FILTER")?>"></font></td>
				<td width="0%"><?ShowAddFavorite(false,"set_filter","form")?></td>
			</tr>
		</table>
	</td>
</tr>
</form>
<?echo EndFilter();?>
<br>