<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/search/prolog.php");
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @var CAdminMessage $message */
$searchDB = CDatabase::GetModuleConnection('search');

$SEARCH_RIGHT = $APPLICATION->GetGroupRight("search");
if($SEARCH_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if($Rebuild <> '')
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");
	$NS = intval($NS)+1;
	$cCustomRank = new CSearchCustomRank;

	if($Next == '')
		$res = $cCustomRank->StartUpdate();

	$res = $cCustomRank->NextUpdate();

	if(is_array($res) && $res["TODO"]>0):
		?><input type="hidden" name="NS" id="NS" value="<?=$NS?>"><?
	else:
		?><input type="hidden" name="NS" id="NSTOP" value="<?=$NS?>"><?
	endif;

	if(!is_array($res))
	{
		$res = array(
			"TODO" => 0,
			"DONE" => 0,
		);
	}

	if($res["TODO"] == 0)
		CAdminMessage::ShowMessage(array(
			"TYPE" => "OK",
			"HTML" => true,
			"MESSAGE" => GetMessage("customrank_saved"),
		));
	else
		CAdminMessage::ShowMessage(array(
			"TYPE" => "PROGRESS",
			"HTML" => true,
			"MESSAGE" => GetMessage("customrank_progress"),
			"DETAILS" => "#PROGRESS_BAR#",
			"PROGRESS_TOTAL" => $res["DONE"]+$res["TODO"],
			"PROGRESS_VALUE" => $res["DONE"],
		));

	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
}
else
{

$sTableID = "tbl_search";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = array(
	"find",
	"find_type",
	"find_id",
	"find_site_id",
	"find_module_id",
	"find_param1",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = array(
	"ID" => ($find != "" && $find_type == "id" ? $find : $find_id),
	"SITE_ID" => ($find_site_id == "NOT_REF" ? "" : ($find != "" && $find_type == "site_id" ? $find : $find_site_id)),
	"MODULE_ID" => $find_module_id,
	"PARAM1" => $find_param1,
);

if ($lAdmin->EditAction() && $SEARCH_RIGHT >= "W" && is_array($FIELDS))
{
	foreach ($FIELDS as $ID => $arFields)
	{
		if (!$lAdmin->IsUpdated($ID))
			continue;

		$searchDB->StartTransaction();
		$ID = intval($ID);
		$cData = new CSearchCustomRank;
		if (($rsData = $cData->GetByID($ID)) && ($arData = $rsData->Fetch()))
		{
			foreach ($arFields as $key => $value)
				$arData[$key] = $value;

			if (!$cData->Update($ID, $arData))
			{
				$lAdmin->AddGroupError(GetMessage("customrank_edit_error").$cData->LAST_ERROR, $ID);
				$searchDB->Rollback();
			}
			else
			{
				$searchDB->Commit();
			}
		}
		else
		{
			$lAdmin->AddGroupError(GetMessage("customrank_edit_error")." ".GetMessage("customrank_no_rule"), $ID);
			$searchDB->Rollback();
		}
	}
}

if (($arID = $lAdmin->GroupAction()) && $SEARCH_RIGHT == "W")
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$cData = new CSearchCustomRank;
		$rsData = $cData->GetList(array(
			$by => $order,
		), $arFilter);
		while ($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}
	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		$ID = intval($ID);
		switch ($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$searchDB->StartTransaction();
			if (!CSearchCustomRank::Delete($ID))
			{
				$searchDB->Rollback();
				$lAdmin->AddGroupError(GetMessage("customrank_error_delete"), $ID);
			}
			else
			{
				$searchDB->Commit();
			}
			break;
		}
	}
}

$cData = new CSearchCustomRank;
$rsData = $cData->GetList(array($by=>$order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("customrank_rules")));

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "id",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "SITE_ID",
		"content" => GetMessage("customrank_site"),
		"sort" => "site_id",
		"default" => true,
	),
	array(
		"id" => "MODULE_ID",
		"content" => GetMessage("customrank_module"),
		"sort" => "module_id",
		"default" => true,
	),
	array(
		"id" => "PARAM1",
		"content" => GetMessage("customrank_param1"),
		"sort" => "param1",
		"default" => true,
	),
	array(
		"id" => "PARAM2",
		"content" => GetMessage("customrank_param2"),
		"sort" => "param2",
		"default" => true,
	),
	array(
		"id" => "ITEM_ID",
		"content" => GetMessage("customrank_param3"),
		"sort" => "item_id",
		"default" => true,
	),
	array(
		"id" => "RANK",
		"content" => GetMessage("customrank_sort"),
		"sort" => "rank",
		"align" => "right",
		"default" => true,
	),
));

