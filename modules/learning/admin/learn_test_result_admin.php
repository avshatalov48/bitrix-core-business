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
Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);
Bitrix\Main\Localization\Loc::loadLanguageFile(__DIR__."/learn_question_edit.php");

$ATTEMPT_ID = isset($_REQUEST['ATTEMPT_ID']) ? intval($_REQUEST['ATTEMPT_ID']) : 0;


//$r = CTestAttempt::GetByID($ATTEMPT_ID);
// was: $r = CTestAttempt::GetList(Array(), Array("ID" => $ATTEMPT_ID, "MIN_PERMISSION" => "W"));
$r = CTestAttempt::GetList(
	array(),
	array(
		'ID' => $ATTEMPT_ID,
		'ACCESS_OPERATIONS' =>
			CLearnAccess::OP_LESSON_READ
			| CLearnAccess::OP_LESSON_WRITE
		)
	);

$arAttempt = $r->GetNext();

$sTableID = "t_test_result_admin";
$oSort = new CAdminSorting($sTableID, "ID", "desc");// sort initializing
$lAdmin = new CAdminList($sTableID, $oSort);// list initializing

$arFilterFields = Array(
	"filter_question_name",
	"filter_id",
	"filter_answered",
	"filter_correct",
	//"filter_point",
);

$lAdmin->InitFilter($arFilterFields);// filter initializing

$arFilter = Array(
	"ID" => $filter_id,
	//"POINT" => $filter_point,
	"ANSWERED" => $filter_answered,
	"CORRECT" => $filter_correct,
	"?QUESTION_NAME" => $filter_question_name,
	//"ATTEMPT_ID" => $ATTEMPT_ID,
);

if ($ATTEMPT_ID > 0)
{
	$arFilter["ATTEMPT_ID"] = $ATTEMPT_ID;
}

if($lAdmin->EditAction()) // save from the list
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$ID = intval($ID);

		$ob = new CTestResult;
		if(!$ob->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
			{
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
			}
			$DB->Rollback();
		}
		else
		{
			$ob->OnTestResultChange($ID);
			$DB->Commit();
		}
	}
}

// group and single actions processing
if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CTestResult::GetList(Array($by => $order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;

		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CTestResult::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
			}
			else
			{
				CTestAttempt::RecountQuestions($ATTEMPT_ID);
				CTestAttempt::OnAttemptChange($ATTEMPT_ID);
				$DB->Commit();
			}
			break;
		}
	}
}

// fetch data
$rsData = CTestResult::GetList(Array($by=>$order),$arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_ADMIN_RESULTS")));


// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"QUESTION_NAME", "content"=>GetMessage('LEARNING_ADMIN_QUESTION_NAME'), "sort" =>"question_name", "default"=>true),
	//array("id"=>"ANSWER_NAME", "content"=>GetMessage('LEARNING_ADMIN_ANSWER_NAME'),"sort"=>"answer_name", "default"=>true),
	array("id"=>"ANSWERED", "content"=>GetMessage('LEARNING_ADMIN_ANSWERED'),"sort" => "answered", "default"=>true),
	array("id"=>"CORRECT", "content"=>GetMessage('LEARNING_ADMIN_CORRECT'),"sort" => "correct", "default"=>true),
	array("id"=>"POINT", "content"=>GetMessage('LEARNING_ADMIN_POINT'),"sort"=>"point", "default"=>true),
	array("id"=>"RESPONSE_TEXT", "content"=>GetMessage('LEARNING_ADMIN_USER_RESPONSE_TEXT'), "default"=>true),
	array("id"=>"CORRECT_REQUIRED", "content"=>GetMessage('LEARNING_CORRECT_REQUIRED'), "default"=> false),
));

// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$arRes['RESPONSE_TEXT'] = '';
	$result = CLQuestion::GetByID($arRes['QUESTION_ID']);
	$arData = $result->Fetch();
	if ($arData['QUESTION_TYPE'] === 'T')
		$arRes['RESPONSE_TEXT'] = htmlspecialcharsbx($arRes['RESPONSE']);
	elseif ( ! empty($arRes['RESPONSE']) )
	{
		$arResponseIDs = explode(',', $arRes['RESPONSE']);
		foreach ($arResponseIDs as $responseID)
		{
			$rsResponse = CLAnswer::GetByID((int) $responseID);
			$arResponseData = $rsResponse->GetNext();

			if ($arRes['RESPONSE_TEXT'] <> '')
				$arRes['RESPONSE_TEXT'] .=  '<hr>';

			$arRes['RESPONSE_TEXT'] .=  $arResponseData['ANSWER'];
		}
	}

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	/*
	if (!$row->bEditMode && $f_ANSWERED=="Y" && $f_POINT == 0  )
		$row->AddViewField("POINT","<div class=\"learning-wrong-answer\">".$f_POINT."</div>");
	else
		$row->AddInputField("POINT", Array("size"=>"3"));
		*/

	$row->AddInputField("POINT", Array("size"=>"3"));

	//$row->AddViewField("ANSWERED",$f_ANSWERED=="Y"?GetMessage("LEARNING_YES"):GetMessage("LEARNING_NO"));

	$row->AddCheckField("ANSWERED");

	//$row->AddViewField("CORRECT",$f_CORRECT=="Y"?GetMessage("LEARNING_YES"):GetMessage("LEARNING_NO"));
	$row->AddCheckField("CORRECT");

	$row->AddViewField('RESPONSE_TEXT', $arRes['RESPONSE_TEXT']);

	$row->AddViewField("CORRECT_REQUIRED", $arData["CORRECT_REQUIRED"] === "Y" ? GetMessage("LEARNING_YES") : GetMessage("LEARNING_NO"));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_test_result_edit.php?lang=".LANG."&ID=".$f_ID."&ATTEMPT_ID=".$ATTEMPT_ID.GetFilterParams("filter_", false))
	);


	$arActions[] = array("SEPARATOR"=>true);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION"=>"if(confirm('".GetMessageJS('LEARNING_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete","ATTEMPT_ID=".$ATTEMPT_ID));

	$row->AddActions($arActions);

}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
));

$adminChain->AddItem(array("TEXT"=>GetMessage("LEARNING_ADMIN_RESULTS"), "LINK"=>""));

$lAdmin->AddAdminContextMenu(Array());

$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE").($arAttempt ? ": ".$arAttempt["~TEST_NAME"].": ".$arAttempt["~USER_NAME"] : ""));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (defined("LEARNING_ADMIN_ACCESS_DENIED"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"), false);

$filter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		GetMessage("LEARNING_ADMIN_ANSWERED"),
		GetMessage("LEARNING_ADMIN_CORRECT"),
	)
);
?>

<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>

	<tr>
		<td><b><?echo GetMessage("LEARNING_ADMIN_QUESTION_NAME")?>:</b></td>
		<td align="left">
			<input type="text" name="filter_question_name" size="50" value="<?echo htmlspecialcharsex($filter_question_name)?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>

	<tr>
		<td>ID:</b></td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialcharsbx($filter_id)?>" size="47"></td>
	</tr>

	<tr>
		<td><?=GetMessage("LEARNING_ADMIN_ANSWERED")?>:</td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("LEARNING_YES"), GetMessage("LEARNING_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("filter_answered", $arr, htmlspecialcharsex($filter_answered), GetMessage('LEARNING_ALL'));
			?>
		</td>
	</tr>

	<tr>
		<td><?=GetMessage("LEARNING_ADMIN_CORRECT")?>:</td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("LEARNING_YES"), GetMessage("LEARNING_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("filter_correct", $arr, htmlspecialcharsex($filter_correct), GetMessage('LEARNING_ALL'));
			?>
		</td>
	</tr>

<?
$filter->Buttons(array(
	"table_id"=>$sTableID,
	"url"=>$APPLICATION->GetCurPage()."?ATTEMPT_ID=".$ATTEMPT_ID.GetFilterParams("filter_", false), "form"=>"form1")
);
$filter->End();?>
</form>


<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
