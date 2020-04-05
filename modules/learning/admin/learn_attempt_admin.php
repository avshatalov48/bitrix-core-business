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
IncludeModuleLangFile(__DIR__."/common.php");

ClearVars();

$sTableID = "t_attempt_admin";
$oSort = new CAdminSorting($sTableID, "ID", "desc");// sort initialization
$lAdmin = new CAdminList($sTableID, $oSort);// list initialization

$arStatus = Array(
	"B" => GetMessage('LEARNING_ATTEMPT_STATUS_B'),
	"D" => GetMessage('LEARNING_ATTEMPT_STATUS_D'),
	"F" => GetMessage('LEARNING_ATTEMPT_STATUS_F'),
	"N" => GetMessage('LEARNING_ATTEMPT_STATUS_N'),
);

$filter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		GetMessage("LEARNING_ADMIN_TEST"),
		GetMessage("LEARNING_ADMIN_DATE_START"),
		GetMessage("LEARNING_ADMIN_DATE_END"),
		GetMessage("LEARNING_ADMIN_COMPLETED"),
		GetMessage('LEARNING_ADMIN_STATUS'),
		GetMessage("LEARNING_ADMIN_SCORE"),
		GetMessage("LEARNING_ADMIN_ATTEMPT_SPEED"),
	)
);

$arFilterFields = Array(
	"filter_user",
	"filter_user_type",
	"filter_id",
	"filter_test_id",
	"filter_date_start_from",
	"filter_date_start_to",
	"filter_date_end_from",
	"filter_date_end_to",
	"filter_completed",
	"filter_status",
	"filter_score_from",
	"filter_score_to",
	"filter_speed_from",
	"filter_speed_to",
);

$lAdmin->InitFilter($arFilterFields);// filter initialization


$arFilter = Array(
	"ID" => $filter_id,
	"TEST_ID" => $filter_test_id,
	"STATUS" => $filter_status,
	"STUDENT_ID" => $filter_student_id,
	"SCORE" => $filter_score,
	"COMPLETED" => $filter_completed,
	'ACCESS_OPERATIONS' => CLearnAccess::OP_LESSON_READ | CLearnAccess::OP_LESSON_WRITE,

);

if(!empty($filter_date_start_from))
	$arFilter[">=DATE_START"] = $filter_date_start_from;
if(!empty($filter_date_start_to))
	$arFilter["<=DATE_START"] = $filter_date_start_to;

if(!empty($filter_date_end_from))
	$arFilter[">=DATE_END"] = $filter_date_end_from;
if(!empty($filter_date_end_to))
	$arFilter["<=DATE_END"] = $filter_date_end_to;

if(!empty($filter_score_from))
	$arFilter[">=SCORE"] = $filter_score_from;
if(!empty($filter_score_to))
	$arFilter["<=SCORE"] = $filter_score_to;

if(!empty($filter_speed_from))
	$arFilter[">=SPEED"] = $filter_speed_from;
if(!empty($filter_speed_to))
	$arFilter["<=SPEED"] = $filter_speed_to;

$filterTypeMap = array(
	"login" => "USER_LOGIN",
	"last_name" => "USER_LAST_NAME",
	"id" => "STUDENT_ID",
	"name" => "USER_NAME",
);

if(!empty($filter_user) && !empty($filter_user_type) && array_key_exists($filter_user_type, $filterTypeMap))
{
	$arFilter["=".$filterTypeMap[$filter_user_type]] = $filter_user;
}

if($lAdmin->EditAction()) //save from the list
{
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;

		$ID = IntVal($ID);

		$res = CTestAttempt::GetList(
			array(),
			array(
				'ID' => $ID,
				'ACCESS_OPERATIONS' =>
					CLearnAccess::OP_LESSON_READ
					| CLearnAccess::OP_LESSON_WRITE
				)
			);

		if(!$res->Fetch())
			continue;

		$DB->StartTransaction();
		$ob = new CTestAttempt;
		if(!$ob->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
			{
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$e->GetString(), $ID);
				$DB->Rollback();
			}
		}
		else
		{
			$bCOMPLETED = false;
			if (is_set($arFields, "COMPLETED") && $arFields["COMPLETED"] == "Y")
				$bCOMPLETED = true;

			$ob->OnAttemptChange($ID, $bCOMPLETED);
		}
		$DB->Commit();
	}
}