while($arRes = $rsData->NavNext(true, "f_")):
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddEditField("SITE_ID", CLang::SelectBox("FIELDS[".$f_ID."][SITE_ID]", $f_SITE_ID, "" ,"BoxUpdateNew('param1',".$f_ID.")"));
	$row->AddViewField("SITE_ID","[".$f_SITE_ID."] ".htmlspecialcharsbx(CSearchCustomRank::__GetParam($lang, $f_SITE_ID)));

	$row->AddSelectField("MODULE_ID",CSearchCustomRank::ModulesList(),array("OnChange"=>"BoxUpdateNew('param1',".$f_ID.")"));

	$strPARAM1=
		'<select name="FIELDS['.$f_ID.'][PARAM1]" OnChange="BoxUpdateNew(\'param1\', '.$f_ID.')">'.
		'<option value="">'.GetMessage("customrank_no").'</option>';

	if($f_MODULE_ID=="iblock" && CModule::IncludeModule("iblock"))
	{
		$rs = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
		while($ar=$rs->Fetch())
			if($arIBType=CIBlockType::GetByIDLang($ar["ID"], LANG))
				$strPARAM1.='<option value="'.htmlspecialcharsbx($ar["ID"]).'" '.($ar["ID"]==$f_PARAM1?" selected":"").'>'.htmlspecialcharsbx("[".$ar["ID"]."] ".$arIBType["~NAME"]).'</option>';
		$strPARAM1.='</select>';
	}
	elseif($f_MODULE_ID=="forum" && CModule::IncludeModule("forum"))
	{
		$rs = CForumNew::GetList(array("sort"=>"asc"), array("LID"=>$f_SITE_ID));
		while($ar=$rs->Fetch())
			$strPARAM1.='<option value="'.htmlspecialcharsbx($ar["ID"]).'" '.($ar["ID"]==$f_PARAM1?" selected":"").'>'.htmlspecialcharsbx("[".$ar["ID"]."] ".$ar["NAME"]).'</option>';
		$strPARAM1.='</select>';
	}
	else
	{
		$strPARAM1='&nbsp;<input type="hidden" name="FIELDS['.$f_ID.'][PARAM1]" value="">';
	}

	$row->AddEditField("PARAM1", '<div id="PARAM1['.$f_ID.']_result_div">'.$strPARAM1.'</div>');

	$f_PARAM1_NAME="[".$f_PARAM1."] ".CSearchCustomRank::__GetParam($lang, $f_SITE_ID, $f_MODULE_ID, $f_PARAM1);
	$row->AddViewField("PARAM1",($f_PARAM1==""?"&nbsp;":$f_PARAM1_NAME));

	$strPARAM2=
		'<select name="FIELDS['.$f_ID.'][PARAM2]" OnChange="BoxUpdateNew(\'param2\', '.$f_ID.')">'.
		'<option value="">'.GetMessage("customrank_no").'</option>';

	if($f_MODULE_ID=="iblock" && CModule::IncludeModule("iblock"))
	{
		$rs = CIBlock::GetList(array("SORT"=>"ASC"),array("TYPE"=>$f_PARAM1,"LID"=>$f_SITE_ID));
		while($ar=$rs->Fetch())
			$strPARAM2.='<option value="'.htmlspecialcharsbx($ar["ID"]).'" '.($ar["ID"]==$f_PARAM2?" selected":"").'>'.htmlspecialcharsbx("[".$ar["ID"]."] ".$ar["NAME"]).'</option>';
		$strPARAM2.='</select>';
	}
	elseif($f_MODULE_ID=="forum" && CModule::IncludeModule("forum"))
	{
		$strPARAM2='<input type="text" size=5 name="FIELDS['.$f_ID.'][PARAM2]" value="">';
	}
	else
	{
		$strPARAM2='&nbsp;<input type="hidden" name="FIELDS['.$f_ID.'][PARAM2]" value="">';
	}

	$row->AddEditField("PARAM2", '<div id="PARAM2['.$f_ID.']_result_div">'.$strPARAM2.'</div>');

	$f_PARAM2_NAME="[".$f_PARAM2."] ".CSearchCustomRank::__GetParam($lang, $f_SITE_ID, $f_MODULE_ID, $f_PARAM1, $f_PARAM2);
	$row->AddViewField("PARAM2",($f_PARAM2==""?"&nbsp;":$f_PARAM2_NAME));

	$row->AddInputField("ITEM_ID", array("size"=>5));
	$f_ITEM_ID_NAME="[".$f_ITEM_ID."] ".CSearchCustomRank::__GetParam($lang, $f_SITE_ID, $f_MODULE_ID, $f_PARAM1, $f_PARAM2, $f_ITEM_ID);
	$row->AddViewField("ITEM_ID",($f_ITEM_ID==""?"&nbsp;":$f_ITEM_ID_NAME));

	$row->AddInputField("RANK", array("size"=>5));

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"DEFAULT" => true,
		"TEXT" => GetMessage("customrank_edit"),
		"ACTION" => $lAdmin->ActionRedirect("search_customrank_edit.php?ID=".$f_ID),
	);
	if ($SEARCH_RIGHT >= "W")
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("customrank_delete"),
			"ACTION" => "if(confirm('".GetMessage('customrank_delete_confirm')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);

	$row->AddActions($arActions);

