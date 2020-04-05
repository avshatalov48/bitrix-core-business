<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2004 - 2006 Bitrix           #
# http://www.bitrix.ru                       #
# mailto:admin@bitrix.ru                     #
##############################################
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("form");

ClearVars();
$strError = '';

IncludeModuleLangFile(__FILE__);

$additional = $_REQUEST['additional'];
InitBVar($additional);

$err_mess = "File: ".__FILE__."<br>Line: ";

if ($additional!="Y") define("HELP_FILE","form_question_list.php");
else define("HELP_FILE","form_field_list.php");

$old_module_version = CForm::IsOldVersion();

$aTabs = array ();
$aTabs[]=array("DIV" => "edit1", "TAB" => GetMessage("FORM_PROP"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_PROP_TITLE"));
if ($additional!="Y")
{
	$aTabs[]=array("DIV" => "edit2", "TAB" => GetMessage("FORM_QUESTION"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_TITLE"));
	$aTabs[]=array("DIV" => "edit3", "TAB" => GetMessage("FORM_ANSWER"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_ANSWER_LIST"));
	$aTabs[]=array("DIV" => "edit7", "TAB" => GetMessage("FORM_VAL"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_VAL_TITLE"));
}
$aTabs[]=array("DIV" => "edit4", "TAB" => GetMessage("FORM_RESULTS"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_RESULTS_SHOW"));
$aTabs[]=array("DIV" => "edit5", "TAB" => GetMessage("FORM_FILTER"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_FILTER_TYPE"));
$aTabs[]=array("DIV" => "edit6", "TAB" => GetMessage("FORM_COMMENT_TOP"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_COMMENTS"));


$tabControl = new CAdminTabControl("tabControl", $aTabs);
$message = null;

/***************************************************************************
                           GET | POST processing
***************************************************************************/

$WEB_FORM_ID = intval($_REQUEST['WEB_FORM_ID']);
$ID = intval($_REQUEST['ID']);
$copy_id = intval($_REQUEST['copy_id']);

$arForm = CForm::GetByID_admin($WEB_FORM_ID);

if (false === $arForm)
{
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo "<a href='form_list.php?lang=".LANGUAGE_ID."' >".GetMessage("FORM_FORM_LIST")."</a>";
	echo ShowError(GetMessage("FORM_NOT_FOUND"));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$txt = "(".htmlspecialcharsbx($arForm['SID']).")&nbsp;".htmlspecialcharsbx($arForm['NAME']);
$link = "form_edit.php?lang=".LANGUAGE_ID."&ID=".$WEB_FORM_ID;
$adminChain->AddItem(array("TEXT"=>$txt, "LINK"=>$link));

$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);
if($F_RIGHT<25) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if ($copy_id > 0 && $F_RIGHT >= 30 && check_bitrix_sessid())
{
	$new_id = CFormField::Copy($copy_id);
	if (strlen($strError)<=0 && intval($new_id)>0)
	{
		LocalRedirect("form_field_edit.php?ID=".$new_id."&additional=".$additional."&WEB_FORM_ID=".$WEB_FORM_ID."&lang=".LANGUAGE_ID ."&strError=".urlencode($strError));
	}
}

if ((strlen($_REQUEST['save']) > 0 || strlen($_REQUEST['apply']) > 0) && $_SERVER['REQUEST_METHOD']=="POST" && $F_RIGHT>=30 && check_bitrix_sessid())
{
	$arIMAGE = $_FILES["IMAGE_ID"];
	$arIMAGE["MODULE_ID"] = "form";
	$arIMAGE["del"] = $_REQUEST['IMAGE_ID_del'];

	$ACTIVE 	= $_REQUEST['ACTIVE'];
	$TITLE 		= $_REQUEST['TITLE'];
	$TITLE_TYPE = $_REQUEST['TITLE_TYPE'];
	$SID 		= $_REQUEST['SID'];
	$C_SORT 	= $_REQUEST['C_SORT'];
	$REQUIRED 	= $_REQUEST['REQUIRED'];
	$FIELD_TYPE = $_REQUEST['FIELD_TYPE'];
	$COMMENTS 	= $_REQUEST['COMMENTS'];
	$IN_EXCEL_TABLE 	= $_REQUEST['IN_EXCEL_TABLE'];
	$IN_RESULTS_TABLE 	= $_REQUEST['IN_RESULTS_TABLE'];
	$FILTER_TITLE 		= $_REQUEST['FILTER_TITLE'];
	$RESULTS_TABLE_TITLE = $_REQUEST['RESULTS_TABLE_TITLE'];

	$arFields = array(
		"FORM_ID"				=> $WEB_FORM_ID,
		"ACTIVE"				=> $ACTIVE,
		"TITLE"					=> $TITLE,
		"TITLE_TYPE"			=> $TITLE_TYPE,
		"SID"					=> $SID,
		"C_SORT"				=> $C_SORT,
		"ADDITIONAL"			=> $additional,
		"REQUIRED"				=> $REQUIRED,
		"IN_RESULTS_TABLE"		=> $IN_RESULTS_TABLE,
		"IN_EXCEL_TABLE"		=> $IN_EXCEL_TABLE,
		"FIELD_TYPE"			=> $FIELD_TYPE,
		"COMMENTS"				=> $COMMENTS,
		"FILTER_TITLE"			=> $FILTER_TITLE,
		"RESULTS_TABLE_TITLE"	=> $RESULTS_TABLE_TITLE,
		"arIMAGE"				=> $arIMAGE,
		);

	$arFields["arANSWER"] = array();
	$ANSWER = $_REQUEST['ANSWER'];

	$bHasAnswers = false;
	if (is_array($ANSWER))
	{
		foreach ($ANSWER as $pid)
		{
			$pid = intval($pid);

			if ($pid<=0) continue;

			$arrA = array();
			$arrA["ID"] 		= $_REQUEST["ANSWER_ID_".$pid];
			$arrA["DELETE"] 	= $_REQUEST["del_".$pid];
			$arrA["MESSAGE"] 	= $_REQUEST["MESSAGE_".$pid];
			$arrA["VALUE"] 		= $_REQUEST["VALUE_".$pid];
			$arrA["C_SORT"] 	= $_REQUEST["C_SORT_".$pid];
			$arrA["ACTIVE"] 	= $_REQUEST["ACTIVE_".$pid];
			$arrA["FIELD_TYPE"] 	= $_REQUEST["FIELD_TYPE_".$pid];
			$arrA["FIELD_WIDTH"]	= $_REQUEST["FIELD_WIDTH_".$pid];
			$arrA["FIELD_HEIGHT"] 	= $_REQUEST["FIELD_HEIGHT_".$pid];
			$arrA["FIELD_PARAM"] 	= $_REQUEST["FIELD_PARAM_".$pid];

			if ($arrA['MESSAGE'] != '' && $arrA['DELETE'] !== 'Y')
				$bHasAnswers = true;

			$arFields["arANSWER"][] = $arrA;
		}
	}

	if (!$bHasAnswers && $additional != 'Y')
	{
		$strError = GetMessage('FORM_NO_ANSWERS');
	}

	if ($additional!="Y")
	{
		$arFields["arFILTER_USER"] 			= $_REQUEST['arFILTER_USER'];
		$arFields["arFILTER_ANSWER_TEXT"] 	= $_REQUEST['arFILTER_ANSWER_TEXT'];
		$arFields["arFILTER_ANSWER_VALUE"] 	= $_REQUEST['arFILTER_ANSWER_VALUE'];
	}
	else
	{
		$arFields["arFILTER_FIELD"] = $_REQUEST['arFILTER_FIELD'];
	}

	/*
	print "<pre>";
	print_r($arFields);
	print "</pre>";
	die();
	*/

	if (strlen($strError) <= 0)
	{
		$res = intval(CFormField::Set($arFields, $ID));
		if ($res > 0)
		{
			if (intval($ID) > 0)
				CFormValidator::Clear($ID);

			$ID = $res;

			// process field validators
			if ($additional != "Y")
			{
				$sValStructSerialized = $_REQUEST["VAL_STRUCTURE"];
				if (CheckSerializedData($sValStructSerialized))
				{
					$arValStructure = unserialize($sValStructSerialized);
					if (count($arValStructure) > 0)
					{
						CFormValidator::SetBatch($WEB_FORM_ID, $ID, $arValStructure);
					}
				}
			}

			if (strlen($strError)<=0)
			{
				if (strlen($_REQUEST['save'])>0)
					LocalRedirect("form_field_list.php?WEB_FORM_ID=".$WEB_FORM_ID."&additional=". $additional."&lang=".LANGUAGE_ID);
				else
					LocalRedirect("form_field_edit.php?ID=".$ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=". $additional."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
			}
		}

		$DB->PrepareFields("b_form_field");
	}
}

$rsField = CFormField::GetByID($ID);
if (!$rsField || !$rsField->ExtractFields())
{
	$ID=0;
	$str_ACTIVE = "Y";
	$str_C_SORT = CFormField::GetNextSort($WEB_FORM_ID);
	$str_TITLE_TYPE = "text";
	$str_IN_RESULTS_TABLE = "Y";
	$str_IN_EXCEL_TABLE = "Y";
}
else
{
####### get validators list
$arCurrentValidators = array();
if ($additional!="Y")
{
	if ($ID > 0)
	{
		$rsCurrentValidators = CFormValidator::GetList($ID, array(), $by="C_SORT", $order="ASC");
		while ($arValidator = $rsCurrentValidators->Fetch())
		{
			$arCurrentValidators[] = $arValidator;
		}
	}
}
#############################
}

if (strlen($strError)>0) $DB->InitTableVarsForEdit("b_form_field", "", "str_");

if ($additional=="Y")
{
	if ($ID>0) $sDocTitle = str_replace("#ID#", $ID, GetMessage("FORM_EDIT_ADDITIONAL_RECORD"));
	else $sDocTitle = GetMessage("FORM_NEW_ADDITIONAL_RECORD");
}
else
{
	if ($ID>0) $sDocTitle = str_replace("#ID#", $ID, GetMessage("FORM_EDIT_RECORD"));
	else $sDocTitle = GetMessage("FORM_NEW_RECORD");
}

$APPLICATION->SetTitle($sDocTitle);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
/***************************************************************************
                               HTML form
****************************************************************************/

$context = new CAdminContextMenuList($arForm['ADMIN_MENU']);
$context->Show();

echo BeginNote('width="100%"');?>
<b><?=GetMessage("FORM_FORM_NAME")?></b> [<a title='<?=GetMessage("FORM_EDIT_FORM")?>' href='form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>'><?=$WEB_FORM_ID?></a>]&nbsp;(<?=htmlspecialcharsbx($arForm["SID"])?>)&nbsp;<?=htmlspecialcharsbx($arForm["NAME"])?>
<?echo EndNote();

$aMenu = array();

/*
if ($additional!="Y")
{
	$aMenu[] = array(
		"ICON"			=> "btn_list",
		"TEXT"			=> GetMessage("FORM_QUESTIONS")." [".$arForm["QUESTIONS"]."]",
		"LINK"			=> "/bitrix/admin/form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID,
		"TEXT_PARAM"	=> " [<a title=".GetMessage("FORM_ADD_QUESTION")."  href='/bitrix/admin/form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."'>+</a>]",
		"TITLE"			=> GetMessage("FORM_QUESTIONS_ALT")
		);

}
else
{
	$aMenu[] = array(
		"ICON"			=> "btn_list",
		"TEXT"			=> GetMessage("FORM_FIELDS")." [".$arForm["C_FIELDS"]."]",
		"LINK"			=> "/bitrix/admin/form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=Y",
		"TEXT_PARAM"	=> " [<a title=".GetMessage("FORM_ADD_FIELD")."  href='/bitrix/admin/form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=Y'>+</a>]",
		"TITLE"			=> GetMessage("FORM_FIELDS_ALT")
		);
}
*/

if ($F_RIGHT>=30 && $ID>0)
{

	if ($additional=="Y")
	{
		$aMenu[] = array(
			"ICON"	=> "btn_new",
			"TEXT"	=> GetMessage("FORM_CREATE"),
			"TITLE"	=> GetMessage("FORM_CREATE_FIELD"),
			"LINK"	=> "form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=Y"
			);

		$aMenu[] = array(
			"ICON"	=> "btn_copy",
			"TEXT"	=> GetMessage("FORM_CP"),
			"TITLE"	=> GetMessage("FORM_COPY_FIELD"),
			"LINK"	=> "form_field_edit.php?ID=".$ID."&amp;copy_id=".$ID."&lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID. "&additional=Y&".bitrix_sessid_get()
			);

		$aMenu[] = array(
			"ICON"	=> "btn_delete",
			"TEXT"	=> GetMessage("FORM_DELETE_FIELD"),
			"TITLE"	=> GetMessage("FORM_DELETE_FIELD"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("FORM_CONFIRM_DELETE_FIELD")."'))window.location='form_field_list.php?action=delete&ID=".$ID."&WEB_FORM_ID=".$WEB_FORM_ID."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."&additional=Y';",
			);
	}
	else
	{
		$aMenu[] = array(
			"ICON"	=> "btn_new",
			"TEXT"	=> GetMessage("FORM_CREATE"),
			"TITLE"	=> GetMessage("FORM_CREATE_QUESTION"),
			"LINK"	=> "form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID
			);

		$aMenu[] = array(
			"ICON"	=> "btn_copy",
			"TEXT"	=> GetMessage("FORM_CP"),
			"TITLE"	=> GetMessage("FORM_COPY_QUESTION"),
			"LINK"	=> "form_field_edit.php?ID=".$ID."&amp;copy_id=".$ID."&lang=".LANGUAGE_ID. "&WEB_FORM_ID=".$WEB_FORM_ID."&".bitrix_sessid_get()
			);

		$aMenu[] = array(
			"ICON"	=> "btn_delete",
			"TEXT"	=> GetMessage("FORM_DELETE_QUESTION"),
			"TITLE"	=> GetMessage("FORM_DELETE_QUESTION"),
			"LINK"	=> "javascript:if(confirm('".GetMessage("FORM_CONFIRM_DELETE_QUESTION")."'))window.location='form_field_list.php?action=delete&ID=".$ID."&WEB_FORM_ID=".$WEB_FORM_ID."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
			"WARNING"=>"Y"
			);
	}

	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}

/*
echo '<pre>'; print_r($arForm); echo '</pre>';

$aMenu[] = array("NEWBAR"=>"Y");

$aMenu[] = array(
	"TEXT"			=> GetMessage("FORM_STATUSES")." [".$arForm["STATUSES"]."]",
	"LINK"			=> "/bitrix/admin/form_status_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID,
	"TEXT_PARAM"	=> " [<a title=".GetMessage("FORM_ADD_STATUS")."  href='/bitrix/admin/form_status_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."'>+</a>]",
	"TITLE"			=> GetMessage("FORM_STATUSES_ALT")
	);
if ($additional!="Y")
{
	$aMenu[] = array(
		"TEXT"			=> GetMessage("FORM_FIELDS")." [".$arForm["C_FIELDS"]."]",
		"LINK"			=> "/bitrix/admin/form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=Y",
		"TEXT_PARAM"	=> " [<a title=".GetMessage("FORM_ADD_FIELD")."  href='/bitrix/admin/form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=Y'>+</a>]",
		"TITLE"			=> GetMessage("FORM_FIELDS_ALT")
		);
}
else
{
	$aMenu[] = array(
		"TEXT"			=> GetMessage("FORM_QUESTIONS")." [".$arForm["QUESTIONS"]."]",
		"LINK"			=> "/bitrix/admin/form_field_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID,
		"TEXT_PARAM"	=> " [<a title=".GetMessage("FORM_ADD_QUESTION")."  href='/bitrix/admin/form_field_edit.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID."'>+</a>]",
		"TITLE"			=> GetMessage("FORM_QUESTIONS_ALT")
		);
}
*/

if($strError)
{
	$aMsg=array();
	$arrErr = explode("<br>",$strError);
	while (list(,$err)=each($arrErr)) $aMsg[]['text']=$err;

	$e = new CAdminException($aMsg);
	$GLOBALS["APPLICATION"]->ThrowException($e);
	$message = new CAdminMessage(GetMessage("FORM_ERROR_SAVE"), $e);
	echo $message->Show();
}

//echo ShowNote($strNote);

if ($additional!="Y"):
?>
<script language="JavaScript">
function FormSubmit()
{
	return jsFormValidatorSettings.PrepareToSubmit();
}
</script>
<?
endif;
?>
<form name="form1" method="POST" action="" enctype="multipart/form-data"<?if ($additional!="Y"):?> onSubmit="return FormSubmit();"<?endif?>>
<script type="text/javascript">
function FIELD_TYPE_CHANGE(i)
{
	v = document.getElementById("FIELD_TYPE_"+i)[document.getElementById("FIELD_TYPE_"+i).selectedIndex].value;
	document.getElementById("FIELD_WIDTH_"+i).disabled=false;
	document.getElementById("FIELD_HEIGHT_"+i).disabled=false;
	if (v!="text" && v!="textarea" && v!="image" && v!="date" && v!="url" && v!="email")
	{
		document.getElementById("FIELD_WIDTH_"+i).disabled=true;
	}
	if (v!="textarea" && v!="multiselect")
	{
		document.getElementById("FIELD_HEIGHT_"+i).disabled=true;
	}
}
</script>
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?> />
<input type="hidden" name="WEB_FORM_ID" value="<?=$WEB_FORM_ID?>" />
<input type="hidden" name="additional" value="<?=$additional?>" />
<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>" />
<input type="hidden" name="VAL_STRUCTURE" value="" />
<?
$tabControl->Begin();
?>
<?
//********************
//General Tab
//********************
$tabControl->BeginNextTab();
?>
	<?if (strlen($str_TIMESTAMP_X)>0) : ?>
	<tr>
		<td><?=GetMessage("FORM_TIMESTAMP")?></td>
		<td><?=$str_TIMESTAMP_X?></td>
	</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?=GetMessage("FORM_ACTIVE")?></td>
		<td width="60%"><?=InputType("checkbox","ACTIVE","Y",$str_ACTIVE,false)?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_C_SORT")?></td>
		<td><input type="text" name="C_SORT" size="5" maxlength="18" value="<?=$str_C_SORT?>" /></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?=GetMessage("FORM_SID")?></td>
		<td><input type="text" name="SID" size="30" maxlength="50" value="<?=$str_SID?>" /></td>
	</tr>
	<?if ($additional!="Y"):?>
	<tr>
		<td><?=GetMessage("FORM_REQUIRED")?></td>
		<td><?echo InputType("checkbox","REQUIRED","Y",$str_REQUIRED,false) ?></td>
	</tr>
	<?endif;?>
	<?if ($additional=="Y"):?>
	<tr>
		<td><?echo GetMessage("FORM_ADDITIONAL_FIELD_TYPE")?></td>
		<td><?
		echo SelectBoxFromArray("FIELD_TYPE", CFormField::GetTypeList(), $str_FIELD_TYPE);
		?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_ADDITIONAL_TITLE")?>:</td>
		<td><input type="text" name="TITLE" size="50" value="<?echo $str_TITLE?>" /></td>
	</tr>
	<?endif;?>
<?
if ($additional!="Y")
{
//********************
//question Tab
//********************
$tabControl->BeginNextTab();

if (is_array($str_IMAGE_ID))
	$str_IMAGE_ID = 0;

$str_IMAGE_ID = intval($str_IMAGE_ID);
?>
	<tr>
		<td><?=GetMessage("FORM_IMAGE")?></td>
		<td><?echo CFile::InputFile("IMAGE_ID", 20, $str_IMAGE_ID);?><?if ($str_IMAGE_ID>0):?><br /><?echo CFile::ShowImage($str_IMAGE_ID, 200, 200, "border=0", "", true)?><?endif;?>
		</td>
	</tr>
	<?
	if(COption::GetOptionString("form", "USE_HTML_EDIT")=="Y" && CModule::IncludeModule("fileman")):?>
	<tr>
		<td align="center" colspan="2"><?
		CFileMan::AddHTMLEditorFrame("TITLE", $str_TITLE, "TITLE_TYPE", $str_TITLE_TYPE, 400);
		?></td>
	</tr>
	<?else:?>
	<tr>
		<td align="center" colspan="2"><? echo InputType("radio","TITLE_TYPE","text",$str_TITLE_TYPE,false)?>&nbsp;<?echo GetMessage("FORM_TEXT")?>/&nbsp;<? echo InputType("radio","TITLE_TYPE","html",$str_TITLE_TYPE,false)?>HTML</td>
	</tr>
	<tr>
		<td align="center" colspan="2"><textarea name="TITLE" style="width:100%" rows="23"><?echo $str_TITLE?></textarea></td>
	</tr>
	<?endif;?>
<?
//********************
//Answer Tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2" style="padding:0px;"><script>
function addAnswer()
{
	var obTable = document.getElementById('answers_table');

	var rows_count = obTable.tBodies[0].rows.length;
	var obLastRow = obTable.tBodies[0].rows[rows_count-1];
	var obRow = obLastRow.cloneNode(true);

	var colInputs = obRow.getElementsByTagName('INPUT');
	var arInputs = [];
	for (var i=0; i<colInputs.length; i++) arInputs[i] = colInputs[i];
	colInputs = null;
	var cnt = arInputs.length;

	arInputs[cnt++] = obRow.getElementsByTagName('SELECT')[0];

	for (var i = 0; i<cnt; i++)
	{
		var new_name = arInputs[i].name.replace('' + rows_count, '' + (rows_count + 1));

		arInputs[i].name = new_name;
		if (arInputs[i].id)
		{
			arInputs[i].id = new_name;
		}

		if(arInputs[i].type.toUpperCase() == 'CHECKBOX')
		{
			arInputs[i].nextSibling.htmlFor = arInputs[i].id;
		}

		if (arInputs[i].name == 'C_SORT_' + (rows_count+1))
		{
			arInputs[i].value = '' + (parseInt(arInputs[i].value) + 100);
		}
		else if (arInputs[i].tagName == 'INPUT')
		{
			if (arInputs[i].type == 'checkbox')
				arInputs[i].defaultChecked = true;
			else
				arInputs[i].value = '';
		}
		else if (arInputs[i].tagName == 'SELECT')
		{
			arInputs[i].selectedIndex = obLastRow.cells[3].getElementsByTagName('SELECT')[0].selectedIndex;
			//arInputs[i].onchange = eval('function() {FIELD_TYPE_CHANGE(\'' + (rows_count+1) + '\'); jsFormValidatorSettings.UpdateAll();}');
			arInputs[i].onchange = new Function('FIELD_TYPE_CHANGE(\'' + (rows_count+1) + '\'); jsFormValidatorSettings.UpdateAll();');
		}

		if (new_name == 'MESSAGE_' + (rows_count+1))
		{
			arInputs[i].onchange = jsFormValidatorSettings.UpdateAll;
		}
	}

	var input1 = BX.create('INPUT', {
		props: {
			type: 'hidden',
			name: 'ANSWER[]',
			value: rows_count + 1
		}
	}),
		input2 = BX.create('INPUT', {
		props: {
			type: 'hidden',
			name: 'ANSWER_ID_' + (rows_count+1),
			value: '0'
		}
	});

	obTable.tBodies[0].appendChild(input1); obTable.tBodies[0].appendChild(input2);
	obTable.tBodies[0].appendChild(obRow);

	setTimeout(function() {
		var r = BX.findChildren(obRow, {tag: /^(input|select|textarea)$/i}, true);
		if (r && r.length > 0)
		{
			for (var i=0,l=r.length;i<l;i++)
			{
				if (r[i].form && r[i].form.BXAUTOSAVE)
					r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
				else
					break;
			}
		}
	}, 10);
}

BX.ready(function() {
	BX.addCustomEvent(document.forms.form1, 'onAutoSaveRestore', function(ob, data){
		while (document.forms.form1['ANSWER[]'].length < data['ANSWER[]'].length)
			addAnswer();
	})
});
</script><table border="0" cellspacing="1" cellpadding="0" width="100%" id="answers_table">
				<thead>
				<tr class="heading">
					<td nowrap="nowrap" width="0%">ID</td>
					<td nowrap="nowrap" width="100%"><?echo GetMessage("FORM_MESSAGE")?><span class="required"><sup>1</sup></span><br />[&nbsp;ANSWER_TEXT&nbsp;]</td>
					<td nowrap="nowrap"><?echo GetMessage("FORM_FIELD_VALUE")?><sup>&nbsp;</sup><br />[&nbsp;ANSWER_VALUE&nbsp;]</td>
					<td nowrap="nowrap"><?echo GetMessage("FORM_FIELD_TYPE")?></td>
					<td nowrap="nowrap"><?echo GetMessage("FORM_FIELD_WIDTH")?></td>
					<td nowrap="nowrap"><?echo GetMessage("FORM_FIELD_HEIGHT")?></td>
					<td nowrap="nowrap"><?echo GetMessage("FORM_FIELD_PARAM")?></td>
					<td nowrap="nowrap"><?echo GetMessage("FORM_SORT")?></td>
					<td nowrap="nowrap"><?echo GetMessage("FORM_ACT")?></td>
					<td nowrap="nowrap"><?echo GetMessage("FORM_DEL")?></td>
				</tr>
				</thead>
				<tbody>
				<?
				$z = CFormAnswer::GetList($ID, $by, $order, array(), $is_filtered);
				$i = 1;
				$arSort = array(0);
				while ($zr=$z->ExtractFields("p_")) :
					$arSort[] = intval($p_C_SORT);
				?>
				<input type="hidden" name="ANSWER[]" value="<?=$i?>">
				<input type="hidden" name="ANSWER_ID_<?=$i?>" value="<?=$p_ID?>">
				<tr>
					<td><?=$p_ID?></td>
					<td><input type="text" name="MESSAGE_<?=$i?>" value="<?=$p_MESSAGE?>" style="width:100%" onChange="jsFormValidatorSettings.UpdateAll();" /></td>
					<td><input type="text" size="16" name="VALUE_<?=$i?>" value="<?=$p_VALUE?>" /></td>
					<td nowrap="nowrap"><?
					echo SelectBoxFromArray("FIELD_TYPE_".$i, CFormAnswer::GetTypeList(), $p_FIELD_TYPE, "", "onchange=\"FIELD_TYPE_CHANGE(".$i.");jsFormValidatorSettings.UpdateAll();\" ");
					?></td>
					<td nowrap="nowrap"><input <?if ($p_FIELD_TYPE!="text" && $p_FIELD_TYPE!="textarea" && $p_FIELD_TYPE!="image" && $p_FIELD_TYPE!="date" && $p_FIELD_TYPE != 'email') echo "disabled"?> type="text" id="FIELD_WIDTH_<?=$i?>" name="FIELD_WIDTH_<?=$i?>" value="<?if (intval($p_FIELD_WIDTH)>0) echo $p_FIELD_WIDTH?>" size="3" /></td>
					<td nowrap="nowrap"><input <?if ($p_FIELD_TYPE!="textarea" && $p_FIELD_TYPE!="multiselect") echo "disabled"?> type="text" id="FIELD_HEIGHT_<?=$i?>" name="FIELD_HEIGHT_<?=$i?>" value="<?if (intval($p_FIELD_HEIGHT)>0) echo $p_FIELD_HEIGHT?>" size="3" /></td>
					<td nowrap="nowrap"><input type="text" name="FIELD_PARAM_<?=$i?>" value="<?=$p_FIELD_PARAM?>" size="8" /></td>
					<td nowrap="nowrap"><input type="text" name="C_SORT_<?=$i?>" value="<?=$p_C_SORT?>" size="3" /></td>
					<td><?
					echo InputType("checkbox", "ACTIVE_".$i,"Y", $p_ACTIVE,false);?></td>
					<td nowrap="nowrap"><input type="checkbox" name="del_<?=$i?>" value="Y" /></td>
				</tr>
				<?
				$i++;
				endwhile;
				//$count = $i+10;
				$count = $i;
				$s = intval(max($arSort))+100;
				while ($i<=$count) :
					if (strlen($strError)>0)
					{
						$message = htmlspecialcharsbx(${"MESSAGE_".$i});
						$value = htmlspecialcharsbx(${"VALUE_".$i});
						$ftype = htmlspecialcharsbx(${"FIELD_TYPE_".$i});
						$width = htmlspecialcharsbx(${"FIELD_WIDTH_".$i});
						$height = htmlspecialcharsbx(${"FIELD_HEIGHT_".$i});
						$param = htmlspecialcharsbx(${"FIELD_PARAM_".$i});
					}
					if (strlen($ftype)<=0) $ftype = $p_FIELD_TYPE;
					if (strlen($ftype)<=0) $ftype = "text";
				?>
				<input type="hidden" name="ANSWER[]" value="<?=$i?>" />
				<input type="hidden" name="ANSWER_ID_<?=$i?>" value="0" />
				<tr>
					<td></td>
					<td><input type="text" name="MESSAGE_<?=$i?>" value="<?=$message?>" style="width:100%" onchange="jsFormValidatorSettings.UpdateAll();" /></td>
					<td><input type="text" name="VALUE_<?=$i?>" value="<?=$value?>" size="16" /></td>
					<td nowrap="nowrap"><?
					echo SelectBoxFromArray("FIELD_TYPE_".$i, CFormAnswer::GetTypeList(), $ftype, "", "onchange=\"FIELD_TYPE_CHANGE(".$i."); jsFormValidatorSettings.UpdateAll();\"");
					?></td>
					<td nowrap="nowrap"><input <?if ($ftype!="text" && $ftype!="textarea" && $ftype!="image" && $ftype!="date" && $ftype != 'email') echo "disabled"?> type="text" id="FIELD_WIDTH_<?=$i?>" name="FIELD_WIDTH_<?=$i?>" value="<?=$width?>" size="3" /></td>
					<td nowrap="nowrap"><input <?if ($ftype!="textarea" && $ftype!="multiselect") echo "disabled"?> type="text" id="FIELD_HEIGHT_<?=$i?>" name="FIELD_HEIGHT_<?=$i?>" value="<?=$height?>" size="3" /></td>
					<td nowrap="nowrap"><input type="text" name="FIELD_PARAM_<?=$i?>" value="<?=$param?>" size="8" /></td>
					<td nowrap="nowrap"><input type="text" name="C_SORT_<?=$i?>" value="<?echo (strlen(${"C_SORT_".$i})>0 && strlen($message)>0) ? htmlspecialcharsbx(${"C_SORT_".$i}) : $s?>" size="3" /></td>
					<td><?
					echo InputType("checkbox", "ACTIVE_".$i, "Y", "Y", false);?></td>
					<td>&nbsp;</td>
				</tr>
				<?
				$i++;
				$s = $s + 100;
				endwhile;
				?>
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td colspan="9"><div onclick="addAnswer()" id="btn_new" style="height: 20px; width: 110px; background-repeat: no-repeat; cursor: pointer;" title="<?=GetMessage('FORM_ADD_ANSWER')?>"><nobr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=GetMessage('FORM_ADD_ANSWER')?></nobr></div></td>
					</tr>
				</tfoot>
			</table>
		</td>
	</tr>
<?
//********************
//Validators Tab
//********************
	$tabControl->BeginNextTab();
	$rsValidators = CFormValidator::GetAllList();
	if ($rsValidators->SelectedRowsCount() > 0)
	{
?>
	<script language="javascript">
	var arValidatorsType = [];
	var arValidators = [];
<?
	while ($arValidatorInfo = $rsValidators->Fetch())
	{
		if (!is_array($arValidatorInfo["TYPES"]))
		{
			if ($arValidatorInfo["TYPES"] == 0) continue;
			$arValidatorInfo["TYPES"] = array($arValidatorInfo["TYPES"]);
		}

		?>

	arValidators['<?=CUtil::JSEscape($arValidatorInfo["NAME"])?>'] = {NAME:'<?=CUtil::JSEscape($arValidatorInfo["NAME"])?>', DESCRIPTION:'<?=CUtil::JSEscape($arValidatorInfo["DESCRIPTION"])?>', HAS_SETTINGS:'<?=is_callable($arValidatorInfo['SETTINGS']) > 0 ? "Y" : "N"?>'};
<?

		foreach ($arValidatorInfo["TYPES"] as $type)
		{
			$type = CUtil::JSEscape($type);
?>
	if (!arValidatorsType['<?=$type?>']) arValidatorsType['<?=$type?>'] = [];
	arValidatorsType['<?=$type?>'][arValidatorsType['<?=$type?>'].length] = '<?=CUtil::JSEscape($arValidatorInfo["NAME"])?>';
<?
		}
	}
?>
	</script>
	<script language="JavaScript">
var arCurrentValidators = new Array();
<?
if (is_array($arCurrentValidators) && count($arCurrentValidators) > 0)
{
	foreach ($arCurrentValidators as $arVal)
	{
?>
	arCurrentValidators[arCurrentValidators.length] = {
		NAME:'<?=CUtil::JSEscape($arVal["NAME"])?>'

<?
		if (is_array($arVal["PARAMS"]) && count($arVal["PARAMS"]) > 0)
		{
?>		,
		PARAMS:[<?
			$i = 0;
			foreach ($arVal["PARAMS"] as $key => $value)
			{
?><?=$i++ == 0 ? "" : ","?>

				{NAME:'<?=CUtil::JSEscape($key)?>', VALUE:'<?=CUtil::JSEscape($value)?>', TITLE:'<?=CUtil::JSEscape($arVal["PARAMS_FULL"][$key]["TITLE"]) ?>:'}<?
			}
?>

		]
<?
		}
?>
	}
<?
	}
}

\Bitrix\Main\Localization\Loc::loadMessages(dirname(__FILE__).'/../form_validator_props.php');
?>
	</script>
	<script>
var _global_BX_UTF = <?if (defined('BX_UTF') && BX_UTF === true):?>true<?else:?>false<?endif?>;
	</script>
	<script src="/bitrix/js/form/form_validators.js?<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/form/form_validators.js')?>"></script>
	<script language="JavaScript">
BX.message({
	WND_TITLE:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_PROPS_TITLE'))?>',

	ADD_TITLE:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_ADD_TITLE'))?>',
	NO_REGISTERED_VALS_TYPE:'<?=CUtil::JSEscape(GetMessage("FORM_VAL_NO_REGISTERED_VALS_TYPE"))?>',
	LIST_HEAD_VAL:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_LIST_HEAD_VAL'))?>',
	LIST_HEAD_PARAMS:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_LIST_HEAD_PARAMS'))?>',
	LIST_HEAD_REMOVE:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_LIST_HEAD_REMOVE'))?>',
	DEL_TITLE:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_DEL_TITLE'))?>',
	NO_CURRENT:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_NO_CURRENT'))?>',
	ERROR_MULTITYPE:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_ERROR_MULTITYPE'))?>',
	INVALID:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_INVALID'))?>',
	VALID:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_VALID'))?>'
});

var jsFormValidatorSettings = new CFormValidatorSettings(false);
	</script>
	<tr style="display: none;"><td colspan="2"><?=CalendarDate('__test', '', 'form1')?></td></tr>
	<tr>
		<td width="40%"><?=GetMessage("FORM_VAL_LIST_TITLE")?> <span id="type_title"></span>:</td>
		<td width="60%" id="validators_list">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" align="center" id="validators_current"></td>
	</tr>
	<script language="JavaScript">
jsFormValidatorSettings.UpdateAll();
</script>
<?
	}
	else
	{
?>
	<tr>
		<td colspan="2"><?=ShowError(GetMessage("FORM_VAL_NO_REGISTERED_VALS"))?></td>
	</tr>
<?
	}
}
?>
<?
//********************
//Result Tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td><?=GetMessage("FORM_IN_RESULTS_TABLE")?></td>
		<td><?echo InputType("checkbox","IN_RESULTS_TABLE","Y",$str_IN_RESULTS_TABLE,false) ?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_IN_EXCEL_TABLE")?></td>
		<td><?echo InputType("checkbox","IN_EXCEL_TABLE","Y",$str_IN_EXCEL_TABLE,false) ?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_RESULTS_TABLE_TITLE")?></td>
		<td><input type="text" name="RESULTS_TABLE_TITLE" size="50" value="<?=$str_RESULTS_TABLE_TITLE?>" /></td>
	</tr>
<?
//********************
//General Tab
//********************
$tabControl->BeginNextTab();
?>
	<?
	CFormField::GetFilterTypeList($arrUSER, $arrANSWER_TEXT, $arrANSWER_VALUE, $arrFIELD);
	if ($ID>0)
	{
		$arrFilter = array();
		$z = CFormField::GetFilterList($WEB_FORM_ID, Array("FIELD_ID" => $ID, "FIELD_ID_EXACT_MATCH" => "Y"));
		while ($zr = $z->Fetch())
			$arrFilter[$zr["PARAMETER_NAME"]][] = $zr["FILTER_TYPE"];
	}
	if ($additional!="Y"):
	?>
	<tr>
		<td><?echo GetMessage("FORM_FILTER_FOR_USER")?><br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt="" /></td>
		<td><?
		echo SelectBoxMFromArray("arFILTER_USER[]",array("REFERENCE"=>$arrUSER["reference"], "REFERENCE_ID"=>$arrUSER["reference_id"]), $arrFilter["USER"],"",false,"5");?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FORM_FILTER_FOR_ANSWER_TEXT")?><br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?
		echo SelectBoxMFromArray("arFILTER_ANSWER_TEXT[]",array("REFERENCE"=>$arrANSWER_TEXT["reference"], "REFERENCE_ID"=>$arrANSWER_TEXT["reference_id"]), $arrFilter["ANSWER_TEXT"],"",false,"5");?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("FORM_FILTER_FOR_ANSWER_VALUE")?><br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt=""></td>
		<td><?
		echo SelectBoxMFromArray("arFILTER_ANSWER_VALUE[]",array("REFERENCE"=>$arrANSWER_VALUE["reference"], "REFERENCE_ID"=>$arrANSWER_VALUE["reference_id"]), $arrFilter["ANSWER_VALUE"],"",false,"5");?></td>
	</tr>
	<?
	else:
	?>
	<tr>
		<td><?echo GetMessage("FORM_FILTER_TYPE")?>:<br><img src="/bitrix/images/form/mouse.gif" width="44" height="21" border=0 alt="" /></td>
		<td><?
		echo SelectBoxMFromArray("arFILTER_FIELD[]",array("REFERENCE"=>$arrFIELD["reference"], "REFERENCE_ID"=>$arrFIELD["reference_id"]), $arrFilter["USER"],"",false,"3");?></td>
	</tr>
	<?endif;?>
	<tr>
		<td><?=GetMessage("FORM_FILTER_TITLE")?></td>
		<td><input type="text" name="FILTER_TITLE" size="50" value="<?=$str_FILTER_TITLE?>" /></td>
	</tr>
<?
//********************
//General Tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><textarea name="COMMENTS" cols="80" rows="5"><?echo $str_COMMENTS?></textarea></td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>(!($F_RIGHT>=30 || CForm::IsAdmin())), "back_url"=>"form_field_list.php?WEB_FORM_ID=".$WEB_FORM_ID."&additional=".$additional."&lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>
<?
if(!$additional):
	echo BeginNote();
?>
<span class="required"><sup>1</sup></span> -  <?=GetMessage("FORM_MESSAGE_SPACE")?>
<?
	echo EndNote();
endif;
?>


<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>