// group and single actions processing
if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CTestAttempt::GetList(Array($by => $order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;

		$ID = intval($ID);
		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$res = CTestAttempt::GetList(
				array(),
				array(
					'ID' => $ID,
					'ACCESS_OPERATIONS' =>
						CLearnAccess::OP_LESSON_READ
						| CLearnAccess::OP_LESSON_WRITE
					)
				);
			if ($ar = $res->Fetch())
			{
				$DB->StartTransaction();
				if(!CTestAttempt::Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("LEARNING_DELETE_ERROR"), $ID);
				}
				else
					CGradeBook::RecountAttempts($ar["STUDENT_ID"], $ar["TEST_ID"]);
				$DB->Commit();
			}
			break;
		}
	}
}

// fetch data
$rsData = CTestAttempt::GetList(
	array($by=>$order),
	$arFilter,
	array(),
	array('nPageSize' => CAdminResult::GetNavSize($sTableID))		// NAV_PARAMS
);
$rsData = new CAdminResult($rsData, $sTableID);

// navigation string setup
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LEARNING_ADMIN_RESULTS")));


// list header
$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"USER_NAME", "content"=>GetMessage('LEARNING_ADMIN_STUDENT'), "sort" =>"student_id", "default"=>true),
	array("id"=>"TEST_NAME", "content"=>GetMessage('LEARNING_ADMIN_TEST'), "sort"=>"test_name", "default"=>true),
	array("id"=>"DATE_START", "content"=>GetMessage('LEARNING_ADMIN_DATE_START'),"sort"=>"date_start", "default"=>true),
	array("id"=>"DATE_END", "content"=>GetMessage('LEARNING_ADMIN_DATE_END'), "sort"=>"date_end", "default"=>true),
	array("id"=>"STATUS", "content"=>GetMessage('LEARNING_ADMIN_STATUS'),"sort"=>"status", "default"=>true),
	array("id"=>"QUESTIONS", "content"=>Getmessage('LEARNING_ADMIN_QUESTIONS'),"sort"=>"questions", "default"=>true,"align"=>"center"),
	array("id"=>"COMPLETED", "content"=>Getmessage('LEARNING_ADMIN_COMPLETED'),"sort"=>"completed", "default"=>true),
	array("id"=>"SCORE", "content"=>GetMessage('LEARNING_ADMIN_SCORE'),"sort"=>"score", "default"=>true),
	array("id"=>"MAX_SCORE", "content"=>GetMessage('LEARNING_ADMIN_MAX_SCORE'),"sort"=>"max_score", "default"=>true),
	array("id"=>"SPEED", "content"=>GetMessage('LEARNING_ADMIN_ATTEMPT_SPEED'),"sort"=>"speed", "default"=>false),
));


// building list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddCalendarField("DATE_START");
	$row->AddCalendarField("DATE_END");
	$row->AddSelectField("STATUS",$arStatus);
	$row->AddCheckField("COMPLETED");
	$row->AddInputField("SCORE", Array("size"=>"3"));
	$row->AddInputField("MAX_SCORE", Array("size"=>"3"));

	$row->AddViewField("ID", '<a href="learn_test_result_admin.php?lang='.LANG.'&ATTEMPT_ID='.$f_ID.'">'.$f_ID.'</a>');

	$row->AddViewField("USER_NAME", "[<a href=\"user_edit.php?lang=".LANG."&ID=".$f_USER_ID."\" title=\"".GetMessage("LEARNING_CHANGE_USER_PROFILE")."\">".$f_USER_ID."</a>] ".$f_USER_NAME);

	$row->AddViewField("QUESTIONS", "<a href=\"learn_test_result_admin.php?lang=".LANG."&ATTEMPT_ID=".$f_ID."\">".$f_QUESTIONS."</a>");

	$row->AddViewField("TEST_NAME", "<a href=\"/bitrix/admin/learn_test_edit.php?lang=".LANGUAGE_ID."&COURSE_ID=".$f_COURSE_ID."&PARENT_LESSON_ID=".$f_LINKED_LESSON_ID."&LESSON_PATH=".$f_LINKED_LESSON_ID."&ID=".$f_TEST_ID."&filter=Y&set_filter=Y\">".$f_TEST_NAME."</a>");

	$row->AddViewField("SPEED", intval($f_SPEED));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"view",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("LEARNING_ADMIN_MENU_RESULTS"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_test_result_admin.php?lang=".LANG."&ATTEMPT_ID=".$f_ID)
	);