endwhile;


$lAdmin->AddFooter(array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $rsData->SelectedRowsCount(),
	),
	array(
		"counter" => true,
		"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
		"value" => "0",
	),
));
$lAdmin->AddGroupActionTable(array(
	"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
));
$aContext = array(
	array(
		"TEXT" => GetMessage("customrank_add"),
		"LINK" => "search_customrank_edit.php?lang=".LANG,
		"TITLE" => GetMessage("customrank_add_title"),
		"ICON" => "btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("customrank_title"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(is_object($message))
	echo $message->Show();

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("customrank_id"),
		GetMessage("customrank_site"),
		GetMessage("customrank_module"),
		GetMessage("customrank_param1"),
	)
);
?>
<script type="text/javascript">
var savedNS;
var stop;
function StartRebuild()
{
	stop=false;
	savedNS='start!';
	document.getElementById('customrank_result_div').innerHTML='';
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	setTimeout('DoNext()', 1000);
}
function DoNext()
{
	if(document.getElementById('NS'))
		newNS=document.getElementById('NS').value;
	else
		newNS=null;
	if(document.getElementById('NSTOP'))
	{
		EndRebuild();
		return;
	}
	if(newNS!=savedNS)
	{
		queryString='lang='+encodeURIComponent('<?echo CUtil::JSEscape(LANG)?>');
		if(savedNS!='start!')
		{
			queryString+='&Next=Y';
			if(document.getElementById('NS'))
				queryString+='&NS='+encodeURIComponent(document.getElementById('NS').value);
		}
		queryString+='&Rebuild=Y';
		savedNS=newNS;
		//alert(queryString);
		GetAdminDiv('customrank_result_div', 'search_customrank_admin.php?'+queryString);
	}
	if(!stop)
		setTimeout('DoNext()', 1000);
}
function StopRebuild()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=false;
}
function ContinueRebuild()
{
	stop=false;
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;
	document.getElementById('continue_button').disabled=true;
	setTimeout('DoNext()', 1000);
}
function EndRebuild()
{
	stop=true;
	document.getElementById('stop_button').disabled=true;
	document.getElementById('start_button').disabled=false;
	document.getElementById('continue_button').disabled=true;
}
var processing = false;
function GetAdminDiv(id, url, is_first, is_last)
{
	processing = true;
	CHttpRequest.Action = function(result)
	{
		if(is_last)
			CloseWaitWindow();
		document.getElementById(id).innerHTML = result;
		processing = false;
	}
	if(is_first)
		ShowWaitWindow();
	CHttpRequest.Send(url);
}
function BoxUpdateParam1(id)
{
	GetAdminDiv('PARAM1['+id+']_result_div'
		,'search_customrank_edit.php?get_select=Y'
		+'&'+'PARAM=1'
		+'&'+'ID='+encodeURIComponent(id)
		+'&'+'FIELD_ID='+encodeURIComponent('FIELDS['+id+'][PARAM1]')
		+'&'+'SITE_ID='+encodeURIComponent(document.getElementsByName('FIELDS['+id+'][SITE_ID]')[0].value)
		+'&'+'MODULE_ID='+encodeURIComponent(document.getElementsByName('FIELDS['+id+'][MODULE_ID]')[0].value)
		+'&'+'PARAM1='+encodeURIComponent(document.getElementsByName('FIELDS['+id+'][PARAM1]')[0].value)
	,true, false);
}
function BoxUpdateParam2(id)
{
	GetAdminDiv('PARAM2['+id+']_result_div'
		,'search_customrank_edit.php?get_select=Y'
		+'&'+'PARAM=2'
		+'&'+'ID='+encodeURIComponent(id)
		+'&'+'FIELD_ID='+encodeURIComponent('FIELDS['+id+'][PARAM2]')
		+'&'+'SITE_ID='+encodeURIComponent(document.getElementsByName('FIELDS['+id+'][SITE_ID]')[0].value)
		+'&'+'MODULE_ID='+encodeURIComponent(document.getElementsByName('FIELDS['+id+'][MODULE_ID]')[0].value)
		+'&'+'PARAM1='+encodeURIComponent(document.getElementsByName('FIELDS['+id+'][PARAM1]')[0].value)
		+'&'+'PARAM2='+encodeURIComponent(document.getElementsByName('FIELDS['+id+'][PARAM2]')[0].value)
	,false, true);
}
function BoxUpdateNew(step, id)
{
	if(step=='param1')
	{
		BoxUpdateParam1(id);
		setTimeout("BoxUpdateNew('param2', "+id+")", 500);
	}
	if(step=='param2')
	{
		if(processing)
			setTimeout("BoxUpdateNew('param2', "+id+")", 500);
		else
			BoxUpdateParam2(id);
	}
}
</script>

