<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/prolog.php");
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);

$STAT_RIGHT = $APPLICATION->GetGroupRight("statistic");
if($STAT_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array(
		"DIV" => "load_csv_tab",
		"TAB" => GetMessage("STAT_FROM_FILE"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage('STAT_LOADING_FROM_CSV'),
	),
	array(
		"DIV" => "load_manual_tab",
		"TAB" => GetMessage("STAT_FROM_FORM"),
		"ICON" => "main_user_edit",
		"TITLE" => GetMessage('STAT_LOADING_FROM_TABLE'),
	),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$step = intval($step);
if($step > 0)
	$time_step = $step;
else
	$step = 20;
$step_loaded = intval($step_loaded);
$step_processed = intval($step_processed);
$step_duplicate = intval($step_duplicate);
$total_loaded=intval($total_loaded);
if($check_unique!="N")
	$check_unique="Y";
if($preview!="N")
	$preview="Y";
$total_duplicate=intval($total_duplicate);
$total_processed=intval($total_processed);
$total_rows=intval($total_rows);
$next_pos=intval($next_pos);

$currency_module = "N";
$base_currency = GetStatisticBaseCurrency();
if ($base_currency <> '')
{
	if (CModule::IncludeModule("currency"))
	{
		$currency_module = "Y";
		$arrRefID = array();
		$arrRef = array();
		$rsCur = CCurrency::GetList("sort", "asc");
		$strJavaCurArray = "
			var arrCur = new Array();
			";
		$i = 0;
			$strJavaCurArray .= "
				arrCur[0] = ' ';";
		while ($arCur = $rsCur->Fetch())
		{
			$arrRef[] = $arCur["CURRENCY"];
			$arrRefID[] = $arCur["CURRENCY"];
			$i++;
			$strJavaCurArray .= "
				arrCur[".$i."] = '".$arCur["CURRENCY"]."';";
		}
		$strJavaCurArray .= "\n\n";
		$arrCurrency = array("REFERENCE" => $arrRef, "REFERENCE_ID" => $arrRefID);
	}
}
else
{
	$strJavaCurArray = "";
}

$upload_dir = $_SERVER["DOCUMENT_ROOT"]."/".COption::GetOptionString("main","upload_dir","/upload/"). "/statistic";
$upload_dir = str_replace("\\","/",$upload_dir);
$upload_dir = str_replace("//","/",$upload_dir);
if(!file_exists($upload_dir))
	mkdir($upload_dir, BX_DIR_PERMISSIONS);

$md5 = md5($APPLICATION->GetCurPage().time());
$INPUT_CSV_FILE = $upload_dir."/".$md5."_in".".csv";
$OUTPUT_CSV_FILE = $INPUT_CSV_FILE;

$arrHandlers = CStatEvent::GetHandlerList($arUSER_HANDLERS);

// prepare file for loading from CSV
$CSV_LOADING_OK = false;
if ($Load!="" && $tabControl_active_tab=="load_csv_tab" && $REQUEST_METHOD=="POST" && $STAT_RIGHT>="W" && check_bitrix_sessid())
{
	$arFile = $_FILES["file_name"];
	$file = $arFile["tmp_name"];
	if (move_uploaded_file($file, $INPUT_CSV_FILE))
	{
		// handler was choosen
		if($handler <> '' && $handler!="NOT_REF" && in_array($handler , $arrHandlers["reference_id"]))
		{
			// include it
			$handler_path = $_SERVER["DOCUMENT_ROOT"].$handler;
			if (file_exists($handler_path))
			{
				$OUTPUT_CSV_FILE = $upload_dir."/".$md5."_out".".csv";
				include($handler_path);
				@unlink($INPUT_CSV_FILE);
			}
		}
		if ($fp=fopen($OUTPUT_CSV_FILE,"rb"))
		{
			$arParams = array();
			$total_rows = 0;
			while ($arrCSV = fgetcsv($fp, 1000, ","))
			{
				$total_rows++;
				// preview was an option
				if ($preview=="Y")
				{
					// save array for output in the table
					$arParams[] = $arrCSV;
					$CSV_LOADING_OK = true;
				}
			}
			@fclose($fp);
			if ($preview=="Y")
			{
				$_SESSION["SESS_NUMS"] = count($arParams);
				@unlink($OUTPUT_CSV_FILE);
			}
			else
			{
				$csvfile = $OUTPUT_CSV_FILE;
			}
		}
	}
}

// prepare file in case events was entered manually
if ($Load!="" && $tabControl_active_tab=="load_manual_tab" && $STAT_RIGHT>="W" && check_bitrix_sessid())
{
	if ($fp_out = fopen($OUTPUT_CSV_FILE,"wb"))
	{
		if (intval($nums)>0) $_SESSION["SESS_NUMS"] = intval($nums);
		$total_rows = 0;
		foreach ($ARR as $pid)
		{
			$pid = intval($pid);
			if (intval(${"EVENT_ID_".$pid})>0 && ${"LOAD_".$pid}=="Y")
			{
				$total_rows++;
				$arrRes = array();
				$arrRes[] = intval(${"EVENT_ID_".$pid});
				$arrRes[] = ${"EVENT3_".$pid};
				$arrRes[] = (${"DATE_ENTER_".$pid} <> '') ? ${"DATE_ENTER_".$pid} : GetTime(time(),"FULL");
				$arrRes[] = ${"PARAM_".$pid};
				$arrRes[] = ${"MONEY_".$pid};
				$arrRes[] = ${"CURRENCY_".$pid};
				$arrRes[] = ${"CHARGEBACK_".$pid};
				array_walk($arrRes, "PrepareResultQuotes");
				$str = implode(",",$arrRes);
				fputs($fp_out, $str."\n");
			}
		}
		@fclose($fp_out);
		// this file will be loaded
		$csvfile = $OUTPUT_CSV_FILE;
	}
}

// check if we have file to load
if(
	$csvfile <> ''
	&& $STAT_RIGHT >= "W"
	&& check_bitrix_sessid())
{
	if(preg_match("/^(".preg_quote($upload_dir."/", "/").")[0-9a-fA-F]{32}_(in|out)\\.csv$/", $csvfile))
	{
		// stepwise load
		$all_loaded = LoadEventsBySteps($csvfile, $time_step, $next_line, $step_processed, $step_loaded, $step_duplicate, $check_unique, $base_currency, $next_pos);
		$total_duplicate += $step_duplicate;
		$total_loaded += $step_loaded;
		$total_processed += $step_processed;
		$next_line = $total_processed + 1;
		$tab=($success=="Y" && $preview=="Y"?"tabControl_active_tab=load_manual_tab":$tabControl->ActiveTabParam());
		$link = $APPLICATION->GetCurPage()."?lang=".LANG;
		if ($all_loaded=="N")
		{
			$success="N";
			$link .= "&csvfile=".urlencode($csvfile)."&time_step=".intval($time_step). "&next_line=".$next_line."&total_loaded=".$total_loaded."&total_rows=".$total_rows."&check_unique=".$check_unique."&total_duplicate=".$total_duplicate."&total_processed=".$total_processed."&next_pos=".$next_pos."&preview=".$preview."&".$tab."&".bitrix_sessid_get();
		}
		elseif ($all_loaded=="Y")
		{
			$success="N";
			$link .= "&success=Y&total_loaded=".$total_loaded. "&check_unique=".$check_unique."&total_duplicate=".$total_duplicate."&total_processed=".$total_processed."&total_rows=".$total_rows."&preview=".$preview."&".$tab;
			$_SESSION["SESS_NUMS"] = 10;
		}
	}
	else
	{
		$success = "Y";
	}
}
else
{
	$link = "";
}

$APPLICATION->SetTitle(GetMessage("STAT_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($success == '' || $success == "Y")
{
	if ($base_currency <> '')
	{
		CAdminMessage::ShowMessage(array(
			"DETAILS" => GetMessage("STAT_BASE_CURRENCY")." ".$base_currency,
			"TYPE" => "OK",
		));
	}
	elseif (CModule::IncludeModule("currency"))
	{
		CAdminMessage::ShowMessage(array(
			"DETAILS" => GetMessage("STAT_BASE_CURRENCY_NOT_INSTALLED").'<br><a href="/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&amp;mid=statistic">'.GetMessage("STAT_CHOOSE_CURRENCY").'</a>',
			"HTML" => true,
			"TYPE" => "ERROR",
		));
	}
}
?>

<?if($success=="N"):?>
	<?
	CAdminMessage::ShowMessage(array(
		"MESSAGE" => GetMessage("STAT_EVENTS_LOADING"),
		"DETAILS" => "<br>",
		"BUTTONS" => array(
			array(
				"ID" => "btn_next",
				"VALUE" => GetMessage("STAT_NEXT_STEP"),
				"ONCLICK" => "DoNext()",
			),
		),
		"HTML"=>true,
		"TYPE" => "PROGRESS",
	));
	?>
	<table class="edit-table" cellspacing="0" cellpadding="0" border="0"><tr><td>
	<table cellspacing="0" cellpadding="0" border="0" class="internal">
		<tr class="heading">
			<td width="60%">&nbsp;</td>
			<td width="20%"><?=GetMessage("STAT_ON_STEP")?></td>
			<td width="20%"><?=GetMessage("STAT_TOTAL")?></td>
		</tr>
		<tr>
			<td nowrap><?=GetMessage("STAT_LOADED")?></td>
			<td align="right"><?=$step_loaded?></td>
			<td align="right"><b><?=$total_loaded?><b></td>
		</tr>
		<tr>
			<td nowrap><?=GetMessage("STAT_PROCESSED")?></td>
			<td align="right"><?=$step_processed?></td>
			<td align="right"><?=$total_processed?></td>
		</tr>
		<?if ($check_unique=="Y"):?>
		<tr>
			<td nowrap><?=GetMessage("STAT_DUPLICATE")?></td>
			<td align="right"><span class="<?echo $total_duplicate > 0? "required": ""?>"><?=$step_duplicate?></span></td>
			<td align="right"><span class="<?echo $total_duplicate > 0 ? "required" : ""?>"><?=$total_duplicate?></span></td>
		</tr>
		<?endif;?>
		<tr>
			<td colspan=2 nowrap><?=GetMessage("STAT_TOTAL_CSV")?></td>
			<td align="right"><b><?=$total_rows?></b></td>
		</tr>
	</table>
	</td></tr></table>

	<script language="JavaScript" type="text/javascript">
	function DoNext()
	{
		window.location='<?=$link?>';
	}
	setTimeout('DoNext()', 500);
	</script>

<?else://if(!$success=="N")?>

<? if ($success=="Y") : ?>
	<table class="edit-table" cellspacing="0" cellpadding="0" border="0"><tr><td>
	<table cellspacing="0" cellpadding="0" border="0" class="internal">
		<tr class="heading">
			<td width="65%">&nbsp;</td>
			<td width="35%"><?=GetMessage("STAT_TOTAL")?></td>
		</tr>
		<tr>
			<td nowrap><?=GetMessage("STAT_LOADED")?></td>
			<td align="right"><b><?=$total_loaded?><b></td>
		</tr>
		<tr>
			<td nowrap><?=GetMessage("STAT_PROCESSED")?></td>
			<td align="right"><?=$total_processed?></td>
		</tr>
		<?if ($check_unique=="Y"):?>
		<tr>
			<td nowrap><?=GetMessage("STAT_DUPLICATE")?></td>
			<td align="right"><span class="<?echo $total_duplicate > 0? "required": ""?>"><?=$total_duplicate?></span></td>
		</tr>
		<?endif;?>
		<tr>
			<td nowrap><?=GetMessage("STAT_TOTAL_CSV")?></td>
			<td align="right"><b><?=$total_rows?></b></td>
		</tr>
	</table>
	</td></tr></table>
<?endif;?>

<script language="JavaScript">
<!--
function addNewRow(currentId)
{
	if(currentId<document.form1.nums.value)
		return;
	id=document.form1.nums.value;
	id++;
	var tbl = document.getElementById("table1");
	var cnt = tbl.rows.length;
	var oRow = tbl.insertRow(cnt);

	var i=0;
	var oCell = oRow.insertCell(i++);
	oCell.innerHTML = id;

	oCell = oRow.insertCell(i++);
	oCell.innerHTML = '<input type="checkbox" value="Y" name="LOAD_'+id+'" checked>';

	oCell = oRow.insertCell(i++);
	oCell.noWrap = true;
	oCell.innerHTML = '<input type="hidden" name="ARR[]" value="'+id+'"><input type="text" size="14" name="EVENT_ID_'+id+'" value="" OnFocus="addNewRow('+id+')"><input type="button" onClick="SelectEvent(\'form1\',\'EVENT_ID_'+id+'\')" title="<?echo GetMessage("STAT_SELECT_EVENT")?>" value="..."></a>';

	oCell = oRow.insertCell(i++);
	oCell.innerHTML = '<input type="text" size="20" name="EVENT3_'+id+'" value="" OnFocus="addNewRow('+id+')">';

	oCell = oRow.insertCell(i++);
	oCell.innerHTML = '<input type="text" name="DATE_ENTER_'+id+'" size="18" value=""> <a href="javascript:void(0);" onClick="Calendar(\'name=DATE_ENTER_'+id+'&from=&to=&form=form1\', document.form1.DATE_ENTER_'+id+'.value);" title="<?echo GetMessage("STAT_CALENDAR")?>"><img src="/bitrix/images/icons/calendar.gif" alt="<?echo GetMessage("STAT_CALENDAR")?>" width="15" height="15" border="0"></a>';

	oCell = oRow.insertCell(i++);
	oCell.innerHTML = '<input type="text" name="PARAM_'+id+'" value="" OnFocus="addNewRow('+id+')">';

	oCell = oRow.insertCell(i++);
	oCell.innerHTML = '<input type="text" size="5" name="MONEY_'+id+'" value="" OnFocus="addNewRow('+id+')">';

	<?if ($currency_module=="Y") :?>
	oCell = oRow.insertCell(i++);
	var strSelectBox;
	<?=$strJavaCurArray?>
	var objPrevSelect = document.getElementsByName("CURRENCY_"+(id-1))[0];
	var prevSelectValue = objPrevSelect[objPrevSelect.selectedIndex].value;
	strSelectBox = '<select name="CURRENCY_'+id+'">';
	for (var ii=0;ii<arrCur.length;ii++)
	{
		strSelectBox = strSelectBox+'<option value="'+arrCur[ii]+'"';
		strSelectBox = strSelectBox+'>'+arrCur[ii]+'</option>';
	}
	strSelectBox = strSelectBox+'</select>';
	oCell.innerHTML = strSelectBox;
	<?endif;?>

	oCell = oRow.insertCell(i++);
	oCell.innerHTML = '<input type="checkbox" value="Y" name="CHARGEBACK_'+id+'">';

	oCell = oRow.insertCell(i++);
	oCell.innerHTML = '<a href="javascript: Copy('+id+')"><img src="/bitrix/images/statistic/copy.gif" width="15" height="15" border=0 class="tb2" hspace="2" alt="<?echo GetMessage("STAT_COPY")?>"></a>';

	document.form1.nums.value = id;
}
function Copy(id)
{
	addNewRow(id);
	if(id<document.form1.nums.value)
	{
		document.getElementsByName("EVENT_ID_"+(id+1))[0].value = document.getElementsByName("EVENT_ID_"+(id))[0].value;
		document.getElementsByName("EVENT3_"+(id+1))[0].value = document.getElementsByName("EVENT3_"+(id))[0].value;
		document.getElementsByName("DATE_ENTER_"+(id+1))[0].value = document.getElementsByName("DATE_ENTER_"+(id))[0].value;
		document.getElementsByName("PARAM_"+(id+1))[0].value = document.getElementsByName("PARAM_"+(id))[0].value;
		document.getElementsByName("MONEY_"+(id+1))[0].value = document.getElementsByName("MONEY_"+(id))[0].value;
		document.getElementsByName("CURRENCY_"+(id+1))[0].selectedIndex = document.getElementsByName("CURRENCY_"+(id))[0].selectedIndex;
	}
}

function SelectEvent(form, field)
{
	jsUtils.OpenWindow('event_multiselect.php?target_control=text&full_name=Y&lang=<?=LANG?>&form='+form+'&field='+field, 600, 600);
}
function ClickPreview()
{
	var v;
	v = document.form1.preview;
	document.getElementById("check_unique1").disabled = v.checked;
	document.getElementsByName("step")[0].disabled = v.checked;
}

//-->
</script>
<?
$strJavaUserHandlerArray = "
	var arrUserHandler = new Array();
	";
if (is_array($arUSER_HANDLERS) && count($arUSER_HANDLERS)>0)
{
	$i = 0;
	foreach($arUSER_HANDLERS as $h)
	{
		$strJavaUserHandlerArray .= "
			arrUserHandler[".$i."] = '".$h."';";
		$i++;
	}
}

?>
<SCRIPT LANGUAGE="JavaScript">
<!--
function SelectHandler()
{
	var objHandlerSelect, strHandlerValue;
	var fileman_edit_link;
	fileman_edit_link = "/bitrix/admin/fileman_file_edit.php?lang=<?=LANGUAGE_ID?>&full_src=Y&path=";
	<?=$strJavaUserHandlerArray?>
	objHandlerSelect = document.form1.handler;
	strHandlerValue = objHandlerSelect[objHandlerSelect.selectedIndex].value;
	document.getElementById("edit_link_span").style.display = "none";
	for (var i=0;i<arrUserHandler.length;i++)
	{
		if (strHandlerValue==arrUserHandler[i])
		{
			document.getElementById("edit_link").href = fileman_edit_link + strHandlerValue;
			document.getElementById("edit_link_span").style.display = "block";
		}
	}
}

function OnSelectAll(fl)
{
	var arCheckbox = document.getElementsByName("ARR[]");
	if(!arCheckbox)
		return;
	if(arCheckbox.length>0)
	{
		for(var i=0; i<arCheckbox.length; i++)
		{
			document.getElementsByName("LOAD_"+(i+1))[0].checked = fl;
		}
	}
}

//-->
</SCRIPT>
<form name="form1" method="POST" action="<?echo $APPLICATION->GetCurPage()?>" enctype="multipart/form-data">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="60%"><?echo GetMessage("STAT_FILE")?></td>
		<td width="40%"><input type="file" name="file_name" size="30" maxlength="255" value=""></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_HANDLER")?></td>
		<td><table cellspacing="0" cellpadding="0" border="0"><tr>
			<td><?echo SelectBoxFromArray("handler", $arrHandlers, $handler, "(".GetMessage("STAT_NO").")", 'OnChange="SelectHandler()"');?></td>
			<td><span id="edit_link_span">[&nbsp;<a target="_blank" class="tablebodylink" href="javascript:void(0)" id="edit_link"><?=GetMessage("STAT_EDIT")?></a>&nbsp;]</span></td>
		</tr></table></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_PREVIEW")?></td>
		<td><?echo InputType("checkbox", "preview", "Y", $preview, false, "", 'OnClick=ClickPreview()'); ?></td>
	</tr>
	<tr>
		<td width="60%"><?echo GetMessage("STAT_UNIQUE")?><sup>1</sup>:</td>
		<td width="40%"><?echo InputType("checkbox", "check_unique", "Y", $check_unique, false, '', '', 'check_unique1'); ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_STEP")?></td>
		<td><input type="text" name="step" size="5" value="<?=$step?>"></td>
	</tr>
<?$tabControl->BeginNextTab();?>
	<tr>
		<td colspan="2">
		<table border="0" cellspacing="0" cellpadding="0" class="internal" id="table1">
		<tr class="heading">
			<td>#</td>
			<td><input type="checkbox" name="selectall" value="Y" onclick="OnSelectAll(this.checked)" checked></td>
			<td><?echo GetMessage("STAT_EVENT_TYPE")?><span class="required">*</span></td>
			<td>event3</td>
			<td><?echo GetMessage("STAT_DATE")." (".FORMAT_DATE.")"?></td>
			<td><?
				$site_id = GetEventSiteID();
				echo str_replace("#GROUP_SITE_ID#",$site_id,GetMessage("STAT_PARAM")) ?></td>
			<td><?=GetMessage("STAT_MONEY")?></td>
			<?if ($currency_module=="Y") :?>
			<td><?=GetMessage("STAT_CURRENCY")?></td>
			<?endif;?>
			<td><?=GetMessage("STAT_CHARGEBACK")?></td>
			<td>&nbsp;</td>
		</tr>
		<?
		$def_count = 10;
		$_SESSION["SESS_NUMS"] = intval($_SESSION["SESS_NUMS"]);
		$count = ($_SESSION["SESS_NUMS"]>0 && $_SESSION["SESS_NUMS"]>$def_count) ? $_SESSION["SESS_NUMS"] : $def_count;
		$i = 1;
		while ($i<=$count) :
			if ($CSV_LOADING_OK)
			{
				$arStr = $arParams[$i-1];
				$event_id = trim($arStr[0]);
				$event3 = trim($arStr[1]);
				$date_enter = trim($arStr[2]);
				$param = trim($arStr[3]);
				$money = trim($arStr[4]);
				$currency = trim($arStr[5]);
				$chargeback = $arStr[6];
			}
			else
			{
				$event_id = $_REQUEST["EVENT_ID_".$i];
				$event3 = $_REQUEST["EVENT3_".$i];
				$date_enter = $_REQUEST["DATE_ENTER_".$i];
				$param = $_REQUEST["PARAM_".$i];
				$money = $_REQUEST["MONEY_".$i];
				$currency = $_REQUEST["CURRENCY_".$i];
				$chargeback = $_REQUEST["CHARGEBACK_".$i];
			}
			if ($currency == '') $currency = $base_currency;
		?>
		<tr>
			<td align="right"><?=$i?></td>
			<td><?echo InputType("checkbox", "LOAD_".$i, "Y", "Y", false); ?></td>
			<td nowrap>
			<input type="hidden" name="ARR[]" value="<?echo $i?>">
			<input type="text" size="14" name="EVENT_ID_<?echo $i?>" value="<?=htmlspecialcharsbx($event_id)?>" OnFocus="addNewRow(<?echo $i?>)">
			<input type="button" onClick="SelectEvent('form1','EVENT_ID_<?echo $i?>')" title="<?echo GetMessage("STAT_SELECT_EVENT")?>" value="...">
			</td>
			<td><input type="text" size="20" name="EVENT3_<?echo $i?>" value="<?=htmlspecialcharsbx($event3)?>" OnFocus="addNewRow(<?echo $i?>)"></td>
			<td nowrap><?echo CalendarDate("DATE_ENTER_".$i, htmlspecialcharsbx($date_enter), "form1", "18")?></td>
			<td><input type="text" style="width:100%" name="PARAM_<?echo $i?>" value="<?=htmlspecialcharsbx($param)?>" OnFocus="addNewRow(<?echo $i?>)"></td>
			<td><input type="text" size="5" name="MONEY_<?echo $i?>" value="<?=htmlspecialcharsbx($money)?>" OnFocus="addNewRow(<?echo $i?>)"></td>
			<?if ($currency_module=="Y") :?>
				<td><?echo SelectBoxFromArray("CURRENCY_".$i, $arrCurrency, $currency, " ");?></td>
			<?endif;?>
			<td><?echo InputType("checkbox", "CHARGEBACK_".$i, "Y", $chargeback, false); ?></td>
			<td><a href="javascript: Copy(<?=$i?>)"><img src="/bitrix/images/statistic/copy.gif" width="15" height="15" border=0 class="tb2" hspace="2" alt="<?echo GetMessage("STAT_COPY")?>"></a></td>
		</tr>
		<?
		$i++;
		endwhile;
		?>
	</table>
	<input type="hidden" name="nums" value="<?echo $i-1?>">
	</td>
	</tr>
	<tr>
		<td width="60%"><?echo GetMessage("STAT_UNIQUE")?><sup>1</sup>:</td>
		<td width="40%"><?echo InputType("checkbox", "check_unique", "Y", $check_unique, false, '', '', 'check_unique2'); ?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("STAT_STEP")?></td>
		<td><input type="text" name="step" size="5" value="<?=$step?>"></td>
	</tr>
<?$tabControl->Buttons();?>
<?if($STAT_RIGHT=="W"):?>
	<?echo bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="submit" name="Load" value="<?echo GetMessage("STAT_LOAD")?>" class="adm-btn-save">
	<input type="reset"  value="<?echo GetMessage("STAT_RESET")?>">
<?endif;?>
<?$tabControl->End();?>
<?=bitrix_sessid_post();?>
</form>

<SCRIPT LANGUAGE="JavaScript">
<!--
ClickPreview();
SelectHandler();
<?if($CSV_LOADING_OK):?>
tabControl.SelectTab('load_manual_tab');
<?endif;?>
//-->
</SCRIPT>

<?echo BeginNote();?>
<sup>1</sup> - <?=GetMessage("STAT_UNIQUE_ALT")?><br>
<span class="required"><?=GetMessage("STAT_ATTENTION_1")." !&nbsp;&nbsp;"?></span><?=GetMessage("STAT_CONVERT_TO_DEFAULT_CUR")?>
<?echo EndNote();?>

<?endif;?>

<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");