<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if (!CModule::IncludeModule('learning'))
{
	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php'); // second system's prolog

	if (IsModuleInstalled('learning') && defined('LEARNING_FAILED_TO_LOAD_REASON'))
		echo LEARNING_FAILED_TO_LOAD_REASON;
	else
		CAdminMessage::ShowMessage(GetMessage('LEARNING_MODULE_NOT_FOUND'));

	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');	// system's epilog
	exit();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/prolog.php");
IncludeModuleLangFile(__FILE__);

ClearVars();

$ATTEMPT_ID = intval($ATTEMPT_ID);
$ID = intval($ID);
$bBadResult = false;
$message = null;

//$r = CTestAttempt::GetByID($ATTEMPT_ID);
// was: $r = CTestAttempt::GetList(Array(), Array("ID" => $ATTEMPT_ID, "MIN_PERMISSION" => "W"));
$r = CTestAttempt::GetList(Array(), Array("ID" => $ATTEMPT_ID, 'ACCESS_OPERATIONS' => CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_WRITE));


if(!$arAttempt = $r->GetNext())
	$bBadResult = true;

if (!$bBadResult)
{
	$r = CTestResult::GetByID($ID);
	if(!$r->ExtractFields("str_"))
		$bBadResult = true;
}


if($bBadResult)
{
	$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aContext = array(
		array(
			"ICON" => "btn_list",
			"TEXT"=>GetMessage("LEARNING_BACK_TO_ADMIN"),
			"LINK"=>"learn_unilesson_admin.php?lang=" . LANG,
			"TITLE"=>GetMessage("LEARNING_BACK_TO_ADMIN")
		),
	);
	$context = new CAdminContextMenu($aContext);
	$context->Show();

	CAdminMessage::ShowMessage(GetMessage("LEARNING_BAD_ATTEMPT_ID_EX"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("LEARNING_ADMIN_TAB1"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("LEARNING_ADMIN_TAB1_EX")),
);

$tabControl = new CAdminForm("testResultTabControl", $aTabs);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $Update <> '' && check_bitrix_sessid())
{
	if ($ANSWERED != "Y")
	{
		$ANSWERED = "N";
		$RESPONSE = "";
		$POINT = 0;
	}
	elseif ($CORRECT != "Y")
	{
		$CORRECT = "N";
		$POINT = 0;
	}

	$arFields = Array(
		"ANSWERED" => $ANSWERED,
		"CORRECT" => $CORRECT,
		"RESPONSE" => $RESPONSE,
		"POINT"=> $POINT,
	);

	$DB->StartTransaction();
	$tr = new CTestResult;
	$res = $tr->Update($ID, $arFields);

	if(!$res)
	{
		$DB->Rollback();
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("LEARNING_ERROR"), $e);
		$bVarsFromForm = true;

	}
	else
	{
		$tr->OnTestResultChange($ID);
		$DB->Commit();
		if($apply == '')
		{
			if($return_url <> '')
				LocalRedirect($return_url);
			else
				LocalRedirect("/bitrix/admin/learn_test_result_admin.php?lang=".LANG."&ATTEMPT_ID=".$ATTEMPT_ID.GetFilterParams("filter_", false));
		}
		LocalRedirect("/bitrix/admin/learn_test_result_edit.php?lang=".LANG."&ID=".$ID."&ATTEMPT_ID=".$ATTEMPT_ID.GetFilterParams("filter_", false));
	}
}

if($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_learn_test_result", "", "str_");
}

$adminChain->AddItem(array("TEXT"=>GetMessage("LEARNING_ADMIN_RESULTS"), "LINK"=>"learn_test_result_admin.php?lang=". LANG."&ATTEMPT_ID=".$ATTEMPT_ID.GetFilterParams("filter_", false)));

$APPLICATION->SetTitle($arAttempt["~TEST_NAME"].": ".GetMessage("LEARNING_ADMIN_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aContext = array();

$aContext = array(
	array(
		"ICON" => "btn_list",
		"TEXT"=> GetMessage("MAIN_ADMIN_MENU_LIST"),
		"LINK"=> "learn_test_result_admin.php?lang=". LANG."&ATTEMPT_ID=".$ATTEMPT_ID.GetFilterParams("filter_", false),
	),
);

$context = new CAdminContextMenu($aContext);
$context->Show();
?>

<?
if ($message)
	echo $message->Show();

?>

<?php $tabControl->BeginEpilogContent();?>
	<?=bitrix_sessid_post()?>
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="from" value="<?echo htmlspecialcharsbx($from)?>">
	<input type="hidden" name="return_url" value="<?echo htmlspecialcharsbx($return_url)?>">
	<input type="hidden" name="ID" value="<?echo $ID?>">
<?php $tabControl->EndEpilogContent();?>
<?$tabControl->Begin();?>
<?$tabControl->BeginNextFormTab();?>
<?php $tabControl->BeginCustomField("USER_NAME", GetMessage("LEARNING_ADMIN_STUDENT"), false);?>
<tr>
	<td width="40%"><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<?=$arAttempt["USER_NAME"]?>
	</td>
</tr>
<?php $tabControl->EndCustomField("USER_NAME");?>
<?php $tabControl->BeginCustomField("ANSWERED", GetMessage("LEARNING_ADMIN_ANSWERED"), false);?>
<tr>
	<td><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<input type="checkbox" name="ANSWERED" value="Y"<?if($str_ANSWERED=="Y")echo " checked"?> onclick="OnChangeAnswered(this.checked)">
	</td>
</tr>
<?php $tabControl->EndCustomField("ANSWERED");?>
<?php $tabControl->BeginCustomField("CORRECT", GetMessage("LEARNING_ADMIN_CORRECT"), false);?>
<tr>
	<td><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<input type="checkbox" name="CORRECT" value="Y"<?if($str_CORRECT=="Y")echo " checked"?> onclick="OnChangeAnswered(this.checked)">
	</td>
</tr>
<?php $tabControl->EndCustomField("CORRECT");?>
<?php $tabControl->BeginCustomField("POINT", GetMessage("LEARNING_ADMIN_POINT"), false);?>
<tr>
	<td><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<input type="text" name="POINT" size="4" maxlength="255" value="<?echo $str_POINT?>">
	</td>
</tr>
<?php $tabControl->EndCustomField("POINT");?>
<?php $tabControl->BeginCustomField("QUESTION", GetMessage("LEARNING_ADMIN_QUESTION"), false);?>
<tr>
	<td><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<?=$str_QUESTION_NAME?> [<a href="learn_question_edit.php?lang=<?=LANG?>&ID=<?=$str_QUESTION_ID?>" title="<?=GetMessage("LEARNING_ADMIN_EDIT_QUESTION")?>"><?=$str_QUESTION_ID?></a>]
	</td>
</tr>
<?php $tabControl->EndCustomField("QUESTION");?>
<?php $tabControl->BeginCustomField("ANSWER", GetMessage("LEARNING_ADMIN_ANSWER"), false);?>
<tr valign="top">
	<td><?php echo $tabControl->GetCustomLabelHTML()?>:</td>
	<td>
		<table>
		<?php if ($str_QUESTION_TYPE == "T"):?>
			<tr>
				<td><textarea rows="5" cols="50" name="RESPONSE"><?php echo $str_RESPONSE?></textarea></td>
			</tr>
		<?php
		else:
			$arR = explode(',', $str_RESPONSE);
			$r = CLAnswer::GetList(Array("ID" => "ASC"),Array("QUESTION_ID"=>$str_QUESTION_ID));
			while($arAnswers = $r->GetNext()):
			?>
				<tr>
					<td>
					<?if ($str_QUESTION_TYPE == "M"):?>
						<input type="checkbox" name="RESPONSE[]" value="<?=$arAnswers["ID"]?>" <?if(in_array($arAnswers["ID"],$arR)) echo "checked"?>>
					<?else:?>
						<input type="radio" name="RESPONSE[]" value="<?=$arAnswers["ID"]?>" <?if(in_array($arAnswers["ID"],$arR)) echo "checked"?>>
					<?endif?>
					</td>
					<td><?=$arAnswers["ANSWER"]?></td>
				</tr>
			<?php endwhile?>
		<?php endif?>
		</table>
	</td>
</tr>
<?php $tabControl->EndCustomField("ANSWER");?>

<?
$tabControl->Buttons(Array("back_url" =>"learn_test_result_admin.php?lang=". LANG."&ATTEMPT_ID=".$ATTEMPT_ID.GetFilterParams("filter_", false)));
$tabControl->arParams["FORM_ACTION"] = $APPLICATION->GetCurPage()."?lang=".LANG."&ATTEMPT_ID=".$ATTEMPT_ID.GetFilterParams("filter_");
$tabControl->Show();?>

<script type="text/javascript">
function OnChangeAnswered(val)
{
	document.forms["testResultTabControl_form"].elements['POINT'].disabled = !val;
	document.forms["testResultTabControl_form"].elements['CORRECT'].disabled = !val && !document.forms["testResultTabControl_form"].elements['ANSWERED'].checked;

	var r = document.forms["testResultTabControl_form"].elements['RESPONSE[]'];

	if (!r)
		return;

	for (i=0; i < r.length; i++)
		r[i].disabled = !val;

}
OnChangeAnswered(<?=($str_ANSWERED=="Y"?"true":"false")?>);
</script>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>