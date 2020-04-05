<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/prolog.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("rub_test_tab"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("rub_test_tab_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// Id of the rubric to test
$arError = array();
$message = null;
$bVarsFromForm = false;
$rubric = false;
$arRubric = false;

$arFieldDescriptions = array(
	"ACTIVE" => GetMessage("rub_ACTIVE"),
	"AUTO" => GetMessage("rub_AUTO"),
	"BODY_TYPE" => GetMessage("rub_BODY_TYPE"),
	"CHARSET" => GetMessage("rub_CHARSET"),
	"DAYS_OF_MONTH" => GetMessage("rub_DAYS_OF_MONTH"),
	"DAYS_OF_WEEK" => GetMessage("rub_DAYS_OF_WEEK"),
	"DESCRIPTION" => GetMessage("rub_DESCRIPTION"),
	"DIRECT_SEND" => GetMessage("rub_DIRECT_SEND"),
	"END_TIME" => GetMessage("rub_END_TIME"),
	"FROM_FIELD" => GetMessage("rub_FROM_FIELD"),
	"ID" => GetMessage("rub_ID"),
	"LAST_EXECUTED" => GetMessage("rub_LAST_EXECUTED"),
	"LID" => GetMessage("rub_LID"),
	"NAME" => GetMessage("rub_NAME"),
	"SITE_ID" => GetMessage("rub_SITE_ID"),
	"SORT" => GetMessage("rub_SORT"),
	"START_TIME" => GetMessage("rub_START_TIME"),
	"SUBJECT" => GetMessage("rub_SUBJECT"),
	"TEMPLATE" => GetMessage("rub_TEMPLATE"),
	"TIMES_OF_DAY" => GetMessage("rub_TIMES_OF_DAY"),
	"VISIBLE" => GetMessage("rub_VISIBLE"),
);

if($ID>0)
{
	global $DB;
	$rubric = CRubric::GetByID($ID);
	if($rubric)
		$arRubric = $rubric->Fetch();
	if(!$arRubric)
		$arError[] = array("id"=>"", "text"=>GetMessage("rub_id_not_found"));
	else
	{
		if($START_TIME=="")
			$START_TIME=$arRubric["LAST_EXECUTED"];
		if($END_TIME=="")
			$END_TIME=ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");
	}
}

if(strlen($Test)>0 && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	if($DB->IsDate($START_TIME, false, false, "FULL")!==true)
		$arError[] = array("id"=>"START_TIME", "text"=>GetMessage("rub_wrong_stime"));
	if($DB->IsDate($END_TIME, false, false, "FULL")!==true)
		$arError[] = array("id"=>"END_TIME", "text"=>GetMessage("rub_wrong_etime"));
	$bTest = count($arError) == 0;
}
else
	$bTest = false;

$APPLICATION->SetTitle(GetMessage("rub_title").$ID);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("POST_LIST"),
		"TITLE"=>GetMessage("rub_list"),
		"LINK"=>"rubric_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"TITLE"=>GetMessage("rubric_mnu_add"),
		"LINK"=>"rubric_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("POST_EDIT"),
		"TITLE"=>GetMessage("rubric_mnu_edit"),
		"LINK"=>"rubric_edit.php?ID=".$ID."&lang=".LANG
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("POST_DELETE"),
		"TITLE"=>GetMessage("rubric_mnu_del"),
		"LINK"=>"javascript:if(confirm('".GetMessage("rubric_mnu_del_conf")."'))window.location='rubric_admin.php?ID=".$ID."&cf=delid&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if(count($arError)>0)
{
	$e = new CAdminException($arError);
	$message = new CAdminMessage(GetMessage("rub_test_error"), $e);
	echo $message->Show();
}
?>

<?if($arRubric):?>
<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
<?
$tabControl->Begin();
?>
<?
//********************
//Template test tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td><?echo GetMessage("rub_name")?></td>
		<td><input type="hidden" name="ID" value="<?echo $ID;?>"><?=htmlspecialcharsbx($arRubric["NAME"])?></td>
	</tr>
	<?
	$arTemplate = CPostingTemplate::GetByID($arRubric["TEMPLATE"]);
	if($arTemplate):
	?>
	<tr>
		<td><?echo GetMessage("rub_tmpl_name")?></td>
		<td><?=htmlspecialcharsbx($arTemplate["NAME"])?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("rub_tmpl_desc")?></td>
		<td><?=htmlspecialcharsbx($arTemplate["DESCRIPTION"])?></td>
	</tr>
	<?endif;?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("rub_times")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("rub_stime").":"?></td>
		<td><?echo CalendarDate("START_TIME", htmlspecialcharsbx($START_TIME), "post_form", "20")?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("rub_etime").":"?></td>
		<td><?echo CalendarDate("END_TIME", htmlspecialcharsbx($END_TIME), "post_form", "20")?></td>
	</tr>
<?
$tabControl->Buttons();
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="submit" name="Test" value="<?echo GetMessage("rub_action")?>" title="<?echo GetMessage("rub_action_title")?>" class="adm-btn-save">
<?
$tabControl->End();
?>
</form>

<?
$tabControl->ShowWarnings("post_form", $message);
?>

<?endif;?>

<?
if($bTest):
	$rubrics = CRubric::GetList(array(), array("ID"=>$ID));
	if($arRubric=$rubrics->Fetch()):
		$arRubric["START_TIME"] = $START_TIME;
		$arRubric["END_TIME"] = $END_TIME;
		$arRubric["SITE_ID"] = $arRubric["LID"];
		//Include language file for template.php
		$rsSite = CSite::GetByID($arRubric["SITE_ID"]);
		$arSite = $rsSite->Fetch();
		$strFileName= $_SERVER["DOCUMENT_ROOT"]."/".$arRubric["TEMPLATE"]."/lang/".$arSite["LANGUAGE_ID"]."/template.php";
		if(file_exists($strFileName))
			include($strFileName);
		//Execute template
		$strFileName= $_SERVER["DOCUMENT_ROOT"]."/".$arRubric["TEMPLATE"]."/template.php";
		if(file_exists($strFileName))
		{
			ob_start();
			$arFields = include($strFileName);
			$strBody = ob_get_contents();
			ob_end_clean();
		}
		if(!is_array($arFields))
			$arFields=array();
?>
<script language="JavaScript">
<!--
function hide(id)
{
	document.getElementById("div_show_"+id).style.display = "inline";
	document.getElementById("div_hide_"+id).style.display = "none";
}
function show(id)
{
	document.getElementById("div_show_"+id).style.display = "none";
	document.getElementById("div_hide_"+id).style.display = "inline";
}
//-->
</script>
<p>
<div id="div_show_INPUT" style="display:inline;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="list-table">
	<tr class="head" align="center" valign="top">
		<td class="left right">
			<a href="javascript:show('INPUT');" ><?=GetMessage("rub_input_show")?></a>
		</td>
	</tr>
</table>
</div>
<div id="div_hide_INPUT" style="display:none;">
<table width="100%" border="0" cellpadding="3" cellspacing="1" class="list-table">
	<tr class="head" align="center" valign="top">
		<td colspan="3" class="left right">
			<a href="javascript:hide('INPUT');"><?=GetMessage("rub_input_hide")?></a>
		</td>
	</tr>
	<?foreach($arRubric as $key=>$value):?>
	<tr>
		<td align="left"  width="20%" class="left"><?echo $arFieldDescriptions[$key]?></td>
		<td align="right" width="10%"><?echo htmlspecialcharsbx($key)?></td>
		<td align="left"  width="70%" class="right"><?echo strlen($value)? htmlspecialcharsbx($value): "&nbsp"?></td>
	</tr>
	<?endforeach?>
</table>
</div>
</p>
<script language="JavaScript">
<!--
hide("INPUT");
//-->
</script>

<p align="center"><b><?=GetMessage("rub_body")?></b></p>

<?if($arFields["BODY_TYPE"]=="html"):?>
	<?=$strBody?>
<?else:?>
	<pre><?=$strBody?></pre>
<?endif?>

<p>
<div id="div_show_OUTPUT" style="display:inline;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="list-table">
	<tr class="head" align="center" valign="top">
		<td class="left right">
			<a href="javascript:show('OUTPUT');" ><?=GetMessage("rub_output_show")?></a>
		</td>
	</tr>
</table>
</div>
<div id="div_hide_OUTPUT" style="display:none;">
<table width="100%" border="0" cellpadding="3" cellspacing="1" class="list-table">
	<tr class="head" align="center" valign="top">
		<td colspan="3" class="left right">
			<a href="javascript:hide('OUTPUT');"><?=GetMessage("rub_output_hide")?></a>
		</td>
	</tr>
	<?foreach($arFields as $key=>$value):
		if($key == "FILES" && is_array($value))
			$value = "<pre>".htmlspecialcharsbx(print_r($value, true))."</pre>";
		else
			$value = htmlspecialcharsbx(print_r($value, true));
	?>
	<tr>
		<td align="left"  width="20%" class="left"><?echo $arFieldDescriptions[$key]?></td>
		<td align="right" width="10%"><?echo htmlspecialcharsbx($key)?></td>
		<td align="left"  width="70%" class="right"><?echo strlen($value)? $value: "&nbsp"?></td>
	</tr>
	<?endforeach?>
</table>
</div>
</p>
<script language="JavaScript">
<!--
hide("OUTPUT");
//-->
</script>

<form method="post" action="posting_edit.php" ENCTYPE="multipart/form-data" name="add_form">
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="RUB_ID[]" value="<?=htmlspecialcharsbx($arRubric["ID"])?>">
<?if(array_key_exists("GROUP_ID", $arFields)):
	if(is_array($arFields["GROUP_ID"]))
	{
		foreach($arFields["GROUP_ID"] as $GROUP_ID)
		{
			?><input type="hidden" name="GROUP_ID[]" value="<?=htmlspecialcharsbx($GROUP_ID)?>"><?
		}
	}
	else
	{
		?><input type="hidden" name="GROUP_ID[]" value="<?=htmlspecialcharsbx($arFields["GROUP_ID"])?>"><?
	}
endif;?>
<?if(array_key_exists("FILES", $arFields) && is_array($arFields["FILES"])):
	foreach($arFields["FILES"] as $i => $arFile)
	{
		$i = htmlspecialcharsbx($i);
		if(is_array($arFile))
		{
			foreach($arFile as $key => $value)
			{
				$key = htmlspecialcharsbx($key);
				$value = htmlspecialcharsbx($value);
				?><input type="hidden" name="FILES[<?echo $i?>][<?echo $key?>]" value="<?echo $value?>"><?
			}
		}
	}
endif;?>
<input type="hidden" name="FROM_FIELD" value="<?=htmlspecialcharsbx($arFields["FROM_FIELD"])?>">
<input type="hidden" name="SUBJECT" value="<?=htmlspecialcharsbx($arFields["SUBJECT"])?>">
<input type="hidden" name="BODY_TYPE" value="<?=htmlspecialcharsbx($arFields["BODY_TYPE"])?>">
<input type="hidden" name="CHARSET" value="<?=htmlspecialcharsbx($arFields["CHARSET"])?>">
<input type="hidden" name="DIRECT_SEND" value="<?=htmlspecialcharsbx($arFields["DIRECT_SEND"])?>">
<input type="hidden" name="BODY" value="<?=htmlspecialcharsbx($strBody)?>">
<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="submit" name="apply" value="<?=GetMessage("rub_add_issue")?>" title="<?=GetMessage("rub_add_issue_act")?>">
</form>
	<?endif?>
<?endif?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>