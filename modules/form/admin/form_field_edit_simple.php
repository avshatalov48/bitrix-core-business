<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/prolog.php");

$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("form");

$strError = '';

ClearVars();

IncludeModuleLangFile(__FILE__);

$err_mess = "File: ".__FILE__."<br>Line: ";
define("HELP_FILE","form_question_list.php");
$old_module_version = CForm::IsOldVersion();

$aTabs = array ();
$aTabs[]=array("DIV" => "edit1", "TAB" => GetMessage("FORM_PROP"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_PROP_TITLE"));
$aTabs[] = array("DIV" => "edit7", "TAB" => GetMessage("FORM_VAL"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_VAL_TITLE"));
$aTabs[]=array("DIV" => "edit6", "TAB" => GetMessage("FORM_COMMENT_TOP"), "ICON" => "form_edit", "TITLE" => GetMessage("FORM_COMMENTS"));

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$err_message = null;

$WEB_FORM_ID = intval($WEB_FORM_ID);
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

$ID = intval($ID);
$F_RIGHT = CForm::GetPermission($WEB_FORM_ID);
if($F_RIGHT<25) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

InitBVar($additional);

if (intval($copy_id)>0 && check_bitrix_sessid() && $F_RIGHT >= 30)
{
	$new_id = CFormField::Copy($copy_id);
	if ($strError == '' && intval($new_id)>0)
	{
		LocalRedirect("form_field_edit_simple.php?ID=".$new_id."&additional=".$additional."&WEB_FORM_ID=".$WEB_FORM_ID."&lang=".LANGUAGE_ID ."&strError=".urlencode($strError));
	}
}

//get/post processing
if (($save <> '' || $apply <> '') && $REQUEST_METHOD=="POST" && $F_RIGHT >= 30 && check_bitrix_sessid())
{
	$arIMAGE = $_FILES["IMAGE_ID"];
	$arIMAGE["MODULE_ID"] = "form";
	$arIMAGE["del"] = ${"IMAGE_ID_del"};
	$arFields = array(
		"FORM_ID"		=> $WEB_FORM_ID,
		"ACTIVE"		=> $ACTIVE,
		"TITLE"			=> $TITLE,
		"TITLE_TYPE"		=> $TITLE_TYPE,
		"C_SORT"		=> $C_SORT,
		"ADDITIONAL"		=> $FIELD_TYPE=='hidden'?'Y':'N',
		"REQUIRED"		=> $REQUIRED,
		"IN_RESULTS_TABLE"	=> "Y",
		"IN_EXCEL_TABLE"	=> "Y",
		"FIELD_TYPE"		=> $FIELD_HIDDEN_TYPE,
		"COMMENTS"		=> $COMMENTS,
		"FILTER_TITLE"		=> $TITLE,
		"RESULTS_TABLE_TITLE"	=> $TITLE,
		"arIMAGE"		=> $arIMAGE,
		);
	$arTypeList=array('multiselect','checkbox','radio','dropdown');

	if (!in_array($FIELD_TYPE, $arTypeList))
	{
		$arrA = array();
		$arrA["ID"] = intval($SINGLE_ANSWER);
		$arrA["MESSAGE"] = " ";
		$arrA["VALUE"] = "";
		$arrA["C_SORT"] = 0;
		$arrA["ACTIVE"] = "Y";
		$arrA["FIELD_TYPE"] = $FIELD_TYPE;
		$arrA["FIELD_WIDTH"] = $FIELD_TYPE=='textarea' ? intval($FIELD_WIDTH) : intval($FIELD_SIZE);
		$arrA["FIELD_HEIGHT"] = intval($FIELD_HEIGHT);
		$arrA["FIELD_PARAM"] = '';
		$arFields["arANSWER"][] = $arrA;
	}
	elseif (is_array($ANSWER))
	{
		$MESSAGE = $_REQUEST["MESSAGE"];
		foreach ($ANSWER as $i => $pid)
		{
			$i = intval($i);
			$pid = intval($pid);
			if ($i<0 || $pid<0) continue;

			$arrA = array();
			$arrA["ID"] = $pid;
			$arrA["MESSAGE"] = $MESSAGE[$i] <> '' ? $MESSAGE[$i] : " ";
			$arrA["VALUE"] = $VALUE[$i];
			$arrA["C_SORT"] = $SORT[$i];
			$arrA["ACTIVE"] = "Y";
			$arrA["FIELD_TYPE"] = $FIELD_TYPE;

			if ($DEF[$i]=="Y" && ($FIELD_TYPE=='checkbox' || $FIELD_TYPE=='radio'))
				$arrA["FIELD_PARAM"] = 'checked';
			elseif ($DEF[$i]=="Y")
				$arrA["FIELD_PARAM"] = 'selected';
			else
				$arrA["FIELD_PARAM"] = '';

			$arFields["arANSWER"][] = $arrA;
		}
	}

	if (is_array($DELETE))
	{
		$i=0;
		foreach ($DELETE as $key => $val)
		{
			if ($val == "Y" || ($i > 0 && !in_array($FIELD_TYPE, $arTypeList))) // if it's not a list kill all answers except first one
			{
				$arrA = array();
				$arrA["ID"] = $key;
				$arrA["DELETE"] = "Y";
				$arFields["arANSWER"][] = $arrA;
			}
			$i++;
		}
	}

	if ($FIELD_TYPE=='hidden')
		$arFields["arFILTER_FIELD"] = array(htmlspecialcharsbx($FIELD_HIDDEN_TYPE));
	else
	{
		$arFields["arFILTER_USER"] = '';
		$arFields["arFILTER_ANSWER_TEXT"] = '';
		$arFields["arFILTER_ANSWER_VALUE"] = '';

		if ($FIELD_TYPE=='date')
			$arFields["arFILTER_USER"] = array('date','exist');
		elseif (in_array($FIELD_TYPE,$arTypeList))
			$arFields["arFILTER_ANSWER_TEXT"] = array('dropdown');
		else
			$arFields["arFILTER_USER"] = array('text','exist');
	}
	if (intval($ID)==0)
	{
		$arFields["SID"]="SIMPLE_QUESTION_".rand(100,999);
	}

	$res = intval(CFormField::Set($arFields, $ID));
	if ($res>0)
	{
		if (intval($ID) > 0)
			CFormValidator::Clear($ID);

		$ID = $res;

		// process field validators
		$sValStructSerialized = $_REQUEST["VAL_STRUCTURE"];
		if (CheckSerializedData($sValStructSerialized))
		{
			$arValStructure = unserialize($sValStructSerialized, ['allowed_classes' => false]);

			if (count($arValStructure) > 0)
			{
				CFormValidator::SetBatch($WEB_FORM_ID, $ID, $arValStructure);
			}
		}

		if ($strError == '')
		{
			if ($save <> '') LocalRedirect("form_field_list.php?WEB_FORM_ID=".$WEB_FORM_ID."&additional=". $additional."&lang=".LANGUAGE_ID);
			else LocalRedirect("form_field_edit_simple.php?ID=".$ID."&WEB_FORM_ID=".$WEB_FORM_ID."&additional=". $additional."&lang=".LANGUAGE_ID."&".$tabControl->ActiveTabParam());
		}
	}
	$DB->PrepareFields("b_form_field");
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

if ($strError <> '') $DB->InitTableVarsForEdit("b_form_field", "", "str_");

if ($ID>0) $sDocTitle = str_replace("#ID#", $ID, GetMessage("FORM_EDIT_RECORD"));
else $sDocTitle = GetMessage("FORM_NEW_RECORD");

//$z = CForm::GetByID($WEB_FORM_ID);
//$arForm = $z->Fetch();

$APPLICATION->SetTitle($sDocTitle);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$context = new CAdminContextMenuList($arForm['ADMIN_MENU']);
$context->Show();

echo BeginNote('width="100%"');?>
<b><?=GetMessage("FORM_FORM_NAME")?></b> [<a title='<?=GetMessage("FORM_EDIT_FORM")?>' href='form_edit.php?lang=<?=LANGUAGE_ID?>&ID=<?=$WEB_FORM_ID?>'><?=$WEB_FORM_ID?></a>]&nbsp;(<?=htmlspecialcharsbx($arForm["SID"])?>)&nbsp;<?=htmlspecialcharsbx($arForm["NAME"])?>
<?echo EndNote();

$aMenu = array();

if ($F_RIGHT>=30 && $ID>0)
{
	$aMenu[] = array(
		"ICON"	=> "btn_new",
		"TEXT"	=> GetMessage("FORM_CREATE"),
		"TITLE"	=> GetMessage("FORM_CREATE_QUESTION"),
		"LINK"	=> "form_field_edit_simple.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$WEB_FORM_ID
		);

	$aMenu[] = array(
		"ICON"	=> "btn_copy",
		"TEXT"	=> GetMessage("FORM_CP"),
		"TITLE"	=> GetMessage("FORM_COPY_QUESTION"),
		"LINK"	=> "form_field_edit_simple.php?ID=".$ID."&amp;copy_id=".$ID."&lang=".LANGUAGE_ID. "&WEB_FORM_ID=".$WEB_FORM_ID."&".bitrix_sessid_get()
		);

	$aMenu[] = array(
		"ICON"	=> "btn_delete",
		"TEXT"	=> GetMessage("FORM_DELETE_QUESTION"),
		"TITLE"	=> GetMessage("FORM_DELETE_QUESTION"),
		"LINK"	=> "javascript:if(confirm('".GetMessage("FORM_CONFIRM_DELETE_QUESTION")."'))window.location='form_field_list.php?action=delete&ID=".$ID."&WEB_FORM_ID=".$WEB_FORM_ID."&".bitrix_sessid_get()."&lang=".LANGUAGE_ID."';",
		);

	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}

####### get answers list
$rAnswer = CFormAnswer::GetList($ID);
$i=0;
$bWarn=false;
$arRow=array();
while($f=$rAnswer->ExtractFields("p_"))
{
	if ($i==0)
		$ftype=$p_FIELD_TYPE;

	$arRows[]=array(
		'ID'		=> $p_ID,
		'MESSAGE'	=> $p_MESSAGE,
		'SORT'		=> $p_C_SORT,
		'DEF'		=> $p_FIELD_PARAM ? 'checked' : '',
		'DEF_HIDDEN'	=> $p_FIELD_PARAM ? 'Y' : '',
		'WIDTH'		=> $p_FIELD_WIDTH==0 ? '' : $p_FIELD_WIDTH,
		'HEIGHT'	=> $p_FIELD_HEIGHT==0 ? '' : $p_FIELD_HEIGHT,
	);

	if ($last_type && $p_FIELD_TYPE!=$last_type && !$bWarn)
		$bWarn=true;
	$last_type=$p_FIELD_TYPE;

	$i++;
}
if ($str_ADDITIONAL=="Y")
	$ftype='hidden';
#############################

####### get validators list
$arCurrentValidators = array();
if ($ID > 0)
{
	$rsCurrentValidators = CFormValidator::GetList($ID);
	while ($arValidator = $rsCurrentValidators->Fetch())
	{
		$arCurrentValidators[] = $arValidator;
	}
}
#############################
if ($bWarn)
	$strError.=GetMessage("FORM_SAVE_WARN")."<br>";

if($strError)
	CAdminMessage::ShowOldStyleError($strError);

?>
<script language="JavaScript">
function FormSubmit()
{
	return jsFormValidatorSettings.PrepareToSubmit();
}
</script>
<form name="form1" method="POST" action="" enctype="multipart/form-data" onSubmit="return FormSubmit();">
<?=bitrix_sessid_post()?>
<input type="hidden" name="ID" value=<?=$ID?> />
<input type="hidden" name="WEB_FORM_ID" value=<?=$WEB_FORM_ID?> />
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
	<? if ($str_TIMESTAMP_X <> '') : ?>
	<tr>
		<td><?=GetMessage("FORM_TIMESTAMP")?></td>
		<td><?=$str_TIMESTAMP_X?></td>
	</tr>
	<?endif;?>
	<tr>
		<td width="40%"><?=GetMessage("FORM_ACTIVE")?></td>
		<td width="60%"><?echo InputType("checkbox","ACTIVE","Y",$str_ACTIVE,false) ?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_C_SORT")?></td>
		<td><input type="text" name="C_SORT" size="5" maxlength="18" value="<?echo $str_C_SORT?>" /></td>
	</tr>
<?
//********************
//question
//********************

$str_IMAGE_ID = intval($str_IMAGE_ID);
?>
	<tr>
		<td valign="top"><?=GetMessage("FORM_IMAGE")?></td>
		<td><?echo CFile::InputFile("IMAGE_ID", 20, $str_IMAGE_ID);?><?if ($str_IMAGE_ID>0):?><br><?echo CFile::ShowImage($str_IMAGE_ID, 200, 200, "border=0", "", true)?><?endif;?></td>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FORM_QUESTION")?></td>
	<?
	if(COption::GetOptionString("form", "USE_HTML_EDIT")=="Y" && CModule::IncludeModule("fileman")):?>
		<td><?
		CFileMan::AddHTMLEditorFrame("TITLE", $str_TITLE, "TITLE_TYPE", $str_TITLE_TYPE, 50);
		?></td>
	</tr>
	<?else:?>
	<tr>
		<td align="center" colspan="2"><? echo InputType("radio","TITLE_TYPE","text",$str_TITLE_TYPE,false)?>&nbsp;<?echo GetMessage("FORM_TEXT")?>/&nbsp;<? echo InputType("radio","TITLE_TYPE","html",$str_TITLE_TYPE,false)?>HTML</td>
	</tr>
	<tr>
		<td align="center" colspan="2"><textarea name="TITLE" style="width:100%" rows="5"><?echo $str_TITLE?></textarea></td>
	</tr>
	<?endif;?>
<?
//********************
//Answer
//********************
?>
<? ################################# JS START ######### ?>
<script language="JavaScript">

var multi = false;
function ShowSelected()
{
	a = document.getElementById("selected_type").value;
<?
$arDisplayTypes=array("text", "textarea", "list", "hidden");
foreach ($arDisplayTypes as $e)
	echo "BX('type_".$e."').style.display=\"none\"\n";
?>
	multi = false;
	if (a == 'textarea')
		var b = a;
	else if (a == 'hidden')
		var b = a;
	else if (a == 'radio' || a == 'dropdown')
	{
		var b = 'list';
		ClearAll(false);
	}
	else if (a == 'checkbox' ||  a == 'multiselect')
	{
		var b = 'list';
		multi = true;
	}
	else
		var b = 'text';

	document.getElementById('type_'+b).style.display="block";

}

function RowInsert()
{
	tbl = BX('tbl_answers');

	oLastRow = tbl.rows[(tbl.rows.length-1)];
	oLastSort = BX(oLastRow.id + '_sort');
	if (isNaN(oLastSort.value) || oLastSort.value == '')
		oLastSort.value = 0;
	Sort = oLastSort.value - (-10);

	var oRow = tbl.insertRow(-1);
	oRow.id='row'+oRow.rowIndex;

	(oRow.insertCell(-1)).innerHTML = '<input type="hidden" name="ANSWER[]" value="0" /><input name="MESSAGE[]" type="text" />\n';
	(oRow.insertCell(-1)).innerHTML = '<input name="SORT[]" id="' + oRow.id + '_sort" value="' + Sort +'" type="text" size="5" />\n';
	(oRow.insertCell(-1)).innerHTML = '<input name="DEF[]" type="hidden" value="" id="'+oRow.id+'_def_hidden" /><input onclick="ClearAll(\'' + oRow.id + '\')" type="checkbox" id="'+ oRow.id +'_def" name="'+ oRow.id +'_def" />';

	BX.adjust(oRow.insertCell(-1), {
		html: '<div onclick="RowDelete(\''+oRow.id+'\',0)" title="<?=GetMessage("FORM_ANS_DEL")?>" id="btn_delete" style="width:20px;height:20px;cursor:pointer"></div>',
		props: {id: oRow.id + '_del'}
	});

	BtnShow(1);

	setTimeout(function() {
		BX.adminFormTools.modifyFormElements(oRow);
		var r = BX.findChildren(oRow, {tag: /^(input|select|textarea)$/i}, true);
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

function RowDelete(id,del_store)
{
	var oRow = document.getElementById(id);
	var i = oRow.rowIndex;
	if (del_store==1)
		document.getElementById(oRow.id+'_DELETE').value="Y";

	tbl = document.getElementById('tbl_answers');
	tbl.deleteRow(i);

	if (tbl.rows.length==2)
		BtnShow(0);
}

function ClearAll(id)
{
	var bAlreadyHaveOne=false;
	var tbl = document.getElementById('tbl_answers');
	var l = tbl.rows.length;

	for(i=1;i<l;i++)
	{
		oRow = tbl.rows[i];
		if (!multi)
		{
			if (id)
			{
				if (oRow.id != id)
				{
					document.getElementById(oRow.id + '_def').checked = false;
				}
			}
			else
			{
				if (!bAlreadyHaveOne && document.getElementById(oRow.id + '_def').checked)
					bAlreadyHaveOne = true;
				else
					document.getElementById(oRow.id + '_def').checked = false;
			}
		}
		if (document.getElementById(oRow.id+'_def').checked)
			document.getElementById(oRow.id+'_def_hidden').value = "Y";
		else
			document.getElementById(oRow.id+'_def_hidden').value = "";
	}
}

function BtnShow(flag)
{
	first_id = tbl.rows[1].id;
	if (flag == 1)
		var v = 'visible';
	else
		var v = 'hidden';
	document.getElementById(first_id+'_del').style.visibility = v;
}

BX.ready(function() {
	BX.addCustomEvent(document.forms.form1, 'onAutoSaveRestore', function(ob, data){
		if (BX.type.isArray(data['MESSAGE[]']) && data['MESSAGE[]'].length > 1)
		{
			RowInsert();
			while (document.forms.form1['MESSAGE[]'].length < data['MESSAGE[]'].length)
				RowInsert();
		}
	})
});
</script>
<? ################################# END ################ ?>
	<tr>
		<td><?=GetMessage("FORM_REQUIRED")?></td>
		<td><?echo InputType("checkbox","REQUIRED","Y",$str_REQUIRED,false) ?></td>
	</tr>
	<tr>
		<td><?=GetMessage("FORM_ANS_TYPE")?></td>
		<td><select name="FIELD_TYPE" id="selected_type" onchange="ShowSelected(); jsFormValidatorSettings.UpdateAll();">
	<?
	$arDisplay = array(
		'text'		=>'none',
		'textarea'	=>'none',
		'list'		=>'none',
	);
	$arCompat_desc[1]=array(
		'radio'		=>GetMessage('F_TYPES_RADIO'),
		'checkbox'	=>GetMessage('F_TYPES_CHECKBOX'),
		'dropdown'	=>GetMessage('F_TYPES_DROPDOWN'),
		'multiselect'	=>GetMessage('F_TYPES_MULTISELECT'),
	);
	$arCompat_desc[2]=array(
		'text'		=>GetMessage('F_TYPES_TEXT'),
		'email'		=>GetMessage('F_TYPES_EMAIL'),
		'url'		=>GetMessage('F_TYPES_URL'),
		'textarea'	=>GetMessage('F_TYPES_TEXTAREA'),
		'date'		=>GetMessage('F_TYPES_DATE'),
		'password'	=>GetMessage('F_TYPES_PASSWORD'),
	);
	$arCompat_desc[3]=array(
		'image'		=>GetMessage('F_TYPES_IMAGE'),
		'file'		=>GetMessage('F_TYPES_FILE'),
	);
	$arCompat_desc[4]=array(
		'hidden'	=>GetMessage('F_TYPES_HIDDEN'),
	);
	$arTypeList_desc = array_merge($arCompat_desc[1],$arCompat_desc[2],$arCompat_desc[3],$arCompat_desc[4]);

	$bResults=false;
	if (intval($WEB_FORM_ID)>0)
	{
		$result = CFormResult::GetList($WEB_FORM_ID, '', '', $arFilter);
		if ($result->Fetch()) // form already has results
		{
			$bResults=true;
			foreach ($arCompat_desc as $val)
			{
				if (in_array($ftype, array_keys($val)))
				{
					$arCompatList = array_keys($val); // list of compatible fields
					break;
				}

			}
		}
	}

	$arTypes = CFormAnswer::GetTypeList();
	foreach ($arTypes['reference'] as $val)
	{
		if (!$ID || !$bResults || in_array($val, $arCompatList))
			print "<option value='$val'".($val == $ftype?" selected=\"selected\"":"").">".$arTypeList_desc[$val]." [".$val."]</option>\n";
	}
	?>
		</select>
	</tr>
	<tr>
		<td valign="top"><?=GetMessage("FORM_ANSWER")?></td>
		<td>
			<input type="hidden" name="SINGLE_ANSWER" value="<?=$arRows[0]['ID']?>" />
			<div id="type_text" style="display:<?=$arDisplay['text']?>">
				<table class="internal">
				<tr class="heading">
					<td><?echo GetMessage("FORM_FIELD_SIZE_VAL")?></td>
				</tr>
				<tr>
					<td><input type="text" size="8" name="FIELD_SIZE" value="<?=$arRows[0]['WIDTH']?>" /></td>
				</tr>
				</table>
			</div>
			<div id="type_textarea">
				<table class="internal">
				<tr class="heading">
					<td><?echo GetMessage("FORM_FIELD_WIDTH_VAL")?></td>
					<td><?echo GetMessage("FORM_FIELD_HEIGHT_VAL")?></td>
				</tr>
				<tr>
					<td><input type="text" size="5" name="FIELD_WIDTH" value="<?=$arRows[0]['WIDTH']?>" /></td>
					<td><input type="text" size="5" name="FIELD_HEIGHT" value="<?=$arRows[0]['HEIGHT']?>" /></td>
				</tr>
				</table>
			</div>
			<div id="type_list">
				<table class="internal" id="tbl_answers">
				<tr class="heading">
					<td><?echo GetMessage("FORM_ANSWER_VAL")?></td>
					<td><?echo GetMessage("FORM_SORT_VAL")?></td>
					<td><?echo GetMessage("FORM_DEF_VAL")?></td>
					<td></td>
				</tr>
				<?
				$i=0;
				if (is_array($arRows))
				{
					foreach($arRows AS $arRow):
						$i++;?>
						<input type="hidden" name="DELETE[<?=$arRow['ID']?>]" id="row<?=$i?>_DELETE" />
						<tr id="row<?=$i?>">
							<td><input type="hidden" name="ANSWER[]" value="<?=$arRow['ID']?>" /><input type="text" name="MESSAGE[]" value="<?=$arRow['MESSAGE']?>" /></td>
							<td><input id="row<?=$i?>_sort" name="SORT[]" value="<?=$arRow['SORT']?>" type="text" size="5" /></td>
							<td><input name="DEF[]" type="hidden" value="<?=$arRow['DEF_HIDDEN']?>" id="row<?=$i?>_def_hidden" /><input OnClick="ClearAll('row<?=$i?>')" type="checkbox" <?=$arRow['DEF']?> id="row<?=$i?>_def" name="row<?=$i?>_def" /></td>
							<td id="row<?=$i?>_del" <?if(count($arRows)<2) print 'style="visibility:hidden"'?>><div onclick="RowDelete('row<?=$i?>',1)" title="<?=GetMessage("FORM_ANS_DEL")?>" id="btn_delete" style="width:20px;height:20px;cursor:pointer;"></div></td>
						</tr>
						<?
					endforeach;
				}
				else
				{ // new record
				?>
					<tr id="row1">
						<td><input type="hidden" name="ANSWER[]" /><input type="text" name="MESSAGE[]" /></td>
						<td><input id="row1_sort" name="SORT[]" type="text" size="5" /></td>
						<td><input name="DEF[]" type="hidden" id="row1_def_hidden" /><input OnClick="ClearAll('row1')" type="checkbox" id="row1_def" name="row1_def" /></td>
						<td id="row1_del" style="visibility:hidden"><div onclick="RowDelete('row1',0)" title="<?=GetMessage("FORM_ANS_DEL")?>" id="btn_delete" style="width:20px;height:20px;cursor:pointer;"></div></td>
					</tr>
				<?
				}
				?>
				</table>
				<table OnClick="RowInsert()" style="cursor:pointer";>
					<tr>
						<td><div title="<?=GetMessage("FORM_ANS_ADD")?>" id="btn_new" style="width:20;height:20;"></div></td>
						<td><?=GetMessage("FORM_ANS_ADD");?></td>
					</tr>
				</table>
			</div>
			<div id="type_hidden">
				<table class="internal">
					<tr class="heading">
						<td><?echo GetMessage("FORM_ADDITIONAL_FIELD_TYPE")?></td>
					</tr>
					<tr>
						<td><?
						echo SelectBoxFromArray("FIELD_HIDDEN_TYPE", CFormField::GetTypeList(), $str_FIELD_TYPE);
						?></td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
<script>ShowSelected();</script>
<?
//********************
// Validators Tab
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

	arValidators['<?=CUtil::JSEscape($arValidatorInfo["NAME"])?>'] = {NAME:'<?=CUtil::JSEscape($arValidatorInfo["NAME"])?>', DESCRIPTION:'<?=CUtil::JSEscape($arValidatorInfo["DESCRIPTION"])?>', HAS_SETTINGS:'<?=is_callable($arValidatorInfo['SETTINGS']) ? "Y" : "N"?>'};
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
	INVALID:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_INVALID'))?>',
	VALID:'<?=CUtil::JSEscape(GetMessage('FORM_VAL_VALID'))?>'
});

var jsFormValidatorSettings = new CFormValidatorSettings(true);
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
?>

<?
//********************
// Last tab
//********************
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2" align="center"><textarea name="COMMENTS" cols="80" rows="5"><?echo $str_COMMENTS?></textarea></td>
	</tr>
<?
$tabControl->EndTab();
$tabControl->Buttons(array("disabled"=>($F_RIGHT<30), "back_url"=>"form_field_list.php?WEB_FORM_ID=".$WEB_FORM_ID."&lang=".LANGUAGE_ID));
$tabControl->End();
?>
</form>

<? require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");