/*
	$arActions[] = array(
		"ICON"=>"edit",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_attempt_edit.php?lang=".LANG."&ID=".$f_ID.GetFilterParams("filter_"))
	);
*/

	$arActions[] = array("SEPARATOR"=>true);

	$arActions[] = array(
		"ICON"=>"edit",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("learn_attempt_edit.php?lang=".LANG."&ID=".$f_ID.GetFilterParams("filter_"))
	);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION"=>"if(confirm('".GetMessageJS('LEARNING_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));

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

$lAdmin->AddAdminContextMenu(Array());

$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("LEARNING_ADMIN_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

if (defined("LEARNING_ADMIN_ACCESS_DENIED"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"), false);
?>


<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>" onsubmit="return this.set_filter.onclick();">
<?$filter->Begin();?>


	<tr>
		<td><b><?=GetMessage("LEARNING_ADMIN_STUDENT")?>:</b></td>
		<td>
			<input type="text" name="filter_user" value="<?echo htmlspecialcharsbx($filter_user)?>" size="25">

			<?=SelectBoxFromArray(
				"filter_user_type",
				array(
					"reference" => array(
						GetMessage("LEARNING_FILTER_USER_LOGIN"),
						GetMessage("LEARNING_FILTER_USER_LAST_NAME"),
						"ID",
						GetMessage("LEARNING_FILTER_USER_NAME")
					),
					"reference_id" => array(
						"login",
						"last_name",
						"id",
						"name",
					)
				),
				$filter_user_type,
				"",
				""
			);?>

		</td>
	</tr>

	<tr>
		<td>ID:</td>
		<td><input type="text" name="filter_id" value="<?echo htmlspecialcharsbx($filter_id)?>" size="47"></td>
	</tr>

	<tr>
		<td><?=GetMessage("LEARNING_ADMIN_TEST")?>:</td>
		<td>
			<select name="filter_test_id">
				<option value=""><?echo GetMessage("LEARNING_ALL")?></option>
			<?
			$l = CTest::GetList(Array(), Array());
			while($l->ExtractFields("l_")):
				?><option value="<?echo $l_ID?>"<?if($filter_test_id==$l_ID)echo " selected"?>><?echo $l_NAME?></option><?
			endwhile;
			?>
			</select>
		</td>
	</tr>


	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_DATE_START").":"?></td>
		<td><?echo CalendarPeriod("filter_date_start_from", htmlspecialcharsex($filter_date_start_from), "filter_date_start_to", htmlspecialcharsex($filter_date_start_to), "form1")?></td>
	</tr>


	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_DATE_END").":"?></td>
		<td><?echo CalendarPeriod("filter_date_end_from", htmlspecialcharsex($filter_date_end_from), "filter_date_end_to", htmlspecialcharsex($filter_date_end_to), "form1")?></td>
	</tr>

	<tr>
		<td><?=GetMessage("LEARNING_ADMIN_COMPLETED")?>:</td>
		<td>
			<?
			$arr = array("reference"=>array(GetMessage("LEARNING_YES"), GetMessage("LEARNING_NO")), "reference_id"=>array("Y","N"));
			echo SelectBoxFromArray("filter_completed", $arr, htmlspecialcharsex($filter_completed), GetMessage('LEARNING_ALL'));
			?>
		</td>
	</tr>

<tr valign="top">
		<td><?=GetMessage('LEARNING_ADMIN_STATUS')?>:</b></td>
		<td>
<?
			$arr = Array(
				"reference" =>array_values($arStatus),
				"reference_id" => array_keys($arStatus),
			);
			echo SelectBoxMFromArray("filter_status[]", $arr, $filter_status, "", false, "3");
?>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_SCORE")?>:</td>
		<td nowrap>
			<input type="text" name="filter_score_from" size="10" value="<?echo htmlspecialcharsex($filter_score_from)?>">
			...
			<input type="text" name="filter_score_to" size="10" value="<?echo htmlspecialcharsex($filter_score_to)?>">
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("LEARNING_ADMIN_ATTEMPT_SPEED")?>:</td>
		<td nowrap>
			<input type="text" name="filter_speed_from" size="10" value="<?echo htmlspecialcharsex($filter_speed_from)?>">
			...
			<input type="text" name="filter_speed_to" size="10" value="<?echo htmlspecialcharsex($filter_speed_to)?>">
		</td>
	</tr>

<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"form1"));$filter->End();?>
</form>


<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>