<h2><?=GetMessage("customrank_step1")?></h2>

<form name="form1" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><b><?=GetMessage("customrank_find")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>" title="<?=GetMessage("customrank_find_title")?>">
		<?
		$arr = array(
			"reference" => array(
				GetMessage("customrank_site"),
				"ID",
			),
			"reference_id" => array(
				"site_id",
				"id",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("customrank_id")?></td>
	<td><input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("customrank_site")?></td>
	<td><?echo CLang::SelectBox("find_site_id", $find_site_id, GetMessage("customrank_all"));?></td>
</tr>
<tr>
	<td><?=GetMessage("customrank_module")?></td>
	<td><?=CSearchCustomRank::ModulesSelectBox("find_module_id", $find_module_id, GetMessage("customrank_all"), "", "")?></td>
</tr>
<tr>
	<td><?=GetMessage("customrank_param1")?></td>
	<td><input type="text" name="find_param1" size="47" value="<?echo htmlspecialcharsbx($find_param1)?>"></td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<h2><?=GetMessage("customrank_step2")?></h2>
<div id="customrank_result_div"></div>
<?echo BeginNote();?>
	<table><tr>
	<td><img src="/bitrix/images/search/warning.gif">&nbsp;</td>
	<td><font class="legendtext">
<?echo htmlspecialcharsbx(GetMessage("customrank_save_note"))?>
	</font></td>
	</tr></table>
<?echo EndNote();?>

<p>
<input type="button" id="start_button" value="<?echo GetMessage("customrank_update")?>" OnClick="StartRebuild();">
<input type="button" id="stop_button" value="<?=GetMessage("customrank_stop")?>" OnClick="StopRebuild();" disabled>
<input type="button" id="continue_button" value="<?=GetMessage("customrank_continue")?>" OnClick="ContinueRebuild();" disabled>
</p>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
?>
